<?php
require_once '../includes/config.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('../login.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Validasi
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama harus diisi";
    }
    
    if (empty($username)) {
        $errors[] = "Username harus diisi";
    } else {
        // Cek apakah username sudah digunakan
        $check_query = "SELECT id FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "Username sudah digunakan";
        }
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    } else {
        // Cek apakah email sudah digunakan
        $check_query = "SELECT id FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "Email sudah digunakan";
        }
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if (empty($role) || ($role != 'admin' && $role != 'petugas')) {
        $errors[] = "Role tidak valid";
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (nama, username, email, password, role, created_at) 
                  VALUES ('$nama', '$username', '$email', '$hashed_password', '$role', NOW())";
        
        if (mysqli_query($conn, $query)) {
            setMessage('Petugas berhasil ditambahkan!', 'success');
            redirect('petugas.php');
        } else {
            $errors[] = "Gagal menambahkan petugas: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Petugas - Perpustakaan Online</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
            outline: none;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .text-muted {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background-color: #4a90e2;
            border: none;
            color: white;
        }
        .btn-primary:hover {
            background-color: #3a7bc8;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-body {
            padding: 2rem;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .dashboard-header h1 {
            margin: 0;
            font-size: 1.75rem;
            color: #333;
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert p {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div style="padding: 1rem; text-align: center;">
                <h2>Admin Panel</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="buku.php"><i class="fas fa-book"></i> Manajemen Buku</a></li>
                <li><a href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
                <li><a href="peminjaman.php"><i class="fas fa-clipboard-list"></i> Peminjaman</a></li>
                <li><a href="anggota.php"><i class="fas fa-users"></i> Anggota</a></li>
                <li><a href="petugas.php" class="active"><i class="fas fa-user-shield"></i> Petugas</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Tambah Petugas</h1>
                <div>
                    <a href="petugas.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            
            <?php
            if (isset($errors) && !empty($errors)) {
                echo '<div class="alert alert-error">';
                foreach ($errors as $error) {
                    echo '<p>' . $error . '</p>';
                }
                echo '</div>';
            }
            ?>
            
            <div class="card">
                <div class="card-body">
                    <div class="form-container">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="nama">Nama Lengkap</label>
                                <input type="text" id="nama" name="nama" class="form-control" value="<?= isset($_POST['nama']) ? $_POST['nama'] : '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" value="<?= isset($_POST['username']) ? $_POST['username'] : '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= isset($_POST['email']) ? $_POST['email'] : '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role" class="form-control" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                                    <option value="petugas" <?= (isset($_POST['role']) && $_POST['role'] == 'petugas') ? 'selected' : '' ?>>Petugas</option>
                                </select>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                                <a href="petugas.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>