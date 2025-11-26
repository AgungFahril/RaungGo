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

$user_id = $_SESSION['user_id'];

// Ambil foto profil
$stmt = $conn->prepare("SELECT foto_profil FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_foto = $res->fetch_assoc();
$foto_profil = $user_foto['foto_profil'] ?? null;
$stmt->close();

// Ambil data profil lengkap
$stmt = $conn->prepare("SELECT u.nama, u.email, u.foto_profil, p.nik, p.no_hp, p.alamat, p.kabupaten, p.provinsi, p.kecamatan, p.kelurahan, p.tempat_lahir, p.tanggal_lahir, p.jenis_kelamin, p.kewarganegaraan, p.no_darurat, p.hubungan_darurat FROM users u LEFT JOIN pendaki_detail p ON u.user_id = p.user_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
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
    <style>
        /* CSS Updated: 2025-11-24 - Grid Layout Modal */
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        /* ANIMATIONS */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: none; } }
        @keyframes slideInLeft { from { transform: translateX(-50px); opacity: 0; } to { transform: none; opacity: 1; } }

        /* BODY */
        body {
            background: linear-gradient(135deg, #f5faf5, #e8f5e9);
            overflow-x: hidden;
            animation: fadeIn 0.8s ease;
            min-height: 100vh;
        }

        /* LAYOUT */
        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #2e7d32, #1b5e20);
            color: #fff;
            padding: 35px 0;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            position: fixed;
            height: 100vh;
            animation: slideInLeft 0.8s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 0 25px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: 0.3s;
        }

        .sidebar-header:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: #43a047;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            margin-right: 15px;
            color: #fff;
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info h3 {
            margin-bottom: 5px;
            font-size: 16px;
        }

        .user-status {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            background: #4caf50;
            border-radius: 50%;
        }

        .sidebar-nav {
            padding: 25px 0;
        }

        .nav-item {
            display: block;
            padding: 15px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-left-color: #81c784;
        }

        .main-content {
            margin-left: 270px;
            flex: 1;
            padding: 40px;
        }

        /* HEADER */
        .page-header {
            margin-bottom: 40px;
            animation: fadeIn 0.8s ease;
        }

        .page-header h1 {
            font-size: 28px;
            color: #1b5e20;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 15px;
        }

        /* PROFILE SECTION */
        .profile-section {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
            animation: fadeIn 0.8s ease 0.2s both;
        }

        .profile-header {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            padding: 40px;
            color: #fff;
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 30px;
            align-items: center;
        }

        .profile-photo {
            width: 200px;
            height: 200px;
            border-radius: 16px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            font-weight: bold;
            color: #2e7d32;
            overflow: hidden;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-header-info h1 {
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .profile-header-info p {
            font-size: 16px;
            opacity: 0.9;
            margin: 5px 0;
        }

        .profile-body {
            padding: 40px;
        }

        .info-section {
            margin-bottom: 40px;
        }

        .info-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #e8f5e9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            background: #f5faf5;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2e7d32;
            transition: 0.3s;
        }

        .info-item:hover {
            background: #eff7ef;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.1);
        }

        .info-label {
            font-size: 12px;
            font-weight: 700;
            color: #1b5e20;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }

        .info-value.empty {
            color: #999;
            font-style: italic;
        }

        .profile-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e8f5e9;
        }

        .btn-action {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: #fff;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }

        .btn-back {
            background: #e8e8e8;
            color: #333;
        }

        .btn-back:hover {
            background: #d8d8d8;
            transform: translateY(-2px);
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 25px;
            }

            .profile-header {
                grid-template-columns: 150px 1fr;
                gap: 20px;
                padding: 30px;
            }

            .profile-photo {
                width: 150px;
                height: 150px;
                font-size: 60px;
            }

            .profile-header-info h1 {
                font-size: 24px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .profile-body {
                padding: 25px;
            }
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideInLeft 0.3s ease;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            padding: 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 20px;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .modal-close:hover {
            transform: scale(1.2);
        }

        .modal-body {
            padding: 30px;
            text-align: center;
        }

        .modal-profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 15px;
            background: #43a047;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            font-weight: bold;
            color: white;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 4px solid #2e7d32;
        }

        .modal-profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-info {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-info h3 {
            font-size: 22px;
            color: #2e7d32;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .modal-info p {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .modal-info-detail {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 12px;
            margin-bottom: 20px;
        }

        .modal-info-item {
            background: #f5faf5 !important;
            padding: 12px !important;
            border-radius: 8px !important;
            text-align: left !important;
            display: block !important;
        }

        .modal-info-label {
            color: #2e7d32 !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
            margin-bottom: 5px !important;
            display: block !important;
        }

        .modal-info-value {
            color: #333 !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            word-break: break-word !important;
            display: block !important;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .modal-actions a {
            flex: 1;
            padding: 11px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .modal-actions a.btn-view {
            background: #2e7d32;
            color: white;
        }

        .modal-actions a.btn-view:hover {
            background: #1b5e20;
        }

        .modal-actions a.btn-edit {
            background: #f5faf5;
            color: #2e7d32;
            border: 2px solid #e8f5e9;
        }

        .modal-actions a.btn-edit:hover {
            background: #d8d8d8;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div>
                <div class="sidebar-header" onclick="openProfileModal()" style="cursor: pointer;">
                    <div class="user-avatar">
                        <?php if (!empty($foto_profil) && file_exists("../uploads/profil/$foto_profil")): ?>
                            <img src="../uploads/profil/<?php echo htmlspecialchars($foto_profil); ?>" alt="Profil">
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
                    <a href="dashboard.php" class="nav-item">🏠 Dashboard</a>
                    <a href="profil_pribadi.php" class="nav-item active">👤 Profil Pribadi</a>
                    <a href="edit_profil.php" class="nav-item">✏️ Edit Profil</a>
                    <a href="booking.php" class="nav-item">📅 Booking</a>
                    <a href="detail_transaksi.php" class="nav-item">📊 Transaksi</a>
                </nav>
            </div>
            <a href="../backend/logout.php" class="nav-item" style="border-top: 1px solid rgba(255, 255, 255, 0.15); background: #e53935;">🚪 Logout</a>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="page-header">
                <h1>📋 Profil Pribadi</h1>
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
                        <p>📧 <?php echo htmlspecialchars($profil['email'] ?? '-'); ?></p>
                        <p>📱 <?php echo htmlspecialchars($profil['no_hp'] ?? 'Belum diisi'); ?></p>
                    </div>
                </div>

                <!-- Profile Body -->
                <div class="profile-body">
                    <!-- Umum -->
                    <div class="info-section">
                        <div class="section-title">📋 Informasi Umum</div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">👤 NIK</div>
                                <div class="info-value <?php echo empty($profil['nik']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['nik'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">👶 Tempat Lahir</div>
                                <div class="info-value <?php echo empty($profil['tempat_lahir']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['tempat_lahir'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">📅 Tanggal Lahir</div>
                                <div class="info-value <?php echo empty($profil['tanggal_lahir']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['tanggal_lahir'] ? date('d-m-Y', strtotime($profil['tanggal_lahir'])) : 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">⚧️ Jenis Kelamin</div>
                                <div class="info-value <?php echo empty($profil['jenis_kelamin']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['jenis_kelamin'] == 'L' ? 'Laki-laki' : ($profil['jenis_kelamin'] == 'P' ? 'Perempuan' : 'Belum diisi')); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🌍 Kewarganegaraan</div>
                                <div class="info-value <?php echo empty($profil['kewarganegaraan']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['kewarganegaraan'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="info-section">
                        <div class="section-title">🏠 Alamat</div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">🏘️ Alamat</div>
                                <div class="info-value <?php echo empty($profil['alamat']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['alamat'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🚸 Kelurahan</div>
                                <div class="info-value <?php echo empty($profil['kelurahan']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['kelurahan'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">📍 Kecamatan</div>
                                <div class="info-value <?php echo empty($profil['kecamatan']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['kecamatan'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🏙️ Kabupaten</div>
                                <div class="info-value <?php echo empty($profil['kabupaten']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['kabupaten'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🗺️ Provinsi</div>
                                <div class="info-value <?php echo empty($profil['provinsi']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['provinsi'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kontak & Darurat -->
                    <div class="info-section">
                        <div class="section-title">📞 Kontak & Darurat</div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">📱 No. HP</div>
                                <div class="info-value <?php echo empty($profil['no_hp']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['no_hp'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🚨 No. Darurat</div>
                                <div class="info-value <?php echo empty($profil['no_darurat']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['no_darurat'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">👨‍👩‍👧‍👦 Hubungan Darurat</div>
                                <div class="info-value <?php echo empty($profil['hubungan_darurat']) ? 'empty' : ''; ?>">
                                    <?php echo htmlspecialchars($profil['hubungan_darurat'] ?? 'Belum diisi'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="profile-actions">
                        <a href="edit_profil.php" class="btn-action btn-edit">✏️ Edit Profil</a>
                        <a href="dashboard.php" class="btn-action btn-back">🏠 Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- PROFILE MODAL -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>👤 Profil Saya</h2>
                <button class="modal-close" onclick="closeProfileModal()">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Foto Profil -->
                <div class="modal-profile-photo">
                    <?php if (!empty($profil['foto_profil']) && file_exists("../uploads/profil/" . $profil['foto_profil'])): ?>
                        <img src="../uploads/profil/<?php echo htmlspecialchars($profil['foto_profil']); ?>" alt="Profil">
                    <?php else: ?>
                        <?php echo strtoupper(substr($profil['nama'] ?? 'U', 0, 1)); ?>
                    <?php endif; ?>
                </div>

                <!-- Nama -->
                <div class="modal-info">
                    <h3><?php echo htmlspecialchars($profil['nama'] ?? 'User'); ?></h3>
                    <p>📧 <?php echo htmlspecialchars($profil['email'] ?? '-'); ?></p>
                    <p>📱 <?php echo htmlspecialchars($profil['no_hp'] ?? '-'); ?></p>
                </div>

                <!-- Info Grid -->
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
                        <div class="modal-info-value"><?php echo htmlspecialchars($profil['jenis_kelamin']); ?></div>
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

                <!-- Action Buttons -->
                <div class="modal-actions">
                    <a href="profil_pribadi.php" class="btn-view">👁 Lihat Detail</a>
                    <a href="edit_profil.php" class="btn-edit">✏️ Edit</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openProfileModal() {
            document.getElementById('profileModal').classList.add('show');
        }

        function closeProfileModal() {
            document.getElementById('profileModal').classList.remove('show');
        }

        // Close modal when clicking outside
        document.getElementById('profileModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfileModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProfileModal();
            }
        });
    </script>
</body>
</html>