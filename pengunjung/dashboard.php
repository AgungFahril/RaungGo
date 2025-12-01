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

// ---------------------------
// AUTO-EXPIRE: tandai pesanan 'gagal' jika unpaid > 24 jam
// ---------------------------
// Pastikan kolom created_at ada di tabel pesanan (Anda sudah menambahkannya)
try {
    // Set status menjadi 'gagal' untuk pesanan yang lebih dari 24 jam menunggu pembayaran
    $sqlExpire = "
        UPDATE pesanan
        SET status_pesanan = 'gagal'
        WHERE status_pesanan = 'menunggu_pembayaran'
        AND TIMESTAMPDIFF(HOUR, created_at, NOW()) >= 24
    ";
    $conn->query($sqlExpire);

    // OPTIONAL: hapus detail anggota yang ter-orphan (jika ada)
    // (Jika ingin menyimpan histori, jangan jalankan ini)
    $sqlCleanup = "
        DELETE pa FROM pesanan_anggota pa
        LEFT JOIN pesanan p ON p.pesanan_id = pa.pesanan_id
        WHERE p.pesanan_id IS NULL
    ";
    $conn->query($sqlCleanup);
} catch(Exception $e) {
    // Log saja, jangan ganggu pengguna
    // file_put_contents(__DIR__.'/../backend/logs/auto_expire.log', $e->getMessage().PHP_EOL, FILE_APPEND);
}

// Data user
$user_id = $_SESSION['user_id'];

