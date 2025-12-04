<?php
// backend/guide_tambah.php
include 'koneksi.php';
include '../includes/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $nama = $conn->real_escape_string($_POST['nama_guide']);
    $hp   = $conn->real_escape_string($_POST['no_hp']);
    $tarif = intval($_POST['tarif']);
    $jalur = intval($_POST['jalur_id']);

    $stmt = $conn->prepare("INSERT INTO guide (nama_guide, no_hp, tarif, jalur_id, available) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("ssii", $nama, $hp, $tarif, $jalur);

    if ($stmt->execute()) {
        header("Location: ../admin/guide.php?msg=add_ok");
    } else {
        header("Location: ../admin/guide.php?msg=add_err");
    }
    exit;
}
header("Location: ../admin/guide.php");
exit;
