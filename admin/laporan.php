<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../backend/koneksi.php';

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$tgl_awal = "$tahun-$bulan-01";
$tgl_akhir = date('Y-m-t', strtotime($tgl_awal));

$queries = [
    'tiket_terjual' => "SELECT COUNT(*) as total FROM pesanan WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun",
    'total_pesanan' => "SELECT COUNT(*) as total FROM pesanan WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun",
    'total_pendaki' => "SELECT COALESCE(SUM(jumlah_pendaki), 0) as total FROM pesanan WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun",
    'jalur_ramai' => "SELECT pendakian_id as nama_jalur, COUNT(*) as jumlah FROM pesanan WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun GROUP BY pendakian_id ORDER BY jumlah DESC LIMIT 5",
    'hari_sibuk' => "SELECT DATE(created_at) as tanggal, DATE_FORMAT(created_at, '%W') as hari, COUNT(*) as jumlah FROM pesanan WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun GROUP BY DATE(created_at), DATE_FORMAT(created_at, '%W') ORDER BY jumlah DESC LIMIT 7",
    'pendapatan' => "SELECT COALESCE(SUM(total_bayar), 0) as total FROM pesanan WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun",
    'rata_pendaki' => "SELECT COALESCE(AVG(jumlah_pendaki), 0) as rata FROM pesanan WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun",
    'status_summary' => "SELECT status_pesanan, COUNT(*) as total FROM pesanan WHERE MONTH(created_at) = $bulan AND YEAR(created_at) = $tahun GROUP BY status_pesanan"
];

$data = [];
foreach ($queries as $key => $q) {
    $result = $conn->query($q);
    if ($result) {
        if (strpos($key, 'ramai') !== false || strpos($key, 'sibuk') !== false || strpos($key, 'summary') !== false) {
            $data[$key] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $data[$key] = $result->fetch_assoc();
        }
    }
}

