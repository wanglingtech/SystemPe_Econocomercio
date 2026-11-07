@echo off
setlocal
title SystemPe - Servidor local

rem Carpeta del proyecto (donde esta este .bat)
set "APP_DIR=%~dp0"
if "%APP_DIR:~-1%"=="\" set "APP_DIR=%APP_DIR:~0,-1%"

rem PHP: usa el del PATH; si no existe, usa la ruta conocida de esta PC
set "PHP_BIN=php"
where php >nul 2>nul || set "PHP_BIN=C:\Users\User\Documents\php 7.4.33 x64\php-7.4.33-Win32-vc15-x64\php.exe"

rem MariaDB (XAMPP). Puedes sobreescribir con SYSTEMPE_MYSQL_BIN
set "MYSQL_BIN=C:\xampp\Resources_xampp\mysql\bin"
if defined SYSTEMPE_MYSQL_BIN set "MYSQL_BIN=%SYSTEMPE_MYSQL_BIN%"

rem Puerto del servidor. Puedes sobreescribir con SYSTEMPE_PORT
if not defined SYSTEMPE_PORT set "SYSTEMPE_PORT=8080"

rem Verifica si MariaDB ya escucha en 3306
powershell -NoProfile -Command "if (Test-NetConnection 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue) { exit 0 } else { exit 1 }" >nul 2>&1
if errorlevel 1 (
    if exist "%MYSQL_BIN%\mysqld.exe" (
        echo Iniciando MariaDB...
        start "MariaDB SystemPe" /min "%MYSQL_BIN%\mysqld.exe" --defaults-file="%MYSQL_BIN%\my.ini" --console
        timeout /t 6 /nobreak >nul
    ) else (
        echo AVISO: no se encontro MariaDB en "%MYSQL_BIN%".
        echo Define SYSTEMPE_MYSQL_BIN con la ruta correcta.
    )
) else (
    echo MariaDB ya esta en ejecucion.
)

cd /d "%APP_DIR%"
echo.
echo SystemPe:
echo   Sistema:        http://systempe.test:%SYSTEMPE_PORT%/login
echo   Tenant ejemplo: http://sistemaventa.systempe.test:%SYSTEMPE_PORT%/login
echo.
echo Presiona Ctrl+C para detener el servidor.
echo.
"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=%SYSTEMPE_PORT%

endlocal
