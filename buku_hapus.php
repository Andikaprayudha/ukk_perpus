<?php
include "koneksi.php"; // Pastikan koneksi.php ada di level yang sama.

if (isset($_GET['id'])) {
    $id_buku = $_GET['id'];

    $query_delete = mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku=$id_buku");

    if ($query_delete) {
        echo '<script>alert("Hapus data buku berhasil"); window.location="?page=buku";</script>';
    } else {
        echo '<script>alert("Hapus data buku gagal: ' . mysqli_error($koneksi) . '");</script>';
    }
} else {
    echo "<script>alert('ID Buku tidak ditemukan'); window.location='?page=buku';</script>";
}
?>