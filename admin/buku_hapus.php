<?php
require_once '../includes/config.php';

// Cek apakah user sudah login dan memiliki role admin atau petugas
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    redirect('../login.php');
}

// Cek apakah ada ID buku
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('danger', 'ID buku tidak valid');
    redirect('buku.php');
}

$id = clean($_GET['id']);

// Cek apakah buku ada
$query = "SELECT * FROM buku WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    setMessage('danger', 'Buku tidak ditemukan');
    redirect('buku.php');
}

$buku = mysqli_fetch_assoc($result);

// Cek apakah buku sedang dipinjam
$query_pinjam = "SELECT COUNT(*) as total FROM peminjaman WHERE buku_id = ? AND status = 'dipinjam'";
$stmt_pinjam = mysqli_prepare($conn, $query_pinjam);
mysqli_stmt_bind_param($stmt_pinjam, "s", $id);
mysqli_stmt_execute($stmt_pinjam);
$result_pinjam = mysqli_stmt_get_result($stmt_pinjam);
$row_pinjam = mysqli_fetch_assoc($result_pinjam);

if ($row_pinjam['total'] > 0) {
    setMessage('danger', 'Buku tidak dapat dihapus karena sedang dipinjam');
    redirect('buku.php');
}

// Hapus buku
$query_delete = "DELETE FROM buku WHERE id = ?";
$stmt_delete = mysqli_prepare($conn, $query_delete);
mysqli_stmt_bind_param($stmt_delete, "s", $id);

if (mysqli_stmt_execute($stmt_delete)) {
    // Hapus gambar jika bukan default
    if ($buku['gambar'] != 'default_book.jpg') {
        $file_path = '../uploads/buku/' . $buku['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    setMessage('success', 'Buku berhasil dihapus');
} else {
    setMessage('danger', 'Gagal menghapus buku: ' . mysqli_error($conn));
}

redirect('buku.php');
?>