<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

if ($_SESSION['role'] != 'pendaki') {
    header("Location: ../admin/dashboard.php");
    exit();
}

include '../backend/koneksi.php';

// Data user
$user_id = $_SESSION['user_id'];

// Ambil foto profil
$stmt = $conn->prepare("SELECT foto_profil FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_foto = $res->fetch_assoc();
$foto_profil = $user_foto['foto_profil'] ?? null;
$stmt->close();

// Statistik
$total_transaksi = $transaksi_sukses = $transaksi_pending = $transaksi_batal = 0;

try {
    $querys = [
        'total' => "SELECT COUNT(*) as total FROM pesanan WHERE user_id = ?",
        'sukses' => "SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? 
                     AND (status_pesanan LIKE '%lunas%' OR status_pesanan LIKE '%terkonfirmasi%' OR status_pesanan LIKE '%selesai%')",
        'pending' => "SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? 
                      AND (status_pesanan LIKE '%menunggu%' OR status_pesanan LIKE '%Pending%')",
        'batal' => "SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? 
                    AND (status_pesanan LIKE '%batal%' OR status_pesanan LIKE '%cancel%')"
    ];
    foreach ($querys as $key => $q) {
        $stmt = $conn->prepare($q);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        if ($key == 'total') $total_transaksi = $r;
        if ($key == 'sukses') $transaksi_sukses = $r;
        if ($key == 'pending') $transaksi_pending = $r;
        if ($key == 'batal') $transaksi_batal = $r;
        $stmt->close();
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard User - Pendakian Gunung Raung</title>
<style>
/* RESET */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}

/* ANIMATIONS */
@keyframes fadeIn{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:none}}
@keyframes slideIn{from{transform:translateX(-50px);opacity:0}to{transform:none;opacity:1}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.6}}

