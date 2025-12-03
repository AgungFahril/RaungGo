<?php
include '../backend/koneksi.php';

$id     = $_POST['id'];
$status = $_POST['status'];

$conn->query("UPDATE pembayaran SET status_pembayaran='$status' WHERE pembayaran_id='$id'");

echo "Status pembayaran berhasil diperbarui!";
?>
