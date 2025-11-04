<?php
session_start();
include 'koneksi.php';

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $password_plain = $_POST['password'];

    // Validasi input sederhana
    if (empty($nama) || empty($email) || empty($password_plain) || empty($no_hp)) {
        $_SESSION['register_message'] = 'Semua kolom wajib diisi!';
        $_SESSION['register_message_type'] = 'error';
        header('Location: ../register.php');
        exit;
    }

    // Cek apakah email sudah terdaftar
    $cek = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $cek->bind_param("s", $email);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_message'] = 'Email sudah terdaftar, silakan gunakan email lain.';
        $_SESSION['register_message_type'] = 'error';
        header('Location: ../register.php');
        exit;
    }
    $cek->close();

    // Hash password
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

    // Simpan ke tabel users
    $insert_user = $conn->prepare("INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, 'pendaki')");
    $insert_user->bind_param("ssss", $nama, $email, $password_hash, $no_hp);

    if ($insert_user->execute()) {
        // Ambil user_id terakhir
        $user_id = $conn->insert_id;

        // Tambahkan juga ke tabel pendaki
        $insert_pendaki = $conn->prepare("
            INSERT INTO pendaki (user_id, nama, email, no_hp)
            VALUES (?, ?, ?, ?)
        ");
        $insert_pendaki->bind_param("isss", $user_id, $nama, $email, $no_hp);
        $insert_pendaki->execute();
        $insert_pendaki->close();

        $_SESSION['register_message'] = 'Registrasi berhasil! Silakan login.';
        $_SESSION['register_message_type'] = 'success';
        header('Location: ../login.php');
        exit;
    } else {
        $_SESSION['register_message'] = 'Terjadi kesalahan saat registrasi.';
        $_SESSION['register_message_type'] = 'error';
        header('Location: ../register.php');
        exit;
    }

} else {
    header("Location: ../register.php");
    exit;
}
?>
