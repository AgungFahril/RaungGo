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
        $whereClause .= " AND ps.status_pesanan IN ('Terkonfirmasi', 'Approved', 'Confirmed', 'lunas')";
    } elseif ($filter === 'pending') {
        $whereClause .= " AND ps.status_pesanan IN ('Menunggu Pembayaran', 'Pending', 'menunggu_pembayaran', 'menunggu_konfirmasi')";
    } elseif ($filter === 'cancelled') {
        $whereClause .= " AND ps.status_pesanan IN ('Dibatalkan', 'Cancelled', 'Rejected', 'batal')";
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
}
.status-table-container {
    max-width: 900px;
    margin: 140px auto;
    padding: 2.5rem;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
.status-table-container h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: #2e7d32;
}
form {
    text-align: center;
    margin-bottom: 25px;
}
input[type="text"] {
    padding: 10px;
    width: 60%;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    background: #43a047;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    margin-left: 10px;
    cursor: pointer;
    font-weight: 600;
}
button:hover { background: #2e7d32; }

.status-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
}
.status-table th, .status-table td {
    border: 1px solid #ddd;
    padding: 0.8rem 1rem;
    text-align: left;
    font-size: 0.95rem;
}
.status-table th {
    background-color: #f8f8f8;
    font-weight: 600;
}
.status-badge {
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #fff;
}
.status-menunggu_pembayaran { background-color: #f39c12; } 
.status-menunggu_konfirmasi { background-color: #3498db; }
.status-berhasil, .status-lunas { background-color: #2ecc71; } 
.status-gagal, .status-ditolak, .status-batal { background-color: #e74c3c; } 
.no-booking {
    text-align: center;
    color: #777;
    margin-top: 2rem;
}
.btn-aksi {
    display: inline-block;
    background: #43a047;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
}
.btn-aksi:hover { background: #2e7d32; }
.btn-bayar {
    background: #e91e63;
}
.btn-bayar:hover { background: #ad1457; }
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
                    $status_class = 'status-' . strtolower(str_replace(' ', '_', $row['status_pesanan']));
                    $status_text = ucfirst(str_replace('_', ' ', $row['status_pesanan']));
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