/* BODY */
body{
    background:linear-gradient(135deg,#f5faf5,#e8f5e9);
    overflow-x:hidden;
    animation:fadeIn .8s ease;
}

/* LAYOUT */
.dashboard-container{display:flex;min-height:100vh}
.sidebar{
    width:270px;
    background:linear-gradient(180deg,#2e7d32,#1b5e20);
    color:#fff;
    padding:35px 0;
    box-shadow:4px 0 20px rgba(0,0,0,0.2);
    position:fixed;
    height:100vh;
    animation:slideIn .8s ease;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}

/* SIDEBAR HEADER */
.sidebar-header{
    display:flex;
    align-items:center;
    padding:0 25px 30px;
    border-bottom:1px solid rgba(255,255,255,0.1);
}
.user-avatar{
    width:65px;height:65px;border-radius:50%;
    background:#43a047;
    display:flex;align-items:center;justify-content:center;
    font-size:26px;font-weight:600;color:white;
    margin-right:16px;
    overflow:hidden;
    border:3px solid rgba(255,255,255,0.2);
}
.user-avatar img{width:100%;height:100%;object-fit:cover}
.user-info h3{font-size:15px;margin-bottom:5px}
.user-status{font-size:12px;color:#FFD700;display:flex;align-items:center}
.status-indicator{width:8px;height:8px;background:#FFD700;border-radius:50%;margin-right:6px;animation:pulse 2s infinite}

/* NAVIGATION */
.sidebar-nav{margin-top:15px}
.nav-item{
    display:block;
    padding:13px 28px;
    color:rgba(255,255,255,0.85);
    text-decoration:none;
    font-weight:500;
    border-left:4px solid transparent;
    transition:all .3s;
}
.nav-item:hover{
    background:rgba(255,255,255,0.1);
    border-left:4px solid #FFD700;
    color:white;
}
.nav-item.active{
    background:rgba(255,255,255,0.15);
    border-left:4px solid #FFD700;
}

/* MAIN */
.main-content{
    flex:1;
    margin-left:270px;
    padding:50px;
    animation:fadeIn .8s ease;
}

/* TOP BAR */
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:2px solid rgba(46,125,50,0.1);
    padding-bottom:20px;
    margin-bottom:40px;
}
.top-bar h1{color:#2e7d32;font-size:28px;font-weight:700}
.top-bar p{color:#555;font-size:14px}

/* BUTTON */
.logout-btn{
    background:#2e7d32;
    color:#fff;
    padding:10px 20px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
    transition:all .3s;
}
.logout-btn:hover{background:#1b5e20}

/* STATS GRID */
.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:25px;
    margin-bottom:40px;
}
.stat-card{
    background:#fff;
    border-radius:14px;
    padding:30px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
    transition:all .3s ease;
    position:relative;
    overflow:hidden;
    border-top:5px solid #3b82f6;
}
.stat-card:hover{transform:translateY(-5px);box-shadow:0 12px 28px rgba(0,0,0,0.1)}
.stat-card.success{border-top:5px solid #43a047}
.stat-card.warning{border-top:5px solid #fbc02d}
.stat-card.danger{border-top:5px solid #e53935}
.stat-icon{font-size:38px;margin-bottom:12px}
.stat-number{font-size:36px;font-weight:700;color:#2e7d32}
.stat-label{color:#666;font-weight:500;margin-bottom:8px}
.stat-link{
    color:#2e7d32;text-decoration:none;font-weight:600;font-size:13px;
    transition:.3s;
}
.stat-link:hover{color:#1b5e20;transform:translateX(5px)}

/* CONTENT */
.content-section{
    background:#fff;
    border-radius:14px;
    padding:30px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    border-left:6px solid #2e7d32;
}
.content-section h2{color:#2e7d32;margin-bottom:10px}
.content-section p{color:#555;line-height:1.7}
.home-button{
    display:inline-block;
    background:#2e7d32;
    color:#fff;
    padding:10px 24px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
    margin-top:15px;
    transition:.3s;
}
.home-button:hover{background:#1b5e20}

/* RESPONSIVE */
@media(max-width:900px){
    .sidebar{position:relative;width:100%;height:auto}
    .main-content{margin-left:0;padding:25px}
    .stats-grid{grid-template-columns:1fr;gap:20px}
}
</style>
</head>
<body>
<div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-header">
                <div class="user-avatar">
                    <?php if (!empty($foto_profil) && file_exists("../uploads/profil/$foto_profil")): ?>
                        <img src="../uploads/profil/<?php echo htmlspecialchars($foto_profil); ?>" alt="Profil">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['nama'],0,1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['nama']); ?></h3>
                    <div class="user-status"><span class="status-indicator"></span> Online</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">🏠 Dashboard</a>
                <a href="edit_profil.php" class="nav-item">👤 Edit Profil</a>
                <a href="booking.php" class="nav-item">📅 Booking</a>
                <a href="../pengunjung/dashboard.php?tab=transaksi" class="nav-item">📊 Transaksi</a>
            </nav>
        </div>
        <a href="../backend/logout.php" class="nav-item" style="border-top:1px solid rgba(255,255,255,0.15)">🚪 Logout</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="top-bar">
            <div>
                <h1>Dashboard User</h1>
                <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>
            </div>
            <a href="../backend/logout.php" class="logout-btn">Logout</a>
        </div>

        <!-- STATISTICS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">ℹ️</div>
                <div class="stat-number"><?php echo $total_transaksi; ?></div>
                <div class="stat-label">Total Transaksi</div>
                <a href="../StatusBooking.php?filter=all" class="stat-link">Selengkapnya →</a>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $transaksi_sukses; ?></div>
                <div class="stat-label">Transaksi Sukses</div>
                <a href="../StatusBooking.php?filter=success" class="stat-link">Selengkapnya →</a>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">⏱</div>
                <div class="stat-number"><?php echo $transaksi_pending; ?></div>
                <div class="stat-label">Transaksi Menunggu</div>
                <a href="../StatusBooking.php?filter=pending" class="stat-link">Selengkapnya →</a>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon">❌</div>
                <div class="stat-number"><?php echo $transaksi_batal; ?></div>
                <div class="stat-label">Transaksi Dibatalkan</div>
                <a href="../StatusBooking.php?filter=cancelled" class="stat-link">Selengkapnya →</a>
            </div>
        </div>

        <!-- INFO -->
        <div class="content-section">
            <h2>📖 Panduan Cepat</h2>
            <p>
                Selamat datang di sistem booking pendakian Gunung Raung. Anda dapat melakukan booking pendakian,
                mengecek status pembayaran, dan melihat informasi SOP pendakian melalui menu di samping.
                Pastikan semua data Anda akurat untuk pengalaman booking yang lancar.
            </p>
            <a href="../index.php" class="home-button">🏠 Kembali ke Halaman Utama</a>
        </div>
    </main>
</div>
</body>
</html>
