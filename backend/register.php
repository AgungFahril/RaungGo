<?php
include 'koneksi.php';
session_start();

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $no_hp = trim($_POST['no_hp']);

    // Cek apakah email sudah terdaftar
    $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['register_message'] = 'Email sudah terdaftar, silakan gunakan email lain.';
        $_SESSION['register_message_type'] = 'error';
        header('Location: ../register.php');
        exit;
    }
    $check->close();

    // Masukkan data ke tabel users
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, 'pendaki')");
    $stmt->bind_param("ssss", $nama, $email, $password, $no_hp);

    if ($stmt->execute()) {
        $stmt->close();

        $_SESSION['register_message'] = 'Registrasi berhasil! Silakan login.';
        $_SESSION['register_message_type'] = 'success';
        header('Location: ../login.php');
        exit;
    } else {
        $_SESSION['register_message'] = 'Gagal registrasi: ' . $conn->error;
        $_SESSION['register_message_type'] = 'error';
        header('Location: ../register.php');
        exit;
    }
} else {
    header("Location: ../register.php");
    exit;
}
?>
