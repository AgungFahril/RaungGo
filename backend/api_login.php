<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include "koneksi.php"; // pastikan path ini sesuai lokasi file koneksi.php kamu
// Ambil method request
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'POST') {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['email']) || !isset($input['password'])) {
        echo json_encode(["status" => "error", "message" => "Email dan password wajib diisi"]);
        exit;
    }
    $email = $conn->real_escape_string($input['email']);
    $password = $input['password'];
    // Cek user berdasarkan email
    $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            echo json_encode([
                "status" => "success",
                "message" => "Login berhasil",
                "data" => [
                    "user_id" => $user['user_id'],
                    "nama" => $user['nama'],
                    "email" => $user['email'],
                    "role" => $user['role']
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Password salah"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email tidak ditemukan"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Metode tidak diizinkan"]);
}
?>
