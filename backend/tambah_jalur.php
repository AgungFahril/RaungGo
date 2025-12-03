<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = $conn->real_escape_string($_POST['nama_jalur']);
    $kuota = (int) $_POST['kuota_harian'];
    $tarif = (int) $_POST['tarif_tiket'];
    $status = $conn->real_escape_string($_POST['status']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);

    $sql = "INSERT INTO jalur_pendakian (nama_jalur, kuota_harian, tarif_tiket, status, deskripsi)
            VALUES ('$nama', $kuota, $tarif, '$status', '$deskripsi')";

    if ($conn->query($sql)) {
        header("Location: ../admin/jalur_pendakian.php?msg=added");
        exit;
    } else {
        echo "Gagal menambahkan jalur: " . $conn->error;
    }
}
