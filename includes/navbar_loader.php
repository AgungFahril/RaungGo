<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) return;

if ($_SESSION['role'] === 'admin') {
    include __DIR__ . '/navbar_admin.php';
} else {
    include __DIR__ . '/navbar_user.php';
}
?>
