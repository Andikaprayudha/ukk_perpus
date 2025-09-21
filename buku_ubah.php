<?php
include "koneksi.php"; // Pastikan koneksi.php ada di level yang sama

// Cek jika ID tidak diberikan
if (!isset($_GET['id'])) {
    echo "<script>alert('ID Buku tidak ditemukan'); window.location='?page=buku';</script>";
    exit;
}

$id_buku = $_GET['id']; // Ambil ID buku dari URL

// Jika form disubmit (untuk proses update)
if (isset($_POST['submit'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun_terbit = mysqli_real_escape_string($koneksi, $_POST['tahun_terbit']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']); // Ambil ID Kategori dari form

    // Update data buku di database
    $query_update = mysqli_query($koneksi, "UPDATE buku SET
                                            judul='$judul',
                                            penulis='$penulis',
                                            penerbit='$penerbit',
                                            tahun_terbit='$tahun_terbit',
                                            deskripsi='$deskripsi',
                                            id_kategori='$id_kategori'
                                            WHERE id_buku=$id_buku");

    if ($query_update) {
        echo '<script>alert("Ubah data buku berhasil"); window.location="?page=buku";</script>';
    } else {
        echo '<script>alert("Ubah data buku gagal: ' . mysqli_error($koneksi) . '");</script>';
    }
}

// Ambil data buku berdasarkan ID untuk ditampilkan di form
$query_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku=$id_buku");
$data_buku = mysqli_fetch_array($query_buku);

// Ambil data kategori untuk dropdown pilihan
$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori");
?>

<h1 class="mt-4">Ubah Data Buku</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-2">Kategori Buku</div>
                        <div class="col-md-8">
                            <select name="id_kategori" class="form-control" required>
                                <?php while($k = mysqli_fetch_array($query_kategori)) { ?>
                                    <option value="<?php echo $k['id_kategori']; ?>"
                                        <?php if ($k['id_kategori'] == $data_buku['id_kategori']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($k['kategori']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Judul</div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data_buku['judul']); ?>" name="judul" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Penulis</div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data_buku['penulis']); ?>" name="penulis" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Penerbit</div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data_buku['penerbit']); ?>" name="penerbit" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Tahun Terbit</div>
                        <div class="col-md-8">
                            <input type="number" class="form-control" value="<?php echo htmlspecialchars($data_buku['tahun_terbit']); ?>" name="tahun_terbit" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Deskripsi</div>
                        <div class="col-md-8">
                            <textarea class="form-control" name="deskripsi" rows="5" required><?php echo htmlspecialchars($data_buku['deskripsi']); ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary" name="submit" value="submit">Simpan</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <a href="?page=buku" class="btn btn-danger">Kembali</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>