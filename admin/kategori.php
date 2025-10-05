<?php
require_once '../includes/config.php';

// Cek apakah user sudah login dan memiliki role admin atau petugas
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    redirect('../login.php');
}

// Proses tambah kategori
if (isset($_POST['tambah'])) {
    $nama = clean($_POST['nama']);
    
    if (empty($nama)) {
        setMessage('danger', 'Nama kategori tidak boleh kosong');
    } else {
        // Cek apakah kategori sudah ada
        $query_check = "SELECT * FROM kategori WHERE nama = ?";
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, "s", $nama);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            setMessage('danger', 'Kategori dengan nama tersebut sudah ada');
        } else {
            // Tambah kategori baru
            $query = "INSERT INTO kategori (nama) VALUES (?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $nama);
            
            if (mysqli_stmt_execute($stmt)) {
                setMessage('success', 'Kategori berhasil ditambahkan');
            } else {
                setMessage('danger', 'Gagal menambahkan kategori: ' . mysqli_error($conn));
            }
        }
    }
}

// Proses edit kategori
if (isset($_POST['edit'])) {
    $id = clean($_POST['id']);
    $nama = clean($_POST['nama']);
    
    if (empty($nama)) {
        setMessage('danger', 'Nama kategori tidak boleh kosong');
    } else {
        // Cek apakah kategori dengan nama yang sama sudah ada (kecuali kategori yang sedang diedit)
        $query_check = "SELECT * FROM kategori WHERE nama = ? AND id != ?";
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, "ss", $nama, $id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            setMessage('danger', 'Kategori dengan nama tersebut sudah ada');
        } else {
            // Update kategori
            $query = "UPDATE kategori SET nama = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $nama, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                setMessage('success', 'Kategori berhasil diperbarui');
            } else {
                setMessage('danger', 'Gagal memperbarui kategori: ' . mysqli_error($conn));
            }
        }
    }
}

// Proses hapus kategori
if (isset($_GET['hapus'])) {
    $id = clean($_GET['hapus']);
    
    // Cek apakah kategori digunakan oleh buku
    $query_check = "SELECT COUNT(*) as total FROM buku WHERE kategori_id = ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "s", $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $row_check = mysqli_fetch_assoc($result_check);
    
    if ($row_check['total'] > 0) {
        setMessage('danger', 'Kategori tidak dapat dihapus karena masih digunakan oleh buku');
    } else {
        // Hapus kategori
        $query = "DELETE FROM kategori WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            setMessage('success', 'Kategori berhasil dihapus');
        } else {
            setMessage('danger', 'Gagal menghapus kategori: ' . mysqli_error($conn));
        }
    }
}

// Ambil data kategori
$query = "SELECT * FROM kategori ORDER BY nama ASC";
$result = mysqli_query($conn, $query);
$kategori = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kategori[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Perpustakaan Online</title>
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
                <li><a href="kategori.php" class="active"><i class="fas fa-tags"></i> Kategori</a></li>
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
                <h1>Manajemen Kategori</h1>
                <div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahKategoriModal">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </button>
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
                            <th>Nama Kategori</th>
                            <th>Jumlah Buku</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kategori)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($kategori as $item): ?>
                                <?php
                                // Hitung jumlah buku per kategori
                                $query_count = "SELECT COUNT(*) as total FROM buku WHERE kategori_id = ?";
                                $stmt_count = mysqli_prepare($conn, $query_count);
                                mysqli_stmt_bind_param($stmt_count, "s", $item['id']);
                                mysqli_stmt_execute($stmt_count);
                                $result_count = mysqli_stmt_get_result($stmt_count);
                                $row_count = mysqli_fetch_assoc($result_count);
                                $jumlah_buku = $row_count['total'];
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $item['nama'] ?></td>
                                    <td><?= $jumlah_buku ?></td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" 
                                                onclick="editKategori('<?= $item['id'] ?>', '<?= $item['nama'] ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?hapus=<?= $item['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Tidak ada data kategori.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Kategori -->
    <div class="modal" id="tambahKategoriModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="nama">Nama Kategori</label>
                            <input type="text" id="nama" name="nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Kategori -->
    <div class="modal" id="editKategoriModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="edit_nama">Nama Kategori</label>
                            <input type="text" id="edit_nama" name="nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        // Fungsi untuk menampilkan modal edit kategori
        function editKategori(id, nama) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            
            // Tampilkan modal
            var modal = document.getElementById('editKategoriModal');
            modal.style.display = 'block';
        }
        
        // Fungsi untuk menutup modal
        var closeButtons = document.getElementsByClassName('close');
        for (var i = 0; i < closeButtons.length; i++) {
            closeButtons[i].onclick = function() {
                var modal = this.closest('.modal');
                modal.style.display = 'none';
            }
        }
        
        // Fungsi untuk menutup modal saat mengklik tombol batal
        var cancelButtons = document.querySelectorAll('.modal .btn-secondary');
        for (var i = 0; i < cancelButtons.length; i++) {
            cancelButtons[i].onclick = function() {
                var modal = this.closest('.modal');
                modal.style.display = 'none';
            }
        }
        
        // Fungsi untuk menampilkan modal tambah kategori
        var tambahButton = document.querySelector('[data-target="#tambahKategoriModal"]');
        tambahButton.onclick = function() {
            var modal = document.getElementById('tambahKategoriModal');
            modal.style.display = 'block';
        }
        
        // Tutup modal jika user mengklik di luar modal
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>