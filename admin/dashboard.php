<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Tahura Raden Soerjo</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-section {
            background: #e74c3c;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            color: white;
        }
        
        .profile-section .profile-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .profile-avatar {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #e74c3c;
        }
        
        .online-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .online-dot {
            width: 8px;
            height: 8px;
            background: #4CAF50;
            border-radius: 50%;
        }
    </style>
</head>
<body>

    <!-- ✅ Navbar khusus admin -->
    <header>
<?php include '../includes/navbar_admin.php'; ?>
    </header>

    <main class="dashboard-content">
        <div class="profile-section">
            <div class="profile-header">
                <div class="profile-avatar">
                    <span><?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?></span>
                </div>
                <div>
                    <h1>Dashboard Admin</h1>
                    <div class="online-status">
                        <span class="online-dot"></span>
                        <span>Online</span>
                    </div>
                </div>
            </div>
            <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']); ?>!</p>
        </div>

        <!-- ✅ Statistik admin -->
        <?php
        require_once('../backend/koneksi.php');
        
        // Query untuk total transaksi
        $query_total = "SELECT COUNT(*) as total FROM bookings";
        $result_total = mysqli_query($conn, $query_total);
        $total_transaksi = mysqli_fetch_assoc($result_total)['total'];

        // Query untuk transaksi sukses
        $query_sukses = "SELECT COUNT(*) as sukses FROM bookings WHERE status = 'confirmed'";
        $result_sukses = mysqli_query($conn, $query_sukses);
        $transaksi_sukses = mysqli_fetch_assoc($result_sukses)['sukses'];

        // Query untuk transaksi menunggu
        $query_menunggu = "SELECT COUNT(*) as menunggu FROM bookings WHERE status = 'pending'";
        $result_menunggu = mysqli_query($conn, $query_menunggu);
        $transaksi_menunggu = mysqli_fetch_assoc($result_menunggu)['menunggu'];

        // Query untuk transaksi dibatalkan
        $query_batal = "SELECT COUNT(*) as batal FROM bookings WHERE status = 'cancelled'";
        $result_batal = mysqli_query($conn, $query_batal);
        $transaksi_batal = mysqli_fetch_assoc($result_batal)['batal'];
        ?>

        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3><?php echo $total_transaksi; ?></h3>
                <p>Total Transaksi</p>
                <a href="data_booking.php" class="stat-link">Selengkapnya →</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3><?php echo $transaksi_sukses; ?></h3>
                <p>Transaksi Sukses</p>
                <a href="data_booking.php?status=confirmed" class="stat-link">Selengkapnya →</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <h3><?php echo $transaksi_menunggu; ?></h3>
                <p>Transaksi Menunggu</p>
                <a href="data_booking.php?status=pending" class="stat-link">Selengkapnya →</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h3><?php echo $transaksi_batal; ?></h3>
                <p>Transaksi Dibatalkan</p>
                <a href="data_booking.php?status=cancelled" class="stat-link">Selengkapnya →</a>
            </div>
        </section>

        <div class="panduan-cepat">
            <h2>📚 Panduan Cepat</h2>
            <p>Selamat datang di sistem booking pendakian Gunung Raung. Anda dapat melakukan booking pendakian, mengecek status pembayaran, dan melihat informasi SOP pendakian melalui menu di samping. Pastikan semua data Anda akurat untuk pengalaman booking yang lancar.</p>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Tahura Raden Soerjo. All Rights Reserved.</p>
    </footer>

</body>
</html>
