<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id      = $_POST['jalur_id'];
    $nama    = $conn->real_escape_string($_POST['nama_jalur']);
    $kuota   = intval($_POST['kuota_harian']);
    $tarif   = intval($_POST['tarif_tiket']);
    $status  = $conn->real_escape_string($_POST['status']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);

    $update = $conn->query("
        UPDATE jalur_pendakian SET
            nama_jalur = '$nama',
            kuota_harian = '$kuota',
            tarif_tiket = '$tarif',
            status = '$status',
            deskripsi = '$deskripsi'
        WHERE jalur_id = '$id'
    ");

    if ($update) {
        header("Location: ../admin/jalur_pendakian.php?msg=updated");
    } else {
        echo "Gagal update data: " . $conn->error;
    }
}
?>
