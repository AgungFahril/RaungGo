<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../backend/koneksi.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Ambil data user
$stmt = $conn->prepare("SELECT nama, email, no_hp FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Ambil data pendaki
$stmt = $conn->prepare("SELECT no_identitas, alamat, umur FROM pendaki WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pendaki_data = $result->fetch_assoc();
$stmt->close();

// Proses update profil
if (isset($_POST['update_profil'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $no_identitas = trim($_POST['no_identitas']);
    $alamat = trim($_POST['alamat']);
    $umur = !empty($_POST['umur']) ? intval($_POST['umur']) : null;

    // Update tabel users
    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, no_hp = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $nama, $email, $no_hp, $user_id);
    
    if ($stmt->execute()) {
        // Update tabel pendaki
        $stmt2 = $conn->prepare("UPDATE pendaki SET nama = ?, no_identitas = ?, alamat = ?, umur = ? WHERE user_id = ?");
        $stmt2->bind_param("sssii", $nama, $no_identitas, $alamat, $umur, $user_id);
        $stmt2->execute();
        $stmt2->close();

        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        $message = 'Profil berhasil diperbarui!';
        $message_type = 'success';
        
        // Refresh data
        $user_data = ['nama' => $nama, 'email' => $email, 'no_hp' => $no_hp];
        $pendaki_data = ['no_identitas' => $no_identitas, 'alamat' => $alamat, 'umur' => $umur];
    } else {
        $message = 'Gagal memperbarui profil.';
        $message_type = 'error';
    }
    $stmt->close();
}

// Proses ubah password
if (isset($_POST['ubah_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $password_baru_confirm = $_POST['password_baru_confirm'];

    // Ambil password lama dari database
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pwd_data = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password_lama, $pwd_data['password'])) {
        $message = 'Password lama salah!';
        $message_type = 'error';
    } elseif ($password_baru !== $password_baru_confirm) {
        $message = 'Password baru tidak cocok!';
        $message_type = 'error';
    } elseif (strlen($password_baru) < 6) {
        $message = 'Password baru minimal 6 karakter!';
        $message_type = 'error';
    } else {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $password_hash, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Password berhasil diubah!';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengubah password.';
            $message_type = 'error';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Pendakian Gunung Raung</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 40px 0;
            box-shadow: 4px 0 20px rgba(231, 76, 60, 0.3);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 0 25px 35px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 10px;
        }

        .user-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #e74c3c, #d43f26);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: bold;
            margin-right: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        .user-info h3 {
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: 600;
            color: #ffffff;
        }

        .user-status {
            font-size: 12px;
            color: #FFD700;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            background: #FFD700;
            border-radius: 50%;
            margin-right: 6px;
        }

        .sidebar-nav {
            margin-top: 20px;
        }

        .nav-item {
            padding: 16px 25px;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
            border-left: 4px solid transparent;
            margin: 5px 0;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #FFD700;
        }

        .nav-item.active {
            background: rgba(0, 0, 0, 0.2);
            border-left-color: #FFD700;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 50px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #e74c3c;
            text-decoration: none;
            margin-bottom: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .back-link:hover {
            color: #c0392b;
            transform: translateX(-5px);
        }

        .page-title {
            font-size: 32px;
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #999;
            margin-bottom: 40px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-section {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            border-left: 5px solid #e74c3c;
        }

        .form-section h2 {
            color: #e74c3c;
            margin-bottom: 30px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-full {
            grid-column: 1 / -1;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding: 25px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?></div>
                <div class="user-info">
                    <h3><?php echo $_SESSION['nama']; ?></h3>
                    <div class="user-status">
                        <div class="status-indicator"></div>
                        Online
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    🏕️ Dashboard
                </a>
                <a href="edit_profil.php" class="nav-item active">
                    👤 Edit Profil
                </a>
                <a href="booking.php" class="nav-item">
                    📅 Booking
                </a>
                <a href="../pengunjung/dashboard.php?tab=transaksi" class="nav-item">
                    📊 Transaksi
                </a>
                <a href="../backend/logout.php" class="nav-item">
                    🚪 Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1 class="page-title">Edit Profil</h1>
            <p class="page-subtitle">Perbarui informasi pribadi dan keamanan akun Anda</p>

            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Form Edit Data Profil -->
            <div class="form-section">
                <h2>📋 Data Pribadi</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap *</label>
                            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user_data['nama']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="no_identitas">NIK *</label>
                            <input type="text" id="no_identitas" name="no_identitas" value="<?php echo htmlspecialchars($pendaki_data['no_identitas'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="no_hp">Nomor HP *</label>
                            <input type="tel" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($user_data['no_hp']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="umur">Umur</label>
                            <input type="number" id="umur" name="umur" min="1" max="150" value="<?php echo htmlspecialchars($pendaki_data['umur'] ?? ''); ?>">
                        </div>
                        <div class="form-group form-full">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat"><?php echo htmlspecialchars($pendaki_data['alamat'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="submit" name="update_profil" class="btn btn-primary">💾 Simpan Perubahan</button>
                        <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>

            <!-- Form Ubah Password -->
            <div class="form-section">
                <h2>🔒 Ubah Password</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group form-full">
                            <label for="password_lama">Password Lama *</label>
                            <input type="password" id="password_lama" name="password_lama" required>
                        </div>
                        <div class="form-group form-full">
                            <label for="password_baru">Password Baru *</label>
                            <input type="password" id="password_baru" name="password_baru" required>
                        </div>
                        <div class="form-group form-full">
                            <label for="password_baru_confirm">Konfirmasi Password Baru *</label>
                            <input type="password" id="password_baru_confirm" name="password_baru_confirm" required>
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="submit" name="ubah_password" class="btn btn-primary">🔄 Ubah Password</button>
                        <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
