<?php
session_start();
include 'koneksi.php'; // koneksi ke database

// ✅ Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu sebelum melakukan booking.'); window.location='../login.php';</script>";
    exit;
}

// ✅ Ambil data dari form
$user_id          = $_SESSION['user_id'];
$pendakian_id     = $_POST['pendakian_id'] ?? '';
$tanggal_naik     = $_POST['tanggal_naik'] ?? '';
$tanggal_turun    = $_POST['tanggal_turun'] ?? '';
$jumlah_pendaki   = $_POST['jumlah_pendaki'] ?? 0;
$nama_ketua       = $_POST['nama_ketua'] ?? '';
$email_ketua      = $_POST['email_ketua'] ?? '';
$telepon_ketua    = $_POST['telepon_ketua'] ?? '';
$alamat_ketua     = $_POST['alamat_ketua'] ?? '';
$no_identitas     = $_POST['no_identitas_ketua'] ?? '';
$tanggal_pesan    = date('Y-m-d');

// ✅ Validasi input wajib
if (empty($pendakian_id) || empty($tanggal_naik) || empty($tanggal_turun) || empty($jumlah_pendaki)) {
    echo "<script>alert('Harap lengkapi semua data booking.'); history.back();</script>";
    exit;
}

// ✅ Cek checkbox SOP
if (!isset($_POST['setuju_sop'])) {
    echo "<script>alert('Anda harus menyetujui SOP pendakian terlebih dahulu.'); history.back();</script>";
    exit;
}

// ✅ Hitung total bayar (misal Rp15.000 per orang)
$biaya_per_orang = 15000;
$total_bayar = $jumlah_pendaki * $biaya_per_orang;

// ✅ Status awal
$status_pesanan = "Menunggu Pembayaran";

// ✅ Simpan ke tabel pesanan
$sql = "INSERT INTO pesanan (user_id, pendakian_id, tanggal_pesan, jumlah_pendaki, total_bayar, status_pesanan)
        VALUES ('$user_id', '$pendakian_id', '$tanggal_pesan', '$jumlah_pendaki', '$total_bayar', '$status_pesanan')";

if ($conn->query($sql) === TRUE) {
    echo "<script>
            alert('Booking berhasil dikirim! Silakan lanjut ke halaman Status Booking untuk melihat detail pemesanan.');
            window.location='../StatusBooking.php';
          </script>";
} else {
    echo "<script>alert('Terjadi kesalahan: " . addslashes($conn->error) . "'); history.back();</script>";
}

$conn->close();
?>
