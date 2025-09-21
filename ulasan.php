<?php
include "koneksi.php"; // Pastikan koneksi.php sudah di-include

// Jika Anda masih debugging session, bisa aktifkan ini sementara:
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// echo '<pre>'; print_r($_SESSION); echo '</pre>';

?>
<h1 class="mt-4">Ulasan Buku</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <?php if (isset($_SESSION['user']) && ($_SESSION['user']['level'] == 'admin' || $_SESSION['user']['level'] == 'petugas' || $_SESSION['user']['level'] == 'peminjam')) { ?>
                    <a href="?page=ulasan_tambah" class="btn btn-primary">+ Tambah Ulasan</a>
                <?php } ?>

                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Buku</th>
                            <th>Ulasan</th>
                            <th>Rating</th>
                            <th>Tanggal Ulasan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        // Query untuk mengambil data ulasan
                        // Melakukan JOIN untuk mendapatkan nama user dan judul buku
                        // Menggunakan alias u_ulasan untuk tabel ulasan sesuai dengan diskusi sebelumnya
                        $query_ulasan = mysqli_query($koneksi, "SELECT
                                                            u_ulasan.*,
                                                            u.username AS nama_user,
                                                            b.judul AS judul_buku
                                                        FROM
                                                            ulasan u_ulasan
                                                        JOIN
                                                            user u ON u_ulasan.id_user = u.id_user
                                                        JOIN
                                                            buku b ON u_ulasan.id_buku = b.id_buku");

                        if (!$query_ulasan) {
                            echo "<tr><td colspan='7'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                        } else {
                            while ($data_ulasan = mysqli_fetch_array($query_ulasan)) {
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($data_ulasan['nama_user']); ?></td>
                                    <td><?php echo htmlspecialchars($data_ulasan['judul_buku']); ?></td>
                                    <td><?php echo htmlspecialchars($data_ulasan['ulasan']); ?></td>
                                    <td><?php echo htmlspecialchars($data_ulasan['rating']); ?></td>
                                    <td><?php echo htmlspecialchars($data_ulasan['tanggal_ulasan']); ?></td>
                                    <td>
                                        <?php
                                        if (isset($_SESSION['user'])) {
                                            $user_level = $_SESSION['user']['level'];
                                            $user_id_session = $_SESSION['user']['id_user']; // ID user yang sedang login

                                            // ADMIN dan PETUGAS bisa mengubah/menghapus SEMUA ulasan
                                            if ($user_level == 'admin' || $user_level == 'petugas') {
                                        ?>
                                                <a href="?page=ulasan_ubah&id=<?php echo $data_ulasan['id_ulasan']; ?>" class="btn btn-info btn-sm">Ubah</a>
                                                <a href="?page=ulasan_hapus&id=<?php echo $data_ulasan['id_ulasan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">Hapus</a>
                                        <?php
                                            // PEMINJAM hanya bisa mengubah/menghapus ulasannya SENDIRI
                                            } else if ($user_level == 'peminjam' && $user_id_session == $data_ulasan['id_user']) {
                                        ?>
                                                <a href="?page=ulasan_ubah&id=<?php echo $data_ulasan['id_ulasan']; ?>" class="btn btn-info btn-sm">Ubah</a>
                                                <a href="?page=ulasan_hapus&id=<?php echo $data_ulasan['id_ulasan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">Hapus</a>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>