<?php
// ================================================
// DETAIL TRANSAKSI PENDAKIAN — FINAL PREMIUM
// ================================================
date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../backend/koneksi.php';

// ✅ Tampilkan pesan sukses/error dari PRG (pembayaran.php)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);


// ✅ Ambil parameter (bisa lewat ID atau kode token)
$pesanan_id = isset($_GET['pesanan_id']) ? intval($_GET['pesanan_id']) : null;
$kode_token = isset($_GET['kode_token']) ? strtoupper(trim($_GET['kode_token'])) : null;

if (!$pesanan_id && !$kode_token) {
    echo "<script>alert('Parameter pesanan tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}

// ✅ Ambil data pesanan
$sql = "
    SELECT ps.*, jp.nama_jalur, jp.tarif_tiket, p.tanggal_pendakian
    FROM pesanan ps
    JOIN pendakian p ON ps.pendakian_id = p.pendakian_id
    JOIN jalur_pendakian jp ON p.jalur_id = jp.jalur_id
    WHERE " . ($pesanan_id ? "ps.pesanan_id = ?" : "ps.kode_token = ?") . "
    LIMIT 1
";
$stmt = $conn->prepare($sql);
if ($pesanan_id) $stmt->bind_param("i", $pesanan_id);
else $stmt->bind_param("s", $kode_token);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pesanan) {
    echo "<script>alert('Data pesanan tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}

// ✅ Ambil data pembayaran
$stmt2 = $conn->prepare("SELECT * FROM pembayaran WHERE pesanan_id = ? ORDER BY tanggal_bayar DESC, pembayaran_id DESC LIMIT 1");
$stmt2->bind_param("i", $pesanan['pesanan_id']);
$stmt2->execute();
$payment = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

// ✅ Konversi zona waktu & format jam pembayaran
function formatWaktu($datetime) {
    if (!$datetime) return '-';
    $time = new DateTime($datetime, new DateTimeZone('Asia/Jakarta'));
    return $time->format('d M Y, H:i') . ' WIB';
}

// ✅ Mapping status ke label & warna
function map_status($ps_status, $pb_status) {
    if ($ps_status === 'menunggu_pembayaran' && !$pb_status) return ['Menunggu Pembayaran', 'pending'];
    if ($ps_status === 'menunggu_konfirmasi' || $pb_status === 'pending') return ['Menunggu Konfirmasi Admin', 'verifikasi'];
    if ($ps_status === 'lunas' || $pb_status === 'terkonfirmasi') return ['Pembayaran Dikonfirmasi', 'sukses'];
    if ($ps_status === 'batal' || $pb_status === 'ditolak') return ['Pembayaran Ditolak / Dibatalkan', 'gagal'];
    return ['Menunggu Proses', 'pending'];
}
[$statusText, $statusClass] = map_status($pesanan['status_pesanan'] ?? null, $payment['status_pembayaran'] ?? null);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Transaksi - <?= htmlspecialchars($pesanan['kode_token']); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f5faf5 url('../images/Gunung_Raung.jpg') no-repeat center top;
    background-size: cover;
    color: #333;
    margin: 0;
}
.container {
    max-width: 950px;
    margin: 110px auto;
    background: rgba(255, 255, 255, 0.97);
    border-radius: 14px;
    padding: 35px 45px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}
