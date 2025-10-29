<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <a href="index.php" class="nav-brand">Tahura Raden Soerjo</a>
    <ul class="nav-menu">
        <li <?= ($currentPage == 'index.php') ? 'class="active"' : '' ?>>
            <a href="index.php">Beranda</a>
        </li>

        <li <?= ($currentPage == 'sop.php') ? 'class="active"' : '' ?>>
            <a href="sop.php">SOP Pendaki</a>
        </li>

        <li <?= ($currentPage == 'PanduanBooking.php') ? 'class="active"' : '' ?>>
            <a href="PanduanBooking.php">Panduan Booking</a>
        </li>

        <li <?= ($currentPage == 'PanduanPembayaran.php') ? 'class="active"' : '' ?>>
            <a href="PanduanPembayaran.php">Panduan Pembayaran</a>
        </li>

        <li <?= ($currentPage == 'StatusBooking.php') ? 'class="active"' : '' ?>>
            <a href="StatusBooking.php">Status Booking</a>
        </li>

        <!-- ✅ Tambahkan logika Booking -->
        <li <?= ($currentPage == 'booking.php') ? 'class="active"' : '' ?>>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!isset($_SESSION['setuju_sop']) || $_SESSION['setuju_sop'] !== true): ?>
                    <!-- Belum menyetujui SOP -->
                    <a href="pengunjung/sop.php">Booking</a>
                <?php else: ?>
                    <!-- Sudah menyetujui SOP -->
                    <a href="pengunjung/booking.php">Booking</a>
                <?php endif; ?>
            <?php else: ?>
                <!-- Belum login -->
                <a href="login.php?from=booking">Booking</a>
            <?php endif; ?>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="admin/dashboard.php">Dashboard</a></li>
            <?php else: ?>
                <li><a href="pengunjung/dashboard.php">Dashboard</a></li>
            <?php endif; ?>

            <li><a href="backend/logout.php" class="login-btn">Logout</a></li>
            <li><span class="user-name">👋 Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pendaki'); ?></span></li>
        <?php else: ?>
            <li <?= ($currentPage == 'login.php') ? 'class="active"' : '' ?>>
                <a href="login.php" class="login-btn">Login</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
