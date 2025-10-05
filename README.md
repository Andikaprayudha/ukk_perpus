# Aplikasi Perpustakaan Digital

## Cara Menjalankan Aplikasi Secara Otomatis

Aplikasi ini dilengkapi dengan fitur autorun yang memudahkan pengguna untuk menjalankan aplikasi dengan cepat.

### Menggunakan File Batch (Windows)

1. Klik dua kali pada file `start.bat` di folder utama aplikasi
2. File batch akan secara otomatis:
   - Menggunakan PHP dari lokasi yang dikonfigurasi (default: C:\php8.4\php.exe)
   - Menjalankan server PHP di port yang ditentukan (default: 8000)
   - Membuka browser dengan halaman yang ditentukan (default: index.php)

### Konfigurasi File Batch

Anda dapat mengubah konfigurasi di file `start.bat` dengan mengedit variabel berikut:
- `PHP_PATH`: Lokasi file PHP.exe di komputer Anda
- `PORT`: Port yang digunakan untuk server PHP
- `START_PAGE`: Halaman awal yang akan dibuka di browser

### Menggunakan XAMPP/WAMP/MAMP

Jika Anda menggunakan XAMPP, WAMP, atau MAMP:

1. Letakkan folder aplikasi di direktori htdocs/www
2. Jalankan Apache dan MySQL
3. Buka browser dan akses http://localhost/ukk_perpus/autorun.php

## Konfigurasi Database

Sebelum menggunakan aplikasi, pastikan untuk:

1. Import file `database.sql` untuk membuat tabel-tabel utama
2. Import file `db/notifikasi.sql` untuk membuat tabel notifikasi

## Konfigurasi Sistem Notifikasi

Untuk mengaktifkan sistem notifikasi otomatis:

1. Atur penjadwal tugas (cron job) untuk menjalankan script `cron/check_overdue.php` setiap hari
2. Di Windows, gunakan Task Scheduler untuk menjalankan script PHP tersebut

## Mengakses Sistem

- Buat akun pengguna melalui halaman registrasi
- Untuk mengakses panel admin, Anda memerlukan akun dengan level 'admin' atau 'petugas'