h1 {
    color: #2e7d32;
    text-align: center;
    margin-bottom: 25px;
}
.status {
    text-align: center;
    font-weight: 700;
    color: white;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 25px;
}
.status.pending { background-color: #f39c12; }
.status.verifikasi { background-color: #3498db; }
.status.sukses { background-color: #43a047; }
.status.gagal { background-color: #e53935; }
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.table th, .table td {
    padding: 10px 14px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
.table th {
    background: #f9f9f9;
    font-weight: 600;
}
.bukti {
    text-align: center;
    margin-top: 25px;
}
.bukti img {
    max-width: 380px;
    border-radius: 12px;
    border: 2px solid #ddd;
    cursor: pointer;
    transition: 0.3s ease;
}
.bukti img:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}
.btn {
    display: inline-block;
    background: #43a047;
    color: white;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 600;
    margin-top: 20px;
}
.btn:hover { background: #2e7d32; }
footer {
    text-align: center;
    margin-top: 25px;
    color: #777;
}
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    justify-content: center;
    align-items: center;
}
.modal img {
    max-width: 80%;
    max-height: 80%;
    border-radius: 10px;
}
.modal:target { display: flex; }
</style>
</head>
<body>

<header>
    <?php if (file_exists(__DIR__ . '/../includes/navbar_user.php')) include '../includes/navbar_user.php'; ?>
</header>

<main class="container">
    <h1>🧾 Detail Transaksi Pendakian</h1>
    <div class="status <?= $statusClass; ?>"><?= $statusText; ?></div>

    <table class="table">
        <tr><th>Kode Token</th><td><?= htmlspecialchars($pesanan['kode_token']); ?></td></tr>
        <tr><th>Nama Jalur</th><td><?= htmlspecialchars($pesanan['nama_jalur']); ?></td></tr>
        <tr><th>Tanggal Pendakian</th><td><?= htmlspecialchars($pesanan['tanggal_pendakian']); ?></td></tr>
        <tr><th>Jumlah Pendaki</th><td><?= htmlspecialchars($pesanan['jumlah_pendaki']); ?> orang</td></tr>
        <tr><th>Total Bayar</th><td>Rp <?= number_format($pesanan['total_bayar'], 0, ',', '.'); ?></td></tr>
        <tr><th>Nama Ketua</th><td><?= htmlspecialchars($pesanan['nama_ketua']); ?></td></tr>
        <tr><th>No. HP Ketua</th><td><?= htmlspecialchars($pesanan['telepon_ketua']); ?></td></tr>
        <tr><th>Tanggal Pesan</th><td><?= formatWaktu($pesanan['tanggal_pesan']); ?></td></tr>
    </table>

    <h3>💳 Informasi Pembayaran</h3>
    <table class="table">
        <tr><th>Metode Pembayaran</th><td><?= htmlspecialchars($payment['metode'] ?? '-'); ?></td></tr>
        <tr><th>Jumlah Bayar</th><td><?= isset($payment['jumlah_bayar']) ? 'Rp ' . number_format($payment['jumlah_bayar'], 0, ',', '.') : '-'; ?></td></tr>
        <tr><th>Tanggal Bayar</th><td><?= formatWaktu($payment['tanggal_bayar'] ?? null); ?></td></tr>
        <tr><th>Status Pembayaran</th><td><?= ucfirst($payment['status_pembayaran'] ?? 'Belum Ada Pembayaran'); ?></td></tr>
    </table>

    <?php if (!empty($payment['bukti_bayar']) && file_exists(__DIR__ . '/../uploads/bukti/' . $payment['bukti_bayar'])): ?>
    <div class="bukti">
        <p><strong>Bukti Pembayaran:</strong></p>
        <a href="#zoom"><img src="../uploads/bukti/<?= htmlspecialchars($payment['bukti_bayar']); ?>" alt="Bukti Pembayaran"></a>
    </div>

    <div id="zoom" class="modal" onclick="window.location='#'">
        <img src="../uploads/bukti/<?= htmlspecialchars($payment['bukti_bayar']); ?>" alt="Bukti Zoom">
    </div>
    <?php endif; ?>

    <div style="text-align:center;">
        <a href="../StatusBooking.php" class="btn">⬅️ Kembali ke Status Booking</a>
    </div>
</main>

<footer>
    &copy; 2025 Tahura Raden Soerjo
</footer>
<?php if (!empty($success_message)): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?= addslashes($success_message) ?>',
    confirmButtonColor: '#43a047'
});
</script>
<?php elseif (!empty($error_message)): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '<?= addslashes($error_message) ?>',
    confirmButtonColor: '#e53935'
});
</script>
<?php endif; ?>

</body>
</html>