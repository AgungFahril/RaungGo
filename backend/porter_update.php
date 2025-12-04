<?php
include 'koneksi.php';
include '../includes/auth_admin.php';

if (isset($_POST['id'])) {

    $id     = intval($_POST['id']);
    $nama   = $conn->real_escape_string($_POST['nama_porter']);
    $hp     = $conn->real_escape_string($_POST['no_hp']);
    $tarif  = intval($_POST['tarif']);
    $jalur  = intval($_POST['jalur_id']);
    $avail  = intval($_POST['available']);

    $sql = "
        UPDATE porter
        SET nama_porter='$nama',
            no_hp='$hp',
            tarif='$tarif',
            jalur_id='$jalur',
            available='$avail'
        WHERE porter_id=$id
    ";

    if ($conn->query($sql)) {
        header("Location: ../admin/porter.php?msg=update_success");
    } else {
        header("Location: ../admin/porter.php?msg=update_fail");
    }
    exit;
}
?>
