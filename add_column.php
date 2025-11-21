<?php
include 'backend/koneksi.php';
$result = $conn->query('ALTER TABLE users ADD COLUMN alamat TEXT AFTER no_hp');
if ($result) {
    echo 'Column added successfully';
} else {
    echo 'Error: ' . $conn->error;
}
?>
