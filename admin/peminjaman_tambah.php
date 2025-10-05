<?php
require_once '../includes/config.php';

// Cek apakah user sudah login dan memiliki role admin atau petugas
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    redirect('../login.php');
}

// Ambil data anggota untuk dropdown
$query_anggota = "SELECT * FROM users WHERE role = 'anggota' ORDER BY nama ASC";
$result_anggota = mysqli_query($conn, $query_anggota);
$anggota = [];
if ($result_anggota) {
    while ($row = mysqli_fetch_assoc($result_anggota)) {
        $anggota[] = $row;
    }
}

// Ambil data buku untuk dropdown
$query_buku = "SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC";
$result_buku = mysqli_query($conn, $query_buku);
$buku = [];
if ($result_buku) {
    while ($row = mysqli_fetch_assoc($result_buku)) {
        $buku[] = $row;
    }
}

// Proses form tambah peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = clean($_POST['user_id']);
    $buku_id = clean($_POST['buku_id']);
    $tanggal_pinjam = date('Y-m-d');
    $status = 'dipinjam';
    
    // Validasi input
    $errors = [];
    if (empty($user_id)) $errors[] = "Peminjam harus dipilih";
    if (empty($buku_id)) $errors[] = "Buku harus dipilih";
    
    // Cek apakah buku tersedia
    if (empty($errors)) {
        $query_check = "SELECT stok FROM buku WHERE id = ?";
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, "s", $buku_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $row_check = mysqli_fetch_assoc($result_check);
        
        if ($row_check['stok'] <= 0) {
            $errors[] = "Stok buku tidak tersedia";
        }
        
        // Cek apakah anggota sudah meminjam buku yang sama dan belum dikembalikan
        $query_check_pinjam = "SELECT * FROM peminjaman WHERE user_id = ? AND buku_id = ? AND status IN ('dipinjam', 'terlambat')";
        $stmt_check_pinjam = mysqli_prepare($conn, $query_check_pinjam);
        mysqli_stmt_bind_param($stmt_check_pinjam, "ss", $user_id, $buku_id);
        mysqli_stmt_execute($stmt_check_pinjam);
        $result_check_pinjam = mysqli_stmt_get_result($stmt_check_pinjam);
        
        if (mysqli_num_rows($result_check_pinjam) > 0) {
            $errors[] = "Anggota sudah meminjam buku ini dan belum dikembalikan";
        }
    }
    
    // Jika tidak ada error, simpan data peminjaman
    if (empty($errors)) {
        // Mulai transaksi
        mysqli_begin_transaction($conn);
        
        try {
            // Simpan data peminjaman
            $query = "INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, status) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $user_id, $buku_id, $tanggal_pinjam, $status);
            mysqli_stmt_execute($stmt);
            
            // Kurangi stok buku
            $query_update = "UPDATE buku SET stok = stok - 1 WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $query_update);
            mysqli_stmt_bind_param($stmt_update, "s", $buku_id);
            mysqli_stmt_execute($stmt_update);
            
            // Commit transaksi
            mysqli_commit($conn);
            
            setMessage('success', 'Peminjaman berhasil ditambahkan');
            redirect('peminjaman.php');
        } catch (Exception $e) {
            // Rollback transaksi jika terjadi error
            mysqli_rollback($conn);
            $errors[] = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Peminjaman - Perpustakaan Online</title>
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
                <h1>Tambah Peminjaman</h1>
                <div>
                    <a href="peminjaman.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div style="background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                <form action="" method="post">
                    <div class="form-group">
                        <label for="user_id">Peminjam <span class="text-danger">*</span></label>
                        <select id="user_id" name="user_id" class="form-control" required>
                            <option value="">Pilih Anggota</option>
                            <?php foreach ($anggota as $item): ?>
                                <option value="<?= $item['id'] ?>" <?= isset($_POST['user_id']) && $_POST['user_id'] == $item['id'] ? 'selected' : '' ?>>
                                    <?= $item['nama'] ?> (<?= $item['username'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="buku_id">Buku <span class="text-danger">*</span></label>
                        <select id="buku_id" name="buku_id" class="form-control" required>
                            <option value="">Pilih Buku</option>
                            <?php foreach ($buku as $item): ?>
                                <option value="<?= $item['id'] ?>" <?= isset($_POST['buku_id']) && $_POST['buku_id'] == $item['id'] ? 'selected' : '' ?>>
                                    <?= $item['judul'] ?> - <?= $item['penulis'] ?> (Stok: <?= $item['stok'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Peminjaman</label>
                        <input type="text" class="form-control" value="<?= date('d/m/Y') ?>" readonly>
                        <small class="text-muted">Tanggal peminjaman otomatis menggunakan tanggal hari ini</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <a href="peminjaman.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>