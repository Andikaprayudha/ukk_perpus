<?php
include "koneksi.php"; // Pastikan koneksi.php ada di level yang sama dengan buku_tambah.php

// Cek apakah form sudah disubmit
if (isset($_POST['submit'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun_terbit = mysqli_real_escape_string($koneksi, $_POST['tahun_terbit']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);

    // Query untuk menyimpan data buku ke database
    $query = mysqli_query($koneksi, "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, deskripsi, id_kategori)
                                    VALUES ('$judul', '$penulis', '$penerbit', '$tahun_terbit', '$deskripsi', '$id_kategori')");

    if ($query) {
        echo '<script>alert("Tambah data buku berhasil"); window.location="?page=buku";</script>';
    } else {
        echo '<script>alert("Tambah data buku gagal: ' . mysqli_error($koneksi) . '");</script>';
    }
}

// Ambil data kategori dari database untuk ditampilkan di dropdown
$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori");
?>

<h1 class="mt-4">Tambah Data Buku</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-2">Kategori Buku</div>
                        <div class="col-md-8">
                            <select name="id_kategori" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                while ($k = mysqli_fetch_array($query_kategori)) {
                                    echo '<option value="' . $k['id_kategori'] . '">' . htmlspecialchars($k['kategori']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Judul</div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="judul" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Penulis</div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="penulis" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Penerbit</div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="penerbit" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Tahun Terbit</div>
                        <div class="col-md-8">
                            <input type="number" class="form-control" name="tahun_terbit" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Deskripsi</div>
                        <div class="col-md-8">
                            <textarea class="form-control" name="deskripsi" rows="5" required></textarea>
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