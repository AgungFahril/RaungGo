<?php
// get_data.php
header('Content-Type: application/json; charset=utf-8');
include '../backend/koneksi.php';

$output = [
  'bulan' => [],
  'jumlah_pesanan' => [],
  'total_pendapatan' => [],
  'nama_jalur' => [],
  'jumlah_jalur' => [],
  'status_pembayaran' => [],
  'jumlah_status' => []
];

// 1) labels bulan & jumlah pesanan (last 12 months)
$res = $conn->query("
  SELECT DATE_FORMAT(tanggal_pesan, '%Y-%m') AS ym, COUNT(*) AS total 
  FROM pesanan 
  WHERE tanggal_pesan IS NOT NULL
  GROUP BY ym
  ORDER BY ym ASC
");
while($r = $res->fetch_assoc()){
  $output['bulan'][] = $r['ym'];
  $output['jumlah_pesanan'][] = (int)$r['total'];
}

// 2) pendapatan per bulan (lunas)
$res = $conn->query("
  SELECT DATE_FORMAT(tanggal_bayar, '%Y-%m') AS ym, SUM(jumlah_bayar) AS total 
  FROM pembayaran 
  WHERE status_pembayaran='lunas' AND tanggal_bayar IS NOT NULL
  GROUP BY ym
  ORDER BY ym ASC
");
while($r = $res->fetch_assoc()){
  $output['total_pendapatan'][] = (float)$r['total'];
}

// 3) jalur populer (Klibaru dan Sumberwringin)
$res = $conn->query("
  SELECT jp.nama_jalur, COUNT(p.pesanan_id) AS total
  FROM pesanan p
  JOIN pendakian d ON p.pendakian_id = d.pendakian_id
  JOIN jalur_pendakian jp ON d.jalur_id = jp.jalur_id
  WHERE jp.nama_jalur IN ('Klibaru', 'Sumberwringin')
  GROUP BY jp.jalur_id
  ORDER BY total DESC
");
while($r = $res->fetch_assoc()){
  $output['nama_jalur'][] = $r['nama_jalur'];
  $output['jumlah_jalur'][] = (int)$r['total'];
}

// 4) status pembayaran
$res = $conn->query("SELECT status_pembayaran, COUNT(*) AS total FROM pembayaran GROUP BY status_pembayaran");
while($r = $res->fetch_assoc()){
  $output['status_pembayaran'][] = $r['status_pembayaran'];
  $output['jumlah_status'][] = (int)$r['total'];
}

echo json_encode($output);
