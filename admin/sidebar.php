<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* SIDEBAR WRAPPER */
    .sidebar {
        width: 260px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: rgba(15, 42, 23, 0.95);
        backdrop-filter: blur(6px);
        border-right: 1px solid rgba(255, 255, 255, 0.07);
        color: #fff;
        padding: 25px 18px;
        overflow-y: auto;
        box-shadow: 4px 0 12px rgba(0,0,0,0.25);
        display: flex;
        flex-direction: column;
    }

    /* LOGO SECTION */
    .sidebar .header {
        display: flex;
        align-items: center;
        margin-bottom: 35px;
    }

    .sidebar .logo-box {
        background: linear-gradient(135deg, #1a8e43, #0d5f2a);
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        font-size: 22px;
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    }

    .sidebar .title {
        margin-left: 12px;
    }

    .sidebar .title h5 {
        margin: 0;
        font-size: 19px;
        letter-spacing: 0.5px;
        font-weight: 700;
    }

    .sidebar .title small {
        opacity: 0.85;
        font-size: 13px;
    }

    /* MENU ITEMS */
    .menu-item {
        display: flex;
        align-items: center;
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 10px;
        font-size: 16px;
        color: #dfffe6;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .menu-item i {
        width: 28px;
        margin-right: 14px;
        font-size: 20px;
    }

    .menu-item:hover {
        background: linear-gradient(135deg, #1e9d4b, #157a36);
        color: white;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    }

    .menu-item.active {
        background: linear-gradient(135deg, #26b859, #157a36);
        color: #fff;
        font-weight: 600;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.35);
    }

    /* SUBMENU */
    .submenu-toggle {
        cursor: pointer;
    }

    .submenu {
        margin-left: 18px;
        display: none;
        flex-direction: column;
    }

    .submenu a {
        padding: 10px 14px;
        font-size: 15px;
        border-radius: 10px;
        margin-bottom: 6px;
        color: #dfffe6;
    }

    .submenu a:hover {
        background: rgba(255,255,255,0.08);
        transform: translateX(4px);
    }

    .submenu a.active {
        font-weight: 600;
        background: rgba(255,255,255,0.12);
        color: #fff;
        transform: translateX(4px);
    }

    /* ARROW ICON */
    .arrow {
        margin-left: auto;
        transition: 0.3s;
        transform: rotate(-90deg);
    }

    .arrow.rotate {
        transform: rotate(0deg);
    }

</style>

<div class="sidebar">

    <!-- LOGO -->
    <div class="header">
        <div class="logo-box">GR</div>
        <div class="title">
            <h5>Gunung Raung</h5>
            <small>Admin Panel</small>
        </div>
    </div>

    <!-- MENU LIST -->
    <a href="dashboard_1.php" class="menu-item"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="pesanan.php" class="menu-item"><i class="fa-solid fa-file-invoice"></i> Pesanan</a>
    <a href="pembayaran.php" class="menu-item"><i class="fa-solid fa-money-bill-wave"></i> Pembayaran</a>
    <a href="pendaki.php" class="menu-item"><i class="fa-solid fa-users"></i> Pendaki / User</a>

    <!-- LAYANAN -->
    <div class="menu-item submenu-toggle">
        <i class="fa-solid fa-concierge-bell"></i> Layanan
        <i class="fa-solid fa-chevron-down arrow"></i>
    </div>

    <div class="submenu">
        <a href="ojek.php" class="submenu-item"><i class="fa-solid fa-motorcycle"></i> Ojek</a>
        <a href="porter.php" class="submenu-item"><i class="fa-solid fa-dolly"></i> Porter</a>
        <a href="guide.php" class="submenu-item"><i class="fa-solid fa-person-hiking"></i> Guide</a>
    </div>

    <a href="jalur_pendakian.php" class="menu-item"><i class="fa-solid fa-map-location-dot"></i> Jalur Pendakian</a>
    <a href="laporan.php" class="menu-item"><i class="fa-solid fa-file-lines"></i> Laporan</a>
    <a href="manajemen_admin.php" class="menu-item"><i class="fa-solid fa-user-shield"></i> Manajemen Admin</a>


</div>

<!-- SCRIPT ACTIVE & SUBMENU -->
<script>
    const currentPage = window.location.pathname.split("/").pop();

    // Highlight menu aktif
    document.querySelectorAll(".menu-item").forEach(item => {
        if (item.getAttribute("href") === currentPage) {
            item.classList.add("active");
        }
    });

    // Highlight submenu aktif + buka otomatis
    document.querySelectorAll(".submenu a").forEach(item => {
        if (item.getAttribute("href") === currentPage) {
            item.classList.add("active");
            document.querySelector(".submenu").style.display = "flex";
            document.querySelector(".arrow").classList.add("rotate");
        }
    });

    // Toggle submenu
    document.querySelector(".submenu-toggle").addEventListener("click", () => {
        const submenu = document.querySelector(".submenu");
        const arrow = document.querySelector(".arrow");

        if (submenu.style.display === "flex") {
            submenu.style.display = "none";
            arrow.classList.remove("rotate");
        } else {
            submenu.style.display = "flex";
            arrow.classList.add("rotate");
        }
    });
</script>
