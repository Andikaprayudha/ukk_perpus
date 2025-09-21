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
    
    // Inisialisasi variabel untuk file buku
    $file_buku = "";
    $upload_error = "";
    
    // Cek apakah ada file yang diunggah
    if(isset($_FILES['file_buku']) && $_FILES['file_buku']['error'] == 0) {
        // Validasi ukuran file (maksimal 10MB)
        $max_size = 10 * 1024 * 1024; // 10MB dalam bytes
        if($_FILES['file_buku']['size'] > $max_size) {
            $upload_error = "Ukuran file terlalu besar. Maksimal 10MB.";
        }
        
        // Validasi tipe file
        $allowed_types = array('application/pdf', 'application/epub+zip', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $file_type = $_FILES['file_buku']['type'];
        
        if(!in_array($file_type, $allowed_types)) {
            $upload_error = "Format file tidak didukung. Format yang didukung: PDF, EPUB, DOC, DOCX.";
        }
        
        // Jika tidak ada error, proses upload file
        if(empty($upload_error)) {
            $upload_dir = "uploads/buku/";
            $file_name = time() . '_' . basename($_FILES['file_buku']['name']);
            $target_file = $upload_dir . $file_name;
            
            if(move_uploaded_file($_FILES['file_buku']['tmp_name'], $target_file)) {
                $file_buku = $target_file;
            } else {
                $upload_error = "Gagal mengunggah file.";
            }
        }
    }
    
    // Jika tidak ada error upload, simpan data ke database
    if(empty($upload_error)) {
        // Cek apakah kolom file_buku ada di tabel buku
        $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM buku LIKE 'file_buku'");
        $column_exists = mysqli_num_rows($check_column) > 0;
        
        // Query untuk menyimpan data buku ke database
        if ($column_exists) {
            $query = mysqli_query($koneksi, "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, deskripsi, id_kategori, file_buku)
                                            VALUES ('$judul', '$penulis', '$penerbit', '$tahun_terbit', '$deskripsi', '$id_kategori', '$file_buku')");
        } else {
            $query = mysqli_query($koneksi, "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, deskripsi, id_kategori)
                                            VALUES ('$judul', '$penulis', '$penerbit', '$tahun_terbit', '$deskripsi', '$id_kategori')");
        }

        if ($query) {
            echo '<script>alert("Tambah data buku berhasil"); window.location="?page=buku";</script>';
        } else {
            echo '<script>alert("Tambah data buku gagal: ' . mysqli_error($koneksi) . '");</script>';
        }
    } else {
        echo '<script>alert("Error: ' . $upload_error . '");</script>';
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
                <form method="post" enctype="multipart/form-data">
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
                    <div class="row mb-3">
                        <div class="col-md-2">File Buku</div>
                        <div class="col-md-8">
                            <input type="file" class="form-control" name="file_buku">
                            <small class="text-muted">Format yang didukung: PDF, EPUB, DOC, DOCX. Ukuran maksimal: 10MB.</small>
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