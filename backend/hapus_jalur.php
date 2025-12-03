<?php
include 'koneksi.php';

if (!isset($_GET['id'])) {
    echo "<script>
            alert('ID jalur tidak ditemukan!');
            window.location.href='../admin/jalur_pendakian.php';
          </script>";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM jalur_pendakian WHERE jalur_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script>
            alert('Jalur berhasil dihapus!');
            window.location.href='../admin/jalur_pendakian.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal menghapus jalur!');
            window.location.href='../admin/jalur_pendakian.php';
          </script>";
}

$stmt->close();
$conn->close();
