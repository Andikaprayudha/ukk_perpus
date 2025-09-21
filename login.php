<?php
session_start(); // TAMBAHKAN INI: Memulai sesi PHP
include "koneksi.php";

// Hapus bagian ini dari login.php:
// if (!isset($_SESSION['user'])) {
//     header('localhost:login.php');
// }

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']); // Tambahkan mysqli_real_escape_string
    $password = mysqli_real_escape_string($koneksi, $_POST['password']); // Tambahkan mysqli_real_escape_string

    // Catatan: Menyimpan password langsung (tanpa hashing) di database sangat tidak aman.
    // Untuk aplikasi produksi, Anda harus menggunakan password_hash() dan password_verify().
    $result = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['user'] = mysqli_fetch_array($result);
        header("Location: index.php"); // Arahkan ke index.php setelah berhasil login
        exit; // Sangat penting untuk menghentikan eksekusi script setelah header()
    } else {
        echo '<script>alert("Username atau password salah");</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login Perpustakaan</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-container {
      background: white;
      padding: 2.5rem 3rem;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      width: 350px;
    }
    h2 {
      color: #1e3c72;
      text-align: center;
      margin-bottom: 1.5rem;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px 15px;
      margin: 0.6rem 0 1.2rem 0;
      border: 1.8px solid #1e3c72;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }
    input[type="text"]:focus, input[type="password"]:focus {
      border-color: #2a5298;
      outline: none;
    }
    .input-group {
      position: relative;
    }
    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #1e3c72;
      font-size: 1rem;
    }
    button {
      background-color: #1e3c72;
      color: white;
      width: 100%;
      padding: 12px;
      font-size: 1.1rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    button:hover {
      background-color: #2a5298;
    }
    .register-link {
      text-align: center;
      margin-top: 1.2rem;
      color: #1e3c72;
    }
    .register-link a {
      color: #2a5298;
      text-decoration: none;
      font-weight: 600;
    }
    .register-link a:hover {
      text-decoration: underline;
    }
</style>
</head>
<body>
<div class="login-container">
    <h2>Login Perpustakaan</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required />
        
        <div class="input-group">
            <input type="password" id="password" name="password" placeholder="Password" required />
            <span class="toggle-password" onclick="togglePassword()">
                <i class="fa-solid fa-eye" id="eyeIcon"></i>
            </span>
        </div>

        <button type="submit" name="login">Login</button>
    </form>
    <div class="register-link">
        Belum punya akun? <a href="register.php">Register di sini</a>
    </div>
</div>

<script>
function togglePassword() {
    const password = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        password.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>