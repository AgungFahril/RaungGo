<?php
// =============================================
// 💳 PEMBAYARAN PENDAKIAN — FINAL FIXED VERSION (logic only)
// =============================================
date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../backend/koneksi.php';

// =============================================
// Ambil pesanan_id dari URL
// =============================================
$pesanan_id = isset($_GET['pesanan_id']) ? intval($_GET['pesanan_id']) : null;
if (!$pesanan_id) {
    header("Location: dashboard.php");
    exit;
}

// =============================================
// Ambil data pesanan
// =============================================
$q = $conn->prepare("
    SELECT ps.*, jp.nama_jalur, p.tanggal_pendakian
    FROM pesanan ps
    JOIN pendakian p ON ps.pendakian_id = p.pendakian_id
    JOIN jalur_pendakian jp ON p.jalur_id = jp.jalur_id
    WHERE ps.pesanan_id = ?
");
$q->bind_param("i", $pesanan_id);
$q->execute();
$pesanan = $q->get_result()->fetch_assoc();
$q->close();

if (!$pesanan) {
    header("Location: dashboard.php");
    exit;
}

// =============================================
// Ambil status pembayaran terakhir (jika ada)
// =============================================
$lastPaymentStatus = null;
$qp = $conn->prepare("SELECT status_pembayaran FROM pembayaran WHERE pesanan_id = ? ORDER BY pembayaran_id DESC LIMIT 1");
$qp->bind_param("i", $pesanan_id);
$qp->execute();
$res_p = $qp->get_result()->fetch_assoc();
$qp->close();
$lastPaymentStatus = $res_p['status_pembayaran'] ?? null;

// =============================================
// Kebijakan akses:
// Izinkan akses ke form pembayaran jika:
// - pesanan.status_pesanan == 'menunggu_pembayaran'
// OR
// - pesanan.status_pesanan == 'menunggu_konfirmasi' AND lastPaymentStatus == 'ditolak'
// Jika tidak, redirect ke detail_transaksi
// =============================================
$ps = $pesanan['status_pesanan'] ?? '';

$allow = false;
if ($ps === 'menunggu_pembayaran') {
    $allow = true;
} elseif ($ps === 'menunggu_konfirmasi' && $lastPaymentStatus === 'ditolak') {
    // user boleh upload ulang bukti
    $allow = true;
}

if (!$allow) {
    // user tidak boleh mengakses halaman bayar saat bukti sudah masuk & sedang diverifikasi, atau pesanan final
    header("Location: detail_transaksi.php?pesanan_id=" . $pesanan_id);
    exit;
}

// =============================================
// Jika form disubmit (upload bukti pembayaran)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_bukti'])) {
    try {
        // Ambil jumlah bayar dari database (trusted)
        $jumlah_bayar = floatval($pesanan['total_bayar']);
        $tanggal_bayar = date('Y-m-d H:i:s');
        $metode = 'transfer_bank';
        $status_pembayaran = 'pending';

        if (empty($_FILES['bukti_bayar']['name'])) {
            throw new Exception("Silakan unggah file bukti pembayaran terlebih dahulu.");
        }

        // Folder upload
        $target_dir = "../uploads/bukti/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($ext, $allowed)) {
            throw new Exception("Format file tidak didukung ($ext). Gunakan JPG, PNG, atau PDF.");
        }

        $filename = "bukti_" . $pesanan_id . "_" . time() . "." . $ext;
        $target_file = $target_dir . $filename;

        if (!move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $target_file)) {
            throw new Exception("Gagal menyimpan file bukti pembayaran ke server.");
        }

        // Insert pembayaran (baru)
        $stmt = $conn->prepare("
            INSERT INTO pembayaran (pesanan_id, metode, jumlah_bayar, tanggal_bayar, bukti_bayar, status_pembayaran)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isdsss", $pesanan_id, $metode, $jumlah_bayar, $tanggal_bayar, $filename, $status_pembayaran);
        $stmt->execute();
        $stmt->close();

        // Update status pesanan -> menunggu_konfirmasi
        $u = $conn->prepare("UPDATE pesanan SET status_pesanan = 'menunggu_konfirmasi' WHERE pesanan_id = ?");
        $u->bind_param("i", $pesanan_id);
        $u->execute();
        $u->close();

        $_SESSION['success_message'] = "Bukti pembayaran berhasil dikirim! Menunggu konfirmasi administrator.";
        // Redirect ke detail_transaksi (langsung)
        header("Location: detail_transaksi.php?pesanan_id=" . $pesanan_id);
        exit;

    } catch (Exception $e) {
        // Log (opsional) dan kirim pesan ke user via session
        error_log("Upload bukti error: " . $e->getMessage());
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: detail_transaksi.php?pesanan_id=" . $pesanan_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pembayaran Pendakian</title>
<link rel="stylesheet" href="../style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* TAMPILAN TIDAK DIUBAH — sama persis dengan versi Anda sebelumnya */
body {
    font-family: 'Poppins', sans-serif;
    background: #f5faf5 url('../images/Gunung_Raung.jpg') no-repeat center top;
    background-size: cover;
    color: #333;
}
.container {
    width: 85%;
    margin: 110px auto;
    background: rgba(255,255,255,0.97);
    border-radius: 14px;
    padding: 30px 40px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
h2 { text-align: center; color: #2e7d32; margin-bottom: 25px; font-weight: 700; }
.rekening-box {
    background: #fffde7;
    border: 1px solid #fff59d;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 25px;
}
input, input[type=file] {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.btn {
    background: #43a047;
    color: white;
    border: none;
    padding: 12px;
    width: 100%;
    border-radius: 8px;
    margin-top: 20px;
    font-weight: 600;
    cursor: pointer;
}
.btn:hover { background: #2e7d32; }
.btn-danger {
    background: #d32f2f !important;
}
.btn-danger:hover {
    background: #b71c1c !important;
}
</style>
</head>
<body>

<?php include '../includes/navbar_user.php'; ?>

<main class="container">
    <h2>💳 Pembayaran Pendakian</h2>

    <div class="rekening-box">
        <p><strong>Kode Token Booking:</strong> <?= htmlspecialchars($pesanan['kode_token']); ?></p>
        <p><strong>Nama Jalur:</strong> <?= htmlspecialchars($pesanan['nama_jalur']); ?></p>
        <p><strong>Tanggal Pendakian:</strong> <?= htmlspecialchars($pesanan['tanggal_pendakian']); ?></p>
        <p><strong>Total Bayar:</strong> Rp <?= number_format($pesanan['total_bayar'], 0, ',', '.'); ?></p>
    </div>

    <div class="rekening-box">
        <p>🏦 <b>Bank BRI</b></p>
        <p>No. Rekening: <b>653101005713502</b></p>
        <p>Atas Nama: <b>Agung Fahril Gunawan</b></p>
    </div>

    <form method="POST" enctype="multipart/form-data" id="formBayar">
        <input type="hidden" name="upload_bukti" value="1">

        <label>Jumlah Bayar (Rp)</label>
        <input type="text" value="Rp <?= number_format($pesanan['total_bayar'], 0, ',', '.'); ?>" readonly>

        <label>Upload Bukti Pembayaran</label>
        <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf" required>

        <button type="button" class="btn" onclick="konfirmasiBayar()">Kirim Bukti Pembayaran</button>
    </form>

    <!-- Tombol Batalkan Pesanan -->
    <button class="btn btn-danger" onclick="batalkanPesanan()">Batalkan Pesanan</button>

</main>

<script>
function konfirmasiBayar() {
    Swal.fire({
        icon: 'question',
        title: 'Kirim Bukti Pembayaran?',
        text: 'Pastikan jumlah dan file bukti sudah benar.',
        showCancelButton: true,
        confirmButtonColor: '#2e7d32'
    }).then((res) => {
        if (res.isConfirmed) {
            document.getElementById('formBayar').submit();
        }
    });
}

function batalkanPesanan() {
    Swal.fire({
        icon: 'warning',
        title: 'Batalkan Pesanan?',
        text: 'Booking yang dibatalkan tidak dapat dipulihkan.',
        showCancelButton: true,
        confirmButtonColor: '#d32f2f'
    }).then((res) => {
        if (res.isConfirmed) {
            window.location = '../backend/proses_batal.php?pesanan_id=<?= $pesanan_id ?>';
        }
    });
}
</script>

<footer style="text-align:center; padding:20px; color:#555;">
    &copy; 2025 Tahura Raden Soerjo
</footer>

</body>
</html>
