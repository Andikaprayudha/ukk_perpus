<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <?php 
    $baseUrl = isset($isAdmin) && $isAdmin ? '../' : '';
    ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand fw-bold" href="index.php">
                    <i class="fas fa-book-reader me-2"></i>Perpustakaan Digital
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="katalog.php">Katalog</a>
                        </li>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'anggota'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="peminjaman_saya.php">Peminjaman Saya</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php
                            // Load notification functions if not loaded
                            $notifPath = isset($isAdmin) && $isAdmin ? '../includes/notifikasi.php' : 'includes/notifikasi.php';
                            require_once $notifPath;
                            
                            // Count unread notifications
                            $unread_count = countUnreadNotifications($conn, $_SESSION['user_id']);
                            ?>
                            
                            <li class="nav-item">
                                <a class="nav-link position-relative" href="notifikasi.php">
                                    <i class="fas fa-bell"></i>
                                    <?php if ($unread_count > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            <?= $unread_count ?>
                                            <span class="visually-hidden">notificações não lidas</span>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div class="d-flex">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <?= $_SESSION['nama'] ?? 'Pengguna' ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'petugas'): ?>
                                        <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user-cog me-2"></i>Profil</a></li>
                                    <li><a class="dropdown-item" href="notifikasi.php"><i class="fas fa-bell me-2"></i>Notifikasi <?= isset($unread_count) && $unread_count > 0 ? "<span class=\"badge bg-danger\">$unread_count</span>" : "" ?></a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-light me-2"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                            <a href="register.php" class="btn btn-outline-light"><i class="fas fa-user-plus me-1"></i>Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="py-4">
        <div class="container">
            <?php
            $message = getMessage();
            if ($message) {
                echo '<div class="alert alert-' . $message['type'] . ' alert-dismissible fade show" role="alert">
                    ' . $message['message'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
            ?>