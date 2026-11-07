# Genera los dumps SQL locales de todas las bases (NO se publican en Git).
#
# Uso:
#   .\scripts\generar-dumps.ps1
#
# Requisitos:
#   - mysqldump en el PATH o en una ruta habitual de MariaDB/XAMPP.
#   - .env con DB_HOST, DB_PORT, DB_USERNAME y DB_PASSWORD válidos.
#
# Los archivos se escriben en docs\sql\ y están ignorados por .gitignore.

$ErrorActionPreference = 'Stop'

$raiz = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $raiz '.env'
$salida = Join-Path $raiz 'docs\sql'

if (-not (Test-Path $envFile)) {
    throw "No existe $envFile"
}
if (-not (Test-Path $salida)) {
    New-Item -ItemType Directory -Path $salida -Force | Out-Null
}

# --- Leer .env (solo las claves necesarias) ---
function Leer-Env([string]$clave) {
    $linea = Get-Content $envFile | Where-Object { $_ -match "^$clave=" } | Select-Object -First 1
    if (-not $linea) { return $null }
    return (($linea -split '=', 2)[1]).Trim().Trim('"').Trim("'")
}

$dbHost = Leer-Env 'DB_HOST'
$dbPort = Leer-Env 'DB_PORT'
$dbUser = Leer-Env 'DB_USERNAME'
$dbPass = Leer-Env 'DB_PASSWORD'
if (-not $dbHost) { $dbHost = '127.0.0.1' }
if (-not $dbPort) { $dbPort = '3306' }

# --- Localizar mysqldump ---
$mysqldump = (Get-Command mysqldump -ErrorAction SilentlyContinue).Source
if (-not $mysqldump) {
    $candidatos = @(
        'C:\xampp\Resources_xampp\mysql\bin\mysqldump.exe',
        'C:\xampp\mysql\bin\mysqldump.exe',
        'C:\Program Files\MariaDB 10.11\bin\mysqldump.exe',
        'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe'
    )
    $mysqldump = $candidatos | Where-Object { Test-Path $_ } | Select-Object -First 1
}
if (-not $mysqldump) {
    throw 'No se encontró mysqldump. Agrégalo al PATH o ajusta la ruta en este script.'
}

# --- Archivo temporal de credenciales (se borra al final) ---
$credenciales = Join-Path $env:TEMP ("systempe-dump-{0}.cnf" -f ([guid]::NewGuid()))
@"
[client]
user=$dbUser
password=$dbPass
host=$dbHost
port=$dbPort
"@ | Set-Content -LiteralPath $credenciales -Encoding ASCII

function Dump-Base([string]$base, [string]$archivo) {
    Write-Host "Exportando $base -> $archivo" -ForegroundColor Cyan
    & $mysqldump --defaults-extra-file="$credenciales" --routines --triggers --single-transaction `
        --default-character-set=utf8mb4 --databases $base 2>$null |
        Set-Content -LiteralPath (Join-Path $salida $archivo) -Encoding UTF8
    $kb = [Math]::Round((Get-Item (Join-Path $salida $archivo)).Length / 1KB, 1)
    Write-Host "  OK ($kb KB)" -ForegroundColor Green
}

try {
    Dump-Base 'tenancy' '01-sistema-tenancy.sql'

    $bases = & $mysqldump --defaults-extra-file="$credenciales" --skip-column-names --execute "SHOW DATABASES LIKE 'tenancy\_%'" 2>$null
    $i = 2
    foreach ($base in $bases) {
        $base = $base.Trim()
        if ([string]::IsNullOrWhiteSpace($base)) { continue }
        $nombre = '{0:D2}-tenant-{1}.sql' -f $i, ($base -replace '^tenancy_', '')
        Dump-Base $base $nombre
        $i++
    }

    Write-Host ''
    Write-Host 'Dumps generados en docs\sql (ignorados por Git).' -ForegroundColor Yellow
}
finally {
    Remove-Item -LiteralPath $credenciales -Force -ErrorAction SilentlyContinue
}
