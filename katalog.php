<?php
require_once 'includes/config.php';

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Filter kategori
$kategori_filter = '';
if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $kategori_id = (int)$_GET['kategori'];
    $kategori_filter = "AND b.kategori_id = $kategori_id";
}

// Search
$search_filter = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = clean($_GET['search']);
    $search_filter = "AND (b.judul LIKE '%$search%' OR b.penulis LIKE '%$search%' OR b.penerbit LIKE '%$search%')";
}

// Query untuk mengambil buku
$query = "SELECT b.*, k.nama as kategori_nama FROM buku b 
          JOIN kategori k ON b.kategori_id = k.id 
          WHERE 1=1 $kategori_filter $search_filter
          ORDER BY b.judul ASC LIMIT $start, $limit";
$result = mysqli_query($conn, $query);
$buku = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $buku[] = $row;
    }
}

// Total buku untuk pagination
$query_total = "SELECT COUNT(*) as total FROM buku b WHERE 1=1 $kategori_filter $search_filter";
$result_total = mysqli_query($conn, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_records = $row_total['total'];
$total_pages = ceil($total_records / $limit);

// Ambil semua kategori untuk filter
$query_kategori = "SELECT * FROM kategori ORDER BY nama ASC";
$result_kategori = mysqli_query($conn, $query_kategori);
$kategori = [];
if ($result_kategori) {
    while ($row = mysqli_fetch_assoc($result_kategori)) {
        $kategori[] = $row;
    }
}

include 'includes/header.php';
?>

<div style="padding: 2rem 0;">
    <h1 style="text-align: center; margin-bottom: 2rem;">Katalog Buku</h1>
    
    <div style="margin-bottom: 2rem;">
        <form action="" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="Cari judul, penulis, atau penerbit..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            <div style="width: 200px;">
                <select name="kategori" class="form-control">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id']) ? 'selected' : '' ?>>
                            <?= $kat['nama'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if (isset($_GET['search']) || isset($_GET['kategori'])): ?>
                    <a href="katalog.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="book-grid">
        <?php foreach ($buku as $item): ?>
            <div class="card">
                <?php if (!empty($item['gambar']) && file_exists('uploads/buku/' . $item['gambar'])): ?>
                    <img src="uploads/buku/<?= $item['gambar'] ?>" alt="<?= $item['judul'] ?>" class="card-img">
                <?php else: ?>
                    <div style="height: 200px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-book" style="font-size: 3rem; color: #adb5bd;"></i>
                    </div>
                <?php endif; ?>
                <div class="card-content">
                    <h3 class="card-title"><?= $item['judul'] ?></h3>
                    <p class="card-text">Penulis: <?= $item['penulis'] ?></p>
                    <p class="card-text">Kategori: <?= $item['kategori_nama'] ?></p>
                    <a href="detail_buku.php?id=<?= $item['id'] ?>" class="btn btn-primary" style="width: 100%;">Lihat Detail</a>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($buku)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                <p>Tidak ada buku yang ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li><a href="?page=<?= $page-1 ?><?= isset($_GET['kategori']) ? '&kategori='.$_GET['kategori'] : '' ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>">Prev</a></li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li><a href="?page=<?= $i ?><?= isset($_GET['kategori']) ? '&kategori='.$_GET['kategori'] : '' ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>" <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a></li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <li><a href="?page=<?= $page+1 ?><?= isset($_GET['kategori']) ? '&kategori='.$_GET['kategori'] : '' ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>