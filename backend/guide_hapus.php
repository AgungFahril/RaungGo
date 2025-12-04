<?php
// backend/guide_hapus.php
include 'koneksi.php';
include '../includes/auth_admin.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM guide WHERE guide_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../admin/guide.php");
exit;
