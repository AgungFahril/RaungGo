<?php
include 'koneksi.php';

if (isset($_POST['simpan'])) {

    $nama_ojek = $conn->real_escape_string($_POST['nama_ojek']);
    $no_hp     = $conn->real_escape_string($_POST['no_hp']);
    $tarif     = intval($_POST['tarif']);
    $jalur_id  = intval($_POST['jalur_id']);

    // default available = aktif
    $available = 1;

    $save = $conn->query("
        INSERT INTO ojek (nama_ojek, no_hp, tarif, available, jalur_id)
        VALUES ('$nama_ojek', '$no_hp', '$tarif', '$available', '$jalur_id')
    ");

    if ($save) {
        header("Location: ../admin/ojek.php?msg=added");
        exit;
    } else {
        echo "Gagal menambah data: " . $conn->error;
    }
}
?>
