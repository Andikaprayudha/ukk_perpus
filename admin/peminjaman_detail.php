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
$query = "SELECT p.*, b.judul as judul_buku, b.penulis, b.penerbit, b.tahun_terbit, b.gambar, 
          u.nama as nama_peminjam, u.username, u.email, u.alamat 
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

// Hitung keterlambatan jika sudah dikembalikan
$denda = 0;
$hari_terlambat = 0;
if ($peminjaman['status'] == 'dikembalikan' && $peminjaman['tanggal_kembali'] > $peminjaman['tanggal_harus_kembali']) {
    $tgl_kembali = new DateTime($peminjaman['tanggal_kembali']);
    $tgl_harus_kembali = new DateTime($peminjaman['tanggal_harus_kembali']);
    $selisih = $tgl_kembali->diff($tgl_harus_kembali);
    $hari_terlambat = $selisih->days;
    $denda = $hari_terlambat * 1000; // Denda Rp 1.000 per hari
}

// Cek status keterlambatan untuk peminjaman yang masih dipinjam
$status_display = $peminjaman['status'];
if ($peminjaman['status'] == 'dipinjam' && date('Y-m-d') > $peminjaman['tanggal_harus_kembali']) {
    $status_display = 'terlambat';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - Perpustakaan Online</title>
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
                <h1>Detail Peminjaman</h1>
                <div>
                    <a href="peminjaman.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <?php if ($peminjaman['status'] == 'dipinjam'): ?>
                    <a href="peminjaman_kembali.php?id=<?= $id ?>" class="btn btn-success"><i class="fas fa-check-circle"></i> Proses Pengembalian</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
                        <h3>Informasi Peminjaman</h3>
                        <table class="table">
                            <tr>
                                <th style="width: 200px;">ID Peminjaman</th>
                                <td><?= $peminjaman['id'] ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Pinjam</th>
                                <td><?= date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])) ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Harus Kembali</th>
                                <td><?= date('d/m/Y', strtotime($peminjaman['tanggal_harus_kembali'])) ?></td>
                            </tr>
                            <?php if ($peminjaman['status'] == 'dikembalikan'): ?>
                            <tr>
                                <th>Tanggal Kembali</th>
                                <td><?= date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php if ($status_display == 'dipinjam'): ?>
                                        <span class="badge" style="background-color: #2196F3; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">Dipinjam</span>
                                    <?php elseif ($status_display == 'terlambat'): ?>
                                        <span class="badge" style="background-color: #F44336; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">Terlambat</span>
                                    <?php elseif ($status_display == 'dikembalikan'): ?>
                                        <span class="badge" style="background-color: #4CAF50; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($hari_terlambat > 0): ?>
                            <tr>
                                <th>Keterlambatan</th>
                                <td><?= $hari_terlambat ?> hari (Denda: Rp <?= number_format($denda, 0, ',', '.') ?>)</td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    
                    <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
                        <h3>Informasi Peminjam</h3>
                        <table class="table">
                            <tr>
                                <th style="width: 200px;">Nama</th>
                                <td><?= $peminjaman['nama_peminjam'] ?></td>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <td><?= $peminjaman['username'] ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= $peminjaman['email'] ?></td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td><?= $peminjaman['alamat'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                        <h3>Informasi Buku</h3>
                        <div style="text-align: center; margin-bottom: 1rem;">
                            <?php
                            $gambar_path = '../uploads/buku/' . $peminjaman['gambar'];
                            if (!empty($peminjaman['gambar']) && file_exists($gambar_path)) {
                                echo '<img src="' . $gambar_path . '" alt="' . $peminjaman['judul_buku'] . '" style="max-width: 100%; height: auto; max-height: 200px; border-radius: 8px;">';
                            } else {
                                echo '<img src="../assets/img/book-placeholder.png" alt="Book Placeholder" style="max-width: 100%; height: auto; max-height: 200px; border-radius: 8px;">';
                            }
                            ?>
                        </div>
                        <table class="table">
                            <tr>
                                <th style="width: 100px;">Judul</th>
                                <td><?= $peminjaman['judul_buku'] ?></td>
                            </tr>
                            <tr>
                                <th>Penulis</th>
                                <td><?= $peminjaman['penulis'] ?></td>
                            </tr>
                            <tr>
                                <th>Penerbit</th>
                                <td><?= $peminjaman['penerbit'] ?></td>
                            </tr>
                            <tr>
                                <th>Tahun</th>
                                <td><?= $peminjaman['tahun_terbit'] ?></td>
                            </tr>
                        </table>
                        <div style="margin-top: 1rem;">
                            <a href="../detail_buku.php?id=<?= $peminjaman['buku_id'] ?>" class="btn btn-primary" style="width: 100%;"><i class="fas fa-info-circle"></i> Lihat Detail Buku</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>