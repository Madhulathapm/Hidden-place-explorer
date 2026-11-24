@echo off
title Hidden Places Explorer - Launcher
echo ========================================================
echo        STARTING HIDDEN PLACES EXPLORER
echo ========================================================
echo.

cd /d C:\xampp

:: 1. Ensure MariaDB / MySQL is running
netstat -ano | findstr "LISTENING" | findstr ":3307" >nul
if errorlevel 1 (
    echo [*] Starting database server on port 3307...
    start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file=C:\xampp\mysql\bin\my.ini --port=3307 --standalone
    timeout /t 2 >nul
) else (
    echo [OK] Database server already running on port 3307.
)

:: 2. Ensure Apache is running
netstat -ano | findstr "LISTENING" | findstr ":80" >nul
if errorlevel 1 (
    echo [*] Starting Apache Web Server...
    start "" /B "C:\xampp\apache\bin\httpd.exe" -d C:/xampp/apache
    timeout /t 2 >nul
) else (
    echo [OK] Apache Web Server already running on port 80.
)

echo.
echo ========================================================
echo   Application is LIVE at:
echo   http://localhost/hidden_places_explorer/
echo
echo   Admin Panel:
echo   http://localhost/hidden_places_explorer/admin/
echo ========================================================
echo.

start "" "http://localhost/hidden_places_explorer/"
exit