// Ambil foto profil dan data profil lengkap
$stmt = $conn->prepare("SELECT foto_profil FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_foto = $res->fetch_assoc();
$foto_profil = $user_foto['foto_profil'] ?? null;
$stmt->close();

// Ambil data profil untuk modal
$profile_data = null;
$stmt_profile = $conn->prepare("
    SELECT u.nama, u.email, u.foto_profil, p.nik, p.no_hp, p.alamat, p.kabupaten, 
           p.provinsi, p.kecamatan, p.kelurahan, p.tempat_lahir, p.tanggal_lahir, 
           p.jenis_kelamin, p.kewarganegaraan, p.no_darurat, p.hubungan_darurat
    FROM users u
    LEFT JOIN pendaki_detail p ON u.user_id = p.user_id
    WHERE u.user_id = ?
");
$stmt_profile->bind_param("i", $user_id);
$stmt_profile->execute();
$profile_data = $stmt_profile->get_result()->fetch_assoc();
$stmt_profile->close();

// Statistik
$total_transaksi = $transaksi_sukses = $transaksi_pending = $transaksi_batal = 0;

try {
    $querys = [
        'total' => "SELECT COUNT(*) as total FROM pesanan WHERE user_id = ?",
        'sukses' => "SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? 
                     AND status_pesanan IN ('lunas', 'terkonfirmasi', 'selesai', 'berhasil')",
        'pending' => "SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? 
                      AND status_pesanan IN ('menunggu_pembayaran', 'menunggu_konfirmasi')",
        'batal' => "SELECT COUNT(*) as total FROM pesanan WHERE user_id = ? 
                    AND status_pesanan IN ('batal', 'dibatalkan', 'gagal')"
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
    overflow-y:auto;
}

.sidebar > div:first-child{
    flex:1;
}

.sidebar > a:last-child{
    margin-top:auto;
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
    flex-shrink:0;
}
.user-avatar img{width:100%;height:100%;object-fit:cover}
.user-info h3{font-size:15px;margin-bottom:5px}
.user-status{font-size:12px;color:#FFD700;display:flex;align-items:center}
.status-indicator{width:8px;height:8px;background:#FFD700;border-radius:50%;margin-right:6px;animation:pulse 2s infinite}

/* NAVIGATION */
.sidebar-nav{
    margin-top:15px;
    display:flex;
    flex-direction:column;
    gap:5px;
}
.sidebar-actions{
    display:flex;
    flex-direction:column;
    gap:8px;
    border-top:1px solid rgba(255,255,255,0.15);
    padding-top:15px;
}
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

/* PROFILE CARD */
.profile-card{
    display:grid;
    grid-template-columns:200px 1fr;
    gap:25px;
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
    margin-bottom:25px;
}
.profile-card-photo{
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#2e7d32,#1b5e20);
    border-radius:12px;
    min-height:180px;
    color:#fff;
    font-size:60px;
    font-weight:bold;
}
.profile-card-photo img{
    width:100%;
    height:100%;
    object-fit:cover;
    border-radius:12px;
}
.profile-info h3{
    color:#2e7d32;
    font-size:24px;
    margin-bottom:5px;
}
.profile-info p{
    color:#666;
    font-size:14px;
    margin:8px 0;
}
.profile-info .label{
    color:#999;
    font-size:12px;
    text-transform:uppercase;
    font-weight:600;
    margin-top:12px;
}
.profile-info a{
    display:inline-block;
    margin-top:15px;
    padding:8px 16px;
    background:#2e7d32;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    font-size:13px;
    font-weight:600;
    transition:.3s;
}
.profile-info a:hover{background:#1b5e20}

/* PROFILE MODAL */
.profile-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}
.profile-modal.show {
    display: flex;
    animation: fadeIn 0.3s ease;
}
.profile-modal-content {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 450px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}
.profile-modal-header {
    background: linear-gradient(135deg, #2e7d32, #1b5e20);
    color: white;
    padding: 25px;
    text-align: center;
    border-radius: 15px 15px 0 0;
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.profile-modal-title {
    flex: 1;
    font-size: 20px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}
.profile-modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 32px;
    cursor: pointer;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.3s;
    padding: 0;
}
.profile-modal-close:hover {
    opacity: 0.8;
}
.profile-modal-body {
    padding: 30px;
    text-align: center;
}
.profile-modal-photo {
    width: 120px;
    height: 120px;
    margin: 0 auto 20px;
    border-radius: 15px;
    background: #43a047;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 4px solid #2e7d32;
    font-size: 50px;
    font-weight: bold;
    color: white;
}
.profile-modal-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile-modal-name {
    font-size: 22px;
    font-weight: 700;
    color: #2e7d32;
    margin-bottom: 15px;
}
.profile-modal-contact {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}
.profile-modal-contact-item {
    font-size: 13px;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.profile-modal-contact-item.email::before {
    content: '📧';
}
.profile-modal-contact-item.phone::before {
    content: '📱';
}
.profile-modal-info {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 15px;
    text-align: left;
}
.profile-modal-info-item {
    padding: 12px !important;
    background: #f5faf5 !important;
    border-radius: 8px !important;
}
.profile-modal-info-label {
    font-size: 11px !important;
    color: #2e7d32 !important;
    text-transform: uppercase !important;
    font-weight: 700 !important;
    margin-bottom: 5px !important;
}
.profile-modal-info-value {
    font-size: 13px !important;
    color: #333 !important;
    font-weight: 600 !important;
    word-break: break-word !important;
}
.profile-modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
.profile-modal-btn {
    flex: 1;
    padding: 11px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.profile-modal-btn-detail {
    background: #2e7d32;
    color: white;
}
.profile-modal-btn-detail:hover {
    background: #1b5e20;
}
.profile-modal-btn-edit {
    background: #f5faf5;
    color: #2e7d32;
    border: 2px solid #e8f5e9;
}
.profile-modal-btn-edit:hover {
    background: #e8f5e9;
}

/* RESPONSIVE */
@media(max-width:900px){
    .sidebar{
        position:relative;
        width:100%;
        height:auto;
        flex-direction:row;
        padding:15px;
        gap:15px;
    }
    .sidebar > div:first-child{
        display:flex;
        gap:15px;
        width:100%;
    }
    .sidebar-header{
        padding:0;
        border-bottom:none;
    }
    .sidebar-nav{
        gap:0;
        flex-direction:row;
        flex-wrap:wrap;
    }
    .nav-item{
        padding:10px 15px;
        font-size:14px;
    }
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
            <div class="sidebar-header" onclick="openProfileModal();" style="cursor:pointer;">
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
                <a href="profil_pribadi.php" class="nav-item">👤 Profil Pribadi</a>
                <a href="edit_profil.php" class="nav-item">✏️ Edit Profil</a>
                <a href="booking.php" class="nav-item">📅 Booking</a>
            </nav>
        </div>
        <div class="sidebar-actions">
            <a href="../index.php" class="nav-item" style="background:#2e7d32;border-left:4px solid #FFD700">🏠 Kembali ke Utama</a>
            <a href="../backend/logout.php" class="nav-item" style="background:#e53935;border-left:4px solid #FFD700">🚪 Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="top-bar">
            <div>
                <h1>Dashboard User</h1>
                <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>
            </div>
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
        </div>
    </main>
</div>

<!-- PROFILE MODAL -->
<div id="profileModal" class="profile-modal" onclick="closeProfileModalOnBg(event)">
    <div class="profile-modal-content" onclick="event.stopPropagation()">
        <div class="profile-modal-header">
            <div class="profile-modal-title">👤 Profil Saya</div>
            <button class="profile-modal-close" onclick="closeProfileModal()">&times;</button>
        </div>
        <div class="profile-modal-body">
            <!-- Foto Profil -->
            <div class="profile-modal-photo" id="modalPhotoContainer">
                <?php echo strtoupper(substr($profile_data['nama'] ?? '', 0, 1)); ?>
            </div>

            <!-- Nama -->
            <div class="profile-modal-name"><?php echo htmlspecialchars($profile_data['nama'] ?? ''); ?></div>

            <!-- Kontak -->
            <div class="profile-modal-contact">
                <div class="profile-modal-contact-item email"><?php echo htmlspecialchars($profile_data['email'] ?? '-'); ?></div>
                <div class="profile-modal-contact-item phone"><?php echo htmlspecialchars($profile_data['no_hp'] ?? '-'); ?></div>
            </div>

            <!-- Informasi Grid -->
            <div class="profile-modal-info">
                <?php if (!empty($profile_data['nik'])): ?>
                <div class="profile-modal-info-item">
                    <div class="profile-modal-info-label">NIK</div>
                    <div class="profile-modal-info-value"><?php echo htmlspecialchars($profile_data['nik']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profile_data['tempat_lahir'])): ?>
                <div class="profile-modal-info-item">
                    <div class="profile-modal-info-label">Tempat Lahir</div>
                    <div class="profile-modal-info-value"><?php echo htmlspecialchars($profile_data['tempat_lahir']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profile_data['tanggal_lahir'])): ?>
                <div class="profile-modal-info-item">
                    <div class="profile-modal-info-label">Tgl Lahir</div>
                    <div class="profile-modal-info-value"><?php echo date('d-m-Y', strtotime($profile_data['tanggal_lahir'])); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profile_data['jenis_kelamin'])): ?>
                <div class="profile-modal-info-item">
                    <div class="profile-modal-info-label">Jenis Kelamin</div>
                    <div class="profile-modal-info-value"><?php echo htmlspecialchars($profile_data['jenis_kelamin']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profile_data['alamat'])): ?>
                <div class="profile-modal-info-item">
                    <div class="profile-modal-info-label">Alamat</div>
                    <div class="profile-modal-info-value"><?php echo htmlspecialchars($profile_data['alamat']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profile_data['kabupaten'])): ?>
                <div class="profile-modal-info-item">
                    <div class="profile-modal-info-label">Kabupaten</div>
                    <div class="profile-modal-info-value"><?php echo htmlspecialchars($profile_data['kabupaten']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profile_data['provinsi'])): ?>
                <div class="profile-modal-info-item">
                    <div class="profile-modal-info-label">Provinsi</div>
                    <div class="profile-modal-info-value"><?php echo htmlspecialchars($profile_data['provinsi']); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="profile-modal-actions">
                <button class="profile-modal-btn profile-modal-btn-detail" onclick="goToDetailProfile()">👁 Lihat Detail</button>
                <button class="profile-modal-btn profile-modal-btn-edit" onclick="goToEditProfile()">✏️ Edit</button>
            </div>
        </div>
    </div>
</div>

<script>
// Ambil foto profil dari PHP
const fotoProfilPath = '<?php echo !empty($profile_data['foto_profil']) && file_exists("../uploads/profil/" . $profile_data['foto_profil']) ? htmlspecialchars($profile_data['foto_profil']) : ''; ?>';

// Fungsi untuk membuka modal profil
function openProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.classList.add('show');
    
    // Set foto profil jika ada
    const photoContainer = document.getElementById('modalPhotoContainer');
    if (fotoProfilPath) {
        photoContainer.innerHTML = '<img src="../uploads/profil/' + fotoProfilPath + '" alt="Profil">';
    }
}

// Fungsi untuk menutup modal profil
function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.classList.remove('show');
}

// Fungsi untuk menutup modal saat klik background
function closeProfileModalOnBg(event) {
    if (event.target.id === 'profileModal') {
        closeProfileModal();
    }
}

// Fungsi untuk pergi ke halaman detail profil
function goToDetailProfile() {
    window.location.href = 'profil_pribadi.php';
}

// Fungsi untuk pergi ke halaman edit profil
function goToEditProfile() {
    window.location.href = 'edit_profil.php';
}

// Tutup modal saat tekan tombol ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeProfileModal();
    }
});
</script>

</body>
</html>
