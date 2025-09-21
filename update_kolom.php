<?php
// File untuk menambahkan kolom file_buku ke tabel buku jika belum ada

// Koneksi ke database
include "koneksi.php";

// Cek apakah kolom file_buku sudah ada di tabel buku
$check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM buku LIKE 'file_buku'");
$column_exists = mysqli_num_rows($check_column) > 0;

// Jika kolom belum ada, tambahkan kolom
if (!$column_exists) {
    $add_column = mysqli_query($koneksi, "ALTER TABLE buku ADD COLUMN file_buku VARCHAR(255) NULL COMMENT 'Path file buku yang diunggah'");
    
    if ($add_column) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;'>
                <h3>Berhasil!</h3>
                <p>Kolom file_buku berhasil ditambahkan ke tabel buku.</p>
                <p><a href='index.php?page=buku' style='color: #155724; text-decoration: underline;'>Kembali ke halaman buku</a></p>
              </div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>
                <h3>Error!</h3>
                <p>Gagal menambahkan kolom file_buku: " . mysqli_error($koneksi) . "</p>
                <p><a href='index.php?page=buku' style='color: #721c24; text-decoration: underline;'>Kembali ke halaman buku</a></p>
              </div>";
    }
} else {
    echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; margin: 20px; border-radius: 5px;'>
            <h3>Informasi</h3>
            <p>Kolom file_buku sudah ada di tabel buku.</p>
            <p><a href='index.php?page=buku' style='color: #0c5460; text-decoration: underline;'>Kembali ke halaman buku</a></p>
          </div>";
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
?>