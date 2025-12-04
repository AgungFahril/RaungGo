<?php
// backend/guide_update.php
include 'koneksi.php';
include '../includes/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id    = intval($_POST['id']);
    $nama  = $conn->real_escape_string($_POST['nama_guide']);
    $hp    = $conn->real_escape_string($_POST['no_hp']);
    $tarif = intval($_POST['tarif']);
    $jalur = intval($_POST['jalur_id']);
    $avail = intval($_POST['available']);

    $stmt = $conn->prepare("UPDATE guide SET nama_guide=?, no_hp=?, tarif=?, jalur_id=?, available=? WHERE guide_id=?");
    $stmt->bind_param("ssiiii", $nama, $hp, $tarif, $jalur, $avail, $id);

    if ($stmt->execute()) {
        header("Location: ../admin/guide.php?msg=upd_ok");
    } else {
        header("Location: ../admin/guide.php?msg=upd_err");
    }
    exit;
}
header("Location: ../admin/guide.php");
exit;
