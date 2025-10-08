<?php
require_once '../includes/config.php';

// Cek role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../login.php');
}

// Set admin flag
$isAdmin = true;

include '../includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Data Anggota</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil data anggota
                        $query = "SELECT * FROM users WHERE role = 'anggota' ORDER BY created_at DESC";
                        $result = mysqli_query($conn, $query);
                        $no = 1;

                        while ($anggota = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($anggota['nama']) . "</td>";
                            echo "<td>" . htmlspecialchars($anggota['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($anggota['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($anggota['telepon']) . "</td>";
                            echo "<td>" . htmlspecialchars($anggota['alamat']) . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($anggota['created_at'])) . "</td>";
                            echo "</tr>";
                        }

                        if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='7' class='text-center'>Belum ada anggota terdaftar</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>