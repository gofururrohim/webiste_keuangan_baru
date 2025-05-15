<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Proses tambah transaksi
if (isset($_POST['tambah_transaksi'])) {
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $jumlah = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    
    // Validasi data
    if (empty($tanggal) || empty($deskripsi) || empty($jumlah) || empty($jenis)) {
        header("Location: dashboard.php?page=transaksi&error=empty");
        exit();
    }
    
    // Validasi jumlah
    if (!is_numeric($jumlah) || $jumlah <= 0) {
        header("Location: dashboard.php?page=transaksi&error=jumlah");
        exit();
    }
    
    // Simpan transaksi ke database
    $query = "INSERT INTO transaksi (user_id, tanggal, deskripsi, jumlah, jenis) 
              VALUES ('$user_id', '$tanggal', '$deskripsi', '$jumlah', '$jenis')";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: dashboard.php?page=transaksi&success=add");
        exit();
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($koneksi);
    }
}
?>