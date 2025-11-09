<?php
session_start();
include '../backend/koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔒 Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=pengunjung/pembayaran.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil pesanan terakhir (atau berdasarkan pesanan_id di URL)
$pesanan_id = $_GET['pesanan_id'] ?? null;

$q = $conn->prepare("
    SELECT ps.pesanan_id, ps.tanggal_pesan, ps.total_bayar, ps.status_pesanan, ps.kode_token,
           jp.nama_jalur, p.tanggal_pendakian
    FROM pesanan ps
    JOIN pendakian p ON ps.pendakian_id = p.pendakian_id
    JOIN jalur_pendakian jp ON p.jalur_id = jp.jalur_id
    WHERE ps.user_id = ? AND ps.pesanan_id = ?
    LIMIT 1
");
$q->bind_param("ii", $user_id, $pesanan_id);
$q->execute();
$result = $q->get_result();
$pesanan = $result->fetch_assoc();
$q->close();

if (!$pesanan) {
    echo "<script>alert('Pesanan tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}

// ✅ Proses upload bukti pembayaran
if (isset($_POST['upload'])) {
    $jumlah_bayar = floatval($_POST['jumlah_bayar']);
    $tanggal_bayar = date('Y-m-d H:i:s');
    $metode = 'transfer_bank';
    $status_pembayaran = 'pending';

    if (!empty($_FILES['bukti_bayar']['name'])) {
        $target_dir = "../uploads/bukti/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $ext = pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION);
        $filename = "bukti_" . $pesanan_id . "_" . time() . "." . strtolower($ext);
        $target_file = $target_dir . $filename;

        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array(strtolower($ext), $allowed)) {
            echo "<script>alert('Format file tidak didukung.');</script>";
            exit;
        }

        move_uploaded_file($_FILES["bukti_bayar"]["tmp_name"], $target_file);

        $stmt = $conn->prepare("INSERT INTO pembayaran (pesanan_id, metode, jumlah_bayar, tanggal_bayar, bukti_bayar, status_pembayaran)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $pesanan_id, $metode, $jumlah_bayar, $tanggal_bayar, $filename, $status_pembayaran);
        $stmt->execute();
        $stmt->close();

        // Update status pesanan jadi menunggu_verifikasi
        $conn->query("UPDATE pesanan SET status_pesanan='menunggu_verifikasi' WHERE pesanan_id=$pesanan_id");

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Bukti Pembayaran Dikirim!',
                html: 'Admin akan memverifikasi pembayaran kamu.<br><br><b>Kode Token: {$pesanan['kode_token']}</b>',
                confirmButtonColor: '#43a047'
            }).then(() => window.location='detail_transaksi.php?pesanan_id=$pesanan_id');
        </script>";
        exit;
    } else {
        echo "<script>alert('Silakan unggah bukti pembayaran.');</script>";
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
    background: rgba(255,255,255,0.96);
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
label { display: block; margin-top: 10px; font-weight: 600; }
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
        <p><strong>Kode Token Booking Anda:</strong></p>
        <h3 style="color:#e91e63;"><?= htmlspecialchars($pesanan['kode_token']); ?></h3>
        <small><em>Simpan kode ini untuk mengecek status di menu “Status Booking”.</em></small>
    </div>

    <div class="rekening-box">
        <p>🏦 <b>Bank BRI</b></p>
        <p>No. Rekening: <b>1234-5678-91011</b></p>
        <p>Atas Nama: <b>Tahura Raden Soerjo</b></p>
        <small><em>Pastikan nominal transfer sesuai total bayar.</em></small>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <label>Jumlah Bayar (Rp)</label>
        <input type="number" name="jumlah_bayar" required>

        <label>Upload Bukti Pembayaran</label>
        <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf" required>

        <button type="submit" name="upload" class="btn">Kirim Bukti Pembayaran</button>
    </form>
</main>

<footer style="text-align:center; padding:20px; color:#555;">
    &copy; 2025 Tahura Raden Soerjo
</footer>

</body>
</html>
