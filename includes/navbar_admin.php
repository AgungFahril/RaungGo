<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// CEK LOGIN USER BIASA
if (!isset($_SESSION['user_id'])) {
    header("Location: /PROJEKSEMESTER3/login.php");
    exit;
}
?>

<!-- NAVBAR USER -->
<nav style="
    width: 100%;
    height: 60px;
    background: #0d5f2a;
    padding: 0 20px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-family: Arial, sans-serif;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 999;
">
    <div style="font-size: 20px; font-weight: bold;">
        Gunung Raung – User Panel
    </div>

    <div>
        <a href="/PROJEKSEMESTER3/user/dashboard.php" style="color: #fff; margin-right: 20px; text-decoration: none;">
            Dashboard
        </a>
        <a href="/PROJEKSEMESTER3/user/pesanan_saya.php" style="color: #fff; margin-right: 20px; text-decoration: none;">
            Pesanan
        </a>
        <a href="/PROJEKSEMESTER3/logout.php" style="color: #ffdddd; text-decoration: none;">
            Logout
        </a>
    </div>
</nav>

<!-- AGAR KONTEN TURUN -->
<style>
body {
    padding-top: 70px; 
}
</style>
