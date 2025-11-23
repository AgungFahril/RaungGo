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

// 1) prepare last 12 months labels (YYYY-MM)
$months = [];
for ($i = 11; $i >= 0; $i--) {
    $months[] = date('Y-m', strtotime("-{$i} months"));
}
$output['bulan'] = $months;

// 2) jumlah pesanan per bulan (last 12 months)
$q = $conn->query("
  SELECT DATE_FORMAT(tanggal_pesan, '%Y-%m') AS ym, COUNT(*) AS total
  FROM pesanan
  WHERE tanggal_pesan IS NOT NULL
    AND tanggal_pesan >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH),'%Y-%m-01')
  GROUP BY ym
  ORDER BY ym ASC
");
$mapPesanan = [];
while ($r = $q->fetch_assoc()) {
    $mapPesanan[$r['ym']] = (int)$r['total'];
}
foreach ($months as $m) {
    $output['jumlah_pesanan'][] = isset($mapPesanan[$m]) ? $mapPesanan[$m] : 0;
}

// 3) total pendapatan per bulan (status terkonfirmasi) — last 12 months
$q = $conn->query("
  SELECT DATE_FORMAT(tanggal_bayar, '%Y-%m') AS ym, SUM(jumlah_bayar) AS total
  FROM pembayaran
  WHERE status_pembayaran = 'terkonfirmasi'
    AND tanggal_bayar IS NOT NULL
    AND tanggal_bayar >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH),'%Y-%m-01')
  GROUP BY ym
  ORDER BY ym ASC
");
$mapPendapatan = [];
while ($r = $q->fetch_assoc()) {
    $mapPendapatan[$r['ym']] = (float)$r['total'];
}
foreach ($months as $m) {
    $output['total_pendapatan'][] = isset($mapPendapatan[$m]) ? $mapPendapatan[$m] : 0;
}

// 4) distribusi jalur (semua jalur, hitung jumlah pesanan via join)
$q = $conn->query("
  SELECT jp.jalur_id, jp.nama_jalur, COUNT(p.pesanan_id) AS total
  FROM jalur_pendakian jp
  LEFT JOIN pendakian d ON jp.jalur_id = d.jalur_id
  LEFT JOIN pesanan p ON p.pendakian_id = d.pendakian_id
  GROUP BY jp.jalur_id
  ORDER BY total DESC, jp.nama_jalur ASC
");
while ($r = $q->fetch_assoc()) {
    $output['nama_jalur'][] = $r['nama_jalur'];
    $output['jumlah_jalur'][] = (int)$r['total'];
}

// 5) status pembayaran counts (semua status yang ada)
$q = $conn->query("SELECT status_pembayaran, COUNT(*) AS total FROM pembayaran GROUP BY status_pembayaran");
while ($r = $q->fetch_assoc()) {
    $output['status_pembayaran'][] = $r['status_pembayaran'];
    $output['jumlah_status'][] = (int)$r['total'];
}

// final
echo json_encode($output, JSON_UNESCAPED_UNICODE);
