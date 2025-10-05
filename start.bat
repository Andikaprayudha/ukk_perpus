@echo off
setlocal

REM Konfigurasi
set XAMPP_PATH=C:\xampp
set PORT=8000

REM Tampilkan banner
echo.
echo Menjalankan server PHP untuk aplikasi Perpustakaan Digital...
echo.
echo Konfigurasi:
echo - XAMPP Path: %XAMPP_PATH%
echo - Port: %PORT%
echo.

REM Periksa apakah XAMPP terinstal
if not exist "%XAMPP_PATH%\php\php.exe" (
    echo ERROR: XAMPP tidak ditemukan di %XAMPP_PATH%
    echo Silakan install XAMPP terlebih dahulu.
    pause
    exit /b 1
)

REM Periksa apakah MySQL sudah berjalan
echo Memeriksa status MySQL...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo WARNING: MySQL belum berjalan
    echo Silakan start MySQL di XAMPP Control Panel
    echo.
    pause
)

REM Buka browser ke halaman check_database
echo Membuka aplikasi di browser...
start http://localhost:%PORT%/check_database.php

REM Jalankan server PHP dari XAMPP
echo Server berjalan di http://localhost:%PORT%
echo Tekan Ctrl+C untuk menghentikan server
echo.
"%XAMPP_PATH%\php\php.exe" -S localhost:%PORT%

endlocal