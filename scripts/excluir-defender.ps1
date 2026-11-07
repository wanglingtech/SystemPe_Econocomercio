#Requires -RunAsAdministrator

<#
    Agrega exclusiones de Windows Defender para acelerar el desarrollo.

    Uso (PowerShell como Administrador):
        .\scripts\excluir-defender.ps1

    Por qué: Defender escanea en tiempo real los ~47.000 archivos de
    node_modules y los ~11.000 de vendor en cada lectura. Excluir la carpeta
    del proyecto, PHP y MariaDB reduce mucho el tiempo de carga.
#>

$ErrorActionPreference = 'Stop'

$project = Split-Path -Parent $PSScriptRoot
$candidatos = @(
    $project,
    (Join-Path $env:LOCALAPPDATA 'Composer'),
    (Join-Path $env:LOCALAPPDATA 'npm-cache'),
    (Join-Path $env:APPDATA 'npm-cache')
)

# PHP: carpeta del binario en el PATH
$php = Get-Command php -ErrorAction SilentlyContinue
if ($php) {
    $candidatos += Split-Path -Parent $php.Source
}

# MariaDB: servicio registrado y rutas habituales
$servicio = Get-Service -Name 'MariaDB*' -ErrorAction SilentlyContinue | Select-Object -First 1
if ($servicio) {
    $candidatos += 'C:\ProgramData\MariaDB'
    $candidatos += 'C:\Program Files\MariaDB 10.11\data'
}
$candidatos += 'C:\xampp\Resources_xampp\mysql'
$candidatos += 'C:\xampp\mysql'

$agregados = 0
foreach ($ruta in ($candidatos | Select-Object -Unique)) {
    if ([string]::IsNullOrWhiteSpace($ruta)) { continue }
    if (-not (Test-Path -LiteralPath $ruta)) { continue }

    $existente = (Get-MpPreference).ExclusionPath
    if ($existente -contains $ruta) {
        Write-Host "Ya excluida: $ruta" -ForegroundColor DarkGray
        continue
    }

    try {
        Add-MpPreference -ExclusionPath $ruta -ErrorAction Stop
        Write-Host "Excluida:   $ruta" -ForegroundColor Green
        $agregados++
    }
    catch {
        Write-Warning "No se pudo excluir $ruta : $($_.Exception.Message)"
    }
}

Write-Host ""
Write-Host "Exclusiones agregadas: $agregados" -ForegroundColor Cyan
Write-Host "Exclusiones actuales:" -ForegroundColor Cyan
(Get-MpPreference).ExclusionPath | ForEach-Object { Write-Host "  $_" }

Write-Host ""
Write-Host "Reinicia el servidor PHP para notar la mejora." -ForegroundColor Yellow
