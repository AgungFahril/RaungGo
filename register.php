<?php
// Memulai sesi untuk bisa membaca $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logika untuk menampilkan pesan error/sukses registrasi
$message = '';
$message_type = ''; // 'success' atau 'error'
if (isset($_SESSION['register_message'])) {
    $message = $_SESSION['register_message'];
    $message_type = $_SESSION['register_message_type'] ?? 'error';
    unset($_SESSION['register_message']);
    unset($_SESSION['register_message_type']);
}

// Opsional: Cek jika sudah login, redirect ke halaman lain
// if (isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Tahura Raden Soerjo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="auth-page"> <div class="auth-container">
        <div class="auth-form-section">
            <div class="auth-form-wrapper">
                 <a href="index.php" class="back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
                <h1 class="auth-title">Daftar Sekarang Juga</h1>
                 <p class="auth-subtitle" style="margin-bottom: 1.5rem; color: #666;">Buat akun baru untuk memulai booking pendakian</p>

                <?php if (!empty($message)): ?>
                    <p style="color: <?= ($message_type == 'success') ? 'green' : 'red'; ?>; margin-bottom: 1rem; text-align: center; background-color: <?= ($message_type == 'success') ? '#e8f5e9' : '#ffebee'; ?>; padding: 10px; border-radius: 5px; border: 1px solid <?= ($message_type == 'success') ? '#a5d6a7' : '#e57373'; ?>;">
                        <?= htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>

                <form action="backend/register.php" method="POST" class="auth-form">
                    <div class="input-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" placeholder="Masukkan nama Anda" required>
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" placeholder="Masukkan email Anda" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Masukkan password Anda" required>
                            <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i> </div>
                    </div>

                    <button type="submit" name="register" class="btn btn-primary">REGISTER</button>
                </form>

                <p class="auth-link">
                    Sudah punya akun?
                    <a href="login.php">Login</a>
                </p>
            </div>
        </div>

        <div class="auth-image-section">
            </div>
    </div>

    <script src="script.js"></script> 
</body>
</html>