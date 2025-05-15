<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    
    // Validasi data
    if (empty($username) || empty($password)) {
        header("Location: login.php?error=empty");
        exit();
    }
    
    // Cek username
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Buat session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.php?error=invalid");
            exit();
        }
    } else {
        header("Location: login.php?error=invalid");
        exit();
    }
}
?>