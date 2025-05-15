<?php
session_start();
// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Keuangan Pribadi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .hero {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .buttons {
            display: flex;
            gap: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: white;
            color: #3498db;
        }
        
        .btn-primary:hover {
            background-color: #f5f5f5;
            transform: translateY(-3px);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }
        
        .features {
            padding: 80px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .features h2 {
            text-align: center;
            margin-bottom: 50px;
            color: #2c3e50;
            font-size: 2rem;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-card h3 {
            color: #3498db;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            color: #555;
            line-height: 1.6;
        }
        
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <section class="hero">
        <h1>Kelola Keuangan Anda dengan Mudah</h1>
        <p>Aplikasi keuangan pribadi yang membantu Anda melacak pemasukan, pengeluaran, dan menganalisis pola keuangan Anda. Mulai perjalanan menuju kebebasan finansial hari ini!</p>
        <div class="buttons">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-secondary">Daftar</a>
        </div>
    </section>
    
    <section class="features">
        <h2>Fitur Utama</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h3>Lacak Transaksi</h3>
                <p>Catat semua pemasukan dan pengeluaran Anda dengan mudah. Kategorikan transaksi untuk analisis yang lebih baik.</p>
            </div>
            
            <div class="feature-card">
                <h3>Visualisasi Data</h3>
                <p>Lihat pola keuangan Anda melalui grafik dan diagram yang mudah dipahami.</p>
            </div>
            
            <div class="feature-card">
                <h3>Laporan Keuangan</h3>
                <p>Dapatkan laporan harian, mingguan, dan bulanan untuk memahami kebiasaan keuangan Anda.</p>
            </div>
            
            <div class="feature-card">
                <h3>Mudah Digunakan</h3>
                <p>Antarmuka yang sederhana dan intuitif, dirancang untuk semua orang.</p>
            </div>
        </div>
    </section>
    
    <footer>
        <p>&copy; 2023 Aplikasi Keuangan Pribadi. Semua hak dilindungi.</p>
    </footer>
</body>
</html>