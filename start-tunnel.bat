@echo off
title Cloudflare Tunnel - Exambro Server
echo ==========================================================
echo   EXAMBRO - CLOUDFLARE TUNNEL RUNNER
echo   Pastikan Laravel Backend sudah jalan di port 8000!
echo ==========================================================
echo.
echo Menghubungkan port 8000 ke internet...
echo Silakan copy URL yang berakhiran .trycloudflare.com di bawah ini:
echo.
cloudflared.exe tunnel --url http://127.0.0.1:8000
pause
