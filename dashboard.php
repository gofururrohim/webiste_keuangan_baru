<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$query = "SELECT nama_depan, nama_belakang, username, email FROM users WHERE id = $user_id";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Ambil data transaksi
$query_transaksi = "SELECT * FROM transaksi WHERE user_id = $user_id ORDER BY tanggal DESC";
$result_transaksi = mysqli_query($koneksi, $query_transaksi);

// Hitung total pemasukan
$query_pemasukan = "SELECT SUM(jumlah) as total FROM transaksi WHERE user_id = $user_id AND jenis = 'pemasukan'";
$result_pemasukan = mysqli_query($koneksi, $query_pemasukan);
$pemasukan = mysqli_fetch_assoc($result_pemasukan);
$total_pemasukan = $pemasukan['total'] ? $pemasukan['total'] : 0;

// Hitung total pengeluaran
$query_pengeluaran = "SELECT SUM(jumlah) as total FROM transaksi WHERE user_id = $user_id AND jenis = 'pengeluaran'";
$result_pengeluaran = mysqli_query($koneksi, $query_pengeluaran);
$pengeluaran = mysqli_fetch_assoc($result_pengeluaran);
$total_pengeluaran = $pengeluaran['total'] ? $pengeluaran['total'] : 0;

// Hitung saldo
$saldo = $total_pemasukan - $total_pengeluaran;

// Ambil data untuk grafik (6 bulan terakhir)
$query_chart = "SELECT MONTH(tanggal) as bulan, jenis, SUM(jumlah) as total 
                FROM transaksi 
                WHERE user_id = $user_id 
                AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY MONTH(tanggal), jenis
                ORDER BY tanggal";
$result_chart = mysqli_query($koneksi, $query_chart);

$chart_data = [];
while ($row = mysqli_fetch_assoc($result_chart)) {
    $bulan = date("M", mktime(0, 0, 0, $row['bulan'], 1));
    if (!isset($chart_data[$bulan])) {
        $chart_data[$bulan] = ['pemasukan' => 0, 'pengeluaran' => 0];
    }
    $chart_data[$bulan][$row['jenis']] = $row['total'];
}

// Konversi data chart ke format JSON untuk JavaScript
$chart_labels = json_encode(array_keys($chart_data));
$chart_pemasukan = json_encode(array_column($chart_data, 'pemasukan'));
$chart_pengeluaran = json_encode(array_column($chart_data, 'pengeluaran'));

