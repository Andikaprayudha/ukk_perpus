<?php
include "koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Anda harus login untuk memberi ulasan!'); window.location='login.php';</script>";
    exit;
}

// Opsional: Jika Anda ingin membatasi siapa yang bisa menambah ulasan, Anda bisa tambahkan cek level di sini juga.
// Misalnya, jika hanya peminjam yang boleh menambah ulasan, uncomment baris di bawah:
// if ($_SESSION['user']['level'] != 'peminjam' && $_SESSION['user']['level'] != 'admin' && $_SESSION['user']['level'] != 'petugas') {
//     echo "<script>alert('Anda tidak memiliki izin untuk menambah ulasan!'); window.location='?page=ulasan';</script>";
//     exit;
// }


$id_user = mysqli_real_escape_string($koneksi, $_SESSION['user']['id_user']); // Ambil ID user yang sedang login

if (isset($_POST['submit'])) {
    $id_buku = mysqli_real_escape_string($koneksi, $_POST['id_buku']);
    $ulasan = mysqli_real_escape_string($koneksi, $_POST['ulasan']);
    $rating = mysqli_real_escape_string($koneksi, $_POST['rating']);

    // Query untuk menyimpan ulasan ke tabel 'ulasan'
    $query_insert = mysqli_query($koneksi, "INSERT INTO ulasan (id_user, id_buku, ulasan, rating)
                                            VALUES ('$id_user', '$id_buku', '$ulasan', '$rating')");

    if ($query_insert) {
        echo '<script>alert("Ulasan berhasil ditambahkan!"); window.location="?page=ulasan";</script>';
    } else {
        echo '<script>alert("Gagal menambahkan ulasan: ' . mysqli_error($koneksi) . '");</script>';
    }
}

// Ambil daftar buku untuk dropdown
$query_buku = mysqli_query($koneksi, "SELECT id_buku, judul FROM buku ORDER BY judul ASC");
?>

<h1 class="mt-4">Tambah Ulasan Buku</h1>
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
                                <?php while ($b = mysqli_fetch_array($query_buku)) { ?>
                                    <option value="<?php echo $b['id_buku']; ?>"><?php echo htmlspecialchars($b['judul']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Ulasan Anda</div>
                        <div class="col-md-8">
                            <textarea name="ulasan" class="form-control" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Rating (1-10)</div>
                        <div class="col-md-8">
                            <input type="number" name="rating" class="form-control" min="1" max="10" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary" name="submit" value="submit">Simpan Ulasan</button>
                            <a href="?page=ulasan" class="btn btn-danger">Kembali</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>