$tiket_terjual = $data['tiket_terjual']['total'] ?? 0;
$total_pesanan = $data['total_pesanan']['total'] ?? 0;
$total_pendaki = $data['total_pendaki']['total'] ?? 0;
$pendapatan = $data['pendapatan']['total'] ?? 0;
$rata_pendaki = round($data['rata_pendaki']['rata'] ?? 0, 1);
$jalur_ramai = $data['jalur_ramai'] ?? [];
$hari_sibuk = $data['hari_sibuk'] ?? [];
$status_summary = $data['status_summary'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan - Dashboard Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}
@keyframes fadeIn{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:none}}
body{background:linear-gradient(135deg,#f5faf5,#e8f5e9);overflow-x:hidden;animation:fadeIn .8s ease;min-height:100vh}
.main-content{flex:1;margin-left:260px;padding:40px}
.top-header{margin-bottom:30px}
.top-header h1{color:#2e7d32;font-size:28px;font-weight:700;margin-bottom:15px}
.filter-bar{display:flex;gap:15px;align-items:center;background:#fff;padding:20px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08)}
.filter-group{display:flex;gap:10px;align-items:center}
.filter-group label{font-weight:600;color:#333;font-size:14px}
.filter-group select{padding:10px 15px;border:2px solid #ddd;border-radius:8px;background:#fff;cursor:pointer;font-size:14px;transition:all .3s}
.filter-group select:focus{border-color:#2e7d32;outline:none}
.btn-filter{padding:10px 25px;background:#2e7d32;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;transition:all .3s}
.btn-filter:hover{background:#1b5e20;transform:translateY(-2px)}
.btn-export{padding:10px 25px;background:#e8e8e8;color:#333;border:none;border-radius:8px;cursor:pointer;font-weight:600;transition:all .3s;margin-left:auto}
.btn-export:hover{background:#d8d8d8}

.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:30px}
.stat-box{background:#fff;padding:25px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);border-top:5px solid #2e7d32;transition:all .3s}
.stat-box:hover{transform:translateY(-5px);box-shadow:0 8px 25px rgba(0,0,0,0.12)}
.stat-box.green{border-top:5px solid #43a047}
.stat-box.blue{border-top:5px solid #1976d2}
.stat-box.orange{border-top:5px solid #f57c00}
.stat-box.red{border-top:5px solid #d32f2f}
.stat-icon{font-size:32px;margin-bottom:10px;color:#2e7d32}
.stat-box.green .stat-icon{color:#43a047}
.stat-box.blue .stat-icon{color:#1976d2}
.stat-box.orange .stat-icon{color:#f57c00}
.stat-box.red .stat-icon{color:#d32f2f}
.stat-number{font-size:32px;font-weight:700;color:#2e7d32;margin-bottom:5px}
.stat-box.blue .stat-number{color:#1976d2}
.stat-box.green .stat-number{color:#43a047}
.stat-box.orange .stat-number{color:#f57c00}
.stat-box.red .stat-number{color:#d32f2f}
.stat-label{color:#666;font-size:14px;font-weight:500}

.content-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:25px;margin-bottom:30px}
.card{background:#fff;border-radius:12px;padding:25px;box-shadow:0 4px 15px rgba(0,0,0,0.08);animation:fadeIn .6s ease}
.card h2{color:#2e7d32;font-size:18px;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.card-table{width:100%}
.card-table th{background:#f5faf5;padding:12px;text-align:left;font-weight:700;color:#2e7d32;border-bottom:2px solid #e8f5e9;font-size:13px}
.card-table td{padding:12px;border-bottom:1px solid #eee;font-size:13px;color:#555}
.card-table tr:hover{background:#f5faf5}
.card-table td:last-child{text-align:right;color:#2e7d32;font-weight:600}

.status-badge{display:inline-block;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600}
.status-lunas{background:#d4edda;color:#155724}
.status-pending{background:#fff3cd;color:#856404}
.status-batal{background:#f8d7da;color:#721c24}

.full-width{grid-column:1/-1}

@media(max-width:1024px){
    .content-grid{grid-template-columns:1fr}
}

@media(max-width:768px){
    .main-content{margin-left:0;padding:20px}
    .filter-bar{flex-direction:column;align-items:stretch}
    .filter-group{flex-direction:column}
    .btn-export{margin-left:0;margin-top:10px}
    .stats-grid{grid-template-columns:1fr}
    .content-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
        <div class="top-header">
            <h1><i class="fas fa-chart-bar"></i> Laporan Bulanan</h1>
        </div>

        <!-- FILTER BAR -->
        <div class="filter-bar">
            <form method="GET" style="display:flex;gap:15px;align-items:center;flex:1;flex-wrap:wrap">
                <div class="filter-group">
                    <label>Bulan:</label>
                    <select name="bulan" onchange="this.form.submit()">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo ($m == $bulan) ? 'selected' : ''; ?>>
                            <?php echo date('F', strtotime("2025-$m-01")); ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Tahun:</label>
                    <select name="tahun" onchange="this.form.submit()">
                        <?php for ($y = 2024; $y <= 2026; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $tahun) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
            <button class="btn-export" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button>
        </div>

        <!-- STATS GRID -->
        <div class="stats-grid">
            <div class="stat-box green">
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-number"><?php echo $tiket_terjual; ?></div>
                <div class="stat-label">Tiket Terjual</div>
            </div>
            <div class="stat-box blue">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?php echo $total_pendaki; ?></div>
                <div class="stat-label">Total Pendaki</div>
            </div>
            <div class="stat-box orange">
                <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="stat-number"><?php echo $rata_pendaki; ?></div>
                <div class="stat-label">Rata-rata Pendaki/Tiket</div>
            </div>
            <div class="stat-box red">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-number">Rp <?php echo number_format($pendapatan, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>

        <!-- CONTENT GRID -->
        <div class="content-grid">
            <!-- JALUR PALING RAMAI -->
            <div class="card">
                <h2><i class="fas fa-mountain"></i> Statistik Pesanan</h2>
                <table class="card-table">
                    <thead>
                        <tr>
                            <th>Keterangan</th>
                            <th style="text-align:right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($jalur_ramai)): ?>
                            <?php foreach ($jalur_ramai as $j): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($j['nama_jalur']); ?></td>
                                <td><?php echo $j['jumlah']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="2" style="text-align:center;color:#999">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- HARI PALING SIBUK -->
            <div class="card">
                <h2><i class="fas fa-calendar-alt"></i> Hari Paling Sibuk</h2>
                <table class="card-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th style="text-align:right">Pesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($hari_sibuk)): ?>
                            <?php foreach ($hari_sibuk as $h): ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($h['tanggal'])); ?> (<?php echo htmlspecialchars($h['hari']); ?>)</td>
                                <td><?php echo $h['jumlah']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="2" style="text-align:center;color:#999">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- STATUS PESANAN -->
            <div class="card full-width">
                <h2><i class="fas fa-info-circle"></i> Ringkasan Status Pesanan</h2>
                <table class="card-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th style="text-align:right">Jumlah</th>
                            <th style="text-align:right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($status_summary)): ?>
                            <?php foreach ($status_summary as $s): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $status = strtoupper(str_replace('_', ' ', $s['status_pesanan']));
                                    $class = 'status-pending';
                                    if (in_array($s['status_pesanan'], ['lunas', 'terkonfirmasi', 'selesai', 'berhasil'])) $class = 'status-lunas';
                                    elseif (in_array($s['status_pesanan'], ['batal', 'dibatalkan', 'gagal'])) $class = 'status-batal';
                                    ?>
                                    <span class="status-badge <?php echo $class; ?>"><?php echo $status; ?></span>
                                </td>
                                <td><?php echo $s['total']; ?></td>
                                <td><?php echo number_format(($s['total'] / max($total_pesanan, 1)) * 100, 1); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align:center;color:#999">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- RINGKASAN KESELURUHAN -->
            <div class="card full-width">
                <h2><i class="fas fa-clipboard-list"></i> Ringkasan Keseluruhan Bulan <?php echo date('F Y', strtotime("$tahun-$bulan-01")); ?></h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px">
                    <div style="padding:15px;background:#f5faf5;border-radius:8px">
                        <div style="font-size:13px;color:#666;margin-bottom:8px">Total Pesanan</div>
                        <div style="font-size:24px;font-weight:700;color:#2e7d32"><?php echo $total_pesanan; ?></div>
                    </div>
                    <div style="padding:15px;background:#f5faf5;border-radius:8px">
                        <div style="font-size:13px;color:#666;margin-bottom:8px">Tiket Terjual (Lunas)</div>
                        <div style="font-size:24px;font-weight:700;color:#2e7d32"><?php echo $tiket_terjual; ?></div>
                    </div>
                    <div style="padding:15px;background:#f5faf5;border-radius:8px">
                        <div style="font-size:13px;color:#666;margin-bottom:8px">Total Pendaki Terdaftar</div>
                        <div style="font-size:24px;font-weight:700;color:#2e7d32"><?php echo $total_pendaki; ?></div>
                    </div>
                    <div style="padding:15px;background:#f5faf5;border-radius:8px">
                        <div style="font-size:13px;color:#666;margin-bottom:8px">Pendapatan (Lunas)</div>
                        <div style="font-size:24px;font-weight:700;color:#2e7d32">Rp <?php echo number_format($pendapatan, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
</div>
</body>
</html>
