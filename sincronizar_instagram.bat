@echo off
REM ============================================================
REM  Sincronizacion Instagram -> Novedades
REM  Colegio Parroquial Juan XXIII
REM  Lo llama el Programador de tareas de Windows a las 19:00.
REM ============================================================

set PHP=C:\xampp\php\php.exe
set SCRIPT=%~dp0sync_instagram.php
set LOG=%~dp0ig_sync.log

if not exist "%PHP%" (
  echo No se encontro PHP en %PHP% >> "%LOG%"
  exit /b 1
)

echo. >> "%LOG%"
echo ===== %date% %time% ===== >> "%LOG%"
"%PHP%" -f "%SCRIPT%" >> "%LOG%" 2>&1

exit /b %errorlevel%
