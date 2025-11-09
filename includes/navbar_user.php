<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Pastikan koneksi database tersedia
require_once __DIR__ . '/../backend/koneksi.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$basePath = (strpos($_SERVER['PHP_SELF'], '/pengunjung/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
    ? '../'
    : '';

$harusLengkapiData = false;
$pesan = "";

// ✅ Jika user login, cek apakah sudah melengkapi data diri
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $cekData = $conn->prepare("SELECT pendaki_id FROM pendaki_detail WHERE user_id = ?");
    $cekData->bind_param("i", $user_id);
    $cekData->execute();
    $cekData->store_result();

    if ($cekData->num_rows === 0) {
        // Belum melengkapi data diri
        $harusLengkapiData = true;
    } else {
        // Sudah lengkap
        $pesan = "Data diri kamu sudah lengkap! Silakan lanjut membaca SOP pendakian.";
    }

    $cekData->close();
}
?>

<nav class="navbar">
    <a href="<?= $basePath ?>index.php" class="nav-brand">Tahura Raden Soerjo</a>
    <ul class="nav-menu">
        <li <?= ($currentPage === 'index.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>index.php">Beranda</a>
        </li>

        <li <?= ($currentPage === 'sop.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>pengunjung/sop.php">SOP Pendaki</a>
        </li>

        <li <?= ($currentPage === 'PanduanBooking.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>PanduanBooking.php">Panduan Booking</a>
        </li>

        <li <?= ($currentPage === 'PanduanPembayaran.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>PanduanPembayaran.php">Panduan Pembayaran</a>
        </li>

        <li <?= ($currentPage === 'StatusBooking.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>StatusBooking.php">Status Booking</a>
        </li>

        <!-- 🔘 Tombol Booking -->
        <li <?= in_array($currentPage, ['kuota.php', 'booking.php']) ? 'class="active"' : '' ?>>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <!-- Belum login -->
                <a href="<?= $basePath ?>login.php?redirect=pengunjung/lengkapi_data.php">Booking</a>

            <?php elseif ($harusLengkapiData): ?>
                <!-- Sudah login tapi belum melengkapi data diri -->
                <a href="<?= $basePath ?>pengunjung/lengkapi_data.php"
                   onclick="alert('Lengkapi data diri terlebih dahulu sebelum booking!')">Booking</a>

            <?php elseif (empty($_SESSION['setuju_sop']) || $_SESSION['setuju_sop'] !== true): ?>
                <!-- Sudah lengkap data diri tapi belum setuju SOP -->
                <a href="<?= $basePath ?>pengunjung/sop.php" id="booking-link">Booking</a>

            <?php else: ?>
                <!-- Sudah lengkap semuanya -->
                <a href="<?= $basePath ?>pengunjung/kuota.php">Booking</a>
            <?php endif; ?>
        </li>

        <!-- 👤 Menu Login / Dashboard -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="<?= $basePath ?>admin/dashboard.php">Dashboard</a></li>
            <?php else: ?>
                <li><a href="<?= $basePath ?>pengunjung/dashboard.php">Dashboard</a></li>
            <?php endif; ?>

            <li><a href="<?= $basePath ?>backend/logout.php" class="login-btn">Logout</a></li>
            <li><span class="user-name">👋 Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pendaki'); ?></span></li>
        <?php else: ?>
            <li <?= ($currentPage === 'login.php') ? 'class="active"' : '' ?>>
                <a href="<?= $basePath ?>login.php" class="login-btn">Login</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- ✅ SweetAlert hanya muncul di halaman utama -->
<?php if (!empty($pesan) && $currentPage === 'index.php'): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        icon: 'success',
        title: 'Data Diri Lengkap ✅',
        text: '<?= addslashes($pesan) ?>',
        confirmButtonColor: '#4CAF50'
    });
});
</script>
<?php endif; ?>
