<?php
require_once 'includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    setMessage('danger', 'Anda harus login untuk melihat peminjaman');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter status
$status_filter = isset($_GET['status']) ? clean($_GET['status']) : '';
$status_query = "";
$status_params = [];
$status_types = "";

if (!empty($status_filter)) {
    if ($status_filter == 'dipinjam') {
        $status_query = " AND p.status = ?";
        $status_params[] = 'dipinjam';
        $status_types = "s";
    } elseif ($status_filter == 'dikembalikan') {
        $status_query = " AND p.status = ?";
        $status_params[] = 'dikembalikan';
        $status_types = "s";
    } elseif ($status_filter == 'terlambat') {
        $status_query = " AND p.status = 'dipinjam' AND p.tanggal_harus_kembali < CURDATE()";
    }
}

// Ambil data peminjaman
$query = "SELECT p.*, b.judul as judul_buku, b.penulis, b.gambar 
          FROM peminjaman p 
          JOIN buku b ON p.buku_id = b.id 
          WHERE p.user_id = ?" . $status_query . "
          ORDER BY 
            CASE 
                WHEN p.status = 'dipinjam' AND p.tanggal_harus_kembali < CURDATE() THEN 1
                WHEN p.status = 'dipinjam' THEN 2
                ELSE 3
            END,
            p.tanggal_pinjam DESC
          LIMIT ? OFFSET ?";

$param_types = "s" . $status_types . "ii";
$params = array_merge([$user_id], $status_params, [$limit, $offset]);

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $param_types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Hitung total data untuk pagination
$query_count = "SELECT COUNT(*) as total FROM peminjaman p WHERE p.user_id = ?" . $status_query;
$param_types_count = "s" . $status_types;
$params_count = array_merge([$user_id], $status_params);

$stmt_count = mysqli_prepare($conn, $query_count);
mysqli_stmt_bind_param($stmt_count, $param_types_count, ...$params_count);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

include 'includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    <h1>Peminjaman Saya</h1>
    
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3>Daftar Peminjaman</h3>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group">
                        <a href="?<?= !empty($status_filter) ? 'status=' . $status_filter . '&' : '' ?>page=1" class="btn btn-outline-primary <?= empty($status_filter) ? 'active' : '' ?>">Semua</a>
                        <a href="?status=dipinjam&page=1" class="btn btn-outline-primary <?= $status_filter == 'dipinjam' ? 'active' : '' ?>">Dipinjam</a>
                        <a href="?status=terlambat&page=1" class="btn btn-outline-primary <?= $status_filter == 'terlambat' ? 'active' : '' ?>">Terlambat</a>
                        <a href="?status=dikembalikan&page=1" class="btn btn-outline-primary <?= $status_filter == 'dikembalikan' ? 'active' : '' ?>">Dikembalikan</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Harus Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                // Cek status keterlambatan
                                $status_display = $row['status'];
                                if ($row['status'] == 'dipinjam' && date('Y-m-d') > $row['tanggal_harus_kembali']) {
                                    $status_display = 'terlambat';
                                }
                                
                                // Hitung keterlambatan dan denda jika sudah dikembalikan
                                $denda = 0;
                                $hari_terlambat = 0;
                                if ($row['status'] == 'dikembalikan' && $row['tanggal_kembali'] > $row['tanggal_harus_kembali']) {
                                    $tgl_kembali = new DateTime($row['tanggal_kembali']);
                                    $tgl_harus_kembali = new DateTime($row['tanggal_harus_kembali']);
                                    $selisih = $tgl_kembali->diff($tgl_harus_kembali);
                                    $hari_terlambat = $selisih->days;
                                    $denda = $hari_terlambat * 1000; // Denda Rp 1.000 per hari
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php
                                            $gambar_path = 'uploads/buku/' . $row['gambar'];
                                            if (!empty($row['gambar']) && file_exists($gambar_path)) {
                                                echo '<img src="' . $gambar_path . '" alt="' . $row['judul_buku'] . '" style="width: 50px; height: 70px; object-fit: cover; margin-right: 10px;">';
                                            } else {
                                                echo '<img src="assets/img/book-placeholder.png" alt="Book Placeholder" style="width: 50px; height: 70px; object-fit: cover; margin-right: 10px;">';
                                            }
                                            ?>
                                            <div>
                                                <strong><?= $row['judul_buku'] ?></strong><br>
                                                <small><?= $row['penulis'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_harus_kembali'])) ?></td>
                                    <td>
                                        <?php if ($status_display == 'dipinjam'): ?>
                                            <span class="badge bg-primary">Dipinjam</span>
                                        <?php elseif ($status_display == 'terlambat'): ?>
                                            <span class="badge bg-danger">Terlambat</span>
                                        <?php elseif ($status_display == 'dikembalikan'): ?>
                                            <span class="badge bg-success">Dikembalikan</span>
                                            <?php if ($hari_terlambat > 0): ?>
                                                <br><small class="text-danger">Terlambat <?= $hari_terlambat ?> hari</small>
                                                <br><small class="text-danger">Denda: Rp <?= number_format($denda, 0, ',', '.') ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="detail_buku.php?id=<?= $row['buku_id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-info-circle"></i> Detail Buku</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="pagination justify-content-center">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= !empty($status_filter) ? 'status=' . $status_filter . '&' : '' ?>page=<?= $page - 1 ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?' . (!empty($status_filter) ? 'status=' . $status_filter . '&' : '') . 'page=1">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
                                    <a class="page-link" href="?' . (!empty($status_filter) ? 'status=' . $status_filter . '&' : '') . 'page=' . $i . '">' . $i . '</a>
                                  </li>';
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?' . (!empty($status_filter) ? 'status=' . $status_filter . '&' : '') . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
                        }
                        ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= !empty($status_filter) ? 'status=' . $status_filter . '&' : '' ?>page=<?= $page + 1 ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tidak ada data peminjaman yang ditemukan.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>