<?php
include "koneksi.php"; // Pastikan koneksi.php ada di level yang sama

// Cek jika ID tidak diberikan
if (!isset($_GET['id'])) {
    echo "<script>alert('ID Buku tidak ditemukan'); window.location='?page=buku';</script>";
    exit;
}

$id_buku = $_GET['id']; // Ambil ID buku dari URL

// Ambil data buku yang akan diubah
$query_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku='$id_buku'");
$data_buku = mysqli_fetch_assoc($query_buku);

// Jika buku tidak ditemukan
if (!$data_buku) {
    echo "<script>alert('Buku tidak ditemukan'); window.location='?page=buku';</script>";
    exit;
}

// Jika form disubmit (untuk proses update)
if (isset($_POST['submit'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun_terbit = mysqli_real_escape_string($koneksi, $_POST['tahun_terbit']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']); // Ambil ID Kategori dari form
    
    // Cek apakah kolom file_buku ada di tabel buku
    $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM buku LIKE 'file_buku'");
    $column_exists = mysqli_num_rows($check_column) > 0;

    // Inisialisasi variabel untuk file buku
    $file_buku = $column_exists ? $data_buku['file_buku'] : null; // Gunakan file yang sudah ada jika kolom ada
    $upload_error = "";
    
    // Cek apakah ada file baru yang diunggah dan kolom file_buku ada
    if($column_exists && isset($_FILES['file_buku']) && $_FILES['file_buku']['error'] == 0) {
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
            // Hapus file lama jika ada
            if(!empty($data_buku['file_buku']) && file_exists($data_buku['file_buku'])) {
                unlink($data_buku['file_buku']);
            }
            
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
    
    // Jika tidak ada error upload, update data ke database
    if(empty($upload_error)) {
        // Bangun query UPDATE secara dinamis
        $update_fields = [
            "judul='$judul'",
            "penulis='$penulis'",
            "penerbit='$penerbit'",
            "tahun_terbit='$tahun_terbit'",
            "deskripsi='$deskripsi'",
            "id_kategori='$id_kategori'"
        ];

        if ($column_exists) {
            $update_fields[] = "file_buku='$file_buku'";
        }

        $query_update = mysqli_query($koneksi, "UPDATE buku SET " . implode(", ", $update_fields) . " WHERE id_buku='$id_buku'");
                                                WHERE id_buku=$id_buku");

        if ($query_update) {
            echo '<script>alert("Ubah data buku berhasil"); window.location="?page=buku";</script>';
        } else {
            echo '<script>alert("Ubah data buku gagal: ' . mysqli_error($koneksi) . '");</script>';
        }
    } else {
        echo '<script>alert("Error: ' . $upload_error . '");</script>';
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
                <form method="post" enctype="multipart/form-data">
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
                    <div class="row mb-3">
                        <div class="col-md-2">File Buku</div>
                        <div class="col-md-8">
                            <input type="file" class="form-control" name="file_buku">
                            <small class="text-muted">Format yang didukung: PDF, EPUB, DOC, DOCX. Ukuran maksimal: 10MB.</small>
                            <?php if(!empty($data_buku['file_buku'])): ?>
                                <div class="mt-2">
                                    <p>File saat ini: <a href="<?php echo $data_buku['file_buku']; ?>" target="_blank"><?php echo basename($data_buku['file_buku']); ?></a></p>
                                </div>
                            <?php endif; ?>
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