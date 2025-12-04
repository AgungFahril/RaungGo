<?php
include 'koneksi.php';
include '../includes/auth_admin.php';

if (isset($_POST['nama_porter'])) {

    $nama   = $conn->real_escape_string($_POST['nama_porter']);
    $hp     = $conn->real_escape_string($_POST['no_hp']);
    $tarif  = intval($_POST['tarif']);
    $jalur  = intval($_POST['jalur_id']);

    $sql = "
        INSERT INTO porter (nama_porter, no_hp, tarif, jalur_id, available)
        VALUES ('$nama', '$hp', '$tarif', '$jalur', 1)
    ";

    if ($conn->query($sql)) {
        header("Location: ../admin/porter.php?msg=add_success");
    } else {
        header("Location: ../admin/porter.php?msg=add_fail");
    }
    exit;
}
?>
