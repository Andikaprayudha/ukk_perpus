<?php
require_once 'includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    setMessage('danger', 'Anda harus login untuk meminjam buku');
    redirect('login.php');
}

// Cek apakah ada ID buku
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('danger', 'ID buku tidak valid');
    redirect('katalog.php');
}

$buku_id = clean($_GET['id']);
$user_id = $_SESSION['user_id'];

// Ambil data buku
$query_buku = "SELECT * FROM buku WHERE id = ?";
$stmt_buku = mysqli_prepare($conn, $query_buku);
mysqli_stmt_bind_param($stmt_buku, "s", $buku_id);
mysqli_stmt_execute($stmt_buku);
$result_buku = mysqli_stmt_get_result($stmt_buku);

if (mysqli_num_rows($result_buku) == 0) {
    setMessage('danger', 'Buku tidak ditemukan');
    redirect('katalog.php');
}

$buku = mysqli_fetch_assoc($result_buku);

// Cek stok buku
if ($buku['stok'] <= 0) {
    setMessage('danger', 'Maaf, stok buku ini sedang kosong');
    redirect('detail_buku.php?id=' . $buku_id);
}

// Cek apakah user sudah meminjam buku yang sama dan belum dikembalikan
$query_check = "SELECT * FROM peminjaman WHERE user_id = ? AND buku_id = ? AND status = 'dipinjam'";
$stmt_check = mysqli_prepare($conn, $query_check);
mysqli_stmt_bind_param($stmt_check, "ss", $user_id, $buku_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    setMessage('danger', 'Anda sudah meminjam buku ini dan belum mengembalikannya');
    redirect('detail_buku.php?id=' . $buku_id);
}

// Proses peminjaman buku
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Generate ID peminjaman
        $peminjaman_id = uniqid('PJM');
        
        // Set tanggal pinjam dan tanggal harus kembali (7 hari dari sekarang)
        $tanggal_pinjam = date('Y-m-d');
        $tanggal_harus_kembali = date('Y-m-d', strtotime('+7 days'));
        $status = 'dipinjam';
        
        // Insert data peminjaman
        $query_insert = "INSERT INTO peminjaman (id, user_id, buku_id, tanggal_pinjam, tanggal_harus_kembali, status) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, "ssssss", $peminjaman_id, $user_id, $buku_id, $tanggal_pinjam, $tanggal_harus_kembali, $status);
        mysqli_stmt_execute($stmt_insert);
        
        // Kurangi stok buku
        $query_update = "UPDATE buku SET stok = stok - 1 WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, "s", $buku_id);
        mysqli_stmt_execute($stmt_update);
        
        // Commit transaksi
        mysqli_commit($conn);
        
        setMessage('success', 'Buku berhasil dipinjam. Silakan kembalikan sebelum ' . date('d/m/Y', strtotime($tanggal_harus_kembali)));
        redirect('peminjaman_saya.php');
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        mysqli_rollback($conn);
        setMessage('danger', 'Gagal meminjam buku: ' . $e->getMessage());
        redirect('detail_buku.php?id=' . $buku_id);
    }
}

include 'includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2>Peminjaman Buku</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <?php
                            $gambar_path = 'uploads/buku/' . $buku['gambar'];
                            if (!empty($buku['gambar']) && file_exists($gambar_path)) {
                                echo '<img src="' . $gambar_path . '" alt="' . $buku['judul'] . '" style="max-width: 100%; height: auto; max-height: 200px; border-radius: 8px;">';
                            } else {
                                echo '<img src="assets/img/book-placeholder.png" alt="Book Placeholder" style="max-width: 100%; height: auto; max-height: 200px; border-radius: 8px;">';
                            }
                            ?>
                        </div>
                        <div class="col-md-8">
                            <h3><?= $buku['judul'] ?></h3>
                            <p><strong>Penulis:</strong> <?= $buku['penulis'] ?></p>
                            <p><strong>Penerbit:</strong> <?= $buku['penerbit'] ?></p>
                            <p><strong>Tahun Terbit:</strong> <?= $buku['tahun_terbit'] ?></p>
                            <p><strong>Stok:</strong> <?= $buku['stok'] ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <h4>Informasi Peminjaman</h4>
                        <p><strong>Tanggal Peminjaman:</strong> <?= date('d/m/Y') ?></p>
                        <p><strong>Tanggal Pengembalian:</strong> <?= date('d/m/Y', strtotime('+7 days')) ?></p>
                        <p><strong>Durasi Peminjaman:</strong> 7 hari</p>
                        <p><strong>Denda Keterlambatan:</strong> Rp 1.000 per hari</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <p><i class="fas fa-exclamation-triangle"></i> Dengan meminjam buku ini, Anda setuju untuk mengembalikannya tepat waktu dan menjaga kondisi buku tetap baik.</p>
                    </div>
                    
                    <form action="" method="post">
                        <div class="form-group text-center" style="margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-book"></i> Konfirmasi Peminjaman</button>
                            <a href="detail_buku.php?id=<?= $buku_id ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>