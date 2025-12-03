<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/koneksi.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$basePath = (strpos($_SERVER['PHP_SELF'], '/pengunjung/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
    ? '../'
    : '';

$harusLengkapiData = false;
$pesan = "";

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

$role = $_SESSION['role'] ?? 'pengunjung';
$namaUser = $_SESSION['nama'] ?? 'Pendaki';
?>

<!-- ✅ NAVBAR - STRUKTUR YANG BENAR -->
<nav class="navbar">
    <div class="sidebar-header">
        <a href="<?= $basePath ?>index.php" class="nav-brand">
            <img src="<?= $basePath ?>images/RaungGo.png" alt="Tahura Raden Soerjo" class="navbar-logo">
        </a>
    </div>
    
    <!-- ✅ TOMBOL HIDE - FIXED DI KANAN TENGAH NAVBAR -->
    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Sembunyikan Menu">
        <i class="fas fa-chevron-left"></i>
        <span style="font-size: 1.2rem;">◄</span>
    </button>
    
    <ul class="nav-menu">
        <li <?= ($currentPage == 'index.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>index.php">
                <i class="fas fa-home"></i> Beranda
            </a>
        </li>
        
        <li <?= ($currentPage == 'sop.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>pengunjung/sop.php">
                <i class="fas fa-book"></i> SOP Pendaki
            </a>
        </li>
        
        <li <?= ($currentPage == 'PanduanBooking.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>PanduanBooking.php">
                <i class="fas fa-calendar-check"></i> Panduan Booking
            </a>
        </li>
        
        <li <?= ($currentPage == 'PanduanPembayaran.php') ? 'class="active"' : '' ?>>
            <a href="<?= $basePath ?>PanduanPembayaran.php">
                <i class="fas fa-credit-card"></i> Panduan Pembayaran
            </a>
        </li>

        <?php if (!empty($_SESSION['user_id'])): ?>
            <?php if ($role === 'admin'): ?>
                <li <?= ($currentPage == 'dashboard.php' && str_contains($_SERVER['REQUEST_URI'], 'admin')) ? 'class="active"' : '' ?>>
                    <a href="<?= $basePath ?>admin/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
            <?php else: ?>
                <li <?= ($currentPage == 'dashboard.php' && str_contains($_SERVER['REQUEST_URI'], 'pengunjung')) ? 'class="active"' : '' ?>>
                    <a href="<?= $basePath ?>pengunjung/dashboard.php">
                        <i class="fas fa-user-circle"></i> Dashboard
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="<?= $basePath ?>backend/logout.php" class="login-btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
            
            <li>
                <span class="user-greeting">
                    👋 Halo, <?= htmlspecialchars($namaUser); ?>
                </span>
            </li>
        <?php else: ?>
            <li>
                <a href="<?= $basePath ?>login.php" class="login-btn btn-login-blue">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<!-- ✅ NAVBAR SELESAI -->

<!-- ✅ TOMBOL SHOW - HARUS DI LUAR NAVBAR -->
<button class="sidebar-show-btn" onclick="toggleSidebar()" title="Tampilkan Menu">
    <i class="fas fa-chevron-right"></i>
    <span style="font-size: 1.2rem;">►</span>
</button>

<!-- ✅ JAVASCRIPT TOGGLE -->
<script>
function toggleSidebar() {
    const navbar = document.querySelector('nav.navbar');
    const showBtn = document.querySelector('.sidebar-show-btn');
    const body = document.body;
    
    console.log('Toggle sidebar executed');
    
    if (navbar) {
        navbar.classList.toggle('hidden');
        console.log('Navbar hidden:', navbar.classList.contains('hidden'));
    }
    
    if (showBtn) {
        showBtn.classList.toggle('active');
    }
    
    if (body) {
        body.classList.toggle('sidebar-hidden');
    }
}
</script>

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