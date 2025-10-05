<?php
require_once 'includes/config.php';

// Cek apakah file functions.php ada, jika tidak ada, buat fungsi setFlashMessage
if (!file_exists('includes/functions.php')) {
    function setFlashMessage($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }
} else {
    require_once 'includes/functions.php';
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    setFlashMessage('error', 'Anda harus login terlebih dahulu');
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    
    // Validasi input
    if (empty($nama) || empty($alamat) || empty($email) || empty($telepon)) {
        $error = "Semua field harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } else {
        // Update data user
        $stmt = $conn->prepare("UPDATE user SET nama = ?, alamat = ?, email = ?, telepon = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama, $alamat, $email, $telepon, $user_id);
        
        if ($stmt->execute()) {
            $success = "Profil berhasil diperbarui";
            
            // Refresh data user
            $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Gagal memperbarui profil: " . $conn->error;
        }
    }
}

// Proses update password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua field password harus diisi";
    } elseif ($new_password != $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak cocok";
    } elseif (strlen($new_password) < 6) {
        $error = "Password baru minimal 6 karakter";
    } else {
        // Verifikasi password saat ini
        $stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if (password_verify($current_password, $user_data['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = "Password berhasil diperbarui";
            } else {
                $error = "Gagal memperbarui password: " . $conn->error;
            }
        } else {
            $error = "Password saat ini tidak valid";
        }
    }
}

