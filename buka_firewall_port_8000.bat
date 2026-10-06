@echo off
title Buka Port 8000 untuk Ujian SEB iOS

net session >nul 2>&1
if %errorlevel%==0 goto :ADMIN

echo Meminta izin Administrator...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process '%~f0' -Verb RunAs"
exit /b

:ADMIN
cls
echo ========================================================
echo  Membuka Port 8000 di Windows Firewall untuk SEB iOS
echo ========================================================
echo.

echo 1. Mengizinkan Port 8000 (TCP Inbound)...
netsh advfirewall firewall delete rule name="Laravel Exam Port 8000" >nul 2>&1
netsh advfirewall firewall add rule name="Laravel Exam Port 8000" dir=in action=allow protocol=TCP localport=8000 profile=any

echo 2. Mengizinkan Eksekusi PHP di Firewall...
netsh advfirewall firewall delete rule name="Laragon PHP Server" >nul 2>&1
netsh advfirewall firewall add rule name="Laragon PHP Server" dir=in action=allow program="C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe" enable=yes profile=any

echo.
echo ========================================================
echo [SUKSES] Port 8000 dan PHP berhasil diizinkan di Firewall!
echo iPhone sekarang sudah bisa mengakses server ujian via Wi-Fi.
echo ========================================================
echo.
pause
