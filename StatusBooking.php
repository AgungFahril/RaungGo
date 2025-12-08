<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'backend/koneksi.php';

$bookings = [];
$filter = $_GET['filter'] ?? 'all';

// Jika ada filter (dari dashboard)
if (isset($_GET['filter']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $whereClause = "ps.user_id = ?";
    $bindType = "i";
    $bindValue = $user_id;
    
    // Filter berdasarkan status
    if ($filter === 'success') {
        $whereClause .= " AND ps.status_pesanan IN ('lunas', 'terkonfirmasi', 'selesai', 'berhasil')";
    } elseif ($filter === 'pending') {
        $whereClause .= " AND ps.status_pesanan IN ('menunggu_pembayaran', 'menunggu_konfirmasi')";
    } elseif ($filter === 'cancelled') {
        $whereClause .= " AND ps.status_pesanan IN ('batal', 'dibatalkan', 'gagal')";
    }
    
    $sql = "
        SELECT 
            ps.kode_token, ps.pesanan_id, ps.status_pesanan, ps.total_bayar, 
            ps.tanggal_pesan, ps.jumlah_pendaki,
            p.tanggal_pendakian, p.tanggal_turun, 
            jp.nama_jalur
        FROM pesanan ps
        JOIN pendakian p ON ps.pendakian_id = p.pendakian_id
        JOIN jalur_pendakian jp ON p.jalur_id = jp.jalur_id
        WHERE $whereClause
        ORDER BY ps.pesanan_id DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($bindType, $bindValue);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
// Jika mencari berdasarkan token (cara lama)
elseif (isset($_POST['cek_token'])) {
    $kode_token = strtoupper(trim($_POST['kode_token']));

    $sql = "
        SELECT 
            ps.kode_token, ps.pesanan_id, ps.status_pesanan, ps.total_bayar, 
            ps.tanggal_pesan, ps.jumlah_pendaki,
            p.tanggal_pendakian, p.tanggal_turun, 
            jp.nama_jalur
        FROM pesanan ps
        JOIN pendakian p ON ps.pendakian_id = p.pendakian_id
        JOIN jalur_pendakian jp ON p.jalur_id = jp.jalur_id
        WHERE ps.kode_token = ?
        ORDER BY ps.pesanan_id DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kode_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Booking - Tahura Raden Soerjo</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f5faf5 url('images/Gunung_Raung.jpg') no-repeat center top;
    background-size: cover;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

.status-table-container {
    max-width: 1100px;
    margin: 130px auto 40px;
    padding: 2.5rem;
    background-color: rgba(255, 255, 255, 0.98);
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.status-table-container h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: #2e7d32;
    font-weight: 700;
    font-size: 28px;
}

form {
    text-align: center;
    margin-bottom: 30px;
}

input[type="text"] {
    padding: 12px 15px;
    width: 60%;
    max-width: 400px;
    border-radius: 8px;
    border: 2px solid #ddd;
    font-size: 15px;
    transition: border 0.3s ease;
}

input[type="text"]:focus {
    border-color: #43a047;
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 160, 71, 0.1);
}

button {
    background: linear-gradient(135deg, #43a047, #2e7d32);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    margin-left: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    box-shadow: 0 4px 12px rgba(67, 160, 71, 0.3);
    transition: all 0.3s ease;
}

button:hover { 
    background: linear-gradient(135deg, #2e7d32, #1b5e20);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(67, 160, 71, 0.4);
}

button:active {
    transform: translateY(0);
}

/* Table wrapper untuk scroll horizontal */
.table-wrapper {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    margin-top: 1.5rem;
    border-radius: 8px;
}

.status-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.status-table th, .status-table td {
    border: 1px solid #e0e0e0;
    padding: 12px 16px;
    text-align: left;
    font-size: 14px;
    white-space: nowrap;
}

.status-table th {
    background-color: #f8f8f8;
    font-weight: 600;
    color: #2e7d32;
}

.status-table tbody tr:hover {
    background-color: #f9fdf9;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    display: inline-block;
    white-space: nowrap;
}

.status-menunggu_pembayaran { background-color: #f39c12; } 
.status-menunggu_konfirmasi { background-color: #3498db; }
.status-berhasil, .status-lunas { background-color: #2ecc71; } 
.status-gagal, .status-ditolak, .status-batal { background-color: #e74c3c; }

.no-booking {
    text-align: center;
    color: #777;
    margin-top: 2rem;
    font-size: 16px;
}

.btn-aksi {
    display: inline-block;
    background: #43a047;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    margin: 2px;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-aksi:hover { 
    background: #2e7d32;
    transform: translateY(-1px);
}

.btn-bayar {
    background: #e91e63;
}

.btn-bayar:hover { 
    background: #ad1457;
}

.scroll-hint {
    display: none;
    text-align: center;
    color: #e91e63;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
    padding: 8px;
    background: #fff3e0;
    border-radius: 6px;
}

footer {
    text-align: center;
    padding: 20px;
    color: #555;
    font-size: 14px;
}

/* === RESPONSIVE MOBILE === */
@media screen and (max-width: 768px) {
    .status-table-container {
        width: 95%;
        margin: 90px auto 25px;
        padding: 20px 15px;
        border-radius: 10px;
    }
    
    .status-table-container h2 {
        font-size: 20px;
        margin-bottom: 1.5rem;
    }
    
    form {
        margin-bottom: 20px;
    }
    
    input[type="text"] {
        width: 100%;
        max-width: 100%;
        padding: 12px;
        font-size: 14px;
        margin-bottom: 10px;
    }
    
    button {
        width: 100%;
        margin-left: 0;
        padding: 12px 20px;
    }
    
    /* Scroll hint muncul di mobile */
    .scroll-hint {
        display: block;
    }
    
    /* Table wrapper dengan scroll */
    .table-wrapper {
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch !important;
        border: 2px solid #e8f5e9;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .status-table {
        min-width: 900px !important;
        font-size: 13px;
    }
    
    .status-table th, .status-table td {
        padding: 10px 12px;
        font-size: 13px;
    }
    
    .status-badge {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .btn-aksi {
        padding: 7px 12px;
        font-size: 12px;
        display: inline-block;
        margin: 2px;
    }
    
    .no-booking {
        font-size: 14px;
        padding: 0 15px;
    }
    
    footer {
        font-size: 13px;
        padding: 18px 15px;
    }
}

@media screen and (max-width: 480px) {
    .status-table-container {
        width: 98%;
        padding: 18px 12px;
    }
    
    .status-table-container h2 {
        font-size: 18px;
    }
    
    .status-table {
        min-width: 850px !important;
        font-size: 12px;
    }
    
    .status-table th, .status-table td {
        padding: 8px 10px;
    }
}
</style>

</head>
<body>

<header>
    <?php include 'includes/navbar_user.php'; ?>
</header>

<main class="content-page">
<div class="status-table-container">
    <?php 
    // Tampilkan judul berdasarkan filter
    if (isset($_GET['filter'])) {
        if ($filter === 'success') {
            echo '<h2>✓ Transaksi Sukses</h2>';
        } elseif ($filter === 'pending') {
            echo '<h2>⏱ Transaksi Menunggu</h2>';
        } elseif ($filter === 'cancelled') {
            echo '<h2>✕ Transaksi Dibatalkan</h2>';
        } else {
            echo '<h2>� Semua Transaksi</h2>';
        }
    } else {
        echo '<h2>�🔍 Cek Status Booking</h2>';
    }
    ?>

    <?php if (!isset($_GET['filter'])): ?>
    <form method="POST">
        <input type="text" name="kode_token" placeholder="Masukkan Kode Token Booking" maxlength="10" required>
        <button type="submit" name="cek_token">Cek Status</button>
    </form>
    <?php endif; ?>

    <?php if (isset($_POST['cek_token']) && empty($bookings)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Kode Token Tidak Ditemukan!',
                text: 'Silakan periksa kembali kode token kamu.',
                confirmButtonColor: '#e53935'
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($bookings)): ?>
    <div class="table-wrapper">
        <table class="status-table">

            <thead>
                <tr>
                    <th>Kode Token</th>
                    <th>Jalur</th>
                    <th>Tanggal Naik</th>
                    <th>Jumlah</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $row): 
                    // Map status dengan benar
                    $status_pesanan = strtolower($row['status_pesanan']);
                    if (in_array($status_pesanan, ['batal', 'dibatalkan', 'cancelled', 'rejected'])) {
                        $status_text = 'Dibatalkan';
                        $status_class = 'status-batal';
                    } elseif (in_array($status_pesanan, ['menunggu_pembayaran', 'pending'])) {
                        $status_text = 'Menunggu Pembayaran';
                        $status_class = 'status-menunggu_pembayaran';
                    } elseif (in_array($status_pesanan, ['menunggu_konfirmasi', 'verifikasi'])) {
                        $status_text = 'Menunggu Konfirmasi';
                        $status_class = 'status-menunggu_konfirmasi';
                    } elseif (in_array($status_pesanan, ['lunas', 'terkonfirmasi', 'approved', 'confirmed'])) {
                        $status_text = 'Terkonfirmasi';
                        $status_class = 'status-lunas';
                    } else {
                        $status_text = ucfirst(str_replace('_', ' ', $row['status_pesanan']));
                        $status_class = 'status-' . strtolower(str_replace(' ', '_', $row['status_pesanan']));
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['kode_token']); ?></td>
                    <td><?= htmlspecialchars($row['nama_jalur']); ?></td>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($row['tanggal_pendakian']))); ?></td>
                    <td><?= htmlspecialchars($row['jumlah_pendaki']); ?> org</td>
                    <td>Rp<?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                    <td><span class="status-badge <?= $status_class; ?>"><?= $status_text; ?></span></td>
                    <td>
                        <?php 
                        // Tombol aksi sesuai status
                        if ($row['status_pesanan'] === 'menunggu_pembayaran'): ?>
                            <a href="pengunjung/pembayaran.php?pesanan_id=<?= $row['pesanan_id']; ?>" class="btn-aksi btn-bayar">Bayar</a>
                            <a href="pengunjung/detail_transaksi.php?pesanan_id=<?= $row['pesanan_id']; ?>" class="btn-aksi">Detail</a>
                        <?php else: ?>
                            <a href="pengunjung/detail_transaksi.php?pesanan_id=<?= $row['pesanan_id']; ?>" class="btn-aksi">Lihat Detail</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
            </table>
    </div> <!-- penutup table-wrapper -->

    <?php elseif (!isset($_POST['cek_token'])): ?>
        <p class="no-booking">Masukkan kode token booking untuk melihat status pendakian kamu.</p>
    <?php endif; ?>
    
    <?php if (isset($_GET['filter'])): ?>
        <div style="margin-top: 30px; text-align: center;">
            <a href="pengunjung/dashboard.php" style="display: inline-flex; align-items: center; gap: 8px; background: #e74c3c; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                ← Kembali ke Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>
</main>

<footer style="text-align:center; padding:20px; color:#555;">
    &copy; 2025 Tahura Raden Soerjo
</footer>

</body>
</html>
