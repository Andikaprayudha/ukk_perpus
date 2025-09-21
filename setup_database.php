<?php
// File untuk membuat database dan tabel secara otomatis jika belum ada

// Koneksi ke MySQL tanpa memilih database
$conn = mysqli_connect('localhost', 'root', '');

// Periksa koneksi
if (!$conn) {
    die("<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>
            <h3>Error!</h3>
            <p>Koneksi ke MySQL gagal: " . mysqli_connect_error() . "</p>
         </div>");
}

// Periksa apakah database ukk_perpus sudah ada
$check_db = mysqli_query($conn, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'ukk_perpus'");

if (mysqli_num_rows($check_db) == 0) {
    // Database belum ada, buat database baru
    $create_db = mysqli_query($conn, "CREATE DATABASE ukk_perpus");
    
    if (!$create_db) {
        die("<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>
                <h3>Error!</h3>
                <p>Gagal membuat database: " . mysqli_error($conn) . "</p>
             </div>");
    }
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;'>
            <h3>Berhasil!</h3>
            <p>Database ukk_perpus berhasil dibuat.</p>
          </div>";
} else {
    echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; margin: 20px; border-radius: 5px;'>
            <h3>Informasi</h3>
            <p>Database ukk_perpus sudah ada.</p>
          </div>";
}

// Pilih database ukk_perpus
mysqli_select_db($conn, 'ukk_perpus');

// Buat tabel-tabel jika belum ada
$tables = [
    "buku" => "CREATE TABLE IF NOT EXISTS `buku` (
        `id_buku` int(11) NOT NULL AUTO_INCREMENT,
        `id_kategori` int(11) DEFAULT NULL,
        `judul` varchar(255) DEFAULT NULL,
        `penulis` varchar(255) DEFAULT NULL,
        `penerbit` varchar(255) DEFAULT NULL,
        `tahun_terbit` varchar(255) DEFAULT NULL,
        `deskripsi` text DEFAULT NULL,
        `stok` int(11) NOT NULL DEFAULT 1,
        `file_buku` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id_buku`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    "kategori" => "CREATE TABLE IF NOT EXISTS `kategori` (
        `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
        `kategori` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id_kategori`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    "peminjaman" => "CREATE TABLE IF NOT EXISTS `peminjaman` (
        `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT,
        `id_user` int(11) DEFAULT NULL,
        `id_buku` int(11) DEFAULT NULL,
        `tanggal_peminjaman` varchar(255) DEFAULT NULL,
        `tanggal_pengembalian` varchar(255) DEFAULT NULL,
        `status_peminjaman` enum('dipinjam','dikembalikan') DEFAULT NULL,
        PRIMARY KEY (`id_peminjaman`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    "ulasan" => "CREATE TABLE IF NOT EXISTS `ulasan` (
        `id_ulasan` int(11) NOT NULL AUTO_INCREMENT,
        `id_user` int(11) DEFAULT NULL,
        `id_buku` int(11) DEFAULT NULL,
        `ulasan` text DEFAULT NULL,
        `rating` int(11) DEFAULT NULL,
        PRIMARY KEY (`id_ulasan`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    "user" => "CREATE TABLE IF NOT EXISTS `user` (
        `id_user` int(11) NOT NULL AUTO_INCREMENT,
        `nama` varchar(255) DEFAULT NULL,
        `username` varchar(255) DEFAULT NULL,
        `password` varchar(255) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `alamat` text DEFAULT NULL,
        `no_telepon` varchar(255) DEFAULT NULL,
        `level` enum('admin','petugas','peminjam') DEFAULT NULL,
        PRIMARY KEY (`id_user`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
];

$tables_created = 0;
$tables_existed = 0;

foreach ($tables as $table_name => $query) {
    // Periksa apakah tabel sudah ada
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE '$table_name'");
    
    if (mysqli_num_rows($check_table) == 0) {
        // Tabel belum ada, buat tabel baru
        $create_table = mysqli_query($conn, $query);
        
        if ($create_table) {
            $tables_created++;
        } else {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>
                    <h3>Error!</h3>
                    <p>Gagal membuat tabel $table_name: " . mysqli_error($conn) . "</p>
                  </div>";
        }
    } else {
        $tables_existed++;
        
        // Jika tabel buku sudah ada, periksa apakah kolom file_buku sudah ada
        if ($table_name == 'buku') {
            $check_column = mysqli_query($conn, "SHOW COLUMNS FROM buku LIKE 'file_buku'");
            
            if (mysqli_num_rows($check_column) == 0) {
                // Kolom file_buku belum ada, tambahkan kolom
                $add_column = mysqli_query($conn, "ALTER TABLE buku ADD COLUMN file_buku VARCHAR(255) NULL");
                
                if ($add_column) {
                    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;'>
                            <h3>Berhasil!</h3>
                            <p>Kolom file_buku berhasil ditambahkan ke tabel buku.</p>
                          </div>";
                } else {
                    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>
                            <h3>Error!</h3>
                            <p>Gagal menambahkan kolom file_buku: " . mysqli_error($conn) . "</p>
                          </div>";
                }
            }
        }
    }
}

if ($tables_created > 0) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;'>
            <h3>Berhasil!</h3>
            <p>$tables_created tabel berhasil dibuat.</p>
          </div>";
}

if ($tables_existed > 0) {
    echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; margin: 20px; border-radius: 5px;'>
            <h3>Informasi</h3>
            <p>$tables_existed tabel sudah ada sebelumnya.</p>
          </div>";
}

// Buat admin default jika belum ada
$check_admin = mysqli_query($conn, "SELECT * FROM user WHERE level = 'admin' LIMIT 1");

if (mysqli_num_rows($check_admin) == 0) {
    // Admin belum ada, buat admin default
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $create_admin = mysqli_query($conn, "INSERT INTO user (nama, username, password, email, alamat, no_telepon, level) 
                                        VALUES ('Administrator', 'admin', '$password', 'admin@perpus.com', 'Perpustakaan', '08123456789', 'admin')");
    
    if ($create_admin) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;'>
                <h3>Berhasil!</h3>
                <p>User admin default berhasil dibuat.</p>
                <p>Username: admin</p>
                <p>Password: admin123</p>
              </div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>
                <h3>Error!</h3>
                <p>Gagal membuat user admin default: " . mysqli_error($conn) . "</p>
              </div>";
    }
}

// Cek apakah direktori uploads/buku sudah ada
if (!file_exists('uploads/buku')) {
    // Buat direktori jika belum ada
    if (mkdir('uploads/buku', 0777, true)) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;'>
                <h3>Berhasil!</h3>
                <p>Direktori uploads/buku berhasil dibuat.</p>
              </div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>
                <h3>Error!</h3>
                <p>Gagal membuat direktori uploads/buku. Pastikan server memiliki izin untuk membuat direktori.</p>
              </div>";
    }
} else {
    echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; margin: 20px; border-radius: 5px;'>
            <h3>Informasi</h3>
            <p>Direktori uploads/buku sudah ada.</p>
          </div>";
}

// Tutup koneksi
mysqli_close($conn);
?>

<div style="text-align: center; margin-top: 30px;">
    <a href="index.php" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Kembali ke Halaman Utama</a>
</div>