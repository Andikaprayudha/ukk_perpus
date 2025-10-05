<?php
require_once 'includes/config.php';

// Cek jika user sudah login
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = clean($_POST['nama']);
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = clean($_POST['email']);
    $alamat = clean($_POST['alamat']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = 'Nama harus diisi';
    }
    
    if (empty($username)) {
        $errors[] = 'Username harus diisi';
    } else {
        // Cek username sudah digunakan atau belum
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $errors[] = 'Username sudah digunakan';
        }
    }
    
    if (empty($password)) {
        $errors[] = 'Password harus diisi';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Konfirmasi password tidak sesuai';
    }
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    } else {
        // Cek email sudah digunakan atau belum
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $errors[] = 'Email sudah digunakan';
        }
    }
    
    // Jika tidak ada error, simpan data user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (nama, username, password, email, alamat, role) 
                  VALUES ('$nama', '$username', '$hashed_password', '$email', '$alamat', 'anggota')";
        
        if (mysqli_query($conn, $query)) {
            setMessage('Registrasi berhasil, silahkan login', 'success');
            redirect('login.php');
        } else {
            setMessage('Terjadi kesalahan: ' . mysqli_error($conn), 'danger');
        }
    } else {
        // Tampilkan error
        $error_message = implode('<br>', $errors);
        setMessage($error_message, 'danger');
    }
}

include 'includes/header.php';
?>

<div style="max-width: 600px; margin: 2rem auto; padding: 2rem; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
    <h2 style="text-align: center; margin-bottom: 1.5rem;">Register</h2>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
            <small style="color: #6c757d;">Password minimal 6 karakter</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea name="alamat" id="alamat" class="form-control" rows="3"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Register</button>
    </form>
    
    <p style="text-align: center; margin-top: 1rem;">
        Sudah punya akun? <a href="login.php">Login</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>