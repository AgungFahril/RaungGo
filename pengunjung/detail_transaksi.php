<?php
session_start();
include '../backend/koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔒 Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=pengunjung/detail_transaksi.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$pesanan_id = $_GET['pesanan_id'] ?? null;

if (!$pesanan_id) {
    echo "<script>alert('Pesanan tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}

// 🔍 Ambil data pesanan + pembayaran (jika ada)
$q = $conn->prepare("
    SELECT ps.*, jp.nama_jalur, p.tanggal_pendakian, 
           pb.metode, pb.jumlah_bayar, pb.tanggal_bayar, pb.bukti_bayar, pb.status_pembayaran
    FROM pesanan ps
    JOIN pendakian p ON ps.pendakian_id = p.pendakian_id
    JOIN jalur_pendakian jp ON p.jalur_id = jp.jalur_id
    LEFT JOIN pembayaran pb ON ps.pesanan_id = pb.pesanan_id
    WHERE ps.user_id = ? AND ps.pesanan_id = ?
");
$q->bind_param("ii", $user_id, $pesanan_id);
$q->execute();
$data = $q->get_result()->fetch_assoc();
$q->close();

if (!$data) {
    echo "<script>alert('Data transaksi tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Transaksi Pendakian</title>
<link rel="stylesheet" href="../style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f5faf5 url('../images/Gunung_Raung.jpg') no-repeat center top;
    background-size: cover;
    color: #333;
}
.container {
    max-width: 900px;
    margin: 120px auto;
    background: rgba(255,255,255,0.96);
    border-radius: 14px;
    padding: 30px 40px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #2e7d32;
    margin-bottom: 25px;
    font-weight: 700;
}
.status-box {
    padding: 12px;
    border-radius: 10px;
    color: #fff;
    text-align: center;
    font-weight: 600;
    margin-bottom: 25px;
}
.status-pending { background: #fbc02d; }
.status-verifikasi { background: #42a5f5; }
.status-sukses { background: #43a047; }
.status-gagal { background: #e53935; }

.detail {
    background: #e8f5e9;
    border-left: 5px solid #43a047;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}
.bukti {
    text-align: center;
    margin-top: 25px;
}
.bukti img {
    max-width: 350px;
    border-radius: 10px;
    border: 1px solid #ccc;
}
.btn {
    display: inline-block;
    margin-top: 20px;
    background: #43a047;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}
.btn:hover { background: #2e7d32; }
</style>
</head>
<body>

<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

<main class="container">
    <h2>🧾 Detail Transaksi</h2>

    <?php
    // Tentukan status gabungan (pesanan + pembayaran)
    $statusPesanan = $data['status_pesanan'];
    $statusPembayaran = $data['status_pembayaran'];

    if ($statusPesanan == 'menunggu_pembayaran' && !$statusPembayaran) {
        $statusClass = 'status-pending';
        $statusText = 'Menunggu Pembayaran';
    } elseif ($statusPesanan == 'menunggu_verifikasi' || $statusPembayaran == 'pending') {
        $statusClass = 'status-verifikasi';
        $statusText = 'Menunggu Verifikasi Admin';
    } elseif ($statusPesanan == 'lunas' || $statusPembayaran == 'terkonfirmasi') {
        $statusClass = 'status-sukses';
        $statusText = 'Pembayaran Berhasil Dikonfirmasi ✅';
    } elseif ($statusPesanan == 'batal' || $statusPembayaran == 'ditolak') {
        $statusClass = 'status-gagal';
        $statusText = 'Pembayaran Ditolak ❌';
    } else {
        $statusClass = 'status-pending';
        $statusText = 'Menunggu Proses';
    }
    ?>

    <div class="status-box <?= $statusClass; ?>">
        <?= $statusText; ?>
    </div>

    <div class="detail">
        <p><strong>Nama Jalur:</strong> <?= htmlspecialchars($data['nama_jalur']); ?></p>
        <p><strong>Tanggal Pendakian:</strong> <?= htmlspecialchars($data['tanggal_pendakian']); ?></p>
        <p><strong>Tanggal Pesan:</strong> <?= htmlspecialchars($data['tanggal_pesan']); ?></p>
        <p><strong>Total Bayar:</strong> Rp<?= number_format($data['total_bayar'], 0, ',', '.'); ?></p>
        <p><strong>Metode Pembayaran:</strong> <?= $data['metode'] ? ucfirst($data['metode']) : '-'; ?></p>
        <p><strong>Jumlah Bayar:</strong> <?= $data['jumlah_bayar'] ? 'Rp' . number_format($data['jumlah_bayar'], 0, ',', '.') : '-'; ?></p>
        <p><strong>Status Pembayaran:</strong> <?= ucfirst($data['status_pembayaran'] ?? 'Belum ada'); ?></p>
    </div>

    <?php if (!empty($data['bukti_bayar'])): ?>
    <div class="bukti">
        <p><strong>Bukti Pembayaran:</strong></p>
        <?php
        $ext = pathinfo($data['bukti_bayar'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
            echo "<img src='../uploads/bukti/" . htmlspecialchars($data['bukti_bayar']) . "' alt='Bukti Pembayaran'>";
        } elseif (strtolower($ext) == 'pdf') {
            echo "<a class='btn' href='../uploads/bukti/" . htmlspecialchars($data['bukti_bayar']) . "' target='_blank'>Lihat File PDF</a>";
        }
        ?>
    </div>
    <?php endif; ?>

    <div style="text-align:center;">
        <a href="../StatusBooking.php" class="btn">⬅️ Kembali ke Status Booking</a>
    </div>
</main>

<footer style="text-align:center; padding:20px; color:#555;">
    &copy; 2025 Tahura Raden Soerjo. All Rights Reserved.
</footer>

</body>
</html>
