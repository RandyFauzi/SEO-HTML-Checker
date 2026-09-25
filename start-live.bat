@echo off
echo Starting Laravel Server...
start cmd /k "php artisan serve --port=8000"

echo Starting Ngrok...
start cmd /k "C:\laragon\bin\ngrok\ngrok.exe http 8000"

echo.
echo Both servers have been started in new windows!
echo Please check the ngrok window for your public URL.
pause
