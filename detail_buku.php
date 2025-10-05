<?php
require_once 'includes/config.php';

// Cek apakah ada ID buku
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('danger', 'ID buku tidak valid');
    redirect('katalog.php');
}

$id = clean($_GET['id']);

// Ambil data buku
$query = "SELECT b.*, k.nama as kategori_nama 
          FROM buku b 
          LEFT JOIN kategori k ON b.kategori_id = k.id 
          WHERE b.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    setMessage('danger', 'Buku tidak ditemukan');
    redirect('katalog.php');
}

$buku = mysqli_fetch_assoc($result);

// Ambil data ulasan
$query_ulasan = "SELECT u.*, us.nama as nama_user 
                FROM ulasan u 
                JOIN users us ON u.user_id = us.id 
                WHERE u.buku_id = ? 
                ORDER BY u.created_at DESC";
$stmt_ulasan = mysqli_prepare($conn, $query_ulasan);
mysqli_stmt_bind_param($stmt_ulasan, "i", $id);
mysqli_stmt_execute($stmt_ulasan);
$result_ulasan = mysqli_stmt_get_result($stmt_ulasan);

// Hitung rata-rata rating
$query_rating = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ulasan FROM ulasan WHERE buku_id = ?";
$stmt_rating = mysqli_prepare($conn, $query_rating);
mysqli_stmt_bind_param($stmt_rating, "i", $id);
mysqli_stmt_execute($stmt_rating);
$result_rating = mysqli_stmt_get_result($stmt_rating);
$rating_data = mysqli_fetch_assoc($result_rating);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_ulasan = $rating_data['total_ulasan'];

// Proses tambah ulasan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ulasan'])) {
    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        setMessage('danger', 'Anda harus login untuk memberikan ulasan');
        redirect('login.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $rating = clean($_POST['rating']);
    $ulasan = clean($_POST['ulasan']);
    
    // Validasi input
    if (empty($rating) || empty($ulasan)) {
        setMessage('danger', 'Rating dan ulasan tidak boleh kosong');
    } elseif ($rating < 1 || $rating > 5) {
        setMessage('danger', 'Rating harus antara 1-5');
    } else {
        // Cek apakah user sudah pernah memberikan ulasan untuk buku ini
        $query_check = "SELECT * FROM ulasan WHERE user_id = ? AND buku_id = ?";
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            // Update ulasan yang sudah ada
            $query_update = "UPDATE ulasan SET rating = ?, ulasan = ? WHERE user_id = ? AND buku_id = ?";
            $stmt_update = mysqli_prepare($conn, $query_update);
            mysqli_stmt_bind_param($stmt_update, "isii", $rating, $ulasan, $user_id, $id);
            
            if (mysqli_stmt_execute($stmt_update)) {
                setMessage('success', 'Ulasan berhasil diperbarui');
                redirect('detail_buku.php?id=' . $id);
            } else {
                setMessage('danger', 'Gagal memperbarui ulasan: ' . mysqli_error($conn));
            }
        } else {
            // Tambah ulasan baru
            $query_insert = "INSERT INTO ulasan (user_id, buku_id, ulasan, rating) VALUES (?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, "iisi", $user_id, $id, $ulasan, $rating);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                setMessage('success', 'Ulasan berhasil ditambahkan');
                redirect('detail_buku.php?id=' . $id);
            } else {
                setMessage('danger', 'Gagal menambahkan ulasan: ' . mysqli_error($conn));
            }
        }
    }
}

// Cek apakah user sudah meminjam buku ini dan belum dikembalikan
$user_borrowed = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query_borrowed = "SELECT * FROM peminjaman WHERE user_id = ? AND buku_id = ? AND status = 'dipinjam'";
    $stmt_borrowed = mysqli_prepare($conn, $query_borrowed);
    mysqli_stmt_bind_param($stmt_borrowed, "ss", $user_id, $id);
    mysqli_stmt_execute($stmt_borrowed);
    $result_borrowed = mysqli_stmt_get_result($stmt_borrowed);
    $user_borrowed = mysqli_num_rows($result_borrowed) > 0;
}