// Menentukan halaman aktif
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Keuangan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        header {
            background-color: #3498db;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        main {
            display: flex;
            flex: 1;
        }
        
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
        }
        
        .menu {
            list-style: none;
        }
        
        .menu li a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .menu li a:hover, .menu li a.active {
            background-color: #3498db;
        }
        
        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .page-title {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .card-title {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .card-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .card.income .card-value {
            color: #27ae60;
        }
        
        .card.expense .card-value {
            color: #e74c3c;
        }
        
        .chart-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .amount {
            font-weight: 500;
        }
        
        .amount.income {
            color: #27ae60;
        }
        
        .amount.expense {
            color: #e74c3c;
        }
        
        .filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filter-container select {
            width: auto;
        }
        
        .profile-info {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .profile-info p {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .profile-info strong {
            display: inline-block;
            width: 100px;
        }
        
        .logout-btn {
            background-color: #e74c3c;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        @media (max-width: 768px) {
            main {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 10px 0;
            }
            
            .menu {
                display: flex;
                overflow-x: auto;
            }
            
            .menu li {
                flex-shrink: 0;
            }
            
            .menu li a {
                padding: 10px 15px;
            }
            
            .cards {
                grid-template-columns: 1fr;
            }
        }
        
        /* Tampilan halaman */
        .page {
            display: none;
        }
        
        .page.active {
            display: block;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <!-- spell-checker: disable -->
        <div class="logo">Aplikasi Keuangan</div>
        <div class="user-info">
            <span class="user-name">Halo, <?php echo $user['nama_depan']; ?></span>
        </div>
        <!-- spell-checker: enable -->
    </header>
    
    <main>
        <div class="sidebar">
            <ul class="menu">
                <li><a href="?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="?page=transaksi" class="<?php echo $page == 'transaksi' ? 'active' : ''; ?>">Transaksi</a></li>
                <li><a href="?page=laporan" class="<?php echo $page == 'laporan' ? 'active' : ''; ?>">Laporan</a></li>
                <li><a href="?page=pengaturan" class="<?php echo $page == 'pengaturan' ? 'active' : ''; ?>">Pengaturan</a></li>
            </ul>
        </div>
        
        <div class="content">
            <!-- Dashboard Page -->
            <div class="page <?php echo $page == 'dashboard' ? 'active' : ''; ?>" id="dashboard">
                <h2 class="page-title">Dashboard</h2>
                
                <div class="cards">
                    <div class="card income">
                        <div class="card-title">Total Pemasukan</div>
                        <div class="card-value">Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?></div>
                    </div>
                    
                    <div class="card expense">
                        <div class="card-title">Total Pengeluaran</div>
                        <div class="card-value">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></div>
                    </div>
                    
                    <div class="card">
                        <div class="card-title">Saldo Saat Ini</div>
                        <div class="card-value" style="color: <?php echo $saldo >= 0 ? '#27ae60' : '#e74c3c'; ?>">
                            Rp <?php echo number_format($saldo, 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3>Grafik Transaksi 6 Bulan Terakhir</h3>
                    <canvas id="transactionChart"></canvas>
                </div>
                
                <div class="recent-transactions">
                    <h3>Transaksi Terbaru</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Deskripsi</th>
                                <th>Jumlah</th>
                                <th>Jenis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 0;
                            while ($row = mysqli_fetch_assoc($result_transaksi)) {
                                if ($count < 5) { // Tampilkan hanya 5 transaksi terbaru
                                    echo "<tr>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                                    echo "<td>" . $row['deskripsi'] . "</td>";
                                    echo "<td class='amount " . $row['jenis'] . "'>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";
                                    echo "<td>" . ucfirst($row['jenis']) . "</td>";
                                    echo "</tr>";
                                    $count++;
                                } else {
                                    break;
                                }
                            }
                            
                            // Reset pointer hasil query
                            mysqli_data_seek($result_transaksi, 0);
                            
                            if ($count == 0) {
                                echo "<tr><td colspan='4' style='text-align: center;'>Belum ada transaksi</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Transaksi Page -->
            <div class="page <?php echo $page == 'transaksi' ? 'active' : ''; ?>" id="transaksi">
                <h2 class="page-title">Transaksi</h2>
                
                <div class="form-container">
                    <h3>Tambah Transaksi Baru</h3>
                    <form action="proses_dashboard.php" method="post">
                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <input type="date" id="tanggal" name="tanggal" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <input type="text" id="deskripsi" name="deskripsi" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="jumlah">Jumlah (Rp)</label>
                            <input type="number" id="jumlah" name="jumlah" required min="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis">Jenis Transaksi</label>
                            <select id="jenis" name="jenis" required>
                                <option value="pemasukan">Pemasukan</option>
                                <option value="pengeluaran">Pengeluaran</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="tambah_transaksi">Simpan Transaksi</button>
                    </form>
                </div>
                
                <div class="transactions-list">
                    <h3>Daftar Transaksi</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Deskripsi</th>
                                <th>Jumlah</th>
                                <th>Jenis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while ($row = mysqli_fetch_assoc($result_transaksi)) {
                                echo "<tr>";
                                echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                                echo "<td>" . $row['deskripsi'] . "</td>";
                                echo "<td class='amount " . $row['jenis'] . "'>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";
                                echo "<td>" . ucfirst($row['jenis']) . "</td>";
                                echo "</tr>";
                            }
                            
                            // Reset pointer hasil query
                            mysqli_data_seek($result_transaksi, 0);
                            
                            if (mysqli_num_rows($result_transaksi) == 0) {
                                echo "<tr><td colspan='4' style='text-align: center;'>Belum ada transaksi</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Laporan Page -->
            <div class="page <?php echo $page == 'laporan' ? 'active' : ''; ?>" id="laporan">
                <h2 class="page-title">Laporan</h2>
                
                <div class="form-container">
                    <h3>Filter Laporan</h3>
                    <form id="filter-form">
                        <div class="filter-container">
                            <div class="form-group">
                                <label for="filter-type">Jenis Filter</label>
                                <select id="filter-type" name="filter-type">
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                    <option value="bulanan">Bulanan</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="date-filter">
                                <label for="filter-date">Tanggal</label>
                                <input type="date" id="filter-date" name="filter-date" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group" id="month-filter" style="display: none;">
                                <label for="filter-month">Bulan</label>
                                <input type="month" id="filter-month" name="filter-month" value="<?php echo date('Y-m'); ?>">
                            </div>
                            
                            <button type="button" id="apply-filter">Terapkan Filter</button>
                        </div>
                    </form>
                </div>
                
                <div class="report-summary">
                    <div class="cards">
                        <div class="card income">
                            <div class="card-title">Total Pemasukan</div>
                            <div class="card-value" id="report-income">Rp 0</div>
                        </div>
                        
                        <div class="card expense">
                            <div class="card-title">Total Pengeluaran</div>
                            <div class="card-value" id="report-expense">Rp 0</div>
                        </div>
                        
                        <div class="card">
                            <div class="card-title">Saldo</div>
                            <div class="card-value" id="report-balance">Rp 0</div>
                        </div>
                    </div>
                </div>
                
                <div class="filtered-transactions">
                    <h3>Transaksi Berdasarkan Filter</h3>
                    <table id="filtered-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Deskripsi</th>
                                <th>Jumlah</th>
                                <th>Jenis</th>
                            </tr>
                        </thead>
                        <tbody id="filtered-transactions-body">
                            <tr>
                                <td colspan="4" style="text-align: center;">Pilih filter untuk menampilkan data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pengaturan Page -->
            <div class="page <?php echo $page == 'pengaturan' ? 'active' : ''; ?>" id="pengaturan">
                <h2 class="page-title">Pengaturan</h2>
                
                <div class="profile-info">
                    <h3>Informasi Profil</h3>
                    <p><strong>Nama:</strong> <?php echo $user['nama_depan'] . ' ' . $user['nama_belakang']; ?></p>
                    <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
                    <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                </div>
                
                <a href="logout.php"><button class="logout-btn">Logout</button></a>
            </div>
        </div>
    </main>
    
    <script>
        // Chart untuk transaksi
        const ctx = document.getElementById('transactionChart').getContext('2d');
        const transactionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $chart_labels; ?>,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: <?php echo $chart_pemasukan; ?>,
                        backgroundColor: 'rgba(39, 174, 96, 0.5)',
                        borderColor: 'rgba(39, 174, 96, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pengeluaran',
                        data: <?php echo $chart_pengeluaran; ?>,
                        backgroundColor: 'rgba(231, 76, 60, 0.5)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
        
        // Filter laporan
        document.getElementById('filter-type').addEventListener('change', function() {
            const filterType = this.value;
            const dateFilter = document.getElementById('date-filter');
            const monthFilter = document.getElementById('month-filter');
            
            if (filterType === 'harian') {
                dateFilter.style.display = 'block';
                monthFilter.style.display = 'none';
            } else if (filterType === 'mingguan') {
                dateFilter.style.display = 'block';
                monthFilter.style.display = 'none';
            } else if (filterType === 'bulanan') {
                dateFilter.style.display = 'none';
                monthFilter.style.display = 'block';
            }
        });
        
        // Fungsi untuk memformat angka ke format Rupiah
        function formatRupiah(angka) {
            return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
        }
        
        // Fungsi untuk menerapkan filter
        document.getElementById('apply-filter').addEventListener('click', function() {
            const filterType = document.getElementById('filter-type').value;
            let filterDate;
            
            if (filterType === 'bulanan') {
                filterDate = document.getElementById('filter-month').value;
            } else {
                filterDate = document.getElementById('filter-date').value;
            }
            
            // Data transaksi (dari PHP)
            const transactions = [
                <?php 
                mysqli_data_seek($result_transaksi, 0);
                while ($row = mysqli_fetch_assoc($result_transaksi)) {
                    echo "{";
                    echo "tanggal: '" . $row['tanggal'] . "',";
                    echo "deskripsi: '" . addslashes($row['deskripsi']) . "',";
                    echo "jumlah: " . $row['jumlah'] . ",";
                    echo "jenis: '" . $row['jenis'] . "'";
                    echo "},";
                }
                ?>
            ];
            
            let filteredTransactions = [];
            
            // Filter berdasarkan jenis
            if (filterType === 'harian') {
                filteredTransactions = transactions.filter(t => t.tanggal === filterDate);
            } else if (filterType === 'mingguan') {
                // Mendapatkan tanggal awal dan akhir minggu
                const date = new Date(filterDate);
                const firstDay = new Date(date.setDate(date.getDate() - date.getDay()));
                const lastDay = new Date(date.setDate(date.getDate() + 6));
                
                filteredTransactions = transactions.filter(t => {
                    const transDate = new Date(t.tanggal);
                    return transDate >= firstDay && transDate <= lastDay;
                });
            } else if (filterType === 'bulanan') {
                // Filter berdasarkan bulan dan tahun
                const [year, month] = filterDate.split('-');
                filteredTransactions = transactions.filter(t => {
                    return t.tanggal.startsWith(`${year}-${month}`);
                });
            }
            
            // Hitung total pemasukan dan pengeluaran
            let totalIncome = 0;
            let totalExpense = 0;
            
            filteredTransactions.forEach(t => {
                if (t.jenis === 'pemasukan') {
                    totalIncome += t.jumlah;
                } else {
                    totalExpense += t.jumlah;
                }
            });
            
            const balance = totalIncome - totalExpense;
            
            // Update tampilan
            document.getElementById('report-income').textContent = formatRupiah(totalIncome);
            document.getElementById('report-expense').textContent = formatRupiah(totalExpense);
            document.getElementById('report-balance').textContent = formatRupiah(balance);
            document.getElementById('report-balance').style.color = balance >= 0 ? '#27ae60' : '#e74c3c';
            
            // Update tabel transaksi
            const tbody = document.getElementById('filtered-transactions-body');
            tbody.innerHTML = '';
            
            if (filteredTransactions.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="4" style="text-align: center;">Tidak ada transaksi pada periode ini</td>';
                tbody.appendChild(row);
            } else {
                filteredTransactions.forEach(t => {
                    const row = document.createElement('tr');
                    const formattedDate = new Date(t.tanggal).toLocaleDateString('id-ID');
                    row.innerHTML = `
                        <td>${formattedDate}</td>
                        <td>${t.deskripsi}</td>
                        <td class="amount ${t.jenis}">${formatRupiah(t.jumlah)}</td>
                        <td>${t.jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'}</td>
                    `;
                    tbody.appendChild(row);
                });
            }
        });
    </script>
</body>
</html>