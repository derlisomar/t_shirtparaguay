@echo off
chcp 65001 >nul
title t_shirtparaguay - actualizar
cd /d "%~dp0"
set PHP=php
if exist "C:\xampp\php\php.exe" set PHP=C:\xampp\php\php.exe
echo.
echo   Esto actualiza la base "tshirtparaguay" a la nueva version.
echo   Tus productos, clientes y pedidos NO se borran.
echo.
pause
"%PHP%" artisan migrate --force
if errorlevel 1 goto error
"%PHP%" artisan optimize:clear
echo.
echo   Listo. La tienda ya tiene la actualizacion.
echo.
pause
exit /b
:error
echo.
echo   Hubo un error. Revisa la contrasena en el archivo .env.
echo   Si no se resuelve, sacale una captura a esta ventana y mandala.
echo.
pause