include 'includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php
                    $gambar_path = 'uploads/buku/' . $buku['gambar'];
                    if (!empty($buku['gambar']) && file_exists($gambar_path)) {
                        echo '<img src="' . $gambar_path . '" alt="' . $buku['judul'] . '" style="max-width: 100%; height: auto; max-height: 300px; border-radius: 8px;">';
                    } else {
                        echo '<img src="assets/img/book-placeholder.png" alt="Book Placeholder" style="max-width: 100%; height: auto; max-height: 300px; border-radius: 8px;">';
                    }
                    ?>
                    
                    <div style="margin-top: 1rem;">
                        <div class="rating-stars">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $avg_rating) {
                                    echo '<i class="fas fa-star text-warning"></i>';
                                } elseif ($i - 0.5 <= $avg_rating) {
                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                } else {
                                    echo '<i class="far fa-star text-warning"></i>';
                                }
                            }
                            ?>
                            <span class="ms-2"><?= $avg_rating ?>/5 (<?= $total_ulasan ?> ulasan)</span>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($buku['stok'] > 0 && !$user_borrowed): ?>
                            <a href="pinjam_buku.php?id=<?= $id ?>" class="btn btn-primary btn-lg w-100 mt-3">
                                <i class="fas fa-book"></i> Pinjam Buku
                            </a>
                        <?php elseif ($user_borrowed): ?>
                            <button class="btn btn-secondary btn-lg w-100 mt-3" disabled>
                                <i class="fas fa-check-circle"></i> Sedang Anda Pinjam
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg w-100 mt-3" disabled>
                                <i class="fas fa-times-circle"></i> Stok Habis
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg w-100 mt-3">
                            <i class="fas fa-sign-in-alt"></i> Login untuk Meminjam
                        </a>
                    <?php endif; ?>
                    
                    <a href="katalog.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Katalog
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h2><?= $buku['judul'] ?></h2>
                </div>
                <div class="card-body">
                    <table class="table">
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
                            <th>Kategori</th>
                            <td><?= $buku['kategori_nama'] ?? 'Tidak ada kategori' ?></td>
                        </tr>
                        <tr>
                            <th>Stok</th>
                            <td>
                                <?php if ($buku['stok'] > 0): ?>
                                    <span class="badge bg-success"><?= $buku['stok'] ?> tersedia</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Stok habis</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    <h4>Deskripsi</h4>
                    <p><?= nl2br($buku['deskripsi']) ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Ulasan (<?= $total_ulasan ?>)</h3>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                            <i class="fas fa-edit"></i> Tulis Ulasan
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_ulasan) > 0): ?>
                        <?php while ($ulasan = mysqli_fetch_assoc($result_ulasan)): ?>
                            <div class="review-item mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5><?= $ulasan['nama_user'] ?></h5>
                                        <div class="rating-stars">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $ulasan['rating']) {
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                } else {
                                                    echo '<i class="far fa-star text-warning"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="text-muted">
                                        <small><?= date('d/m/Y', strtotime($ulasan['tanggal'])) ?></small>
                                    </div>
                                </div>
                                <p class="mt-2"><?= nl2br($ulasan['ulasan']) ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada ulasan untuk buku ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ulasan -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Tulis Ulasan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select class="form-select" id="rating" name="rating" required>
                            <option value="">Pilih Rating</option>
                            <option value="5">5 - Sangat Bagus</option>
                            <option value="4">4 - Bagus</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Kurang</option>
                            <option value="1">1 - Sangat Kurang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ulasan" class="form-label">Ulasan</label>
                        <textarea class="form-control" id="ulasan" name="ulasan" rows="4" required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="submit_ulasan" class="btn btn-primary">Kirim Ulasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>