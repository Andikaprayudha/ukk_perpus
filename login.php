<?php
require_once 'includes/config.php';

// Cek jika user sudah login
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($username) || empty($password)) {
        setMessage('Username dan password harus diisi', 'danger');
    } else {
        // Cek user di database
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect berdasarkan role
                if ($user['role'] == 'admin' || $user['role'] == 'petugas') {
                    redirect('admin/index.php');
                } else {
                    redirect('index.php');
                }
            } else {
                setMessage('Password salah', 'danger');
            }
        } else {
            setMessage('Username tidak ditemukan', 'danger');
        }
    }
}

include 'includes/header.php';
?>

<div style="max-width: 500px; margin: 2rem auto; padding: 2rem; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
    <h2 style="text-align: center; margin-bottom: 1.5rem;">Login</h2>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 1rem;">
        Belum punya akun? <a href="register.php">Register</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>