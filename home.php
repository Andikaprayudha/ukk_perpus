<?php
// Pastikan koneksi.php sudah di-include di index.php atau di tempat lain yang diakses sebelum home.php
// Pastikan session_start() sudah dipanggil di awal index.php

// Inisialisasi variabel $koneksi (jika belum didefinisikan dari include "koneksi.php" di index.php)
if (!isset($koneksi)) {
    include "koneksi.php";
}

// Query untuk Total Kategori
$query_kategori = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kategori");
$data_kategori = mysqli_fetch_assoc($query_kategori);
$total_kategori = $data_kategori['total'];

// Query untuk Total Buku
$query_buku = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM buku");
$data_buku = mysqli_fetch_assoc($query_buku);
$total_buku = $data_buku['total'];

// Query untuk Total Ulasan (asumsi nama tabel 'ulasan')
$query_ulasan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM ulasan");
$data_ulasan = mysqli_fetch_assoc($query_ulasan);
$total_ulasan = $data_ulasan['total'];

// Query untuk Total User (asumsi nama tabel 'user')
$query_user = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM user");
$data_user = mysqli_fetch_assoc($query_user);
$total_user = $data_user['total'];

?>

<h1 class="mt-4">Dashboard</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <?php echo $total_kategori; ?>
                Total Kategori
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="?page=kategori">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">
                <?php echo $total_buku; ?>
                Total Buku
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="?page=buku">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <?php echo $total_ulasan; ?>
                Total Ulasan
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="?page=ulasan">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-danger text-white mb-4">
            <div class="card-body">
                <?php echo $total_user; ?>
                Total User
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="?page=user">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>