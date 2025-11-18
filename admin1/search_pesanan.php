<?php
include '../backend/koneksi.php';

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$status = $_GET['status'] ?? '';
$jalur = $_GET['jalur'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$bulan = $_GET['bulan'] ?? '';

$sql = "SELECT p.pesanan_id, p.user_id, p.tanggal_pesan, p.jumlah_pendaki, p.total_bayar, p.status_pesanan, p.nama_ketua, p.telepon_ketua, p.no_identitas, u.nama as user_nama, j.nama_jalur
        FROM pesanan p
        LEFT JOIN users u ON p.user_id = u.user_id
        LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
        LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
        WHERE 1";

$where = [];

if (!empty($q)) {
    $q = $conn->real_escape_string($q);
    $where[] = "(p.nama_ketua LIKE '%$q%' OR p.pesanan_id LIKE '%$q%' OR p.no_identitas LIKE '%$q%' OR p.telepon_ketua LIKE '%$q%' OR u.nama LIKE '%$q%')";
}

if (!empty($status)) {
    $status = $conn->real_escape_string($status);
    $where[] = "p.status_pesanan = '$status'";
}

if (!empty($jalur)) {
    $jalur = $conn->real_escape_string($jalur);
    $where[] = "j.nama_jalur = '$jalur'";
}

if (!empty($tanggal)) {
    $tanggal = $conn->real_escape_string($tanggal);
    $where[] = "DATE(p.tanggal_pesan) = '$tanggal'";
}

if (!empty($bulan)) {
    $bulan = $conn->real_escape_string($bulan);
    $where[] = "DATE_FORMAT(p.tanggal_pesan, '%Y-%m') = '$bulan'";
}

if ($where) {
    $sql .= " AND " . implode(' AND ', $where);
}

$sql .= " ORDER BY p.tanggal_pesan DESC LIMIT 200";

$result = $conn->query($sql);
$rows = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

echo json_encode($rows);
?>
