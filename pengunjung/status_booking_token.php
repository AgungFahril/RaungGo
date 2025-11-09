<?php
session_start();
include '../backend/koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$kode_token = $_POST['kode_token'] ?? '';

if (!$kode_token) {
    echo "<script>alert('Masukkan kode token terlebih dahulu!'); history.back();</script>";
    exit;
}

$q = $conn->prepare("
    SELECT pesanan_id FROM pesanan WHERE kode_token = ?
");
$q->bind_param("s", $kode_token);
$q->execute();
$res = $q->get_result()->fetch_assoc();
$q->close();

if (!$res) {
    echo "<script>alert('Kode token tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}

// Redirect ke detail transaksi
header("Location: detail_transaksi.php?pesanan_id=" . $res['pesanan_id']);
exit;
?>
