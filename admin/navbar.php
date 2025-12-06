<?php
// navbar.php

// --- AUTO PATH FIX ---
$basePath = "";

// Jika file berada dalam folder admin/, otomatis naik satu level
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $basePath = "../";
}

// --- Judul Halaman ---
$page = basename($_SERVER['PHP_SELF'], ".php");
$pageTitle = ucwords(str_replace("_", " ", $page));

if ($page === "dashboard_1") {
    $pageTitle = "Dashboard";
}

?>
<header class="topbar d-flex align-items-center justify-content-between px-3"
    style="
        background: linear-gradient(135deg, #1a8e43 0%, #0d5f2a 100%);
        color: white;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        box-shadow: 0 4px 14px rgba(0,0,0,0.2);
        height: 68px;
        position: sticky;
        top: 0;
        z-index: 1000;
    ">

    <!-- LEFT: TITLE -->
    <div class="d-flex align-items-center gap-3">
        <button id="menuToggle" class="btn btn-sm btn-outline-light d-md-none">
            <i class="material-icons">menu</i>
        </button>
        <h4 class="mb-0 fw-bold"><?= $pageTitle ?></h4>
    </div>

    <!-- RIGHT: PROFILE -->
    <div class="d-flex align-items-center gap-3">

        <div class="text-white small opacity-90">Halo, Admin</div>

        <!-- Avatar + Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name=Admin&background=1a8e43&color=fff&size=40&rounded=true"
                    alt="avatar" width="38" height="38" class="rounded-circle shadow-sm">
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" 
                aria-labelledby="dropdownUser"
                style="border-radius: 12px; overflow: hidden;">
                
                <li><h6 class="dropdown-header">Admin Panel</h6></li>

                <li>
                    <a class="dropdown-item" href="<?= $basePath ?>admin/manajemen_admin.php">
                        <i class="fa-solid fa-user-shield me-2"></i> Pengaturan Admin
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <a class="dropdown-item text-danger" href="<?= $basePath ?>backend/logout.php">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                    </a>
                </li>

            </ul>
        </div>
    </div>
</header>
