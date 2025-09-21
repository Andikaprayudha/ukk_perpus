<?php
include "koneksi.php";

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $no_telepon = $_POST['no_telepon'];
    $alamat = $_POST['alamat'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $query = "INSERT INTO user (username, email, no_telepon, alamat, password, role) 
              VALUES ('$username', '$email', '$no_telepon', '$alamat', '$password', '$role')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo '<script>alert("Registrasi berhasil! Silakan login."); location.href="login.php";</script>';
    } else {
        echo '<script>alert("Registrasi gagal.");</script>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            background-color: #e3f2fd;
            font-family: Arial;
        }
        .container {
            width: 400px;
            margin: 100px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #90caf9;
        }
        .btn {
            background-color: #2196f3;
            color: #fff;
            padding: 10px;
            width: 100%;
            border: none;
            margin-top: 10px;
        }
        .role-select {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register Akun</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Nama Lengkap" required class="form-control"><br>
            <input type="email" name="email" placeholder="Email" required class="form-control"><br>
            <input type="text" name="no_telepon" placeholder="No. Telepon" required class="form-control"><br>
            <textarea name="alamat" placeholder="Alamat" class="form-control" rows="3" required></textarea><br>
            <input type="password" name="password" placeholder="Password" required class="form-control"><br>
            <div class="role-select">
                <label><input type="radio" name="role" value="admin" required> Admin</label>
                <label><input type="radio" name="role" value="peminjam"> Peminjam Buku</label>
            </div>
            <button type="submit" name="register" class="btn">Daftar</button>
        </form>
    </div>
</body>
</html>
