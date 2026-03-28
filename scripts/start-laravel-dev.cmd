@echo off
cd /d C:\Users\sampo\learning-system
".\tools\php83\php.exe" artisan serve --host=127.0.0.1 --port=8080 >> storage\logs\laravel-serve.console.log 2>&1
