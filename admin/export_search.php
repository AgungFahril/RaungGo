<?php
// export_search.php
include '../includes/auth_admin.php';   // proteksi admin
include '../backend/koneksi.php';       // koneksi database

$q = isset($_GET['q']) ? $conn->real_escape_string(trim($_GET['q'])) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string(trim($_GET['status'])) : '';
$jalur = isset($_GET['jalur']) ? $conn->real_escape_string(trim($_GET['jalur'])) : '';
$bulan = isset($_GET['bulan']) ? $conn->real_escape_string(trim($_GET['bulan'])) : '';

$conditions = [];
if($q !== '') $conditions[] = "(p.kode_token LIKE '%$q%' OR p.nama_ketua LIKE '%$q%' OR jp.nama_jalur LIKE '%$q%')";
if($status !== '') $conditions[] = "b.status_pembayaran = '$status'";
if($jalur !== '') $conditions[] = "jp.nama_jalur = '$jalur'";
if($bulan !== '') {
  if(preg_match('/^\d{4}-\d{2}$/', $bulan)) $conditions[] = "DATE_FORMAT(p.tanggal_pesan, '%Y-%m') = '$bulan'";
  else $conditions[] = "MONTH(p.tanggal_pesan) = '$bulan'";
}

$where = count($conditions) ? 'WHERE '.implode(' AND ', $conditions) : '';

$sql = "
SELECT p.pesanan_id, p.kode_token, p.nama_ketua, jp.nama_jalur, p.tanggal_pesan, IFNULL(b.status_pembayaran,'-') AS status_pembayaran, p.total_bayar
FROM pesanan p
LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
LEFT JOIN jalur_pendakian jp ON d.jalur_id = jp.jalur_id
LEFT JOIN pembayaran b ON p.pesanan_id = b.pesanan_id
$where
ORDER BY p.tanggal_pesan DESC
LIMIT 1000
";

$res = $conn->query($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=export_search_'.date('Ymd_His').'.csv');

$out = fopen('php://output','w');
fputcsv($out, ['pesanan_id','kode_token','nama_ketua','nama_jalur','tanggal_pesan','total_bayar','status_pembayaran']);
while($r = $res->fetch_assoc()){
  fputcsv($out, [$r['pesanan_id'],$r['kode_token'],$r['nama_ketua'],$r['nama_jalur'],$r['tanggal_pesan'],$r['total_bayar'],$r['status_pembayaran']]);
}
fclose($out);
exit;
