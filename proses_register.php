<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_depan = mysqli_real_escape_string($koneksi, $_POST['nama_depan']);
    $nama_belakang = mysqli_real_escape_string($koneksi, $_POST['nama_belakang']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    
    // Validasi data
    if (empty($nama_depan) || empty($nama_belakang) || empty($email) || empty($username) || empty($password)) {
        header("Location: register.php?error=empty");
        exit();
    }
    
    // Validasi panjang password
    if (strlen($password) < 6) {
        header("Location: register.php?error=password");
        exit();
    }
    
    // Cek apakah username sudah ada
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        header("Location: register.php?error=username");
        exit();
    }
    
    // Cek apakah email sudah ada
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        header("Location: register.php?error=email");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Simpan data ke database
    $query = "INSERT INTO users (nama_depan, nama_belakang, email, username, password) 
              VALUES ('$nama_depan', '$nama_belakang', '$email', '$username', '$hashed_password')";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: login.php?success=register");
        exit();
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($koneksi);
    }
}
?>