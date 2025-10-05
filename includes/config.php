<?php
// Aktifkan tampilan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perpustakaan_db');

// Cek apakah ekstensi mysqli tersedia
if (!extension_loaded('mysqli')) {
    die("Ekstensi mysqli tidak tersedia. Pastikan XAMPP sudah berjalan dan MySQL service sudah diaktifkan.");
}

// Koneksi ke database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error() . 
        "<br>Pastikan:<br>" .
        "1. MySQL service di XAMPP sudah berjalan (tombol 'Start' di XAMPP Control Panel)<br>" .
        "2. Database '" . DB_NAME . "' sudah dibuat di phpMyAdmin (http://localhost/phpmyadmin)<br>" .
        "3. Username dan password database sudah benar");
}

// Fungsi untuk membersihkan input
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk mengalihkan halaman
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan pesan
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Fungsi untuk mendapatkan pesan
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>