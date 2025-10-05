<?php
require_once '../includes/config.php';

// Cek apakah user sudah login dan memiliki role admin atau petugas
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    redirect('../login.php');
}

// Ambil statistik untuk dashboard
// Total buku
$query_buku = "SELECT COUNT(*) as total FROM buku";
$result_buku = mysqli_query($conn, $query_buku);
$total_buku = mysqli_fetch_assoc($result_buku)['total'];

// Total anggota
$query_anggota = "SELECT COUNT(*) as total FROM users WHERE role = 'anggota'";
$result_anggota = mysqli_query($conn, $query_anggota);
$total_anggota = mysqli_fetch_assoc($result_anggota)['total'];

// Total peminjaman
$query_peminjaman = "SELECT COUNT(*) as total FROM peminjaman";
$result_peminjaman = mysqli_query($conn, $query_peminjaman);
$total_peminjaman = mysqli_fetch_assoc($result_peminjaman)['total'];

// Peminjaman aktif
$query_aktif = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'";
$result_aktif = mysqli_query($conn, $query_aktif);
$total_aktif = mysqli_fetch_assoc($result_aktif)['total'];

// Peminjaman terbaru
$query_terbaru = "SELECT p.*, u.nama as user_nama, b.judul as buku_judul 
                 FROM peminjaman p 
                 JOIN users u ON p.user_id = u.id 
                 JOIN buku b ON p.buku_id = b.id 
                 ORDER BY p.created_at DESC LIMIT 5";
$result_terbaru = mysqli_query($conn, $query_terbaru);
$peminjaman_terbaru = [];
if ($result_terbaru) {
    while ($row = mysqli_fetch_assoc($result_terbaru)) {
        $peminjaman_terbaru[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan Online</title>
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
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="buku.php"><i class="fas fa-book"></i> Manajemen Buku</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="peminjaman.php"><i class="fas fa-clipboard-list"></i> Peminjaman</a></li>
                <li><a href="anggota.php"><i class="fas fa-users"></i> Anggota</a></li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <li><a href="petugas.php"><i class="fas fa-user-shield"></i> Petugas</a></li>
                <?php endif; ?>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <div>
                    <span>Selamat datang, <?= $_SESSION['nama'] ?></span>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="stat-card">
                    <h3>Total Buku</h3>
                    <div class="number"><?= $total_buku ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Total Anggota</h3>
                    <div class="number"><?= $total_anggota ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Total Peminjaman</h3>
                    <div class="number"><?= $total_peminjaman ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Peminjaman Aktif</h3>
                    <div class="number"><?= $total_aktif ?></div>
                </div>
            </div>
            
            <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                <h2>Peminjaman Terbaru</h2>
                
                <?php if (!empty($peminjaman_terbaru)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($peminjaman_terbaru as $pinjam): ?>
                                <tr>
                                    <td><?= $pinjam['user_nama'] ?></td>
                                    <td><?= $pinjam['buku_judul'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($pinjam['tanggal_kembali'])) ?></td>
                                    <td>
                                        <?php if ($pinjam['status'] == 'dipinjam'): ?>
                                            <span style="background-color: #cce5ff; color: #004085; padding: 0.25rem 0.5rem; border-radius: 4px;">Dipinjam</span>
                                        <?php elseif ($pinjam['status'] == 'dikembalikan'): ?>
                                            <span style="background-color: #d4edda; color: #155724; padding: 0.25rem 0.5rem; border-radius: 4px;">Dikembalikan</span>
                                        <?php else: ?>
                                            <span style="background-color: #f8d7da; color: #721c24; padding: 0.25rem 0.5rem; border-radius: 4px;">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="peminjaman_detail.php?id=<?= $pinjam['id'] ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Belum ada data peminjaman.</p>
                <?php endif; ?>
                
                <div style="text-align: right; margin-top: 1rem;">
                    <a href="peminjaman.php" class="btn btn-secondary">Lihat Semua</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>