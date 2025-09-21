<?php
include "koneksi.php";

if (!isset($_GET['id'])) {
    echo "<script>alert('ID Ulasan tidak ditemukan'); window.location='?page=ulasan';</script>";
    exit;
}

$id_ulasan = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data ulasan yang akan diubah
$query_ulasan_edit = mysqli_query($koneksi, "SELECT * FROM ulasan WHERE id_ulasan=$id_ulasan");
$data_ulasan_edit = mysqli_fetch_array($query_ulasan_edit);

if (!$data_ulasan_edit) {
    echo "<script>alert('Data ulasan tidak ditemukan!'); window.location='?page=ulasan';</script>";
    exit;
}

// Cek hak akses:
// ADMIN/PETUGAS bisa mengubah semua ulasan.
// PEMINJAM hanya bisa mengubah ulasan yang dia buat sendiri.
if (isset($_SESSION['user'])) {
    $user_level = $_SESSION['user']['level'];
    $user_id_session = $_SESSION['user']['id_user']; // ID user yang sedang login

    if (($user_level == 'peminjam' && $user_id_session != $data_ulasan_edit['id_user']) && ($user_level != 'admin' && $user_level != 'petugas')) {
        echo "<script>alert('Anda tidak memiliki izin untuk mengubah ulasan ini!'); window.location='?page=ulasan';</script>";
        exit;
    }
} else {
    // Jika tidak login sama sekali
    echo "<script>alert('Anda harus login untuk mengubah ulasan!'); window.location='login.php';</script>";
    exit;
}


// Jika form disubmit
if (isset($_POST['submit'])) {
    $id_buku = mysqli_real_escape_string($koneksi, $_POST['id_buku']);
    $ulasan = mysqli_real_escape_string($koneksi, $_POST['ulasan']);
    $rating = mysqli_real_escape_string($koneksi, $_POST['rating']);

    $query_update = mysqli_query($koneksi, "UPDATE ulasan SET
                                            id_buku='$id_buku',
                                            ulasan='$ulasan',
                                            rating='$rating'
                                            WHERE id_ulasan=$id_ulasan");

    if ($query_update) {
        echo '<script>alert("Ulasan berhasil diubah!"); window.location="?page=ulasan";</script>';
    } else {
        echo '<script>alert("Gagal mengubah ulasan: ' . mysqli_error($koneksi) . '");</script>';
    }
}

// Ambil daftar buku untuk dropdown
$query_buku_option = mysqli_query($koneksi, "SELECT id_buku, judul FROM buku ORDER BY judul ASC");
?>

<h1 class="mt-4">Ubah Ulasan Buku</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-2">Pilih Buku</div>
                        <div class="col-md-8">
                            <select name="id_buku" class="form-control" required>
                                <option value="">-- Pilih Buku --</option>
                                <?php while ($b = mysqli_fetch_array($query_buku_option)) { ?>
                                    <option value="<?php echo $b['id_buku']; ?>"
                                        <?php if ($b['id_buku'] == $data_ulasan_edit['id_buku']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($b['judul']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Ulasan Anda</div>
                        <div class="col-md-8">
                            <textarea name="ulasan" class="form-control" rows="5" required><?php echo htmlspecialchars($data_ulasan_edit['ulasan']); ?></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Rating (1-5)</div>
                        <div class="col-md-8">
                            <input type="number" name="rating" class="form-control" value="<?php echo htmlspecialchars($data_ulasan_edit['rating']); ?>" min="1" max="5" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary" name="submit" value="submit">Simpan Perubahan</button>
                            <a href="?page=ulasan" class="btn btn-danger">Kembali</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>