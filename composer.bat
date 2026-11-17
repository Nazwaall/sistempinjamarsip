@echo off
set PATH=%~dp0;C:\xampp\php;%PATH%
"C:\xampp\php\php.exe" -c "%~dp0php-custom.ini" "C:\composer\composer.phar" %*
