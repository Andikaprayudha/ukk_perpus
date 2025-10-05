<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Koneksi ke database tanpa menggunakan config.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'perpustakaan_db';

// Koneksi ke MySQL
$conn = new mysqli($host, $user, $pass);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Cek apakah database sudah ada
$query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    // Database belum ada, redirect ke setup.php
    header('Location: setup.php');
    exit;
}

// Pilih database
$conn->select_db($dbname);

// Cek apakah tabel notifikasi sudah ada
$check_notifikasi = $conn->query("SHOW TABLES LIKE 'notifikasi'");
if ($check_notifikasi->num_rows == 0) {
    // Tabel notifikasi belum ada, buat tabel
    $create_notifikasi = "
    CREATE TABLE IF NOT EXISTS notifikasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        peminjaman_id INT DEFAULT NULL,
        type ENUM('reminder', 'overdue', 'info') NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE SET NULL
    )";
    
    if (!$conn->query($create_notifikasi)) {
        die("Error membuat tabel notifikasi: " . $conn->error);
    }
    
    echo "Tabel notifikasi berhasil dibuat. <a href='index.php'>Klik di sini untuk melanjutkan</a>";
    exit;
}

// Tutup koneksi
$conn->close();

// Semua tabel sudah ada, redirect ke index.php
header('Location: index.php');
exit;
?>