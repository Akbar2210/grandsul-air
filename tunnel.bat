@echo off
title Cloudflare Tunnel - Grand Sulawesi Air
cd /d "%~dp0"

echo ========================================================
echo   CLOUDFLARE TUNNEL - GRAND SULAWESI AIR
echo   Mengarahkan ke: http://127.0.0.1:8000
echo ========================================================
echo.

if not exist "%~dp0cloudflared.exe" (
    echo [ERROR] cloudflared.exe tidak ditemukan di folder ini!
    echo Pastikan file cloudflared.exe ada di %~dp0
    pause
    exit /b
)

echo Menghubungkan ke Cloudflare Tunnel...
echo Tunggu beberapa detik hingga URL *.trycloudflare.com muncul di bawah ini:
echo.
"%~dp0cloudflared.exe" tunnel --url http://127.0.0.1:8000 --logfile "%~dp0tunnel.log"

pause
