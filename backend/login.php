<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Email dan password tidak boleh kosong!';
        header('Location: ../login.php');
        exit;
    }

    // Ambil data user berdasarkan email
    $stmt = $conn->prepare("SELECT user_id, nama, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $data['password'])) {
            // Simpan data user ke session
            $_SESSION['user_id'] = $data['user_id'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['email'] = $data['email'];

            // 🔹 Arahkan sesuai role (untuk sekarang fokus ke pengunjung)
            if ($data['role'] === 'admin') {
                // Admin login (nanti akan digunakan)
                header("Location: ../admin/dashboard.php");
                exit;
            } else {
                // Pengunjung login → langsung ke halaman utama pengunjung
                header("Location: ../index.php");
                exit;
            }

        } else {
            // Password salah
            $_SESSION['login_error'] = 'Password yang Anda masukkan salah!';
            header('Location: ../login.php');
            exit;
        }
    } else {
        // Email tidak ditemukan
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
