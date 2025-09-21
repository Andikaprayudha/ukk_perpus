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

// Opsional: Cek apakah status peminjaman sudah dikembalikan sebelum dihapus
$check_status = mysqli_query($koneksi, "SELECT status_peminjaman FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
$data_status = mysqli_fetch_array($check_status);

if ($data_status && $data_status['status_peminjaman'] == 'dipinjam') {
    echo '<script>alert("Tidak bisa menghapus peminjaman yang masih berstatus Dipinjam. Kembalikan buku terlebih dahulu!"); window.location="?page=peminjaman";</script>';
    exit;
}


// Lakukan proses hapus
$query_delete = mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");

if ($query_delete) {
    echo '<script>alert("Data peminjaman berhasil dihapus!"); window.location="?page=peminjaman";</script>';
} else {
    echo '<script>alert("Gagal menghapus data peminjaman: ' . mysqli_error($koneksi) . '");</script>';
}
?>