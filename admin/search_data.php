<?php
// search_data.php
header('Content-Type: application/json; charset=utf-8');
include '../includes/auth_admin.php';   // proteksi admin
include '../backend/koneksi.php';       // koneksi database

$q      = isset($_GET['q']) ? $conn->real_escape_string(trim($_GET['q'])) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string(trim($_GET['status'])) : '';
$jalur  = isset($_GET['jalur']) ? $conn->real_escape_string(trim($_GET['jalur'])) : '';
$bulan  = isset($_GET['bulan']) ? $conn->real_escape_string(trim($_GET['bulan'])) : '';

$conditions = [];

// 🔍 Search text
if($q !== ''){
    $conditions[] = "(p.kode_token LIKE '%$q%' 
                   OR p.nama_ketua LIKE '%$q%' 
                   OR jp.nama_jalur LIKE '%$q%')";
}

// 🔄 Normalisasi status
if($status !== ''){
    $status_map = [
        'lunas'          => 'terkonfirmasi',
        'LUNAS'          => 'terkonfirmasi',
        'terkonfirmasi'  => 'terkonfirmasi',
        'Terkonfirmasi'  => 'terkonfirmasi',
        'pending'        => 'pending',
        'Pending'        => 'pending'
    ];

    $normal = $status_map[$status] ?? $status;

    // cek di pembayaran ATAU di pesanan
    $conditions[] = "(b.status_pembayaran = '$normal' 
                      OR p.status_pesanan = '$normal')";
}

// 🎯 Filter jalur
if($jalur !== ''){
    $conditions[] = "jp.nama_jalur = '$jalur'";
}

// 📅 Filter bulan
if($bulan !== ''){
    if(preg_match('/^\d{4}-\d{2}$/', $bulan)){
        $conditions[] = "DATE_FORMAT(p.tanggal_pesan, '%Y-%m') = '$bulan'";
    } else {
        $conditions[] = "MONTH(p.tanggal_pesan) = '$bulan'";
    }
}

$where = count($conditions) ? 'WHERE '.implode(' AND ', $conditions) : '';

$sql = "
SELECT 
    p.pesanan_id, 
    p.kode_token, 
    p.nama_ketua, 
    jp.nama_jalur, 
    DATE_FORMAT(p.tanggal_pesan, '%Y-%m-%d') AS tanggal_pesan,
    IFNULL(b.status_pembayaran, p.status_pesanan) AS status_pembayaran, 
    p.total_bayar
FROM pesanan p
LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
LEFT JOIN jalur_pendakian jp ON d.jalur_id = jp.jalur_id
LEFT JOIN pembayaran b ON p.pesanan_id = b.pesanan_id
$where
ORDER BY p.tanggal_pesan DESC
LIMIT 200
";

$res = $conn->query($sql);
$data = [];

while($r = $res->fetch_assoc()){
    $data[] = $r;
}

echo json_encode($data);
