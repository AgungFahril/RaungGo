<?php
/**
 * Script untuk menambah column 'nik' ke table 'anggota_pendaki' jika belum ada
 */

include 'backend/koneksi.php';

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM anggota_pendaki LIKE 'nik'");
if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $sql = "ALTER TABLE anggota_pendaki ADD COLUMN nik VARCHAR(20) NOT NULL AFTER nama_anggota";
    
    if ($conn->query($sql)) {
        echo "✅ Column nik berhasil ditambahkan ke table anggota_pendaki";
    } else {
        echo "❌ Error menambahkan column: " . $conn->error;
    }
} else {
    echo "✅ Column nik sudah ada di table anggota_pendaki";
}

$conn->close();
?>