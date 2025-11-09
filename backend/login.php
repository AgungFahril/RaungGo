<?php
session_start();
include 'koneksi.php';

// Aktifkan error log saat debugging (nonaktifkan di produksi)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $redirect = $_GET['redirect'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Email dan password tidak boleh kosong!';
        header('Location: ../login.php');
        exit;
    }

    // 🔹 Ambil data user berdasarkan email
    $stmt = $conn->prepare("SELECT user_id, nama, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $nama, $email_db, $hashed_password, $role);
        $stmt->fetch();

        // 🔐 Verifikasi password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['nama'] = $nama;
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $email_db;

            // 🔍 Cek apakah user sudah melengkapi data diri
            $cekData = $conn->prepare("SELECT 1 FROM pendaki_detail WHERE user_id = ?");
            $cekData->bind_param("i", $user_id);
            $cekData->execute();
            $cekData->store_result();

            $sudahLengkap = ($cekData->num_rows > 0);
            $cekData->close();

            // 🔁 Logika redirect
            if ($role === 'admin') {
                header("Location: ../admin/dashboard.php");
                exit;
            }

            // Jika pendaki login dari tombol booking
            if (!empty($redirect) && str_contains($redirect, 'booking')) {
                if ($sudahLengkap) {
                    header("Location: ../pengunjung/sop.php");
                } else {
                    header("Location: ../pengunjung/lengkapi_data.php");
                }
                exit;
            }

            // Jika login biasa dari halaman utama
            if ($sudahLengkap) {
                header("Location: ../pengunjung/dashboard.php");
            } else {
                header("Location: ../pengunjung/lengkapi_data.php");
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
