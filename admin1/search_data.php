<?php
// search_data.php
header('Content-Type: application/json; charset=utf-8');
include '../backend/koneksi.php';

$q = isset($_GET['q']) ? $conn->real_escape_string(trim($_GET['q'])) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string(trim($_GET['status'])) : '';
$jalur = isset($_GET['jalur']) ? $conn->real_escape_string(trim($_GET['jalur'])) : '';
$bulan = isset($_GET['bulan']) ? $conn->real_escape_string(trim($_GET['bulan'])) : '';

$conditions = [];
if($q !== ''){
  $conditions[] = "(p.kode_token LIKE '%$q%' OR p.nama_ketua LIKE '%$q%' OR jp.nama_jalur LIKE '%$q%')";
}
if($status !== ''){
  // normalize common words
  $map = [
    'terkonfirmasi'=>'terkonfirmasi','terkonfirmasi'=>'terkonfirmasi','LUNAS'=>'terkonfirmasi',
    'pending'=>'pending','Pending'=>'pending'
  ];
  $stat = $map[$status] ?? $status;
  $conditions[] = "b.status_pembayaran = '$stat'";
}
if($jalur !== ''){
  $conditions[] = "jp.nama_jalur = '$jalur'";
}
if($bulan !== ''){
  // $bulan expected as '01'..'12' or 'YYYY-MM' - try both
  if(preg_match('/^\d{4}-\d{2}$/', $bulan)){
    $conditions[] = "DATE_FORMAT(p.tanggal_pesan, '%Y-%m') = '$bulan'";
  } else {
    $conditions[] = "MONTH(p.tanggal_pesan) = '$bulan'";
  }
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
LIMIT 100
";

$res = $conn->query($sql);
$data = [];
while($r = $res->fetch_assoc()){
  $data[] = $r;
}

echo json_encode($data);
