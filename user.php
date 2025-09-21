<?php
// Pastikan session sudah dimulai di index.php
// include "koneksi.php"; // Ini sudah di-include di index.php, tidak perlu lagi di sini

// Periksa level user untuk akses halaman ini
if (!isset($_SESSION['user']) || ($_SESSION['user']['level'] != 'admin' && $_SESSION['user']['level'] != 'petugas')) {
    echo "<script>alert('Anda tidak memiliki izin untuk mengakses halaman ini!'); window.location='?page=home';</script>";
    exit;
}

// Ambil parameter aksi dari URL (misal: ?page=user&act=tambah)
$action = isset($_GET['act']) ? $_GET['act'] : 'list'; // Default action adalah 'list' (menampilkan daftar user)

// Include koneksi database (sudah di-include di index.php, tapi bisa diulang jika ini file mandiri)
include "koneksi.php";

?>

<h1 class="mt-4">Manajemen User</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Manajemen User - <?php echo ucfirst($action); ?></li>
</ol>

<?php
switch ($action) {
    case 'list': // Menampilkan daftar user (Read)
        // Ambil data user dari database
        $query = mysqli_query($koneksi, "SELECT * FROM user ORDER BY username ASC");
?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Daftar Pengguna
                <a href="?page=user&act=tambah" class="btn btn-primary btn-sm float-end">Tambah User</a>
            </div>
            <div class="card-body">
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Level</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($data = mysqli_fetch_array($query)) {
                        ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($data['username']); ?></td>
                                <td><?php echo htmlspecialchars($data['email']); ?></td>
                                <td><?php echo htmlspecialchars($data['nama']); ?></td>
                                <td><?php echo htmlspecialchars($data['alamat']); ?></td>
                                <td><?php echo htmlspecialchars($data['level']); ?></td>
                                <td>
                                    <a href="?page=user&act=ubah&id=<?php echo $data['id_user']; ?>" class="btn btn-info btn-sm">Ubah</a>
                                    <a href="?page=user&act=hapus&id=<?php echo $data['id_user']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php
        break;

    case 'tambah': // Form tambah user (Create)
        if (isset($_POST['submit_tambah'])) {
            $username = mysqli_real_escape_string($koneksi, $_POST['username']);
            $password = md5($_POST['password']); // HASH password
            $email = mysqli_real_escape_string($koneksi, $_POST['email']);
            $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
            $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
            $level = mysqli_real_escape_string($koneksi, $_POST['level']);

            // Validasi: Cek username unik
            $check_username = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username'");
            if (mysqli_num_rows($check_username) > 0) {
                echo '<script>alert("Username sudah ada!");</script>';
            } else {
                $query_insert = mysqli_query($koneksi, "INSERT INTO user (username, password, email, nama_lengkap, alamat, level) VALUES ('$username', '$password', '$email', '$nama_lengkap', '$alamat', '$level')");
                if ($query_insert) {
                    echo '<script>alert("User berhasil ditambahkan!"); window.location="?page=user";</script>';
                } else {
                    echo '<script>alert("Gagal menambahkan user: ' . mysqli_error($koneksi) . '");</script>';
                }
            }
        }
?>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post">
                            <div class="row mb-3">
                                <div class="col-md-2">Username</div>
                                <div class="col-md-8"><input type="text" class="form-control" name="username" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Password</div>
                                <div class="col-md-8"><input type="password" class="form-control" name="password" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Email</div>
                                <div class="col-md-8"><input type="email" class="form-control" name="email" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Nama Lengkap</div>
                                <div class="col-md-8"><input type="text" class="form-control" name="nama_lengkap" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Alamat</div>
                                <div class="col-md-8"><textarea name="alamat" rows="5" class="form-control" required></textarea></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Level</div>
                                <div class="col-md-8">
                                    <select name="level" class="form-control" required>
                                        <option value="admin">Admin</option>
                                        <option value="petugas">Petugas</option>
                                        <option value="peminjam">Peminjam</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"></div>
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary" name="submit_tambah">Simpan</button>
                                    <a href="?page=user" class="btn btn-danger">Kembali</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
<?php
        break;

    case 'ubah': // Form ubah user (Update)
        $id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

        // Ambil data user yang akan diubah
        $query_data = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user = '$id'");
        $data_user = mysqli_fetch_array($query_data);

        if (!$data_user) {
            echo '<script>alert("Data user tidak ditemukan!"); window.location="?page=user";</script>';
            exit;
        }

        if (isset($_POST['submit_ubah'])) {
            $username = mysqli_real_escape_string($koneksi, $_POST['username']);
            $email = mysqli_real_escape_string($koneksi, $_POST['email']);
            $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama']);
            $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
            $level = mysqli_real_escape_string($koneksi, $_POST['level']);

            // Cek username unik (kecuali untuk user yang sedang diubah)
            $check_username_edit = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username' AND id_user != '$id'");
            if (mysqli_num_rows($check_username_edit) > 0) {
                echo '<script>alert("Username sudah ada!");</script>';
            } else {
                $query_update = "UPDATE user SET username = '$username', email = '$email', nama = '$nama', alamat = '$alamat', level = '$level' WHERE id_user = '$id'";
                if (!empty($_POST['password'])) { // Update password hanya jika diisi
                    $password = md5($_POST['password']);
                    $query_update = "UPDATE user SET username = '$username', password = '$password', email = '$email', nama = '$nama', alamat = '$alamat', level = '$level' WHERE id_user = '$id'";
                }

                $execute_update = mysqli_query($koneksi, $query_update);

                if ($execute_update) {
                    echo '<script>alert("User berhasil diubah!"); window.location="?page=user";</script>';
                } else {
                    echo '<script>alert("Gagal mengubah user: ' . mysqli_error($koneksi) . '");</script>';
                }
            }
        }
?>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post">
                            <div class="row mb-3">
                                <div class="col-md-2">Username</div>
                                <div class="col-md-8"><input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($data_user['username']); ?>" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Password</div>
                                <div class="col-md-8"><input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah password"></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Email</div>
                                <div class="col-md-8"><input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($data_user['email']); ?>" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Nama Lengkap</div>
                                <div class="col-md-8"><input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($data_user['nama']); ?>" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Alamat</div>
                                <div class="col-md-8"><textarea name="alamat" rows="5" class="form-control" required><?php echo htmlspecialchars($data_user['alamat']); ?></textarea></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">Level</div>
                                <div class="col-md-8">
                                    <select name="level" class="form-control" required>
                                        <option value="admin" <?php echo ($data_user['level'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        <option value="petugas" <?php echo ($data_user['level'] == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                                        <option value="peminjam" <?php echo ($data_user['level'] == 'peminjam') ? 'selected' : ''; ?>>Peminjam</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"></div>
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary" name="submit_ubah">Simpan Perubahan</button>
                                    <a href="?page=user" class="btn btn-danger">Kembali</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
<?php
        break;

    case 'hapus': // Hapus user (Delete)
        // Cek apakah user memiliki riwayat peminjaman
        $id_user = $_GET['id'];
        $cek_peminjaman = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user = $id_user");
        $data = mysqli_fetch_assoc($cek_peminjaman);

    if ($data['total'] > 0) {
    echo "<script>alert('User tidak bisa dihapus karena masih memiliki riwayat peminjaman.'); window.location.href='?page=user';</script>";
} else {
    mysqli_query($koneksi, "DELETE FROM user WHERE id_user = $id_user");
    echo "<script>window.location.href='?page=user';</script>";
}

        // Pastikan untuk langsung keluar setelah redirect
        exit;
        break;

    default: // Default: kembali ke daftar user
        echo '<script>window.location="?page=user";</script>';
        exit;
        break;
}
?>