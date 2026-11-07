# Configuracion inicial de SystemPe (ejecutar como Administrador)
$log = 'C:\Users\User\Downloads\systempe\configurar-systempe.log'
function Log($msg) {
    $line = ('[{0}] {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $msg)
    Add-Content -LiteralPath $log -Value $line
    Write-Host $line
}

$isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) { Log 'ERROR: se requieren permisos de administrador'; exit 1 }
Log 'Inicio de configuracion'

try {
    $hostsPath = 'C:\Windows\System32\drivers\etc\hosts'
    $entries = @(
        '127.0.0.1 systempe.test',
        '127.0.0.1 sistemaventa.systempe.test',
        '127.0.0.1 hotelprueba.systempe.test',
        '127.0.0.1 econocomercio.systempe.test'
    )
    foreach ($entry in $entries) {
        try {
            $hostName = ($entry -split '\s+')[1]
            $current = Get-Content $hostsPath -Raw
            if ($current -match ('(?m)^\s*[0-9.:]+\s+' + [regex]::Escape($hostName) + '\s*$')) {
                Log "hosts ya existe: $hostName"
            } else {
                Add-Content -LiteralPath $hostsPath -Value $entry
                Log "hosts agregado: $entry"
            }
        } catch {
            Log ("hosts ERROR (" + $entry + "): " + $_.Exception.Message)
        }
    }
} catch { Log ('hosts bloque ERROR: ' + $_.Exception.Message) }

try {
    $mysqld = 'C:\xampp\Resources_xampp\mysql\bin\mysqld.exe'
    $ini = 'C:\xampp\Resources_xampp\mysql\bin\my.ini'
    $serviceName = 'MariaDB-SystemPe'
    $service = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
    if (-not $service) {
        $out = & $mysqld --install $serviceName "--defaults-file=$ini" 2>&1
        Log ('mysqld --install: ' + ($out -join ' '))
        Start-Sleep -Seconds 2
        $service = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
    }
    if ($service) {
        Log ('servicio encontrado: ' + $service.Name + ' estado=' + $service.Status)
        $portBusy = $false
        try { $portBusy = [bool](Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue) } catch { $portBusy = $true }
        if ($portBusy -and $service.Status -ne 'Running') {
            Log 'puerto 3306 ocupado por instancia manual; el servicio arrancara al reiniciar'
        } elseif ($service.Status -ne 'Running') {
            Start-Service -Name $serviceName
            Log 'servicio iniciado'
        } else {
            Log 'servicio ya en ejecucion'
        }
    } else {
        Log 'ERROR: no se pudo registrar el servicio'
    }
} catch { Log ('servicio ERROR: ' + $_.Exception.Message) }

Log 'Fin de configuracion'
