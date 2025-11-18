<?php
// Aktifkan error reporting saat debugging (nonaktifkan di produksi)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'koneksi.php';

// ✅ Pastikan koneksi database
if (!$conn) {
    die('❌ Gagal koneksi ke database: ' . mysqli_connect_error());
}

// ✅ Pastikan user login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu sebelum melakukan booking.'); window.location='../login.php';</script>";
    exit;
}

// ✅ Ambil data dari form
$user_id        = $_SESSION['user_id'];
$pendakian_id   = $_POST['pendakian_id'] ?? '';
$jumlah_pendaki = $_POST['jumlah_pendaki'] ?? '';
$nama_ketua     = $_POST['nama_ketua'] ?? '';
$telepon_ketua  = $_POST['telepon_ketua'] ?? '';
$alamat_ketua   = $_POST['alamat_ketua'] ?? '';
$no_identitas   = $_POST['no_identitas_ketua'] ?? '';
$tanggal_pesan  = date('Y-m-d H:i:s');
$status_pesanan = 'menunggu_pembayaran';

// ✅ Validasi wajib
if (
    empty($pendakian_id) || empty($jumlah_pendaki) || empty($nama_ketua) ||
    empty($telepon_ketua) || empty($alamat_ketua) || empty($no_identitas)
) {
    echo "<script>alert('Harap lengkapi semua data booking.'); history.back();</script>";
    exit;
}

// ✅ Ambil tarif tiket dari tabel pendakian
$q = $conn->prepare("
    SELECT j.tarif_tiket 
    FROM pendakian p 
    JOIN jalur_pendakian j ON p.jalur_id = j.jalur_id 
    WHERE p.pendakian_id = ?
");
$q->bind_param("i", $pendakian_id);
$q->execute();
$res = $q->get_result();
$data_tarif = $res->fetch_assoc();
$q->close();

$tarif = $data_tarif['tarif_tiket'] ?? 15000;
$total_bayar = intval($tarif) * intval($jumlah_pendaki);

// ✅ Buat kode token unik
$kode_token = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

// ✅ Simpan ke tabel pesanan
$sql = "INSERT INTO pesanan 
(user_id, pendakian_id, tanggal_pesan, jumlah_pendaki, total_bayar, status_pesanan, kode_token, nama_ketua, telepon_ketua, alamat_ketua, no_identitas)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<script>alert('Prepare gagal: " . addslashes($conn->error) . "'); history.back();</script>";
    exit;
}

$stmt->bind_param(
    'iisiissssss',
    $user_id,
    $pendakian_id,
    $tanggal_pesan,
    $jumlah_pendaki,
    $total_bayar,
    $status_pesanan,
    $kode_token,
    $nama_ketua,
    $telepon_ketua,
    $alamat_ketua,
    $no_identitas
);

if (!$stmt->execute()) {
    echo "<script>alert('Gagal eksekusi query: " . addslashes($stmt->error) . "'); history.back();</script>";
    exit;
}

$pesanan_id = $stmt->insert_id;
$stmt->close();
$conn->close();

// ✅ Gunakan buffer agar tidak ada output sebelum <script>
ob_clean();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Memproses Booking...</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    icon: 'success',
    title: 'Booking Berhasil!',
    html: 'Kode Token Anda: <b><?= $kode_token ?></b><br><small>Simpan kode ini untuk cek status pembayaran.</small>',
    confirmButtonColor: '#43a047'
}).then(() => {
    window.location = '../pengunjung/pembayaran.php?pesanan_id=<?= $pesanan_id ?>';
});
</script>
</body>
</html>
