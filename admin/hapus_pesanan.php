<?php
include '../backend/koneksi.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die('ID pesanan tidak valid');
}

// Cek apakah pesanan ada
$check = $conn->query("SELECT pesanan_id FROM pesanan WHERE pesanan_id = $id");
if (!$check || $check->num_rows == 0) {
    die('Pesanan tidak ditemukan');
}

// Hapus anggota pendaki terlebih dahulu (foreign key constraint)
$conn->query("DELETE FROM anggota_pendaki WHERE pesanan_id = $id");

// Hapus pembayaran terkait
$conn->query("DELETE FROM pembayaran WHERE pesanan_id = $id");

// Hapus pesanan
$result = $conn->query("DELETE FROM pesanan WHERE pesanan_id = $id");

if ($result) {
    echo "<script>alert('Pesanan berhasil dihapus'); window.location='pesanan.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus pesanan'); window.location='pesanan.php';</script>";
}
?>
