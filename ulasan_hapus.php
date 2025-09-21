<?php
include "koneksi.php";

if (!isset($_GET['id'])) {
    echo "<script>alert('ID Ulasan tidak ditemukan'); window.location='?page=ulasan';</script>";
    exit;
}

$id_ulasan = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data ulasan untuk cek hak akses
$query_ulasan_cek = mysqli_query($koneksi, "SELECT id_user FROM ulasan WHERE id_ulasan=$id_ulasan");
$data_ulasan_cek = mysqli_fetch_array($query_ulasan_cek);

if (!$data_ulasan_cek) {
    echo "<script>alert('Ulasan tidak ditemukan!'); window.location='?page=ulasan';</script>";
    exit;
}

// Cek hak akses:
// ADMIN/PETUGAS bisa menghapus semua ulasan.
// PEMINJAM hanya bisa menghapus ulasan yang dia buat sendiri.
if (isset($_SESSION['user'])) {
    $user_level = $_SESSION['user']['level'];
    $user_id_session = $_SESSION['user']['id_user']; // ID user yang sedang login

    if (($user_level == 'peminjam' && $user_id_session != $data_ulasan_cek['id_user']) && ($user_level != 'admin' && $user_level != 'petugas')) {
        echo "<script>alert('Anda tidak memiliki izin untuk menghapus ulasan ini!'); window.location='?page=ulasan';</script>";
        exit;
    }
} else {
    // Jika tidak login sama sekali
    echo "<script>alert('Anda harus login untuk menghapus ulasan!'); window.location='login.php';</script>";
    exit;
}

// Lakukan proses hapus
$query_delete = mysqli_query($koneksi, "DELETE FROM ulasan WHERE id_ulasan=$id_ulasan");

if ($query_delete) {
    echo '<script>alert("Ulasan berhasil dihapus!"); window.location="?page=ulasan";</script>';
} else {
    echo '<script>alert("Gagal menghapus ulasan: ' . mysqli_error($koneksi) . '");</script>';
}
?>