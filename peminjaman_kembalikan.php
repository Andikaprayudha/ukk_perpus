<?php
include "koneksi.php";

// Redirect jika belum login atau bukan admin/petugas
if (!isset($_SESSION['user']) || ($_SESSION['user']['level'] != 'admin' && $_SESSION['user']['level'] != 'petugas')) {
    echo "<script>alert('Anda tidak memiliki izin untuk mengakses halaman ini!'); window.location='?page=peminjaman';</script>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<script>alert('ID Peminjaman tidak ditemukan!'); window.location='?page=peminjaman';</script>";
    exit;
}

$id_peminjaman = mysqli_real_escape_string($koneksi, $_GET['id']);
$tanggal_pengembalian = date('Y-m-d'); // Tanggal pengembalian hari ini

// Ambil id_buku dari peminjaman untuk menambah stok kembali
$get_id_buku = mysqli_query($koneksi, "SELECT id_buku FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
$data_id_buku = mysqli_fetch_array($get_id_buku);
$id_buku = $data_id_buku['id_buku'];

// Update status peminjaman dan tanggal pengembalian
$query_update = mysqli_query($koneksi, "UPDATE peminjaman SET
                                        tanggal_pengembalian = '$tanggal_pengembalian',
                                        status_peminjaman = 'dikembalikan'
                                        WHERE id_peminjaman = '$id_peminjaman'");

if ($query_update) {
    // Tambah stok buku kembali
    mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id_buku = '$id_buku'");
    echo '<script>alert("Buku berhasil dikembalikan!"); window.location="?page=peminjaman";</script>';
} else {
    echo '<script>alert("Gagal mengembalikan buku: ' . mysqli_error($koneksi) . '");</script>';
}
?>