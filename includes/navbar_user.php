<?php

// 1. Ambil nama file halaman yang sedang dibuka
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

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li <?= ($currentPage == 'dashboard.php' && str_contains($_SERVER['REQUEST_URI'], 'admin')) ? 'class="active"' : '' ?>>
                    <a href="admin/dashboard.php">Dashboard</a>
                </li>
            <?php else: ?>
                <li <?= ($currentPage == 'dashboard.php' && str_contains($_SERVER['REQUEST_URI'], 'pengunjung')) ? 'class="active"' : '' ?>>
                    <a href="pengunjung/dashboard.php">Dashboard</a>
                </li>
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