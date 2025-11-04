<?php
session_start();
include 'koneksi.php';

// Aktifkan error log saat debugging di hosting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Email dan password tidak boleh kosong!';
        header('Location: ../login.php');
        exit;
    }

    // 🔹 Ambil data user berdasarkan email
    $stmt = $conn->prepare("SELECT user_id, nama, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // 🔹 Gunakan bind_result agar tidak pakai get_result() (yang sering error di hosting)
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $nama, $email_db, $hashed_password, $role);
        $stmt->fetch();

        // Verifikasi password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['nama'] = $nama;
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $email_db;

            // 🔹 Cek apakah user sudah punya data di tabel pendaki
            $qPendaki = $conn->prepare("SELECT id_pendaki FROM pendaki WHERE user_id = ?");
            $qPendaki->bind_param("i", $user_id);
            $qPendaki->execute();
            $qPendaki->store_result();

            if ($qPendaki->num_rows > 0) {
                $qPendaki->bind_result($id_pendaki);
                $qPendaki->fetch();
                $_SESSION['id_pendaki'] = $id_pendaki;
            } else {
                // Jika belum ada → buat otomatis
                $insert = $conn->prepare("INSERT INTO pendaki (user_id, nama, email, no_hp) VALUES (?, ?, ?, '')");
                $insert->bind_param("iss", $user_id, $nama, $email_db);
                $insert->execute();
                $_SESSION['id_pendaki'] = $conn->insert_id;
                $insert->close();
            }
            $qPendaki->close();

            // 🔹 Redirect sesuai kondisi
            if (!empty($redirect) && $redirect === 'booking') {
                header("Location: ../pengunjung/sop.php");
                exit;
            }

            if ($role === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $_SESSION['login_error'] = 'Password yang Anda masukkan salah!';
            header('Location: ../login.php');
            exit;
        }
    } else {
        $_SESSION['login_error'] = 'Email tidak terdaftar!';
        header('Location: ../login.php');
        exit;
    }

    $stmt->close();
} else {
    header("Location: ../login.php");
    exit;
}
?>
