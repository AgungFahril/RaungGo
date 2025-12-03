<?php
// aksi_transaksi.php
include '../backend/koneksi.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_pembayaran.php'); exit;
}

$action = $_POST['action'] ?? '';
$id = intval($_POST['pembayaran_id'] ?? 0);

if(!$id || !$action) {
  header('Location: data_pembayaran.php'); exit;
}

if($action == 'confirm') {
  // set pembayaran lunas and set pesanan status to disetujui
  $conn->begin_transaction();
  $upd = $conn->query("UPDATE pembayaran SET status_pembayaran='lunas' WHERE pembayaran_id=$id");
  // get pesanan_id
  $res = $conn->query("SELECT pesanan_id FROM pembayaran WHERE pembayaran_id=$id");
  $pid = $res->fetch_assoc()['pesanan_id'] ?? 0;
  if($pid) $conn->query("UPDATE pesanan SET status_pesanan='disetujui' WHERE pesanan_id=".$pid);
  $conn->commit();
} elseif($action == 'reject') {
  $conn->query("UPDATE pembayaran SET status_pembayaran='ditolak' WHERE pembayaran_id=$id");
  $res = $conn->query("SELECT pesanan_id FROM pembayaran WHERE pembayaran_id=$id");
  $pid = $res->fetch_assoc()['pesanan_id'] ?? 0;
  if($pid) $conn->query("UPDATE pesanan SET status_pesanan='dibatalkan' WHERE pesanan_id=".$pid);
}

header('Location: data_pembayaran.php');
exit;
