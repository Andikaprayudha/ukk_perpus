<?php
require_once '../includes/config.php';

// Cek apakah user sudah login dan memiliki role admin atau petugas
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    redirect('../login.php');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Filter status
$status_filter = isset($_GET['status']) ? clean($_GET['status']) : '';
$where_clause = '';
if (!empty($status_filter)) {
    $where_clause = "WHERE p.status = '$status_filter'";
}

// Query untuk mengambil peminjaman
$query = "SELECT p.*, b.judul as judul_buku, u.nama as nama_peminjam 
          FROM peminjaman p 
          JOIN buku b ON p.buku_id = b.id 
          JOIN users u ON p.user_id = u.id 
          $where_clause
          ORDER BY p.tanggal_pinjam DESC 
          LIMIT $start, $limit";
$result = mysqli_query($conn, $query);
$peminjaman = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $peminjaman[] = $row;
    }
}

// Total peminjaman untuk pagination
$query_total = "SELECT COUNT(*) as total FROM peminjaman p $where_clause";
$result_total = mysqli_query($conn, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_records = $row_total['total'];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman - Perpustakaan Online</title>
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
                <h1>Manajemen Peminjaman</h1>
                <div>
                    <a href="peminjaman_tambah.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Peminjaman</a>
                </div>
            </div>
            
            <?php
            $message = getMessage();
            if ($message) {
                echo '<div class="alert alert-' . $message['type'] . '">' . $message['message'] . '</div>';
            }
            ?>
            
            <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                <div style="margin-bottom: 1rem;">
                    <form action="" method="get" style="display: flex; gap: 0.5rem;">
                        <select name="status" class="form-control" style="width: auto;">
                            <option value="">Semua Status</option>
                            <option value="dipinjam" <?= $status_filter == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="dikembalikan" <?= $status_filter == 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                            <option value="terlambat" <?= $status_filter == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <?php if (!empty($status_filter)): ?>
                            <a href="peminjaman.php" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($peminjaman)): ?>
                            <?php $no = $start + 1; ?>
                            <?php foreach ($peminjaman as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $item['nama_peminjam'] ?></td>
                                    <td><?= $item['judul_buku'] ?></td>
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
                                    <td>
                                        <?php if ($item['status'] == 'dipinjam' || $item['status'] == 'terlambat'): ?>
                                            <a href="peminjaman_kembali.php?id=<?= $item['id'] ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.875rem; margin-right: 0.25rem;">
                                                <i class="fas fa-check"></i> Kembalikan
                                            </a>
                                        <?php endif; ?>
                                        <a href="peminjaman_detail.php?id=<?= $item['id'] ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Tidak ada data peminjaman.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li><a href="?page=<?= $page-1 ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?>">Prev</a></li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li><a href="?page=<?= $i ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?>" <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a></li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li><a href="?page=<?= $page+1 ?><?= !empty($status_filter) ? '&status='.$status_filter : '' ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>