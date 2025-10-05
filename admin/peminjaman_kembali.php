<?php
require_once '../includes/config.php';

// Cek apakah user sudah login dan memiliki role admin atau petugas
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    redirect('../login.php');
}

// Cek apakah ada ID peminjaman
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('danger', 'ID peminjaman tidak valid');
    redirect('peminjaman.php');
}

$id = clean($_GET['id']);

// Ambil data peminjaman
$query = "SELECT p.*, b.judul as judul_buku, u.nama as nama_peminjam 
          FROM peminjaman p 
          JOIN buku b ON p.buku_id = b.id 
          JOIN users u ON p.user_id = u.id 
          WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    setMessage('danger', 'Peminjaman tidak ditemukan');
    redirect('peminjaman.php');
}

$peminjaman = mysqli_fetch_assoc($result);

// Cek apakah peminjaman sudah dikembalikan
if ($peminjaman['status'] == 'dikembalikan') {
    setMessage('danger', 'Buku sudah dikembalikan sebelumnya');
    redirect('peminjaman.php');
}

// Proses pengembalian buku
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_kembali = date('Y-m-d');
    $status = 'dikembalikan';
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Update status peminjaman
        $query_update = "UPDATE peminjaman SET status = ?, tanggal_kembali = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, "sss", $status, $tanggal_kembali, $id);
        mysqli_stmt_execute($stmt_update);
        
        // Tambah stok buku
        $query_buku = "UPDATE buku SET stok = stok + 1 WHERE id = ?";
        $stmt_buku = mysqli_prepare($conn, $query_buku);
        mysqli_stmt_bind_param($stmt_buku, "s", $peminjaman['buku_id']);
        mysqli_stmt_execute($stmt_buku);
        
        // Commit transaksi
        mysqli_commit($conn);
        
        setMessage('success', 'Buku berhasil dikembalikan');
        redirect('peminjaman.php');
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        mysqli_rollback($conn);
        setMessage('danger', 'Gagal memproses pengembalian: ' . $e->getMessage());
        redirect('peminjaman.php');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Buku - Perpustakaan Online</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div style="padding: 1rem; text-align: center;">
                <h2>Admin Panel</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="buku.php"><i class="fas fa-book"></i> Manajemen Buku</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="peminjaman.php" class="active"><i class="fas fa-clipboard-list"></i> Peminjaman</a></li>
                <li><a href="anggota.php"><i class="fas fa-users"></i> Anggota</a></li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <li><a href="petugas.php"><i class="fas fa-user-shield"></i> Petugas</a></li>
                <?php endif; ?>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Pengembalian Buku</h1>
                <div>
                    <a href="peminjaman.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            
            <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                <div style="margin-bottom: 1.5rem;">
                    <h3>Detail Peminjaman</h3>
                    <table class="table">
                        <tr>
                            <th style="width: 150px;">Peminjam</th>
                            <td><?= $peminjaman['nama_peminjam'] ?></td>
                        </tr>
                        <tr>
                            <th>Buku</th>
                            <td><?= $peminjaman['judul_buku'] ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pinjam</th>
                            <td><?= date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])) ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($peminjaman['status'] == 'dipinjam'): ?>
                                    <span class="badge" style="background-color: #2196F3; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">Dipinjam</span>
                                <?php elseif ($peminjaman['status'] == 'terlambat'): ?>
                                    <span class="badge" style="background-color: #F44336; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">Terlambat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <h3>Konfirmasi Pengembalian</h3>
                    <p>Apakah Anda yakin ingin memproses pengembalian buku ini?</p>
                    <p>Tanggal Pengembalian: <strong><?= date('d/m/Y') ?></strong></p>
                </div>
                
                <form action="" method="post">
                    <div class="form-group">
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Konfirmasi Pengembalian</button>
                        <a href="peminjaman.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>