<?php
include "koneksi.php"; // Pastikan koneksi.php ada di level yang sama.

if (isset($_GET['id'])) {
    $id_buku = $_GET['id'];
    
    // Periksa apakah buku sedang dipinjam
    $check_peminjaman = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_buku=$id_buku AND status_peminjaman='dipinjam'");
    
    // Jika buku sedang dipinjam, tampilkan pesan error
    if (mysqli_num_rows($check_peminjaman) > 0) {
        echo '<script>
            alert("Buku tidak dapat dihapus karena sedang dipinjam. Kembalikan buku terlebih dahulu.");
            window.location="?page=buku";
        </script>';
        exit;
    }
    
    // Cek apakah kolom file_buku ada di tabel buku
    $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM buku LIKE 'file_buku'");
    $column_exists = mysqli_num_rows($check_column) > 0;
    
    // Jika kolom file_buku ada, ambil informasi file dan hapus
    if ($column_exists) {
        // Ambil informasi file buku sebelum dihapus
        $query_file = mysqli_query($koneksi, "SELECT file_buku FROM buku WHERE id_buku=$id_buku");
        $data_file = mysqli_fetch_array($query_file);
        
        // Hapus file fisik jika ada
        if (!empty($data_file['file_buku']) && file_exists($data_file['file_buku'])) {
            unlink($data_file['file_buku']);
        }
    }
    
    // Hapus data dari database
    $query_delete = mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku=$id_buku");
    
    if ($query_delete) {
        echo '<script>alert("Hapus data buku berhasil"); window.location="?page=buku";</script>';
    } else {
        echo '<script>alert("Buku tidak dapat dihapus karena masih terkait dengan data peminjaman. Hapus data peminjaman terlebih dahulu."); window.location="?page=buku";</script>';
    }
} else {
    echo "<script>alert('ID Buku tidak ditemukan'); window.location='?page=buku';</script>";
}
?>