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

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= $basePath ?>css/responsive-navbar.css">

<!-- Navbar -->
<nav class="navbar">
    <div class="sidebar-header">
        <a href="<?= $basePath ?>index.php" class="nav-brand">
            <img src="<?= $basePath ?>images/RaungGo.png" alt="RaungGo Logo" class="navbar-logo">
        </a>
    </div>
    
    <!-- Toggle Button for Desktop -->
    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Sembunyikan Menu">
        <span>◄</span>
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

<!-- Show Button for Mobile -->
<button class="sidebar-show-btn" onclick="toggleSidebar()" title="Tampilkan Menu">
    <span>►</span>
</button>

<!-- Overlay for Mobile -->
<div class="navbar-overlay" onclick="closeSidebar()"></div>

<!-- JavaScript -->
<script>
function toggleSidebar() {
    const navbar = document.querySelector('nav.navbar');
    const overlay = document.querySelector('.navbar-overlay');
    const body = document.body;
    const showBtn = document.querySelector('.sidebar-show-btn');
    const isMobile = window.innerWidth <= 768;
    
    if (!navbar) return;
    
    navbar.classList.toggle('hidden');
    
    if (isMobile) {
        if (overlay) {
            overlay.classList.toggle('active');
        }
        
        if (body) {
            body.classList.toggle('sidebar-open');
            body.classList.toggle('no-scroll');
        }
        
        if (showBtn) {
            showBtn.classList.toggle('active');
        }
    } else {
        if (body) {
            body.classList.toggle('sidebar-hidden');
        }
        
        if (showBtn) {
            showBtn.classList.toggle('active');
        }
    }
}

function closeSidebar() {
    const navbar = document.querySelector('nav.navbar');
    const overlay = document.querySelector('.navbar-overlay');
    const body = document.body;
    const showBtn = document.querySelector('.sidebar-show-btn');
    
    if (navbar) {
        navbar.classList.add('hidden');
    }
    
    if (overlay) {
        overlay.classList.remove('active');
    }
    
    if (body) {
        body.classList.remove('sidebar-open');
        body.classList.remove('no-scroll');
    }
    
    if (showBtn) {
        showBtn.classList.add('active');
    }
}

function closeSidebar() {
    const navbar = document.querySelector('nav.navbar');
    const overlay = document.querySelector('.navbar-overlay');
    const body = document.body;
    const showBtn = document.querySelector('.sidebar-show-btn');
    
    if (navbar) {
        navbar.classList.add('hidden');
    }
    
    if (overlay) {
        overlay.classList.remove('active');
    }
    
    if (body) {
        body.classList.remove('sidebar-open');
        body.classList.remove('no-scroll');
    }
    
    if (showBtn) {
        showBtn.classList.add('active');
    }
}

// Handle window resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        const isMobile = window.innerWidth <= 768;
        const navbar = document.querySelector('nav.navbar');
        const overlay = document.querySelector('.navbar-overlay');
        const body = document.body;
        const showBtn = document.querySelector('.sidebar-show-btn');
        
        // Clear semua state saat resize
        if (overlay) {
            overlay.classList.remove('active');
        }
        if (body) {
            body.classList.remove('sidebar-open');
            body.classList.remove('no-scroll');
            body.classList.remove('sidebar-hidden');
            // Reset inline styles
            body.style.marginLeft = '';
            body.style.paddingLeft = '';
            body.style.left = '';
            body.style.transform = '';
            body.style.position = '';
        }
        
        if (isMobile) {
            // Di mobile, cek apakah sidebar sedang terbuka
            const isOpen = navbar && !navbar.classList.contains('hidden');
            
            if (isOpen) {
                // Jika terbuka, show overlay dan hide button
                if (overlay) overlay.classList.add('active');
                if (body) {
                    body.classList.add('sidebar-open');
                    body.classList.add('no-scroll');
                }
                if (showBtn) showBtn.classList.remove('active');
            } else {
                // Jika tertutup, show button
                if (showBtn) showBtn.classList.add('active');
            }
        } else {
            // Di desktop, remove show button
            if (showBtn) showBtn.classList.remove('active');
        }
    }, 250);
});

// Close sidebar when clicking on menu item (mobile only)
document.addEventListener('DOMContentLoaded', function() {
    const menuLinks = document.querySelectorAll('.nav-menu a');
    const body = document.body;
    
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });
    
    // Initialize - Di mobile, sidebar default hidden
    if (window.innerWidth <= 768) {
        const navbar = document.querySelector('nav.navbar');
        const showBtn = document.querySelector('.sidebar-show-btn');
        
        if (navbar) {
            navbar.classList.add('hidden');
        }
        if (showBtn) {
            showBtn.classList.add('active');
        }
    }
});
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