// Ambil data peminjaman
$stmt = $conn->prepare("
    SELECT p.*, b.judul, b.gambar 
    FROM peminjaman p 
    JOIN buku b ON p.buku_id = b.id 
    WHERE p.user_id = ? 
    ORDER BY p.tanggal_pinjam DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$peminjaman_result = $stmt->get_result();

// Ambil data ulasan
$stmt = $conn->prepare("
    SELECT u.*, b.judul, b.gambar 
    FROM ulasan u 
    JOIN buku b ON u.buku_id = b.id 
    WHERE u.user_id = ? 
    ORDER BY u.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ulasan_result = $stmt->get_result();

// Hitung jumlah buku yang dipinjam
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_peminjaman = $result->fetch_assoc()['total'];

// Hitung jumlah ulasan
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM ulasan WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_ulasan = $result->fetch_assoc()['total'];

// Hitung jumlah buku yang sedang dipinjam
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM peminjaman WHERE user_id = ? AND status = 'dipinjam'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_sedang_dipinjam = $result->fetch_assoc()['total'];

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Profil -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="assets/img/user-avatar.png" alt="Profile" class="rounded-circle img-fluid" style="width: 120px;">
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($user['nama']) ?></h5>
                    <p class="text-muted mb-3"><?= htmlspecialchars($user['role']) ?></p>
                    <div class="d-flex justify-content-center">
                        <div class="px-3 border-end">
                            <h6 class="mb-0"><?= $total_peminjaman ?></h6>
                            <small class="text-muted">Peminjaman</small>
                        </div>
                        <div class="px-3">
                            <h6 class="mb-0"><?= $total_ulasan ?></h6>
                            <small class="text-muted">Ulasan</small>
                        </div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="fas fa-user me-2"></i> Profil Saya
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-lock me-2"></i> Keamanan
                    </a>
                    <a href="#borrowing" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-book me-2"></i> Peminjaman
                    </a>
                    <a href="#reviews" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-star me-2"></i> Ulasan
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Konten Utama -->
        <div class="col-lg-9">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <div class="tab-content">
                <!-- Tab Profil -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Informasi Profil</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="telepon" class="form-label">Nomor Telepon</label>
                                        <input type="text" class="form-control" id="telepon" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($user['alamat']) ?></textarea>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Keamanan -->
                <div class="tab-pane fade" id="security">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Ubah Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Saat Ini</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">Password minimal 6 karakter.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-primary">Ubah Password</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Peminjaman -->
                <div class="tab-pane fade" id="borrowing">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Riwayat Peminjaman Terbaru</h5>
                            <a href="peminjaman_saya.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <?php if ($peminjaman_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Buku</th>
                                                <th>Tanggal Pinjam</th>
                                                <th>Tanggal Kembali</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($pinjam = $peminjaman_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?= !empty($pinjam['gambar']) ? 'assets/img/books/' . $pinjam['gambar'] : 'assets/img/books/default.jpg' ?>" 
                                                                 alt="<?= htmlspecialchars($pinjam['judul']) ?>" 
                                                                 class="me-2" 
                                                                 style="width: 40px; height: 60px; object-fit: cover;">
                                                            <div>
                                                                <a href="detail_buku.php?id=<?= $pinjam['buku_id'] ?>" class="text-decoration-none">
                                                                    <?= htmlspecialchars($pinjam['judul']) ?>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= date('d M Y', strtotime($pinjam['tanggal_pinjam'])) ?></td>
                                                    <td><?= date('d M Y', strtotime($pinjam['tanggal_kembali'])) ?></td>
                                                    <td>
                                                        <?php
                                                        $status_class = '';
                                                        $status_text = '';
                                                        
                                                        if ($pinjam['status'] == 'dipinjam') {
                                                            // Cek apakah terlambat
                                                            $tanggal_kembali = new DateTime($pinjam['tanggal_kembali']);
                                                            $today = new DateTime();
                                                            
                                                            if ($today > $tanggal_kembali) {
                                                                $status_class = 'badge bg-danger';
                                                                $status_text = 'Terlambat';
                                                            } else {
                                                                $status_class = 'badge bg-warning';
                                                                $status_text = 'Dipinjam';
                                                            }
                                                        } else {
                                                            $status_class = 'badge bg-success';
                                                            $status_text = 'Dikembalikan';
                                                        }
                                                        ?>
                                                        <span class="<?= $status_class ?>"><?= $status_text ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <img src="assets/img/empty-state.svg" alt="No data" class="img-fluid mb-3" style="max-width: 200px;">
                                    <h5>Belum ada riwayat peminjaman</h5>
                                    <p class="text-muted">Anda belum pernah meminjam buku dari perpustakaan kami.</p>
                                    <a href="katalog.php" class="btn btn-primary">Jelajahi Katalog</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Statistik Peminjaman -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                                <i class="fas fa-book text-primary fa-2x"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Total Peminjaman</h6>
                                            <h3 class="mb-0"><?= $total_peminjaman ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                                <i class="fas fa-clock text-warning fa-2x"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Sedang Dipinjam</h6>
                                            <h3 class="mb-0"><?= $total_sedang_dipinjam ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Ulasan -->
                <div class="tab-pane fade" id="reviews">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Ulasan Saya</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($ulasan_result->num_rows > 0): ?>
                                <?php while ($ulasan = $ulasan_result->fetch_assoc()): ?>
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0 me-3">
                                            <img src="<?= !empty($ulasan['gambar']) ? 'assets/img/books/' . $ulasan['gambar'] : 'assets/img/books/default.jpg' ?>" 
                                                 alt="<?= htmlspecialchars($ulasan['judul']) ?>" 
                                                 class="rounded" 
                                                 style="width: 60px; height: 90px; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0">
                                                    <a href="detail_buku.php?id=<?= $ulasan['buku_id'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($ulasan['judul']) ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted"><?= date('d M Y', strtotime($ulasan['tanggal_ulasan'])) ?></small>
                                            </div>
                                            <div class="mb-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= ($i <= $ulasan['rating']) ? 'text-warning' : 'text-muted' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="mb-0"><?= htmlspecialchars($ulasan['ulasan']) ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <img src="assets/img/empty-state.svg" alt="No data" class="img-fluid mb-3" style="max-width: 200px;">
                                    <h5>Belum ada ulasan</h5>
                                    <p class="text-muted">Anda belum memberikan ulasan untuk buku yang telah dibaca.</p>
                                    <a href="katalog.php" class="btn btn-primary">Jelajahi Katalog</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>