<?php
// Aktifkan error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Setup Database Perpustakaan Digital</h1>";

// Konfigurasi database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'perpustakaan_db';

try {
    // Koneksi ke MySQL tanpa database
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        throw new Exception("Koneksi ke MySQL gagal: " . $conn->connect_error);
    }
    
    echo "<p>✅ Berhasil terhubung ke MySQL</p>";
    
    // Cek apakah database sudah ada
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    
    if ($result->num_rows > 0) {
        echo "<p>ℹ️ Database '$dbname' sudah ada</p>";
    } else {
        // Buat database baru
        if ($conn->query("CREATE DATABASE $dbname")) {
            echo "<p>✅ Database '$dbname' berhasil dibuat</p>";
        } else {
            throw new Exception("Gagal membuat database: " . $conn->error);
        }
    }
    
    // Pilih database
    if (!$conn->select_db($dbname)) {
        throw new Exception("Gagal memilih database: " . $conn->error);
    }
    
    echo "<p>✅ Database '$dbname' dipilih</p>";
    
    // Baca file SQL
    $sql_file = file_get_contents('database.sql');
    if ($sql_file === false) {
        throw new Exception("Gagal membaca file database.sql");
    }
    
    echo "<p>✅ File database.sql berhasil dibaca</p>";
    
    // Pisahkan perintah SQL dan hapus perintah USE dan CREATE DATABASE
    $sql_file = preg_replace('/CREATE DATABASE.*?;/i', '', $sql_file);
    $sql_file = preg_replace('/USE.*?;/i', '', $sql_file);
    
    // Pisahkan perintah SQL
    $queries = array_filter(
        array_map(
            'trim',
            explode(';', $sql_file)
        ),
        'strlen'
    );
    
    // Jalankan setiap perintah SQL
    foreach ($queries as $query) {
        if (strlen(trim($query)) > 0) {
            if (!$conn->query($query)) {
                throw new Exception("Gagal menjalankan query: " . $conn->error . "<br>Query: " . htmlspecialchars($query));
            }
        }
    }
    
    // Pastikan tabel notifikasi dibuat
    $check_notifikasi = $conn->query("SHOW TABLES LIKE 'notifikasi'");
    if ($check_notifikasi->num_rows == 0) {
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
            throw new Exception("Gagal membuat tabel notifikasi: " . $conn->error);
        }
        echo "<p>✅ Tabel notifikasi berhasil dibuat</p>";
    }
    
    echo "<p>✅ Struktur database berhasil dibuat</p>";
    echo "<p>✅ Setup database selesai!</p>";
    echo "<p>Silakan jalankan aplikasi dengan mengklik <a href='index.php'>di sini</a></p>";
    
    // Tutup koneksi
    $conn->close();
    
} catch (Exception $e) {
    die("<p>❌ Error: " . $e->getMessage() . "</p>");
}
?>