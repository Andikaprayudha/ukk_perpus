<?php
include "koneksi.php";
// Pastikan koneksi.php sudah di-include di file utama (index.php)
// atau bisa juga di-include di sini jika buku.php diakses langsung
// include "koneksi.php"; // Uncomment jika buku.php diakses langsung

?>
<h1 class="mt-4">Buku</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <a href="?page=buku_tambah" class="btn btn-primary">+ Tambah Data Buku</a>
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tahun Terbit</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        // Sesuaikan query ini dengan nama tabel dan kolom di database Anda
                        // Pastikan ada JOIN jika ingin menampilkan nama kategori dari tabel lain
                        $query_buku = mysqli_query($koneksi, "SELECT b.*, k.kategori AS nama_kategori
                                                               FROM buku b
                                                               LEFT JOIN kategori k ON b.id_kategori = k.id_kategori");

                        if (!$query_buku) {
                            echo "<tr><td colspan='8'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                        } else {
                            while ($data_buku = mysqli_fetch_array($query_buku)) {
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($data_buku['nama_kategori']); ?></td>
                                    <td><?php echo htmlspecialchars($data_buku['judul']); ?></td>
                                    <td><?php echo htmlspecialchars($data_buku['penulis']); ?></td>
                                    <td><?php echo htmlspecialchars($data_buku['penerbit']); ?></td>
                                    <td><?php echo htmlspecialchars($data_buku['tahun_terbit']); ?></td>
                                    <td><?php echo htmlspecialchars($data_buku['deskripsi']); ?></td>
                                    <td>
                                        <a href="?page=buku_ubah&id=<?php echo $data_buku['id_buku']; ?>" class="btn btn-info btn-sm">Ubah</a>
                                        <a href="?page=buku_hapus&id=<?php echo $data_buku['id_buku']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">Hapus</a>

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