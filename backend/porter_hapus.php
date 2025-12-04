<?php
include 'koneksi.php';
include '../includes/auth_admin.php';

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $sql = "DELETE FROM porter WHERE porter_id=$id";

    if ($conn->query($sql)) {
        header("Location: ../admin/porter.php?msg=delete_success");
    } else {
        header("Location: ../admin/porter.php?msg=delete_fail");
    }
    exit;
}
?>
