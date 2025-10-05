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

// Ambil data buku
$query = "SELECT b.*, k.nama as kategori_nama FROM buku b 
          JOIN kategori k ON b.kategori_id = k.id 
          WHERE b.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    setMessage('danger', 'Buku tidak ditemukan');
    redirect('buku.php');
}

$buku = mysqli_fetch_assoc($result);

// Ambil data ulasan
$query_ulasan = "SELECT u.*, us.nama as nama_user FROM ulasan u 
                JOIN users us ON u.user_id = us.id 
                WHERE u.buku_id = ? 
                ORDER BY u.tanggal DESC";
$stmt_ulasan = mysqli_prepare($conn, $query_ulasan);
mysqli_stmt_bind_param($stmt_ulasan, "s", $id);
mysqli_stmt_execute($stmt_ulasan);
$result_ulasan = mysqli_stmt_get_result($stmt_ulasan);
$ulasan = [];
if ($result_ulasan) {
    while ($row = mysqli_fetch_assoc($result_ulasan)) {
        $ulasan[] = $row;
    }
}

// Hitung rata-rata rating
$avg_rating = 0;
$query_avg = "SELECT AVG(rating) as avg_rating FROM ulasan WHERE buku_id = ?";
$stmt_avg = mysqli_prepare($conn, $query_avg);
mysqli_stmt_bind_param($stmt_avg, "s", $id);
mysqli_stmt_execute($stmt_avg);
$result_avg = mysqli_stmt_get_result($stmt_avg);
if ($row_avg = mysqli_fetch_assoc($result_avg)) {
    $avg_rating = round($row_avg['avg_rating'], 1);
}

// Ambil data peminjaman
$query_peminjaman = "SELECT p.*, u.nama as nama_user FROM peminjaman p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.buku_id = ? 
                    ORDER BY p.tanggal_pinjam DESC";
$stmt_peminjaman = mysqli_prepare($conn, $query_peminjaman);
mysqli_stmt_bind_param($stmt_peminjaman, "s", $id);
mysqli_stmt_execute($stmt_peminjaman);
$result_peminjaman = mysqli_stmt_get_result($stmt_peminjaman);
$peminjaman = [];
if ($result_peminjaman) {
    while ($row = mysqli_fetch_assoc($result_peminjaman)) {
        $peminjaman[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - Perpustakaan Online</title>
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
                <li><a href="buku.php" class="active"><i class="fas fa-book"></i> Manajemen Buku</a></li>
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
                <h1>Detail Buku</h1>
                <div>
                    <a href="buku_edit.php?id=<?= $buku['id'] ?>" class="btn btn-secondary"><i class="fas fa-edit"></i> Edit</a>
                    <a href="buku.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            
            <?php
            $message = getMessage();
            if ($message) {
                echo '<div class="alert alert-' . $message['type'] . '">' . $message['message'] . '</div>';
            }
            ?>
            
            <div class="row">
                <div class="col-md-4">
                    <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
                        <div style="text-align: center; margin-bottom: 1.5rem;">
                            <img src="../uploads/buku/<?= !empty($buku['gambar']) ? $buku['gambar'] : 'default_book.jpg' ?>" alt="<?= $buku['judul'] ?>" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                <div style="font-size: 1.25rem; color: #f8c01d; margin-right: 0.5rem;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $avg_rating): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i - 0.5 <= $avg_rating): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <div style="font-weight: bold;"><?= $avg_rating ?>/5</div>
                            </div>
                            <div>
                                <span class="badge" style="background-color: #4CAF50; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">
                                    <?= $buku['kategori_nama'] ?>
                                </span>
                                <span class="badge" style="background-color: #2196F3; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">
                                    Stok: <?= $buku['stok'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
                        <h2 style="margin-top: 0; margin-bottom: 1rem;"><?= $buku['judul'] ?></h2>
                        
                        <table class="table" style="margin-bottom: 1.5rem;">
                            <tr>
                                <th style="width: 150px;">Penulis</th>
                                <td><?= $buku['penulis'] ?></td>
                            </tr>
                            <tr>
                                <th>Penerbit</th>
                                <td><?= $buku['penerbit'] ?></td>
                            </tr>
                            <tr>
                                <th>Tahun Terbit</th>
                                <td><?= $buku['tahun_terbit'] ?></td>
                            </tr>
                            <tr>
                                <th>ISBN</th>
                                <td><?= !empty($buku['isbn']) ? $buku['isbn'] : '-' ?></td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td><?= $buku['kategori_nama'] ?></td>
                            </tr>
                            <tr>
                                <th>Stok</th>
                                <td><?= $buku['stok'] ?></td>
                            </tr>
                        </table>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="margin-top: 0; margin-bottom: 0.5rem;">Deskripsi</h3>
                            <p style="margin: 0; line-height: 1.6;"><?= !empty($buku['deskripsi']) ? nl2br($buku['deskripsi']) : 'Tidak ada deskripsi' ?></p>
                        </div>
                    </div>
                    
                    <!-- Riwayat Peminjaman -->
                    <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem;">
                        <h3 style="margin-top: 0; margin-bottom: 1rem;">Riwayat Peminjaman</h3>
                        
                        <?php if (!empty($peminjaman)): ?>
                            <div style="overflow-x: auto;">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Peminjam</th>
                                            <th>Tanggal Pinjam</th>
                                            <th>Tanggal Kembali</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($peminjaman as $item): ?>
                                            <tr>
                                                <td><?= $item['nama_user'] ?></td>
                                                <td><?= date('d/m/Y', strtotime($item['tanggal_pinjam'])) ?></td>
                                                <td><?= !empty($item['tanggal_kembali']) ? date('d/m/Y', strtotime($item['tanggal_kembali'])) : '-' ?></td>
                                                <td>
                                                    <?php if ($item['status'] == 'dipinjam'): ?>
                                                        <span class="badge" style="background-color: #2196F3; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">Dipinjam</span>
                                                    <?php elseif ($item['status'] == 'dikembalikan'): ?>
                                                        <span class="badge" style="background-color: #4CAF50; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">Dikembalikan</span>
                                                    <?php elseif ($item['status'] == 'terlambat'): ?>
                                                        <span class="badge" style="background-color: #F44336; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">Terlambat</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>Belum ada riwayat peminjaman untuk buku ini.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Ulasan -->
                    <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                        <h3 style="margin-top: 0; margin-bottom: 1rem;">Ulasan Pengguna</h3>
                        
                        <?php if (!empty($ulasan)): ?>
                            <?php foreach ($ulasan as $item): ?>
                                <div style="border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <div style="font-weight: bold;"><?= $item['nama_user'] ?></div>
                                        <div style="color: #666; font-size: 0.875rem;"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></div>
                                    </div>
                                    <div style="margin-bottom: 0.5rem;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $item['rating']): ?>
                                                <i class="fas fa-star" style="color: #f8c01d;"></i>
                                            <?php else: ?>
                                                <i class="far fa-star" style="color: #f8c01d;"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <div style="line-height: 1.6;"><?= nl2br($item['ulasan']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Belum ada ulasan untuk buku ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>