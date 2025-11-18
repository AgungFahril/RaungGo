<?php
include 'koneksi.php';

try {
    // Cek apakah kolom foto_profil sudah ada
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'foto_profil'");
    
    if ($result->num_rows == 0) {
        // Tambahkan kolom foto_profil
        $sql = "ALTER TABLE users ADD COLUMN foto_profil VARCHAR(255) NULL DEFAULT NULL AFTER email";
        
        if ($conn->query($sql)) {
            echo "✓ Kolom 'foto_profil' berhasil ditambahkan ke tabel 'users'";
        } else {
            echo "✗ Error: " . $conn->error;
        }
    } else {
        echo "✓ Kolom 'foto_profil' sudah ada di tabel 'users'";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
