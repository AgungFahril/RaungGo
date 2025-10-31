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
    
    // Transaksi Sukses/Approved
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? AND status_pesanan IN ('Terkonfirmasi', 'Approved', 'Confirmed')");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaksi_sukses = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }
    
    // Transaksi Pending
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? AND status_pesanan IN ('Menunggu Pembayaran', 'Pending')");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaksi_pending = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }
    
    // Transaksi Dibatalkan
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? AND status_pesanan IN ('Dibatalkan', 'Cancelled', 'Rejected')");
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
            border-bottom: 3px solid #e74c3c;
        }

        .top-bar h1 {
            font-size: 32px;
            color: #e74c3c;
            font-weight: 700;
        }

        .top-bar > div p {
            color: #999;
            font-size: 14px;
            margin-top: 8px;
        }

        .logout-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
            background: linear-gradient(135deg, #c0392b, #a93226);
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
            border-top: 5px solid #e74c3c;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: -80px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(231, 76, 60, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(231, 76, 60, 0.15);
            border-top-color: #d43f26;
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
            border-top-color: #e74c3c;
        }

        .stat-card.danger:hover {
            border-top-color: #c0392b;
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
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.15), rgba(211, 63, 38, 0.1));
            border: 2px solid rgba(231, 76, 60, 0.1);
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
            background: linear-gradient(135deg, rgba(192, 57, 43, 0.15), rgba(231, 76, 60, 0.1));
            border-color: rgba(231, 76, 60, 0.2);
        }

        .stat-number {
            font-size: 48px;
            font-weight: 700;
            color: #e74c3c;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .stat-card.success .stat-number {
            color: #27ae60;
        }

        .stat-card.warning .stat-number {
            color: #f39c12;
        }

        .stat-card.danger .stat-number {
            color: #e74c3c;
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
            color: #e74c3c;
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
            color: #c0392b;
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
            border-left: 5px solid #e74c3c;
        }

        .content-section h2 {
            color: #e74c3c;
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

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #e74c3c;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #c0392b;
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
                box-shadow: 0 4px 20px rgba(139, 58, 58, 0.2);
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
                    📊 Dashboard
                </a>
                <a href="booking.php" class="nav-item">
                    📅 Booking
                </a>
                <a href="pembayaran.php" class="nav-item">
                    💳 Pembayaran
                </a>
                <a href="sop.php" class="nav-item">
                    📋 SOP
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
                    <a href="booking.php" class="stat-link">Selengkapnya</a>
                </div>

                <!-- Transaksi Sukses -->
                <div class="stat-card success">
                    <div class="stat-icon">✓</div>
                    <div class="stat-number"><?php echo $transaksi_sukses; ?></div>
                    <div class="stat-label">Transaksi Sukses</div>
                    <a href="booking.php" class="stat-link">Selengkapnya</a>
                </div>

                <!-- Transaksi Pending -->
                <div class="stat-card warning">
                    <div class="stat-icon">⏱</div>
                    <div class="stat-number"><?php echo $transaksi_pending; ?></div>
                    <div class="stat-label">Transaksi Menunggu</div>
                    <a href="booking.php" class="stat-link">Selengkapnya</a>
                </div>

                <!-- Transaksi Dibatalkan -->
                <div class="stat-card danger">
                    <div class="stat-icon">✕</div>
                    <div class="stat-number"><?php echo $transaksi_batal; ?></div>
                    <div class="stat-label">Transaksi Dibatalkan</div>
                    <a href="booking.php" class="stat-link">Selengkapnya</a>
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
            </div>
        </div>
    </div>
</body>
</html>
