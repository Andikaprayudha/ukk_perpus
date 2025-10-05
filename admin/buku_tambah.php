<?php
require_once '../includes/config.php';

// Cek apakah user sudah login dan memiliki role admin atau petugas
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    redirect('../login.php');
}

// Ambil data kategori untuk dropdown
$query_kategori = "SELECT * FROM kategori ORDER BY nama ASC";
$result_kategori = mysqli_query($conn, $query_kategori);
$kategori = [];
if ($result_kategori) {
    while ($row = mysqli_fetch_assoc($result_kategori)) {
        $kategori[] = $row;
    }
}

// Proses form tambah buku
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = clean($_POST['judul']);
    $penulis = clean($_POST['penulis']);
    $penerbit = clean($_POST['penerbit']);
    $tahun_terbit = clean($_POST['tahun_terbit']);
    $isbn = clean($_POST['isbn']);
    $kategori_id = clean($_POST['kategori_id']);
    $stok = clean($_POST['stok']);
    $deskripsi = clean($_POST['deskripsi']);
    
    // Validasi input
    $errors = [];
    if (empty($judul)) $errors[] = "Judul buku harus diisi";
    if (empty($penulis)) $errors[] = "Penulis harus diisi";
    if (empty($penerbit)) $errors[] = "Penerbit harus diisi";
    if (empty($tahun_terbit)) $errors[] = "Tahun terbit harus diisi";
    if (empty($kategori_id)) $errors[] = "Kategori harus dipilih";
    if (empty($stok)) $errors[] = "Stok harus diisi";
    
    // Jika tidak ada error, proses upload gambar dan simpan data
    if (empty($errors)) {
        $gambar = "default_book.jpg"; // Default gambar
        
        // Cek apakah ada file yang diupload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $file_type = $_FILES['gambar']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_name = time() . '_' . $_FILES['gambar']['name'];
                $upload_dir = '../uploads/buku/';
                
                // Pastikan direktori upload ada
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                    $gambar = $file_name;
                } else {
                    $errors[] = "Gagal mengupload gambar";
                }
            } else {
                $errors[] = "Format gambar tidak didukung. Gunakan JPG, JPEG, atau PNG";
            }
        }
        
        if (empty($errors)) {
            // Simpan data buku ke database
            $query = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori_id, stok, deskripsi, gambar) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssssssss", $judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori_id, $stok, $deskripsi, $gambar);
            
            if (mysqli_stmt_execute($stmt)) {
                setMessage('success', 'Buku berhasil ditambahkan');
                redirect('buku.php');
            } else {
                $errors[] = "Gagal menyimpan data: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - Perpustakaan Online</title>
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
                <h1>Tambah Buku Baru</h1>
                <div>
                    <a href="buku.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
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
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="judul">Judul Buku <span class="text-danger">*</span></label>
                        <input type="text" id="judul" name="judul" class="form-control" value="<?= isset($_POST['judul']) ? $_POST['judul'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="penulis">Penulis <span class="text-danger">*</span></label>
                        <input type="text" id="penulis" name="penulis" class="form-control" value="<?= isset($_POST['penulis']) ? $_POST['penulis'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="penerbit">Penerbit <span class="text-danger">*</span></label>
                        <input type="text" id="penerbit" name="penerbit" class="form-control" value="<?= isset($_POST['penerbit']) ? $_POST['penerbit'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tahun_terbit">Tahun Terbit <span class="text-danger">*</span></label>
                        <input type="number" id="tahun_terbit" name="tahun_terbit" class="form-control" min="1900" max="<?= date('Y') ?>" value="<?= isset($_POST['tahun_terbit']) ? $_POST['tahun_terbit'] : date('Y') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" class="form-control" value="<?= isset($_POST['isbn']) ? $_POST['isbn'] : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori_id">Kategori <span class="text-danger">*</span></label>
                        <select id="kategori_id" name="kategori_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori as $item): ?>
                                <option value="<?= $item['id'] ?>" <?= isset($_POST['kategori_id']) && $_POST['kategori_id'] == $item['id'] ? 'selected' : '' ?>><?= $item['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="stok">Stok <span class="text-danger">*</span></label>
                        <input type="number" id="stok" name="stok" class="form-control" min="0" value="<?= isset($_POST['stok']) ? $_POST['stok'] : '1' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4"><?= isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="gambar">Gambar Sampul</label>
                        <input type="file" id="gambar" name="gambar" class="form-control" accept="image/jpeg, image/png, image/jpg">
                        <small class="text-muted">Format: JPG, JPEG, PNG. Ukuran maksimal: 2MB</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <a href="buku.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>