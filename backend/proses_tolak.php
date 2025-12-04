<?php
include 'koneksi.php';

// Validasi ID
if (!isset($_GET['id'])) {
    header("Location: ../admin/pesanan.php?msg=ID pesanan tidak ditemukan&type=danger");
    exit;
}

$id = intval($_GET['id']);

// Ambil kode token
$get = $conn->query("SELECT kode_token FROM pesanan WHERE pesanan_id = $id");

if ($get->num_rows == 0) {
    header("Location: ../admin/pesanan.php?msg=Pesanan tidak ditemukan&type=danger");
    exit;
}

$data = $get->fetch_assoc();
$token = $data['kode_token'];

// Update status menjadi DIBATALKAN
$conn->query("UPDATE pesanan SET status_pesanan = 'dibatalkan' WHERE pesanan_id = $id");

// Redirect
header("Location: ../admin/pesanan.php?msg=Pesanan dengan kode token $token telah ditolak&type=danger");
exit;
