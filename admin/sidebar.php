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
        box-shadow: 4px 0 12px rgba(0, 0, 0, 0.25);
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
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
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
    .sidebar a.menu-item {
        display: flex;
        align-items: center;
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 10px;
        font-size: 16px;
        color: #dfffe6;
        text-decoration: none;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .sidebar a.menu-item i {
        width: 25px;
        margin-right: 14px;
        font-size: 20px;
        opacity: 0.95;
    }

    /* HOVER EFFECT */
    .sidebar a.menu-item:hover {
        background: linear-gradient(135deg, #1e9d4b, #157a36);
        color: #fff;
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    /* ACTIVE MENU (AUTOMATIC) */
    .sidebar a.menu-item.active {
        background: linear-gradient(135deg, #26b859, #157a36);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
        transform: translateX(4px);
    }

</style>

<div class="sidebar">

    <div class="header">
        <div class="logo-box">GR</div>
        <div class="title">
            <h5>Gunung Raung</h5>
            <small>Admin Panel</small>
        </div>
    </div>

    <a href="dashboard_1.php" class="menu-item">
        <i class="fa-solid fa-chart-line"></i> Dashboard
    </a>

    <a href="pesanan.php" class="menu-item">
        <i class="fa-solid fa-file-invoice"></i> Pesanan
    </a>

    <a href="pembayaran.php" class="menu-item">
        <i class="fa-solid fa-money-bill-wave"></i> Data Pembayaran
    </a>

    <a href="jalur_pendakian.php" class="menu-item">
        <i class="fa-solid fa-map-location-dot"></i> Data Jalur
    </a>

</div>

<!-- AUTO ACTIVE SCRIPT -->
<script>
    const currentPage = window.location.pathname.split("/").pop();
    document.querySelectorAll(".menu-item").forEach(item => {
        if (item.getAttribute("href") === currentPage) {
            item.classList.add("active");
        }
    });
</script>
