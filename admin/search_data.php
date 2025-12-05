<?php
include '../backend/koneksi.php';
header("Content-Type: application/json");

// Ambil semua parameter
$q      = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";
$jalur  = isset($_GET['jalur']) ? mysqli_real_escape_string($conn, $_GET['jalur']) : "";
$bulan  = isset($_GET['bulan']) ? mysqli_real_escape_string($conn, $_GET['bulan']) : "";
$from   = isset($_GET['from']) ? mysqli_real_escape_string($conn, $_GET['from']) : "";
$to     = isset($_GET['to']) ? mysqli_real_escape_string($conn, $_GET['to']) : "";

// Query dasar (JOIN BENAR SESUAI DATABASE)
$sql = "
    SELECT
        p.pesanan_id,
        p.kode_token,
        p.nama_ketua,
        p.tanggal_pesan,
        p.total_bayar,
        j.nama_jalur,
        pay.status_pembayaran
    FROM pesanan p
    LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
    LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
    LEFT JOIN pembayaran pay ON pay.pesanan_id = p.pesanan_id
    WHERE 1
";

// -----------------------------------------------------
// FILTER: keyword pencarian
// -----------------------------------------------------
if ($q !== "") {
    $sql .= "
        AND (
            p.kode_token LIKE '%$q%' OR
            p.pesanan_id LIKE '%$q%' OR
            p.nama_ketua LIKE '%$q%' OR
            j.nama_jalur LIKE '%$q%'
        )
    ";
}

// -----------------------------------------------------
// FILTER: status pembayaran
// -----------------------------------------------------
if ($status !== "") {
    $sql .= " AND pay.status_pembayaran = '$status' ";
}

// -----------------------------------------------------
// FILTER: jalur pendakian
// -----------------------------------------------------
if ($jalur !== "") {
    $sql .= " AND j.nama_jalur = '$jalur' ";
}

// -----------------------------------------------------
// FILTER: bulan (format YYYY-MM)
// -----------------------------------------------------
if ($bulan !== "") {
    $sql .= " AND DATE_FORMAT(p.tanggal_pesan, '%Y-%m') = '$bulan' ";
}

// -----------------------------------------------------
// FILTER: date range
// -----------------------------------------------------
if ($from !== "" && $to !== "") {
    $sql .= " AND DATE(p.tanggal_pesan) BETWEEN '$from' AND '$to' ";
}
elseif ($from !== "") {
    $sql .= " AND DATE(p.tanggal_pesan) >= '$from' ";
}
elseif ($to !== "") {
    $sql .= " AND DATE(p.tanggal_pesan) <= '$to' ";
}

// Urutkan dari terbaru
$sql .= " ORDER BY p.tanggal_pesan DESC ";

$result = mysqli_query($conn, $sql);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Pastikan nilai tidak null
    $data[] = [
        "pesanan_id"        => $row['pesanan_id'] ?? "",
        "kode_token"        => $row['kode_token'] ?? "",
        "nama_ketua"        => $row['nama_ketua'] ?? "-",
        "nama_jalur"        => $row['nama_jalur'] ?? "-",
        "tanggal_pesan"     => $row['tanggal_pesan'] ?? "-",
        "total_bayar"       => $row['total_bayar'] ?? 0,
        "status_pembayaran" => $row['status_pembayaran'] ?? "pending"
    ];
}

echo json_encode($data);
exit;
