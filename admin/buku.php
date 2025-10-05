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

// Query untuk mengambil buku
$query = "SELECT b.*, k.nama as kategori_nama FROM buku b 
          JOIN kategori k ON b.kategori_id = k.id 
          ORDER BY b.judul ASC LIMIT $start, $limit";
$result = mysqli_query($conn, $query);
$buku = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $buku[] = $row;
    }
}

// Total buku untuk pagination
$query_total = "SELECT COUNT(*) as total FROM buku";
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
    <title>Manajemen Buku - Perpustakaan Online</title>
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
                <h1>Manajemen Buku</h1>
                <div>
                    <a href="buku_tambah.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Buku</a>
                </div>
            </div>
            
            <?php
            $message = getMessage();
            if ($message) {
                echo '<div class="alert alert-' . $message['type'] . '">' . $message['message'] . '</div>';
            }
            ?>
            
            <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($buku)): ?>
                            <?php $no = $start + 1; ?>
                            <?php foreach ($buku as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $item['judul'] ?></td>
                                    <td><?= $item['penulis'] ?></td>
                                    <td><?= $item['penerbit'] ?></td>
                                    <td><?= $item['tahun_terbit'] ?></td>
                                    <td><?= $item['kategori_nama'] ?></td>
                                    <td><?= $item['stok'] ?></td>
                                    <td>
                                        <a href="buku_detail.php?id=<?= $item['id'] ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem; margin-right: 0.25rem;"><i class="fas fa-eye"></i></a>
                                        <a href="buku_edit.php?id=<?= $item['id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem; margin-right: 0.25rem;"><i class="fas fa-edit"></i></a>
                                        <a href="buku_hapus.php?id=<?= $item['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Tidak ada data buku.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li><a href="?page=<?= $page-1 ?>">Prev</a></li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li><a href="?page=<?= $i ?>" <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a></li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li><a href="?page=<?= $page+1 ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>