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
if (!empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $cekData = $conn->prepare("SELECT pendaki_id FROM pendaki_detail WHERE user_id = ?");
    $cekData->bind_param("i", $user_id);
    $cekData->execute();
    $cekData->store_result();

    if ($cekData->num_rows === 0) {
        $harusLengkapiData = true;
    } else {
        $pesan = "Data diri kamu sudah lengkap! Silakan lanjut membaca SOP pendakian.";
    }

    $cekData->close();
}

// ✅ Nilai default jika session belum ada
$role = $_SESSION['role'] ?? 'pengunjung';
$namaUser = $_SESSION['nama'] ?? 'Pendaki';
?>

<nav class="navbar" style="background:#2e7d32;padding:10px 20px;color:white;">
    <a href="<?= $basePath ?>index.php" class="nav-brand" style="font-weight:700;color:white;text-decoration:none;">Tahura Raden Soerjo</a>
    <ul class="nav-menu" style="list-style:none;display:flex;gap:15px;margin:0;padding:0;align-items:center;">
        <li><a href="<?= $basePath ?>index.php" style="color:white;text-decoration:none;">Beranda</a></li>
        <li><a href="<?= $basePath ?>pengunjung/sop.php" style="color:white;text-decoration:none;">SOP Pendaki</a></li>
        <li><a href="<?= $basePath ?>PanduanBooking.php" style="color:white;text-decoration:none;">Panduan Booking</a></li>
        <li><a href="<?= $basePath ?>PanduanPembayaran.php" style="color:white;text-decoration:none;">Panduan Pembayaran</a></li>
        <li><a href="<?= $basePath ?>StatusBooking.php" style="color:white;text-decoration:none;">Status Booking</a></li>

        <!-- 🔘 Tombol Booking -->
        <li>
            <?php if (empty($_SESSION['user_id'])): ?>
                <a href="<?= $basePath ?>login.php?redirect=pengunjung/lengkapi_data.php" style="color:#fff;background:#43a047;padding:6px 12px;border-radius:6px;text-decoration:none;">Booking</a>
            <?php elseif ($harusLengkapiData): ?>
                <a href="<?= $basePath ?>pengunjung/lengkapi_data.php" onclick="alert('Lengkapi data diri terlebih dahulu sebelum booking!')" style="color:#fff;background:#fbc02d;padding:6px 12px;border-radius:6px;text-decoration:none;">Lengkapi Data</a>
            <?php elseif (empty($_SESSION['setuju_sop']) || $_SESSION['setuju_sop'] !== true): ?>
                <a href="<?= $basePath ?>pengunjung/sop.php" style="color:#fff;background:#1e88e5;padding:6px 12px;border-radius:6px;text-decoration:none;">Setujui SOP</a>
            <?php else: ?>
                <a href="<?= $basePath ?>pengunjung/kuota.php" style="color:#fff;background:#43a047;padding:6px 12px;border-radius:6px;text-decoration:none;">Booking</a>
            <?php endif; ?>
        </li>

        <!-- 👤 Menu Login / Dashboard -->
        <?php if (!empty($_SESSION['user_id'])): ?>
            <?php if ($role === 'admin'): ?>
                <li><a href="<?= $basePath ?>admin/dashboard.php" style="color:white;text-decoration:none;">Dashboard</a></li>
            <?php else: ?>
                <li><a href="<?= $basePath ?>pengunjung/dashboard.php" style="color:white;text-decoration:none;">Dashboard</a></li>
            <?php endif; ?>

            <li><a href="<?= $basePath ?>backend/logout.php" style="color:#fff;background:#e53935;padding:6px 12px;border-radius:6px;text-decoration:none;">Logout</a></li>
            <li><span style="font-weight:600;">👋 Halo, <?= htmlspecialchars($namaUser); ?></span></li>
        <?php else: ?>
            <li><a href="<?= $basePath ?>login.php" style="color:#fff;background:#1e88e5;padding:6px 12px;border-radius:6px;text-decoration:none;">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

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
