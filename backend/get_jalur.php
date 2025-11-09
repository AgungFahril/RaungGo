<?php
include 'koneksi.php';

$jalur_id = $_GET['jalur_id'] ?? 0;

$q = $conn->prepare("SELECT deskripsi FROM jalur_pendakian WHERE jalur_id = ?");
$q->bind_param("i", $jalur_id);
$q->execute();
$res = $q->get_result()->fetch_assoc();
$q->close();

header('Content-Type: application/json');
echo json_encode($res);
?>
