<?php
include 'koneksi.php';

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $del = $conn->query("DELETE FROM ojek WHERE ojek_id = $id");

    if ($del) {
        header("Location: ../admin/ojek.php?msg=deleted");
        exit;
    } else {
        echo "Gagal menghapus data: " . $conn->error;
    }
}
?>
