<?php
include '../backend/koneksi.php';

$id = $_GET['id'];
$aksi = $_GET['aksi'];

if ($aksi == "setujui") {
    $conn->query("UPDATE pesanan SET status_pesanan='disetujui' WHERE pesanan_id='$id'");
}

if ($aksi == "batalkan") {
    $conn->query("UPDATE pesanan SET status_pesanan='dibatalkan' WHERE pesanan_id='$id'");
}

header("Location: pesanan.php");
exit;
?>
