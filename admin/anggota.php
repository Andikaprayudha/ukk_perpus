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

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where_clause = "WHERE nama LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%'";
}

// Query untuk mengambil anggota (hanya role anggota)
$query = "SELECT * FROM users WHERE role = 'anggota' $where_clause ORDER BY nama ASC LIMIT $start, $limit";
$result = mysqli_query($conn, $query);
$anggota = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $anggota[] = $row;
    }
}

// Total anggota untuk pagination
$query_total = "SELECT COUNT(*) as total FROM users WHERE role = 'anggota' $where_clause";
$result_total = mysqli_query($conn, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_records = $row_total['total'];
$total_pages = ceil($total_records / $limit);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Cek apakah anggota memiliki peminjaman aktif
    $check_query = "SELECT COUNT(*) as count FROM peminjaman WHERE user_id = $id AND status = 'dipinjam'";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] > 0) {
        setMessage('Tidak dapat menghapus anggota yang masih memiliki peminjaman aktif!', 'error');
    } else {
        $delete_query = "DELETE FROM users WHERE id = $id AND role = 'anggota'";
        if (mysqli_query($conn, $delete_query)) {
            setMessage('Anggota berhasil dihapus!', 'success');
        } else {
            setMessage('Gagal menghapus anggota!', 'error');
        }
    }
    redirect('anggota.php');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Anggota - Perpustakaan Online</title>
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
                <li><a href="peminjaman.php"><i class="fas fa-clipboard-list"></i> Peminjaman</a></li>
                <li><a href="anggota.php" class="active"><i class="fas fa-users"></i> Anggota</a></li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <li><a href="petugas.php"><i class="fas fa-user-shield"></i> Petugas</a></li>
                <?php endif; ?>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Manajemen Anggota</h1>
            </div>
            
            <?php
            $message = getMessage();
            if ($message) {
                echo '<div class="alert alert-' . $message['type'] . '">' . $message['message'] . '</div>';
            }
            ?>
            
            <!-- Search Form -->
            <div style="background-color: white; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 1rem;">
                <form method="GET" style="display: flex; gap: 1rem; align-items: center;">
                    <input type="text" name="search" placeholder="Cari anggota..." value="<?= htmlspecialchars($search) ?>" 
                           style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Cari</button>
                    <?php if (!empty($search)): ?>
                        <a href="anggota.php" class="btn btn-outline"><i class="fas fa-times"></i> Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($anggota)): ?>
                            <?php $no = $start + 1; ?>
                            <?php foreach ($anggota as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($item['nama']) ?></td>
                                    <td><?= htmlspecialchars($item['username']) ?></td>
                                    <td><?= htmlspecialchars($item['email']) ?></td>
                                    <td><?= htmlspecialchars($item['telepon'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(substr($item['alamat'] ?? '-', 0, 50)) ?><?= strlen($item['alamat'] ?? '') > 50 ? '...' : '' ?></td>
                                    <td><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                                    <td>
                                        <a href="anggota_detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="anggota_edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="anggota.php?action=delete&id=<?= $item['id'] ?>" 
                                           class="btn btn-sm btn-danger btn-delete" title="Hapus"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem;">
                                    <?php if (!empty($search)): ?>
                                        Tidak ada anggota yang ditemukan dengan kata kunci "<?= htmlspecialchars($search) ?>"
                                    <?php else: ?>
                                        Belum ada data anggota
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-sm btn-outline">
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-sm btn-outline">
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div style="text-align: center; margin-top: 1rem; color: #666;">
                        Menampilkan <?= $start + 1 ?> - <?= min($start + $limit, $total_records) ?> dari <?= $total_records ?> anggota
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>