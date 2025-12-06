<?php
/**
 * File: pengunjung/check_data_lengkap.php
 * Middleware untuk memvalidasi apakah user sudah melengkapi data diri
 * Letakkan file ini di folder pengunjung/
 */

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include koneksi database jika belum
if (!isset($conn)) {
    include __DIR__ . '/../backend/koneksi.php';
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil nama file saat ini
$current_page = basename($_SERVER['PHP_SELF']);

// Daftar halaman yang BOLEH diakses tanpa data lengkap
$allowed_pages = [
    'lengkapi_data.php',  // Halaman untuk melengkapi data
    'profil.php',         // Halaman profil (jika user ingin edit profil)
];

// Jika bukan halaman yang diizinkan, cek apakah data sudah lengkap
if (!in_array($current_page, $allowed_pages)) {
    $user_id = $_SESSION['user_id'];
    
    // Cek apakah user sudah melengkapi data
    $check = $conn->prepare("SELECT user_id FROM pendaki_detail WHERE user_id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $result = $check->get_result();
    $data_lengkap = $result->num_rows > 0;
    $check->close();
    
    // Jika data belum lengkap, redirect ke halaman lengkapi_data.php
    if (!$data_lengkap) {
        $_SESSION['alert_message'] = 'Silakan lengkapi data diri terlebih dahulu untuk mengakses fitur ini!';
        $_SESSION['alert_type'] = 'warning';
        header("Location: lengkapi_data.php");
        exit;
    }
}
?>