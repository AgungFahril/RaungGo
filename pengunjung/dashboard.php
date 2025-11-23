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

<<<<<<< HEAD
// Ambil data profil lengkap untuk modal
$stmt_profil = $conn->prepare("
    SELECT u.nama, u.email, u.foto_profil, p.nik, p.alamat, p.no_hp, p.provinsi, p.kabupaten, p.kecamatan
    FROM users u
    LEFT JOIN pendaki_detail p ON u.user_id = p.user_id
    WHERE u.user_id = ?
");
$stmt_profil->bind_param("i", $user_id);
$stmt_profil->execute();
$profil_data = $stmt_profil->get_result()->fetch_assoc();
$stmt_profil->close();

=======
>>>>>>> eb398f44a08b98e31566c884da9dd3137fce150b
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
<<<<<<< HEAD
@keyframes slideInUp{from{transform:translateY(30px);opacity:0}to{transform:none;opacity:1}}
=======
>>>>>>> eb398f44a08b98e31566c884da9dd3137fce150b
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
<<<<<<< HEAD

/* MODAL PROFIL */
.modal{
    display:none;
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,0.7);
    z-index:2000;
    animation:fadeIn .3s ease;
    align-items:center;
    justify-content:center;
    backdrop-filter:blur(4px);
}
.modal.active{display:flex}
.modal-content{
    background:#fff;
    border-radius:24px;
    padding:0;
    max-width:650px;
    width:95%;
    max-height:90vh;
    overflow:hidden;
    box-shadow:0 25px 50px rgba(0,0,0,0.3);
    animation:slideInUp .3s ease;
    border:2px solid #2e7d32;
}
.modal-header{
    background:linear-gradient(135deg,#2e7d32,#1b5e20);
    padding:30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:#fff;
    position:relative;
}
.modal-header::after{
    content:'';
    position:absolute;
    right:-50px;
    top:-50px;
    width:200px;
    height:200px;
    background:radial-gradient(circle,rgba(255,255,255,0.1),transparent);
    border-radius:50%;
}
.modal-header h2{font-size:28px;font-weight:700;position:relative;z-index:1}
.modal-close{
    background:rgba(255,255,255,0.2);
    border:none;
    font-size:28px;
    cursor:pointer;
    color:#fff;
    transition:all .3s;
    width:45px;
    height:45px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    position:relative;
    z-index:1;
}
.modal-close:hover{background:rgba(255,255,255,0.3);transform:rotate(90deg)}

.profile-section{
    padding:40px;
    overflow-y:auto;
    max-height:calc(90vh - 100px);
}

.profile-photo{
    text-align:center;
    margin-bottom:30px;
}
.profile-photo img{
    width:160px;
    height:160px;
    border-radius:50%;
    border:6px solid #2e7d32;
    object-fit:cover;
    box-shadow:0 10px 30px rgba(46,125,50,0.3);
    transition:all .3s;
}
.profile-photo img:hover{transform:scale(1.05);box-shadow:0 15px 40px rgba(46,125,50,0.4)}
.profile-photo .avatar-fallback{
    width:160px;
    height:160px;
    border-radius:50%;
    background:linear-gradient(135deg,#2e7d32,#1b5e20);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:70px;
    color:#fff;
    font-weight:bold;
    border:6px solid #2e7d32;
    box-shadow:0 10px 30px rgba(46,125,50,0.3);
    margin:0 auto;
}

.profile-info{
    background:linear-gradient(135deg,#f8fbf7 0%,#eef5eb 100%);
    border-radius:16px;
    padding:30px;
    border:2px solid rgba(46,125,50,0.1);
    margin-bottom:20px;
}
.profile-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px 0;
    border-bottom:1px solid rgba(46,125,50,0.15);
    transition:all .2s;
}
.profile-item:last-child{border-bottom:none}
.profile-item:hover{background:rgba(46,125,50,0.05);padding:16px 12px;border-radius:8px;margin:0 -12px}
.profile-label{
    color:#2e7d32;
    font-weight:700;
    min-width:130px;
    font-size:14px;
    display:flex;
    align-items:center;
    gap:8px;
}
.profile-value{
    color:#333;
    font-weight:600;
    text-align:right;
    flex:1;
    word-break:break-word;
    font-size:15px;
}
.profile-value.empty{color:#999;font-style:italic;font-weight:400}

.modal-footer{
    padding:25px 40px;
    background:#f8fbf7;
    border-top:1px solid rgba(46,125,50,0.1);
    display:flex;
    gap:12px;
    justify-content:flex-end;
}
.btn-edit,.btn-close{
    padding:12px 28px;
    border-radius:10px;
    border:none;
    cursor:pointer;
    font-weight:600;
    transition:all .3s;
    font-size:14px;
}
.btn-edit{
    background:linear-gradient(135deg,#2e7d32,#1b5e20);
    color:#fff;
    box-shadow:0 4px 15px rgba(46,125,50,0.2);
}
.btn-edit:hover{
    background:linear-gradient(135deg,#1b5e20,#0d3d1a);
    transform:translateY(-2px);
    box-shadow:0 6px 25px rgba(46,125,50,0.3);
}
.btn-close{
    background:#e8e8e8;
    color:#333;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}
.btn-close:hover{background:#d8d8d8;transform:translateY(-2px)}

@keyframes slideInUp{from{transform:translateY(40px);opacity:0}to{transform:none;opacity:1}}

/* PROFIL CARD */
.profile-card{
    background:#fff;
    border-radius:16px;
    padding:0;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
    margin-bottom:40px;
    overflow:hidden;
    animation:slideInUp .5s ease;
    display:grid;
    grid-template-columns:280px 1fr;
    border:2px solid rgba(46,125,50,0.1);
    transition:all .3s;
}
.profile-card:hover{box-shadow:0 12px 35px rgba(46,125,50,0.15)}

.profile-card-photo{
    background:linear-gradient(135deg,#2e7d32,#1b5e20);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:30px;
    min-height:300px;
}
.profile-card-photo img{
    width:200px;
    height:200px;
    border-radius:50%;
    border:6px solid white;
    object-fit:cover;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}
.profile-card-photo .avatar-fallback{
    width:200px;
    height:200px;
    border-radius:50%;
    background:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:100px;
    color:#2e7d32;
    font-weight:bold;
    border:6px solid white;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

.profile-card-info{
    padding:40px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}
.profile-card-name{
    font-size:32px;
    font-weight:700;
    color:#2e7d32;
    margin-bottom:5px;
}
.profile-card-email{
    color:#666;
    font-size:14px;
    margin-bottom:25px;
}
.profile-card-details{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-bottom:20px;
}
.detail-item{
    background:#f8fbf7;
    padding:16px;
    border-radius:10px;
    border-left:4px solid #2e7d32;
}
.detail-item-label{
    color:#2e7d32;
    font-weight:700;
    font-size:12px;
    text-transform:uppercase;
    display:flex;
    align-items:center;
    gap:6px;
    margin-bottom:6px;
}
.detail-item-value{
    color:#333;
    font-weight:600;
    font-size:15px;
    word-break:break-word;
}
.detail-item-value.empty{
    color:#999;
    font-style:italic;
    font-weight:400;
}

.profile-card-actions{
    display:flex;
    gap:12px;
}
.btn-profile-edit{
    flex:1;
    background:linear-gradient(135deg,#2e7d32,#1b5e20);
    color:#fff;
    border:none;
    padding:12px 20px;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    transition:all .3s;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
}
.btn-profile-edit:hover{
    background:linear-gradient(135deg,#1b5e20,#0d3d1a);
    transform:translateY(-2px);
    box-shadow:0 6px 20px rgba(46,125,50,0.3);
}

@media(max-width:1000px){
    .profile-card{grid-template-columns:1fr}
    .profile-card-photo{min-height:200px}
    .profile-card-photo img,
    .profile-card-photo .avatar-fallback{width:150px;height:150px;font-size:75px}
    .profile-card-details{grid-template-columns:1fr}
}

=======
>>>>>>> eb398f44a08b98e31566c884da9dd3137fce150b
</style>
</head>
<body>
<div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div>
<<<<<<< HEAD
            <div class="sidebar-header" onclick="openProfilModal()" style="cursor:pointer;transition:.3s" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background=''">
=======
            <div class="sidebar-header">
>>>>>>> eb398f44a08b98e31566c884da9dd3137fce150b
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
<<<<<<< HEAD
                <a href="profil.php" class="nav-item">👤 Profil Pribadi</a>
                <a href="edit_profil.php" class="nav-item">✏️ Edit Profil</a>
=======
                <a href="dashboard.php" class="nav-item active">🏠 Dashboard</a>
                <a href="edit_profil.php" class="nav-item">👤 Edit Profil</a>
>>>>>>> eb398f44a08b98e31566c884da9dd3137fce150b
                <a href="booking.php" class="nav-item">📅 Booking</a>
                <a href="../pengunjung/dashboard.php?tab=transaksi" class="nav-item">📊 Transaksi</a>
            </nav>
        </div>
<<<<<<< HEAD
        <a href="../backend/logout.php" class="nav-item" style="border-top:1px solid rgba(255,255,255,0.15); background:#e53935; margin-top:auto;">🚪 Logout</a>
=======
        <a href="../backend/logout.php" class="nav-item" style="border-top:1px solid rgba(255,255,255,0.15)">🚪 Logout</a>
>>>>>>> eb398f44a08b98e31566c884da9dd3137fce150b
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
<<<<<<< HEAD

<!-- MODAL PROFIL -->
<div id="profilModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>👤 Profil Pribadi</h2>
            <button class="modal-close" onclick="closeProfilModal()">&times;</button>
        </div>

        <div class="profile-section">
            <div class="profile-photo">
                <?php if (!empty($profil_data['foto_profil']) && file_exists("../uploads/profil/{$profil_data['foto_profil']}")): ?>
                    <img src="../uploads/profil/<?php echo htmlspecialchars($profil_data['foto_profil']); ?>" alt="Foto Profil">
                <?php else: ?>
                    <div class="avatar-fallback"><?php echo strtoupper(substr($profil_data['nama'], 0, 1)); ?></div>
                <?php endif; ?>
            </div>

            <div class="profile-info">
                <div class="profile-item">
                    <span class="profile-label">👤 Nama</span>
                    <span class="profile-value"><?php echo htmlspecialchars($profil_data['nama'] ?? '-'); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">📧 Email</span>
                    <span class="profile-value"><?php echo htmlspecialchars($profil_data['email'] ?? '-'); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">🆔 NIK</span>
                    <span class="profile-value"><?php echo htmlspecialchars($profil_data['nik'] ?? '-'); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">📍 Alamat</span>
                    <span class="profile-value"><?php echo htmlspecialchars($profil_data['alamat'] ?? '-'); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">📱 No. HP</span>
                    <span class="profile-value"><?php echo htmlspecialchars($profil_data['no_hp'] ?? '-'); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">🏘️ Kota</span>
                    <span class="profile-value"><?php echo htmlspecialchars($profil_data['kabupaten'] ?? '-'); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">🗺️ Provinsi</span>
                    <span class="profile-value"><?php echo htmlspecialchars($profil_data['provinsi'] ?? '-'); ?></span>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <a href="edit_profil.php" class="btn-edit">✏️ Edit Profil</a>
            <button class="btn-close" onclick="closeProfilModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
function openProfilModal(){document.getElementById('profilModal').classList.add('active')}
function closeProfilModal(){document.getElementById('profilModal').classList.remove('active')}
document.addEventListener('click',e=>{
    const modal=document.getElementById('profilModal');
    if(e.target===modal)closeProfilModal()
})
document.addEventListener('keydown',e=>{
    if(e.key==='Escape')closeProfilModal()
})
</script>
=======
>>>>>>> eb398f44a08b98e31566c884da9dd3137fce150b
</body>
</html>
