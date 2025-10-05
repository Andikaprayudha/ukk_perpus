<?php
// Aktifkan tampilan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';

// Ambil buku terbaru
$query = "SELECT b.*, k.nama as kategori_nama FROM buku b 
          LEFT JOIN kategori k ON b.kategori_id = k.id 
          ORDER BY b.created_at DESC LIMIT 8";
$result = mysqli_query($conn, $query);
$buku_terbaru = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $buku_terbaru[] = $row;
    }
}

// Ambil kategori untuk ditampilkan
$query_kategori = "SELECT k.*, COUNT(b.id) as jumlah_buku 
                  FROM kategori k 
                  LEFT JOIN buku b ON k.id = b.kategori_id 
                  GROUP BY k.id 
                  ORDER BY jumlah_buku DESC 
                  LIMIT 6";
$result_kategori = mysqli_query($conn, $query_kategori);
$kategori_populer = [];
if ($result_kategori) {
    while ($row = mysqli_fetch_assoc($result_kategori)) {
        $kategori_populer[] = $row;
    }
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero bg-primary text-white text-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">Selamat Datang di Perpustakaan Digital</h1>
                <p class="lead mb-4">Temukan ribuan buku dari berbagai kategori. Baca, pinjam, dan tingkatkan pengetahuan Anda bersama kami.</p>
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="katalog.php" class="btn btn-light btn-lg px-4 gap-3">
                        <i class="fas fa-book-open me-2"></i>Jelajahi Katalog
                    </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Layanan Kami</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-4">
                            <i class="fas fa-book fa-2x p-3"></i>
                        </div>
                        <h3 class="fs-4">Koleksi Lengkap</h3>
                        <p class="mb-0">Akses ke ribuan judul buku dari berbagai kategori dan genre untuk semua usia.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-4">
                            <i class="fas fa-laptop fa-2x p-3"></i>
                        </div>
                        <h3 class="fs-4">Akses Digital</h3>
                        <p class="mb-0">Pinjam buku secara online dan akses kapan saja dan di mana saja.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-4">
                            <i class="fas fa-sync fa-2x p-3"></i>
                        </div>
                        <h3 class="fs-4">Peminjaman Mudah</h3>
                        <p class="mb-0">Proses peminjaman dan pengembalian yang cepat dan efisien tanpa ribet.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Books Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Buku Terbaru</h2>
            <a href="katalog.php" class="btn btn-outline-primary">Lihat Semua</a>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($buku_terbaru as $buku): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative book-cover">
                            <?php if (!empty($buku['gambar']) && file_exists('uploads/buku/' . $buku['gambar'])): ?>
                                <img src="uploads/buku/<?= $buku['gambar'] ?>" class="card-img-top" alt="<?= $buku['judul'] ?>" style="height: 250px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 250px;">
                                    <i class="fas fa-book fa-3x text-secondary"></i>
                                </div>
                            <?php endif; ?>
                            <?php if ($buku['stok'] > 0): ?>
                                <span class="position-absolute top-0 end-0 badge bg-success m-2">Tersedia</span>
                            <?php else: ?>
                                <span class="position-absolute top-0 end-0 badge bg-danger m-2">Stok Habis</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?= $buku['judul'] ?></h5>
                            <p class="card-text text-muted mb-0">Oleh: <?= $buku['penulis'] ?></p>
                            <p class="card-text"><small class="text-muted"><?= $buku['kategori_nama'] ?? 'Umum' ?></small></p>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="detail_buku.php?id=<?= $buku['id'] ?>" class="btn btn-primary w-100">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($buku_terbaru)): ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Belum ada buku yang tersedia.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Kategori Populer</h2>
            <a href="katalog.php" class="btn btn-outline-primary">Lihat Semua Kategori</a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($kategori_populer as $kategori): ?>
                <div class="col-md-4 col-lg-2">
                    <a href="katalog.php?kategori=<?= $kategori['id'] ?>" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm text-center">
                            <div class="card-body">
                                <div class="category-icon rounded-circle bg-primary bg-opacity-10 mx-auto mb-3">
                                    <i class="fas fa-bookmark text-primary fa-2x p-3"></i>
                                </div>
                                <h5 class="card-title"><?= $kategori['nama'] ?></h5>
                                <p class="card-text text-muted"><?= $kategori['jumlah_buku'] ?> buku</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($kategori_populer)): ?>
                <div class="col-12 text-center py-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Belum ada kategori yang tersedia.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="mb-4">Siap untuk mulai membaca?</h2>
                <p class="lead mb-4">Bergabunglah dengan perpustakaan digital kami dan akses ribuan buku dengan mudah.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <a href="login.php" class="btn btn-light btn-lg px-4 me-sm-3">Masuk</a>
                        <a href="register.php" class="btn btn-outline-light btn-lg px-4">Daftar</a>
                    </div>
                <?php else: ?>
                    <a href="katalog.php" class="btn btn-light btn-lg px-4">Jelajahi Katalog</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.feature-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
}

.category-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
}

.book-cover img {
    transition: transform 0.3s ease;
}

.book-cover:hover img {
    transform: scale(1.05);
}

.hero {
    background: linear-gradient(rgba(13, 110, 253, 0.9), rgba(13, 110, 253, 0.7)), url('assets/img/library-bg.jpg');
    background-size: cover;
    background-position: center;
}
</style>

<?php include 'includes/footer.php'; ?>