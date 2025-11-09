<?php
session_start();
include 'koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Pastikan sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu sebelum melakukan booking.'); window.location='../login.php';</script>";
    exit;
}

// ✅ Ambil data dari form
$user_id        = $_SESSION['user_id'];
$pendakian_id   = $_POST['pendakian_id'] ?? '';
$jumlah_pendaki = $_POST['jumlah_pendaki'] ?? '';
$nama_ketua     = $_POST['nama_ketua'] ?? '';
$email_ketua    = $_POST['email_ketua'] ?? '';
$telepon_ketua  = $_POST['telepon_ketua'] ?? '';
$alamat_ketua   = $_POST['alamat_ketua'] ?? '';
$no_identitas   = $_POST['no_identitas_ketua'] ?? '';
$tanggal_pesan  = date('Y-m-d H:i:s');
$status_pesanan = 'menunggu_pembayaran';

// ✅ Validasi wajib
if (empty($pendakian_id) || empty($jumlah_pendaki) || empty($nama_ketua) || empty($telepon_ketua) || empty($alamat_ketua) || empty($no_identitas)) {
    echo "<script>alert('Harap lengkapi semua data booking.'); history.back();</script>";
    exit;
}

// ✅ Ambil tarif tiket
$q = $conn->prepare("SELECT j.tarif_tiket FROM pendakian p 
                     JOIN jalur_pendakian j ON p.jalur_id = j.jalur_id 
                     WHERE p.pendakian_id = ?");
$q->bind_param("i", $pendakian_id);
$q->execute();
$res = $q->get_result();
$tarif = $res->fetch_assoc()['tarif_tiket'] ?? 15000;
$q->close();

// ✅ Hitung total bayar
$total_bayar = $tarif * $jumlah_pendaki;

// ✅ Buat kode token unik (8 karakter)
$kode_token = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));



// ✅ Simpan ke tabel pesanan
$stmt = $conn->prepare("INSERT INTO pesanan 
    (user_id, pendakian_id, tanggal_pesan, jumlah_pendaki, total_bayar, status_pesanan, kode_token, nama_ketua, telepon_ketua, alamat_ketua, no_identitas)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisiissssss", $user_id, $pendakian_id, $tanggal_pesan, $jumlah_pendaki, $total_bayar, $status_pesanan, $kode_token, $nama_ketua, $telepon_ketua, $alamat_ketua, $no_identitas);

if ($stmt->execute()) {
    $pesanan_id = $stmt->insert_id;
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Booking Berhasil!',
            html: 'Kode Token Anda: <b>$kode_token</b><br><small>Simpan kode ini untuk cek status pembayaran.</small>',
            confirmButtonColor: '#43a047'
        }).then(() => window.location='../pengunjung/pembayaran.php?pesanan_id=$pesanan_id');
    </script>";
} else {
    echo "<script>alert('Terjadi kesalahan: " . addslashes($conn->error) . "'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>
