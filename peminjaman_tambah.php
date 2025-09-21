    <?php
    include "koneksi.php";

    // Redirect jika belum login atau bukan admin/petugas
    if (!isset($_SESSION['user']) || ($_SESSION['user']['level'] != 'admin' && $_SESSION['user']['level'] != 'petugas')) {
        echo "<script>alert('Anda tidak memiliki izin untuk mengakses halaman ini!'); window.location='?page=peminjaman';</script>";
        exit;
    }

    if (isset($_POST['submit'])) {
        $id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
        $id_buku = mysqli_real_escape_string($koneksi, $_POST['id_buku']);
        $tanggal_peminjaman = date('Y-m-d'); // Tanggal peminjaman otomatis hari ini

        // Cek apakah buku tersedia (Anda bisa menambahkan kolom stok di tabel buku)
        // Misalnya, ambil stok buku
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM buku WHERE id_buku = '$id_buku'");
        $data_stok = mysqli_fetch_array($cek_stok);

        if ($data_stok && $data_stok['stok'] > 0) {
            // Kurangi stok buku
            mysqli_query($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '$id_buku'");

            // Insert data peminjaman
            $query_insert = mysqli_query($koneksi, "INSERT INTO peminjaman (id_user, id_buku, tanggal_peminjaman, status_peminjaman)
                                                    VALUES ('$id_user', '$id_buku', '$tanggal_peminjaman', 'dipinjam')");

            if ($query_insert) {
                echo '<script>alert("Peminjaman berhasil ditambahkan!"); window.location="?page=peminjaman";</script>';
            } else {
                echo '<script>alert("Gagal menambahkan peminjaman: ' . mysqli_error($koneksi) . '");</script>';
            }
        } else {
            echo '<script>alert("Buku tidak tersedia atau stok habis!");</script>';
        }
    }

    // Ambil daftar user level peminjam untuk dropdown
    $query_users = mysqli_query($koneksi, "SELECT id_user, username FROM user WHERE level = 'peminjam' ORDER BY username ASC");

    // Ambil daftar buku yang tersedia (stok > 0, jika ada kolom stok) untuk dropdown
    $query_buku = mysqli_query($koneksi, "SELECT id_buku, judul FROM buku ORDER BY judul ASC");
    ?>

    <h1 class="mt-4">Tambah Peminjaman</h1>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <form method="post">
                        <div class="row mb-3">
                            <div class="col-md-2">Peminjam</div>
                            <div class="col-md-8">
                                <select name="id_user" class="form-control" required>
                                    <option value="">-- Pilih Peminjam --</option>
                                    <?php while ($u = mysqli_fetch_array($query_users)) { ?>
                                        <option value="<?php echo $u['id_user']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-2">Judul Buku</div>
                            <div class="col-md-8">
                                <select name="id_buku" class="form-control" required>
                                    <option value="">-- Pilih Buku --</option>
                                    <?php while ($b = mysqli_fetch_array($query_buku)) { ?>
                                        <option value="<?php echo $b['id_buku']; ?>"><?php echo htmlspecialchars($b['judul']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-primary" name="submit" value="submit">Simpan Peminjaman</button>
                                <a href="?page=peminjaman" class="btn btn-danger">Kembali</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>