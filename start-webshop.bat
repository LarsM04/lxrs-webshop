@echo off
rem Start de webshop met een dubbelklik: Docker Desktop, de containers en Brave.
cd /d "%~dp0"

set "DOCKER_DESKTOP=%LOCALAPPDATA%\Programs\DockerDesktop\Docker Desktop.exe"
set "BRAVE=%LOCALAPPDATA%\BraveSoftware\Brave-Browser\Application\brave.exe"
set "URL=http://localhost:8000"

docker info >nul 2>&1
if not errorlevel 1 goto docker_draait
echo Docker Desktop opstarten...
start "" "%DOCKER_DESKTOP%"
:wacht_op_docker
ping -n 4 127.0.0.1 >nul
docker info >nul 2>&1
if errorlevel 1 goto wacht_op_docker
:docker_draait

echo Containers starten...
docker compose up -d
if errorlevel 1 (
    echo.
    echo Er ging iets mis bij het starten van de containers. Zie de melding hierboven.
    pause
    exit /b 1
)

echo Wachten tot de website reageert...
:wacht_op_site
ping -n 3 127.0.0.1 >nul
curl -s -o nul %URL%
if errorlevel 1 goto wacht_op_site

if exist "%BRAVE%" (
    start "" "%BRAVE%" %URL%
) else (
    start "" %URL%
)

echo.
echo Webshop:   %URL%
echo phpMyAdmin: http://localhost:8080
echo Stoppen:   docker compose down
ping -n 6 127.0.0.1 >nul
