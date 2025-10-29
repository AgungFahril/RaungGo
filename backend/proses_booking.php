<?php
session_start();
include 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Ambil data dari form
$user_id         = $_SESSION['user_id'];
$tanggal_naik    = $_POST['tanggal_naik'] ?? '';
$tanggal_turun   = $_POST['tanggal_turun'] ?? '';
$jumlah_pendaki  = $_POST['jumlah_pendaki'] ?? '';
$telepon_ketua   = $_POST['telepon_ketua'] ?? '';
$setuju_sop      = isset($_POST['setuju_sop']) ? 1 : 0;

// Validasi input dasar
if (empty($tanggal_naik) || empty($tanggal_turun) || empty($jumlah_pendaki)) {
    echo "<script>alert('Harap isi semua kolom wajib.'); history.back();</script>";
    exit();
}

// Validasi tanggal
if ($tanggal_turun < $tanggal_naik) {
    echo "<script>alert('Tanggal turun tidak boleh lebih awal dari tanggal naik!'); history.back();</script>";
    exit();
}

// ===============================================
// LOGIKA TAMBAHAN (sementara karena belum ada kolom lengkap):
// - pendakian_id sementara kita set default ke '1' (misal Gunung Raung)
// - total_bayar dihitung sederhana, misal 50.000/orang
// ===============================================
$pendakian_id = 1;
$total_bayar = $jumlah_pendaki * 50000; // contoh tarif 50rb/orang
$status_pesanan = 'Pending';
$tanggal_pesan = date('Y-m-d H:i:s');

// Simpan ke tabel pesanan
$query = "INSERT INTO pesanan (user_id, pendakian_id, tanggal_pesan, jumlah_pendaki, total_bayar, status_pesanan)
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iisiis", $user_id, $pendakian_id, $tanggal_pesan, $jumlah_pendaki, $total_bayar, $status_pesanan);

if ($stmt->execute()) {
    echo "<script>
        alert('Booking berhasil dikirim! Silakan cek status di menu Status Booking.');
        window.location.href = '../pengunjung/StatusBooking.php';
    </script>";
} else {
    echo "<script>
        alert('Terjadi kesalahan saat menyimpan data: " . addslashes($conn->error) . "');
        history.back();
    </script>";
}

$stmt->close();
$conn->close();
?>
