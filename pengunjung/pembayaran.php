<?php
// =============================================
// 💳 PEMBAYARAN PENDAKIAN — FINAL VERSION
// =============================================
date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../backend/koneksi.php';

$logFile = "../backend/error_upload.txt";

// --- Ambil pesanan_id dari URL
$pesanan_id = $_GET['pesanan_id'] ?? null;
if (!$pesanan_id) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}

// --- Ambil data pesanan
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
    echo "<script>alert('Data pesanan tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}

// --- Jika sudah bayar / menunggu konfirmasi → langsung ke detail
if (in_array($pesanan['status_pesanan'], ['menunggu_konfirmasi', 'lunas', 'dibatalkan'])) {
    header("Location: detail_transaksi.php?pesanan_id={$pesanan_id}");
    exit;
}

// --- Jika form disubmit (upload bukti pembayaran)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_bukti'])) {
    try {
        $jumlah_bayar = intval($pesanan['total_bayar']);
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

        // Simpan pembayaran
        $stmt = $conn->prepare("
            INSERT INTO pembayaran (pesanan_id, metode, jumlah_bayar, tanggal_bayar, bukti_bayar, status_pembayaran)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isdsss", $pesanan_id, $metode, $jumlah_bayar, $tanggal_bayar, $filename, $status_pembayaran);
        $stmt->execute();
        $stmt->close();

        // Update status pesanan
        $conn->query("UPDATE pesanan SET status_pesanan='menunggu_konfirmasi' WHERE pesanan_id=$pesanan_id");

        $_SESSION['success_message'] = "Bukti pembayaran berhasil dikirim!";

        // Redirect aman
        header("Location: detail_transaksi.php?pesanan_id={$pesanan_id}");
        echo "<script>window.location.href='detail_transaksi.php?pesanan_id={$pesanan_id}';</script>";
        exit;

    } catch (Exception $e) {

        // Simpan log error
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . "\n", FILE_APPEND);

        $_SESSION['error_message'] = $e->getMessage();

        header("Location: detail_transaksi.php?pesanan_id={$pesanan_id}");
        echo "<script>window.location.href='detail_transaksi.php?pesanan_id={$pesanan_id}';</script>";
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
h2 { 
    text-align: center; 
    color: #2e7d32; 
    margin-bottom: 25px; 
    font-weight: 700; 
}
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
</style>
</head>
<body>

<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

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

<?php if ($pesanan['status_pesanan'] !== 'menunggu_pembayaran'): ?>
    <script>
        Swal.fire(
            'Pembayaran Tidak Tersedia',
            'Pesanan sudah dibayar atau sedang diverifikasi admin.',
            'warning'
        ).then(()=> {
            window.location = 'detail_transaksi.php?pesanan_id=<?= intval($pesanan_id) ?>';
        });
    </script>
<?php exit; endif; ?>

    <form method="POST" enctype="multipart/form-data" id="formBayar">
        <input type="hidden" name="upload_bukti" value="1">

        <label>Jumlah Bayar (Rp)</label>
        <input type="text" value="Rp <?= number_format($pesanan['total_bayar'], 0, ',', '.'); ?>" readonly>

        <label>Upload Bukti Pembayaran</label>
        <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf" required>

        <button type="button" class="btn" onclick="konfirmasiBayar()">Kirim Bukti Pembayaran</button>
    </form>

    <button class="btn" style="background:#d32f2f" onclick="batalkanPesanan()">Batalkan Pesanan</button>
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
        text: 'Booking ini tidak bisa dipulihkan.',
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
