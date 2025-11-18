<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

if ($_SESSION['role'] != 'pendaki') {
    header("Location: ../admin/dashboard.php");
    exit();
}

include '../backend/koneksi.php';

// Ambil data statistik booking user
$user_id = $_SESSION['user_id'];
$total_transaksi = 0;
$transaksi_sukses = 0;
$transaksi_pending = 0;
$transaksi_batal = 0;

// Ambil foto profil user
$stmt = $conn->prepare("SELECT foto_profil FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_foto = $result->fetch_assoc();
$foto_profil = $user_foto['foto_profil'] ?? null;
$stmt->close();

// Try to fetch from pesanan table (Indonesian for orders)
try {
    // Total Transaksi
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_transaksi = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }
    
    // Transaksi Sukses/Approved - menggunakan status yang umum dipakai
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? AND (status_pesanan LIKE '%lunas%' OR status_pesanan LIKE '%Terkonfirmasi%' OR status_pesanan LIKE '%Approved%' OR status_pesanan LIKE '%Confirmed%' OR status_pesanan LIKE '%selesai%')");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaksi_sukses = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }
    
    // Transaksi Pending/Menunggu Pembayaran
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? AND (status_pesanan LIKE '%menunggu_pembayaran%' OR status_pesanan LIKE '%Menunggu Pembayaran%' OR status_pesanan LIKE '%Pending%' OR status_pesanan LIKE '%menunggu_konfirmasi%')");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaksi_pending = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }
    
    // Transaksi Dibatalkan
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? AND (status_pesanan LIKE '%batal%' OR status_pesanan LIKE '%Dibatalkan%' OR status_pesanan LIKE '%Cancelled%' OR status_pesanan LIKE '%Rejected%')");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaksi_batal = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }
} catch (Exception $e) {
    // If pesanan table doesn't exist, use default values (0)
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Pendakian Gunung Raung</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f0f0 100%);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #16a34a 0%, #15803d 100%);
            color: white;
            padding: 40px 0;
            box-shadow: 4px 0 20px rgba(22, 163, 74, 0.3);
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
            background: linear-gradient(135deg, #16a34a, #15803d);
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
            overflow: hidden;
            object-fit: cover;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
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
            padding-left: 22px;
        }

        .nav-item:active {
            background: rgba(0, 0, 0, 0.2);
            border-left-color: #FFD700;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 50px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 3px solid #16a34a;
        }

        .top-bar h1 {
            font-size: 32px;
            color: #16a34a;
            font-weight: 700;
        }

        .top-bar > div p {
            color: #999;
            font-size: 14px;
            margin-top: 8px;
        }

        .logout-btn {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.4);
            background: linear-gradient(135deg, #15803d, #14532d);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
            border-top: 5px solid #3b82f6;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: -80px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(59, 130, 246, 0.15);
            border-top-color: #2563eb;
        }

        .stat-card.success {
            border-top-color: #27ae60;
        }

        .stat-card.success:hover {
            border-top-color: #2ecc71;
        }

        .stat-card.warning {
            border-top-color: #f39c12;
        }

        .stat-card.warning:hover {
            border-top-color: #e67e22;
        }

        .stat-card.danger {
            border-top-color: #ef4444;
        }

        .stat-card.danger:hover {
            border-top-color: #dc2626;
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, rgba(22, 163, 74, 0.15), rgba(21, 128, 61, 0.1));
            border: 2px solid rgba(22, 163, 74, 0.1);
        }

        .stat-card.success .stat-icon {
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.15), rgba(46, 204, 113, 0.1));
            border-color: rgba(46, 204, 113, 0.2);
        }

        .stat-card.warning .stat-icon {
            background: linear-gradient(135deg, rgba(230, 126, 34, 0.15), rgba(243, 156, 18, 0.1));
            border-color: rgba(243, 156, 18, 0.2);
        }

        .stat-card.danger .stat-icon {
            background: linear-gradient(135deg, rgba(21, 128, 61, 0.15), rgba(22, 163, 74, 0.1));
            border-color: rgba(22, 163, 74, 0.2);
        }

        .stat-number {
            font-size: 48px;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .stat-card.success .stat-number {
            color:  #2e7d32;
        }

        .stat-card.warning .stat-number {
            color: #f39c12;
        }

        .stat-card.danger .stat-number {
            color: #ef4444;
        }

        .stat-label {
            font-size: 16px;
            color: #555;
            margin-bottom: 15px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .stat-link {
            font-size: 13px;
            color: #2e7d32;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .stat-link:hover {
            color: #15803d;
            transform: translateX(5px);
        }

        .stat-link::after {
            content: '→';
            margin-left: 8px;
            transition: transform 0.3s;
        }

        .stat-link:hover::after {
            transform: translateX(3px);
        }

        .content-section {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #16a34a;
        }

        .content-section h2 {
            color: #16a34a;
            margin-bottom: 20px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .content-section p {
            color: #666;
            line-height: 1.8;
            font-size: 15px;
        }

        .home-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #2e7d32, #15803d);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
            margin-top: 20px;
            display: inline-flex;
        }

        .home-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.4);
            background: linear-gradient(135deg, #15803d, #14532d);
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #16a34a;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #15803d;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                margin-left: 250px;
                padding: 35px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 30px 0;
                box-shadow: 0 4px 20px rgba(22, 163, 74, 0.2);
            }

            .main-content {
                margin-left: 0;
                padding: 25px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 30px;
            }

            .logout-btn {
                margin-top: 15px;
                width: 100%;
                text-align: center;
            }

            .stat-card {
                padding: 30px;
            }

            .stat-number {
                font-size: 36px;
            }

            .top-bar h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="user-avatar">
                    <?php if (!empty($foto_profil) && file_exists('../uploads/profil/' . $foto_profil)): ?>
                        <img src="/ProjekSemester3/uploads/profil/<?php echo htmlspecialchars($foto_profil); ?>" alt="Foto Profil" onerror="this.parentElement.innerHTML='<?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>'">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h3><?php echo $_SESSION['nama']; ?></h3>
                    <div class="user-status">
                        <div class="status-indicator"></div>
                        Online
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <a href="edit_profil.php" class="nav-item">
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
            <div class="top-bar">
                <div>
                    <h1>Dashboard User</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Selamat datang, <?php echo $_SESSION['nama']; ?>!</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <!-- Total Transaksi -->
                <div class="stat-card">
                    <div class="stat-icon">ℹ️</div>
                    <div class="stat-number"><?php echo $total_transaksi; ?></div>
                    <div class="stat-label">Total Transaksi</div>
                    <a href="../StatusBooking.php?filter=all" class="stat-link">Selengkapnya</a>
                </div>

                <!-- Transaksi Sukses -->
                <div class="stat-card success">
                    <div class="stat-icon">✓</div>
                    <div class="stat-number"><?php echo $transaksi_sukses; ?></div>
                    <div class="stat-label">Transaksi Sukses</div>
                    <a href="../StatusBooking.php?filter=success" class="stat-link">Selengkapnya</a>
                </div>

                <!-- Transaksi Pending -->
                <div class="stat-card warning">
                    <div class="stat-icon">⏱</div>
                    <div class="stat-number"><?php echo $transaksi_pending; ?></div>
                    <div class="stat-label">Transaksi Menunggu</div>
                    <a href="../StatusBooking.php?filter=pending" class="stat-link">Selengkapnya</a>
                </div>

                <!-- Transaksi Dibatalkan -->
                <div class="stat-card danger">
                    <div class="stat-icon">✕</div>
                    <div class="stat-number"><?php echo $transaksi_batal; ?></div>
                    <div class="stat-label">Transaksi Dibatalkan</div>
                    <a href="../StatusBooking.php?filter=cancelled" class="stat-link">Selengkapnya</a>
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="content-section">
                <h2>📖 Panduan Cepat</h2>
                <p style="color: #7f8c8d; line-height: 1.6;">
                    Selamat datang di sistem booking pendakian Gunung Raung. Anda dapat melakukan booking pendakian, 
                    mengecek status pembayaran, dan melihat informasi SOP pendakian melalui menu di samping. 
                    Pastikan semua data Anda akurat untuk pengalaman booking yang lancar.
                </p>
                <a href="../index.php" class="home-button">🏠 Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>
</body>
</html>
