<!-- SIDEBAR -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    .sidebar {
        width: 260px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: linear-gradient(180deg, #0b3d15, #145c24);
        color: #fff;
        padding: 20px 15px;
        overflow-y: auto;
    }

    .sidebar .logo-box {
        background: #2d6a38;
        width: 55px;
        height: 55px;
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        font-size: 20px;
        color: white;
    }

    .sidebar .title {
        margin-left: 10px;
    }

    .sidebar .title h5 {
        margin: 0;
        font-weight: 700;
    }

    .sidebar .title small {
        font-size: 12px;
        opacity: 0.8;
    }

    .sidebar a.menu-item {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        border-radius: 10px;
        color: #e8ffe8;
        text-decoration: none;
        margin-bottom: 8px;
        transition: 0.2s;
        font-size: 15px;
        cursor: pointer;
    }

    .sidebar a.menu-item i {
        width: 20px;
        margin-right: 10px;
        font-size: 17px;
    }

    .sidebar a.menu-item:hover,
    .sidebar a.menu-item.active {
        background: #1c7c3b;
        color: white;
        transform: translateX(4px);
    }

    /* SUBMENU STYLE */
    .submenu {
        display: none;
        padding-left: 30px;
        margin-top: -5px;
    }

    .submenu a {
        font-size: 14px;
        padding: 8px 10px;
        margin-bottom: 6px;
        display: block;
        color: #d9ffe0;
        border-radius: 8px;
    }

    .submenu a:hover {
        background: #1c7c3b;
        transform: translateX(4px);
    }

    /* Arrow icon */
    .menu-arrow {
        margin-left: auto;
        transition: 0.3s;
    }

    .rotate {
        transform: rotate(90deg);
    }
</style>

<div class="sidebar">

    <div class="d-flex align-items-center mb-4">
        <div class="logo-box">GR</div>
        <div class="title">
            <h5>Gunung Raung</h5>
            <small>Admin Panel</small>
        </div>
    </div>

    <a href="dashboard_1.php" class="menu-item">
        <i class="fa-solid fa-table-columns"></i> Dashboard
    </a>

    <a href="pesanan.php" class="menu-item">
        <i class="fa-solid fa-file-invoice"></i> Pesanan
    </a>

    <!-- MENU PEMBAYARAN (PARENT) -->
    <a class="menu-item" onclick="toggleSubmenu()">
        <i class="fa-solid fa-money-bill"></i> Pembayaran
        <i class="fa-solid fa-chevron-right menu-arrow" id="arrowMenu"></i>
    </a>

    <!-- SUBMENU PEMBAYARAN -->
    <div class="submenu" id="submenuPembayaran">
        <a href="pembayaran.php"><i class="fa-solid fa-circle-dot"></i> Data Pembayaran</a>
        <a href="menunggu_pembayaran.php"><i class="fa-solid fa-spinner"></i> Menunggu Konfirmasi</a>
    </div>

    <a href="jalur_pendakian.php" class="menu-item">
        <i class="fa-solid fa-map-location-dot"></i> Data Jalur
    </a>

</div>

<script>
    function toggleSubmenu() {
        let submenu = document.getElementById("submenuPembayaran");
        let arrow = document.getElementById("arrowMenu");

        if (submenu.style.display === "block") {
            submenu.style.display = "none";
            arrow.classList.remove("rotate");
        } else {
            submenu.style.display = "block";
            arrow.classList.add("rotate");
        }
    }
</script>
