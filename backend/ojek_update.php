<?php
include 'koneksi.php';

if (isset($_POST['id'])) {

    $id         = intval($_POST['id']);
    $nama_ojek  = $conn->real_escape_string($_POST['nama_ojek']);
    $no_hp      = $conn->real_escape_string($_POST['no_hp']);
    $tarif      = intval($_POST['tarif']);
    $jalur_id   = intval($_POST['jalur_id']);
    $available  = intval($_POST['available']);

    $update = $conn->query("
        UPDATE ojek SET
            nama_ojek = '$nama_ojek',
            no_hp     = '$no_hp',
            tarif     = '$tarif',
            jalur_id  = '$jalur_id',
            available = '$available'
        WHERE ojek_id = $id
    ");

    if ($update) {
        header("Location: ../admin/ojek.php?msg=updated");
        exit;
    } else {
        echo "Gagal mengupdate data: " . $conn->error;
    }
}
?>
