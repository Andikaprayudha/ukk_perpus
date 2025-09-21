<?php
include "koneksi.php";

// Cek jika ID tidak diberikan
if (!isset($_GET['id'])) {
    echo "<script>alert('ID Kategori tidak ditemukan'); window.location='?page=kategori';</script>";
    exit;
}

$id = $_GET['id'];

// Jika form disubmit
if (isset($_POST['submit'])) {
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $query = mysqli_query($koneksi, "UPDATE kategori SET kategori='$kategori' WHERE id_kategori=$id");

    if ($query) {
        echo '<script>alert("Ubah data berhasil"); window.location="?page=kategori";</script>';
    } else {
        echo '<script>alert("Ubah data gagal: ' . mysqli_error($koneksi) . '");</script>';
    }
}

// Ambil data kategori berdasarkan ID
$query = mysqli_query($koneksi, "SELECT * FROM kategori WHERE id_kategori=$id");
$data = mysqli_fetch_array($query);
?>

<h1 class="mt-4">Ubah Kategori Buku</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-2">Nama Kategori</div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['kategori']); ?>" name="kategori" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary" name="submit" value="submit">Simpan</button>
                            <a href="?page=kategori" class="btn btn-danger">Kembali</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
