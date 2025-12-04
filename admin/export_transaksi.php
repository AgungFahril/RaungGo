<?php
include '../includes/auth_admin.php';   // proteksi admin
include '../backend/koneksi.php';       // koneksi database

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transaksi_export_'.date('Ymd_His').'.csv');

$out = fopen('php://output','w');
fputcsv($out, ['pembayaran_id','pesanan_id','pemesan','jumlah_bayar','tanggal_bayar','status_pembayaran']);

$where = "";
if(isset($_GET['pembayaran_id'])) $where = " WHERE bayar.pembayaran_id = ".intval($_GET['pembayaran_id']);

$res = $conn->query("SELECT bayar.*, u.nama AS pemesan FROM pembayaran bayar LEFT JOIN pesanan p ON bayar.pesanan_id=p.pesanan_id LEFT JOIN users u ON p.user_id=u.user_id $where ORDER BY bayar.tanggal_bayar DESC");

while($r = $res->fetch_assoc()){
  fputcsv($out, [$r['pembayaran_id'],$r['pesanan_id'], $r['pemesan'], $r['jumlah_bayar'], $r['tanggal_bayar'], $r['status_pembayaran']]);
}
fclose($out);
exit;
