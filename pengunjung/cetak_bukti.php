<?php
// =============================================================
// CETAK BUKTI BOOKING PENDAKIAN – PDF A4 TANPA QR
// =============================================================
date_default_timezone_set('Asia/Jakarta');
session_start();
include '../backend/koneksi.php';

// Composer autoload dompdf
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// =========================
// 1. Ambil pesanan_id
// =========================
$pesanan_id = isset($_GET['pesanan_id']) ? intval($_GET['pesanan_id']) : 0;

if (!$pesanan_id) {
    die("ID pesanan tidak ditemukan.");
}

// =========================
// 2. Ambil data pesanan + pendakian + jalur
// =========================
$sql = "
    SELECT ps.*, 
           p.tanggal_pendakian, p.tanggal_turun,
           jp.nama_jalur, jp.tarif_tiket
    FROM pesanan ps
    JOIN pendakian p ON ps.pendakian_id = p.pendakian_id
    JOIN jalur_pendakian jp ON p.jalur_id = jp.jalur_id
    WHERE ps.pesanan_id = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pesanan_id);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pesanan) die("Data pesanan tidak ditemukan.");

// =========================
// 3. Ambil data pembayaran terbaru
// =========================
$q2 = $conn->prepare("
    SELECT *
    FROM pembayaran
    WHERE pesanan_id = ?
    ORDER BY pembayaran_id DESC
    LIMIT 1
");
$q2->bind_param("i", $pesanan_id);
$q2->execute();
$payment = $q2->get_result()->fetch_assoc();
$q2->close();

// =========================
// 4. Ambil daftar anggota dari tabel pesanan_anggota
// =========================
$q3 = $conn->prepare("
    SELECT nama, nik, ktp, surat_sehat, porter_harga, ojek_harga
    FROM pesanan_anggota
    WHERE pesanan_id = ?
    ORDER BY anggota_id ASC
");
$q3->bind_param("i", $pesanan_id);
$q3->execute();
$anggota = $q3->get_result()->fetch_all(MYSQLI_ASSOC);
$q3->close();

// =========================
// 5. HTML untuk PDF
// =========================
$html = '
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 13px; }
h2 { text-align:center; margin-bottom:5px; }
h3 { margin-top:20px; margin-bottom:8px; }
.table { width:100%; border-collapse: collapse; }
.table td, .table th { padding:6px; border:1px solid #444; }
.header { text-align:center; margin-bottom:15px; font-weight:bold; }
.section-title { background:#eee; padding:5px; font-weight:bold; margin-top:15px; }
</style>

<div class="header">
    <h2>BUKTI PEMESANAN PENDAKIAN</h2>
    <div>TAHURA RADEN SOERJO – Gunung Arjuno-Welirang</div>
</div>

<div class="section-title">Data Pemesanan</div>
<table class="table">
<tr><td>Kode Token</td><td>'.$pesanan['kode_token'].'</td></tr>
<tr><td>Nama Jalur</td><td>'.$pesanan['nama_jalur'].'</td></tr>
<tr><td>Tanggal Naik</td><td>'.$pesanan['tanggal_pendakian'].'</td></tr>
<tr><td>Tanggal Turun</td><td>'.$pesanan['tanggal_turun'].'</td></tr>
<tr><td>Jumlah Pendaki</td><td>'.$pesanan['jumlah_pendaki'].' orang</td></tr>
<tr><td>Total Bayar</td><td>Rp '.number_format($pesanan['total_bayar'],0,",",".").'</td></tr>
<tr><td>Status Pesanan</td><td>'.$pesanan['status_pesanan'].'</td></tr>
<tr><td>Tanggal Pemesanan</td><td>'.$pesanan['tanggal_pesan'].'</td></tr>
</table>

<div class="section-title">Data Pembayaran</div>
<table class="table">
<tr><td>Metode</td><td>'.($payment['metode'] ?? '-').'</td></tr>
<tr><td>Jumlah Bayar</td><td>'.(isset($payment['jumlah_bayar']) ? 'Rp '.number_format($payment['jumlah_bayar'],0,",",".") : '-').'</td></tr>
<tr><td>Tanggal Bayar</td><td>'.($payment['tanggal_bayar'] ?? '-').'</td></tr>
<tr><td>Status Pembayaran</td><td>'.($payment['status_pembayaran'] ?? 'Belum Bayar').'</td></tr>
</table>

<div class="section-title">Daftar Anggota Pendaki</div>
<table class="table">
<tr><th>No</th><th>Nama</th><th>NIK</th><th>KTP</th><th>Surat Sehat</th></tr>';

$no = 1;
foreach ($anggota as $a) {
    $html .= '
    <tr>
        <td>'.$no++.'</td>
        <td>'.$a['nama'].'</td>
        <td>'.$a['nik'].'</td>
        <td>'.$a['ktp'].'</td>
        <td>'.$a['surat_sehat'].'</td>
    </tr>';
}

$html .= '</table>

<br><br>
<div style="margin-top:30px; text-align:right;">
Petugas Validasi<br><br><br>
(_________________________)
</div>
';

// =========================
// 6. Generate PDF
// =========================
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("bukti_booking_".$pesanan['kode_token'].".pdf", [
    "Attachment" => true
]);
exit;

?>
