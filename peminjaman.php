<?php
include "koneksi.php";

// Redirect jika belum login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id_session = $_SESSION['user']['id_user'];
$user_level = $_SESSION['user']['level'];

?>
<h1 class="mt-4">Data Peminjaman</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <?php
                // Tombol "Tambah Peminjaman" hanya untuk Admin dan Petugas
                if ($user_level == 'admin' || $user_level == 'petugas') {
                ?>
                    <a href="?page=peminjaman_tambah" class="btn btn-primary mb-3">+ Tambah Peminjaman</a>
                <?php } ?>

                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Judul Buku</th>
                            <th>Tanggal Peminjaman</th>
                            <th>Tanggal Pengembalian</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        $query_peminjaman = "";

                        // Query untuk Admin/Petugas (tampilkan semua peminjaman)
                        if ($user_level == 'admin' || $user_level == 'petugas') {
                            $query_peminjaman = mysqli_query($koneksi, "SELECT
                                                                    p.*,
                                                                    u.username AS nama_peminjam,
                                                                    b.judul AS judul_buku
                                                                FROM
                                                                    peminjaman p
                                                                JOIN
                                                                    user u ON p.id_user = u.id_user
                                                                JOIN
                                                                    buku b ON p.id_buku = b.id_buku
                                                                ORDER BY p.tanggal_peminjaman DESC");
                        }
                        // Query untuk Peminjam (tampilkan hanya peminjamannya sendiri)
                        else if ($user_level == 'peminjam') {
                            $query_peminjaman = mysqli_query($koneksi, "SELECT
                                                                    p.*,
                                                                    u.username AS nama_peminjam,
                                                                    b.judul AS judul_buku
                                                                FROM
                                                                    peminjaman p
                                                                JOIN
                                                                    user u ON p.id_user = u.id_user
                                                                JOIN
                                                                    buku b ON p.id_buku = b.id_buku
                                                                WHERE p.id_user = '$user_id_session'
                                                                ORDER BY p.tanggal_peminjaman DESC");
                        }

                        if (empty($query_peminjaman) || !$query_peminjaman) {
                            echo "<tr><td colspan='7'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                        } else if (mysqli_num_rows($query_peminjaman) == 0) {
                            echo "<tr><td colspan='7'>Belum ada data peminjaman.</td></tr>";
                        } else {
                            while ($data_peminjaman = mysqli_fetch_array($query_peminjaman)) {
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($data_peminjaman['nama_peminjam']); ?></td>
                                    <td><?php echo htmlspecialchars($data_peminjaman['judul_buku']); ?></td>
                                    <td><?php echo htmlspecialchars($data_peminjaman['tanggal_peminjaman']); ?></td>
                                    <td>
                                        <?php
                                        // Tampilkan tanggal pengembalian jika sudah ada, atau status "Belum Dikembalikan"
                                        if ($data_peminjaman['tanggal_pengembalian'] != null && $data_peminjaman['tanggal_pengembalian'] != '0000-00-00' && $data_peminjaman['tanggal_pengembalian'] != '') {
                                            echo htmlspecialchars($data_peminjaman['tanggal_pengembalian']);
                                        } else {
                                            echo "Belum Dikembalikan";
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(ucfirst($data_peminjaman['status_peminjaman'])); ?></td>
                                    <td>
                                        <?php if (($user_level == 'admin' || $user_level == 'petugas')) {
                                            if ($data_peminjaman['status_peminjaman'] == 'dipinjam') { ?>
                                                <a href="?page=peminjaman_kembalikan&id=<?php echo $data_peminjaman['id_peminjaman']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin ingin mengembalikan buku ini?');">Kembalikan</a>
                                            <?php } else { // Jika sudah dikembalikan, admin/petugas bisa menghapus riwayatnya
                                                ?>
                                                <a href="?page=peminjaman_hapus&id=<?php echo $data_peminjaman['id_peminjaman']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?');">Hapus</a>
                                            <?php }
                                        } ?>
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