<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pendaki') {
    header("Location: " . (!isset($_SESSION['user_id']) ? "../login.html" : "../admin/dashboard.php"));
    exit();
}
include '../backend/koneksi.php';
$stmt = $conn->prepare("SELECT u.nama, u.email, u.foto_profil, p.nik, p.no_hp, p.alamat, p.kabupaten, p.provinsi, p.kecamatan, p.kelurahan, p.tempat_lahir, p.tanggal_lahir, p.jenis_kelamin, p.kewarganegaraan, p.no_darurat, p.hubungan_darurat FROM users u LEFT JOIN pendaki_detail p ON u.user_id = p.user_id WHERE u.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$profil = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pribadi - Pendakian Gunung Raung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}
@keyframes fadeIn{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:none}}
@keyframes slideIn{from{transform:translateX(-50px);opacity:0}to{transform:none;opacity:1}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.6}}

body{background:linear-gradient(135deg,#f5faf5,#e8f5e9);overflow-x:hidden;animation:fadeIn .8s ease;min-height:100vh}

.container{display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{width:270px;background:linear-gradient(180deg,#2e7d32,#1b5e20);color:#fff;padding:35px 0;box-shadow:4px 0 20px rgba(0,0,0,0.2);position:fixed;height:100vh;animation:slideIn .8s ease;display:flex;flex-direction:column;overflow-y:auto;z-index:100}
.sidebar > div:first-child{flex:1}

.sidebar-header{display:flex;align-items:center;padding:0 25px 30px;border-bottom:1px solid rgba(255,255,255,0.1);cursor:pointer;transition:.3s}
.sidebar-header:hover{background:rgba(255,255,255,0.05)}

.user-avatar{width:65px;height:65px;border-radius:50%;background:#43a047;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:#fff;margin-right:15px;overflow:hidden;border:3px solid rgba(255,255,255,0.2);flex-shrink:0}
.user-avatar img{width:100%;height:100%;object-fit:cover}

.user-info h3{font-size:15px;margin-bottom:5px;font-weight:600}
.user-status{font-size:12px;color:#FFD700;display:flex;align-items:center;gap:6px}
.status-indicator{width:8px;height:8px;background:#FFD700;border-radius:50%;animation:pulse 2s infinite}

.sidebar-nav{margin-top:15px;display:flex;flex-direction:column;gap:5px}
.sidebar-actions{display:flex;flex-direction:column;gap:8px;border-top:1px solid rgba(255,255,255,0.15);padding:15px 0;margin-top:auto}

.nav-item{display:block;padding:13px 28px;color:rgba(255,255,255,0.85);text-decoration:none;font-weight:500;border-left:4px solid transparent;transition:all .3s}
.nav-item i{margin-right:10px;width:18px;text-align:center}
.nav-item:hover,.nav-item.active{background:rgba(255,255,255,0.1);border-left-color:#FFD700;color:#fff}

/* MAIN CONTENT */
.main-content{margin-left:270px;flex:1;padding:50px;animation:fadeIn .8s ease}

.page-header{margin-bottom:30px}
.page-header h1{font-size:28px;color:#1b5e20;margin-bottom:8px;font-weight:700}
.page-header p{color:#666;font-size:14px}

/* PROFILE SECTION */
.profile-section{background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.08);overflow:hidden;margin-bottom:30px}

.profile-header{background:linear-gradient(135deg,#2e7d32,#1b5e20);padding:40px;color:#fff;display:grid;grid-template-columns:200px 1fr;gap:30px;align-items:center}

.profile-photo{width:200px;height:200px;border-radius:16px;background:#fff;display:flex;align-items:center;justify-content:center;font-size:80px;font-weight:700;color:#2e7d32;overflow:hidden;border:4px solid rgba(255,255,255,0.3)}
.profile-photo img{width:100%;height:100%;object-fit:cover}

.profile-header-info h1{font-size:32px;margin-bottom:10px;font-weight:700}
.profile-header-info p{font-size:15px;opacity:0.95;margin:6px 0;display:flex;align-items:center;gap:8px}
.profile-header-info p i{font-size:14px}

/* PROFILE BODY */
.profile-body{padding:40px}

.info-section{margin-bottom:35px}
.info-section:last-child{margin-bottom:0}

.section-title{font-size:18px;font-weight:700;color:#2e7d32;margin-bottom:20px;padding-bottom:12px;border-bottom:3px solid #e8f5e9;display:flex;align-items:center;gap:10px}
.section-title i{font-size:20px}

.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}

.info-item{background:#f5faf5;padding:18px;border-radius:10px;border-left:4px solid #2e7d32;transition:all .3s}
.info-item:hover{background:#eff7ef;transform:translateY(-2px);box-shadow:0 4px 12px rgba(46,125,50,0.12)}

.info-label{font-size:11px;font-weight:700;color:#1b5e20;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px}
.info-value{font-size:15px;color:#333;font-weight:500}
.info-value.empty{color:#999;font-style:italic}

/* PROFILE ACTIONS */
.profile-actions{display:flex;gap:15px;margin-top:30px;padding-top:25px;border-top:2px solid #e8f5e9;flex-wrap:wrap}

.btn-action{padding:14px 24px;border-radius:10px;font-size:14px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all .3s;border:none;cursor:pointer}

.btn-edit{background:linear-gradient(135deg,#2e7d32,#1b5e20);color:#fff}
.btn-edit:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(46,125,50,0.3)}

.btn-back{background:#e8e8e8;color:#333}
.btn-back:hover{background:#d8d8d8;transform:translateY(-2px)}

/* MODAL */
.modal{display:none;position:fixed;z-index:10000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center}
.modal.show{display:flex;animation:fadeIn .3s ease}

.modal-content{background:#fff;border-radius:16px;width:90%;max-width:450px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 40px rgba(0,0,0,0.3)}

.modal-header{background:linear-gradient(135deg,#2e7d32,#1b5e20);padding:25px;color:#fff;display:flex;justify-content:space-between;align-items:center}
.modal-header h2{font-size:20px;font-weight:700;display:flex;align-items:center;gap:10px}

.modal-close{background:none;border:none;color:#fff;font-size:32px;cursor:pointer;padding:0;width:40px;height:40px;display:flex;align-items:center;justify-content:center;transition:.3s}
.modal-close:hover{transform:scale(1.1)}

.modal-body{padding:30px;text-align:center}

.modal-profile-photo{width:120px;height:120px;border-radius:15px;background:#43a047;display:flex;align-items:center;justify-content:center;font-size:50px;font-weight:700;color:#fff;margin:0 auto 20px;overflow:hidden;border:4px solid #2e7d32}
.modal-profile-photo img{width:100%;height:100%;object-fit:cover}

.modal-info{margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #eee}
.modal-info h3{font-size:22px;color:#2e7d32;font-weight:700;margin-bottom:10px}
.modal-info p{color:#666;font-size:13px;margin:5px 0;display:flex;align-items:center;justify-content:center;gap:6px}

.modal-info-detail{display:grid;grid-template-columns:1fr 1fr;gap:12px;text-align:left}
.modal-info-item{background:#f5faf5;padding:12px;border-radius:8px}
.modal-info-label{color:#2e7d32;font-weight:700;font-size:11px;text-transform:uppercase;margin-bottom:5px;display:block}
.modal-info-value{color:#333;font-weight:600;font-size:13px;word-break:break-word;display:block}

.modal-actions{display:flex;gap:12px;padding-top:15px;border-top:1px solid #eee}
.modal-actions a{flex:1;padding:12px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;transition:.3s;display:flex;align-items:center;justify-content:center;gap:6px}
.modal-actions a.btn-view{background:#2e7d32;color:#fff}
.modal-actions a.btn-view:hover{background:#1b5e20}
.modal-actions a.btn-edit{background:#f5faf5;color:#2e7d32;border:2px solid #e8f5e9}
.modal-actions a.btn-edit:hover{background:#e8f5e9}

/* HAMBURGER & OVERLAY - HIDDEN ON DESKTOP */
.mobile-menu-btn,
.sidebar-overlay{
    display:none;
}

/* === TABLET === */
@media(max-width:900px) and (min-width:769px){
    .sidebar{width:220px}
    .main-content{margin-left:220px;padding:35px}
    .profile-header{grid-template-columns:150px 1fr;gap:20px;padding:30px}
    .profile-photo{width:150px;height:150px;font-size:60px}
    .profile-header-info h1{font-size:26px}
    .info-grid{grid-template-columns:repeat(2,1fr)}
}

/* === MOBILE === */
@media(max-width:768px){
    .container{flex-direction:column}
    
    /* Sidebar Mobile */
    .sidebar{
        position:fixed;
        top:0;
        left:-100%;
        width:280px;
        max-width:85vw;
        height:100vh;
        z-index:9999;
        transition:left 0.4s ease;
        padding:20px 0;
    }
    
    .sidebar.show{left:0}
    
    .sidebar-header{padding:0 20px 20px}
    .user-avatar{width:60px;height:60px;font-size:24px}
    .sidebar-nav{margin-top:15px;gap:5px}
    .nav-item{padding:12px 20px;font-size:14px}
    .sidebar-actions{gap:8px;padding:15px 0}
    
    /* Mobile Menu Button */
    .mobile-menu-btn{
        display:flex;
        position:fixed;
        top:15px;
        left:15px;
        width:50px;
        height:50px;
        background:#2e7d32;
        color:#fff;
        border:none;
        border-radius:12px;
        font-size:20px;
        cursor:pointer;
        z-index:9998;
        align-items:center;
        justify-content:center;
        box-shadow:0 4px 12px rgba(0,0,0,0.3);
        transition:all 0.3s;
    }
    
    .mobile-menu-btn:active{transform:scale(0.95)}
    
    /* Overlay */
    .sidebar-overlay{
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100vw;
        height:100vh;
        background:rgba(0,0,0,0.6);
        z-index:9998;
    }
    
    .sidebar-overlay.show{display:block}
    
    /* Main Content */
    .main-content{
        margin-left:0;
        padding:80px 20px 30px 20px;
        width:100%;
    }
    
    .page-header{margin-bottom:20px}
    .page-header h1{font-size:22px}
    .page-header p{font-size:13px}
    
    /* Profile Header */
    .profile-header{
        grid-template-columns:1fr;
        gap:20px;
        padding:25px;
        text-align:center;
    }
    
    .profile-photo{
        width:140px;
        height:140px;
        font-size:60px;
        margin:0 auto;
    }
    
    .profile-header-info h1{font-size:22px}
    .profile-header-info p{font-size:14px;justify-content:center}
    
    /* Profile Body */
    .profile-body{padding:20px}
    
    .section-title{font-size:16px}
    
    .info-grid{
        grid-template-columns:1fr;
        gap:15px;
    }
    
    .info-item{padding:16px}
    .info-label{font-size:10px}
    .info-value{font-size:14px}
    
    /* Profile Actions */
    .profile-actions{
        flex-direction:column;
        gap:12px;
    }
    
    .btn-action{
        width:100%;
        justify-content:center;
        padding:14px 20px;
    }
    
    /* Modal */
    .modal-content{width:95%;max-width:380px}
    .modal-header{padding:20px}
    .modal-header h2{font-size:18px}
    .modal-body{padding:20px}
    .modal-profile-photo{width:100px;height:100px;font-size:42px}
    .modal-info h3{font-size:20px}
    .modal-info-detail{grid-template-columns:1fr}
    .modal-actions{flex-direction:column}
}

/* === SMALL MOBILE === */
@media(max-width:375px){
    .main-content{padding:70px 15px 25px 15px}
    .profile-header{padding:20px}
    .profile-photo{width:120px;height:120px;font-size:50px}
    .profile-header-info h1{font-size:20px}
    .profile-body{padding:16px}
    .info-item{padding:14px}
}
</style>
</head>
<body>

<!-- Mobile Menu Button -->
<button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleMobileSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

<div class="container">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="mobileSidebar">
        <div>
            <div class="sidebar-header" onclick="openProfileModal()">
                <div class="user-avatar">
                    <?php if (!empty($profil['foto_profil']) && file_exists("../uploads/profil/" . $profil['foto_profil'])): ?>
                        <img src="../uploads/profil/<?php echo htmlspecialchars($profil['foto_profil']); ?>" alt="Profil">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['nama']); ?></h3>
                    <div class="user-status"><span class="status-indicator"></span> Online</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="profil_pribadi.php" class="nav-item active"><i class="fas fa-user"></i> Profil Pribadi</a>
                <a href="edit_profil.php" class="nav-item"><i class="fas fa-edit"></i> Edit Profil</a>
                <a href="booking.php" class="nav-item"><i class="fas fa-calendar-alt"></i> Booking</a>
            </nav>
        </div>
        <div class="sidebar-actions">
            <a href="../index.php" class="nav-item" style="background:#2e7d32;border-left:4px solid #FFD700"><i class="fas fa-home"></i> Kembali ke Utama</a>
            <a href="../backend/logout.php" class="nav-item" style="background:#e53935;border-left:4px solid #FFD700"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-user-circle"></i> Profil Pribadi</h1>
            <p>Lihat dan kelola data pribadi Anda</p>
        </div>

        <!-- PROFILE SECTION -->
        <div class="profile-section">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-photo">
                    <?php if (!empty($profil['foto_profil']) && file_exists("../uploads/profil/" . $profil['foto_profil'])): ?>
                        <img src="../uploads/profil/<?php echo htmlspecialchars($profil['foto_profil']); ?>" alt="Profil">
                    <?php else: ?>
                        <?php echo strtoupper(substr($profil['nama'] ?? 'U', 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="profile-header-info">
                    <h1><?php echo htmlspecialchars($profil['nama'] ?? 'User'); ?></h1>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($profil['email'] ?? '-'); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($profil['no_hp'] ?? 'Belum diisi'); ?></p>
                </div>
            </div>

            <!-- Profile Body -->
            <div class="profile-body">
                <div class="info-section">
                    <div class="section-title"><i class="fas fa-clipboard-list"></i> Informasi Umum</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">NIK</div>
                            <div class="info-value <?php echo empty($profil['nik']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['nik'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tempat Lahir</div>
                            <div class="info-value <?php echo empty($profil['tempat_lahir']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['tempat_lahir'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tanggal Lahir</div>
                            <div class="info-value <?php echo empty($profil['tanggal_lahir']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['tanggal_lahir'] ? date('d-m-Y', strtotime($profil['tanggal_lahir'])) : 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Jenis Kelamin</div>
                            <div class="info-value <?php echo empty($profil['jenis_kelamin']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['jenis_kelamin'] == 'L' ? 'Laki-laki' : ($profil['jenis_kelamin'] == 'P' ? 'Perempuan' : 'Belum diisi')); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Kewarganegaraan</div>
                            <div class="info-value <?php echo empty($profil['kewarganegaraan']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['kewarganegaraan'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <div class="section-title"><i class="fas fa-map-marker-alt"></i> Alamat</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Alamat</div>
                            <div class="info-value <?php echo empty($profil['alamat']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['alamat'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Kelurahan</div>
                            <div class="info-value <?php echo empty($profil['kelurahan']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['kelurahan'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Kecamatan</div>
                            <div class="info-value <?php echo empty($profil['kecamatan']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['kecamatan'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Kabupaten</div>
                            <div class="info-value <?php echo empty($profil['kabupaten']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['kabupaten'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Provinsi</div>
                            <div class="info-value <?php echo empty($profil['provinsi']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['provinsi'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <div class="section-title"><i class="fas fa-phone-alt"></i> Kontak & Darurat</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">No. HP</div>
                            <div class="info-value <?php echo empty($profil['no_hp']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['no_hp'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">No. Darurat</div>
                            <div class="info-value <?php echo empty($profil['no_darurat']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['no_darurat'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Hubungan Darurat</div>
                            <div class="info-value <?php echo empty($profil['hubungan_darurat']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil['hubungan_darurat'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="edit_profil.php" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit Profil</a>
                    <a href="dashboard.php" class="btn-action btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- PROFILE MODAL -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-circle"></i> Profil Saya</h2>
            <button class="modal-close" onclick="closeProfileModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-profile-photo">
                <?php if (!empty($profil['foto_profil']) && file_exists("../uploads/profil/" . $profil['foto_profil'])): ?>
                    <img src="../uploads/profil/<?php echo htmlspecialchars($profil['foto_profil']); ?>" alt="Profil">
                <?php else: ?>
                    <?php echo strtoupper(substr($profil['nama'] ?? 'U', 0, 1)); ?>
                <?php endif; ?>
            </div>

            <div class="modal-info">
                <h3><?php echo htmlspecialchars($profil['nama'] ?? 'User'); ?></h3>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($profil['email'] ?? '-'); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($profil['no_hp'] ?? '-'); ?></p>
            </div>

            <div class="modal-info-detail">
                <?php if (!empty($profil['nik'])): ?>
                <div class="modal-info-item">
                    <div class="modal-info-label">NIK</div>
                    <div class="modal-info-value"><?php echo htmlspecialchars($profil['nik']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profil['tempat_lahir'])): ?>
                <div class="modal-info-item">
                    <div class="modal-info-label">Tempat Lahir</div>
                    <div class="modal-info-value"><?php echo htmlspecialchars($profil['tempat_lahir']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profil['tanggal_lahir'])): ?>
                <div class="modal-info-item">
                    <div class="modal-info-label">Tgl Lahir</div>
                    <div class="modal-info-value"><?php echo date('d-m-Y', strtotime($profil['tanggal_lahir'])); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profil['jenis_kelamin'])): ?>
                <div class="modal-info-item">
                    <div class="modal-info-label">Jenis Kelamin</div>
                    <div class="modal-info-value"><?php echo htmlspecialchars($profil['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profil['alamat'])): ?>
                <div class="modal-info-item">
                    <div class="modal-info-label">Alamat</div>
                    <div class="modal-info-value"><?php echo htmlspecialchars($profil['alamat']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profil['kabupaten'])): ?>
                <div class="modal-info-item">
                    <div class="modal-info-label">Kabupaten</div>
                    <div class="modal-info-value"><?php echo htmlspecialchars($profil['kabupaten']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profil['provinsi'])): ?>
                <div class="modal-info-item">
                    <div class="modal-info-label">Provinsi</div>
                    <div class="modal-info-value"><?php echo htmlspecialchars($profil['provinsi']); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="modal-actions">
                <a href="profil_pribadi.php" class="btn-view"><i class="fas fa-eye"></i> Lihat Detail</a>
                <a href="edit_profil.php" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
            </div>
        </div>
    </div>
</div>

<script>
// Profile Modal
const modal = document.getElementById('profileModal');

function openProfileModal() {
    modal.classList.add('show');
}

function closeProfileModal() {
    modal.classList.remove('show');
}

modal.addEventListener('click', e => {
    if (e.target === modal) closeProfileModal();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeProfileModal();
});

// Mobile Sidebar
function toggleMobileSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btn = document.getElementById('mobileMenuBtn');
    
    if (!sidebar || !overlay || !btn) return;
    
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
    
    const icon = btn.querySelector('i');
    if (sidebar.classList.contains('show')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
        document.body.style.overflow = 'hidden';
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
        document.body.style.overflow = 'auto';
    }
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btn = document.getElementById('mobileMenuBtn');
    
    if (sidebar) sidebar.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
    if (btn) {
        const icon = btn.querySelector('i');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
    document.body.style.overflow = 'auto';
}

document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            setTimeout(closeMobileSidebar, 200);
        }
    });
});

window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeMobileSidebar();
    }
});
</script>

</body>
</html>
