<?php
date_default_timezone_set('Asia/Jakarta');
session_start();
include 'koneksi.php';

// Matikan debug
$DEBUG = false;

ob_start(); // --- FIX PALING PENTING (mencegah blank page)

if (!isset($_GET['pesanan_id'])) {
    header("Location: ../pengunjung/kuota.php");
    exit;
}

$pesanan_id = intval($_GET['pesanan_id']);

try {

    $conn->begin_transaction();

    $st = $conn->prepare("SELECT pesanan_id, pendakian_id, status_pesanan, jumlah_pendaki 
                          FROM pesanan WHERE pesanan_id = ? LIMIT 1");
    $st->bind_param("i", $pesanan_id);
    $st->execute();
    $pesanan = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$pesanan) throw new Exception("Pesanan tidak ditemukan.");
    if ($pesanan['status_pesanan'] !== 'menunggu_pembayaran')
        throw new Exception("Pesanan tidak dapat dibatalkan karena status: ".$pesanan['status_pesanan']);

    $pendakian_id = intval($pesanan['pendakian_id']);
    $jumlah_pendaki = intval($pesanan['jumlah_pendaki']);

    if ($jumlah_pendaki <= 0) {
        $qx = $conn->prepare("SELECT COUNT(*) AS cnt FROM pesanan_anggota WHERE pesanan_id = ?");
        $qx->bind_param("i", $pesanan_id);
        $qx->execute();
        $jumlah_pendaki = intval($qx->get_result()->fetch_assoc()['cnt']);
        $qx->close();
    }

    if ($jumlah_pendaki <= 0) throw new Exception("Jumlah pendaki tidak valid.");

    $st2 = $conn->prepare("SELECT kuota_tersedia FROM pendakian WHERE pendakian_id = ? FOR UPDATE");
    $st2->bind_param("i", $pendakian_id);
    $st2->execute();
    $dataPend = $st2->get_result()->fetch_assoc();
    $st2->close();

    if (!$dataPend) throw new Exception("Data pendakian tidak ditemukan.");

    $newKuota = intval($dataPend['kuota_tersedia']) + $jumlah_pendaki;

    $upd = $conn->prepare("UPDATE pendakian SET kuota_tersedia = ? WHERE pendakian_id = ?");
    $upd->bind_param("ii", $newKuota, $pendakian_id);
    $upd->execute();
    $upd->close();

    $hasWaktuBatal = (int)$conn->query("SHOW COLUMNS FROM pesanan LIKE 'waktu_batal'")->num_rows;
    if ($hasWaktuBatal) {
        $up = $conn->prepare("UPDATE pesanan SET status_pesanan='dibatalkan', waktu_batal = NOW() WHERE pesanan_id = ?");
    } else {
        $up = $conn->prepare("UPDATE pesanan SET status_pesanan='dibatalkan' WHERE pesanan_id = ?");
    }
    $up->bind_param("i", $pesanan_id);
    $up->execute();
    $up->close();

    $conn->commit();

    ob_clean(); // buang semua output yang mengganggu SweetAlert
    ?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    icon: 'success',
    title: 'Pesanan Dibatalkan',
    text: 'Kuota telah dikembalikan. Anda akan diarahkan ke halaman cek kuota.',
    confirmButtonColor: '#2e7d32'
}).then(()=>{ window.location='../pengunjung/kuota.php'; });
</script>
</body>
</html>

<?php
    exit;

} catch (Exception $e) {

    $conn->rollback();

    $msg = addslashes($e->getMessage());

    ob_clean();
    ?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    icon:'error',
    title:'Gagal Membatalkan',
    text:'<?= $msg ?>',
    confirmButtonColor:'#d32f2f'
}).then(()=>{ window.location='../pengunjung/detail_transaksi.php?pesanan_id=<?= $pesanan_id ?>'; });
</script>
</body>
</html>

<?php
    exit;
}
