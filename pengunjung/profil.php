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

// Ambil data profil lengkap
$stmt_profil = $conn->prepare("
    SELECT u.user_id, u.nama, u.email, u.foto_profil, u.password, 
           p.nik, p.alamat, p.no_hp, p.no_darurat, p.hubungan_darurat, 
           p.provinsi, p.kabupaten, p.kecamatan, p.kelurahan, p.tempat_lahir, 
           p.tanggal_lahir, p.jenis_kelamin, p.kewarganegaraan
    FROM users u
    LEFT JOIN pendaki_detail p ON u.user_id = p.user_id
    WHERE u.user_id = ?
");
$stmt_profil->bind_param("i", $user_id);
$stmt_profil->execute();
$profil_data = $stmt_profil->get_result()->fetch_assoc();
$stmt_profil->close();

// Ambil statistik bookings
$stmt_booking = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ?");
$stmt_booking->bind_param("i", $user_id);
$stmt_booking->execute();
$booking_stats = $stmt_booking->get_result()->fetch_assoc();
$stmt_booking->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pribadi - Pendakian Gunung Raung</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: none; }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: none; }
        }

        body {
            background: linear-gradient(135deg, #f5faf5, #e8f5e9);
            min-height: 100vh;
            padding: 20px;
            animation: fadeIn 0.6s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .header h1 {
            color: #2e7d32;
            font-size: 28px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: white;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #1b5e20, #0d3d1a);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }

        .btn-back {
            background: #e8e8e8;
            color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-back:hover {
            background: #d8d8d8;
            transform: translateY(-2px);
        }

        /* MAIN CONTENT */
        .content {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        /* SIDEBAR */
        .sidebar {
            animation: slideInLeft 0.6s ease;
        }

        .profile-box {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-photo {
            margin-bottom: 20px;
        }

        .profile-photo img {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            border: 6px solid #2e7d32;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.3);
        }

        .profile-photo .avatar-fallback {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            color: white;
            font-weight: bold;
            border: 6px solid #2e7d32;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.3);
            margin: 0 auto;
        }

        .profile-name {
            font-size: 26px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .profile-email {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
            word-break: break-all;
        }

        .profile-status {
            background: #f0f7f0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #43a047;
        }

        .status-label {
            color: #2e7d32;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .status-value {
            color: #333;
            font-weight: 600;
            font-size: 18px;
        }

        /* MAIN INFO */
        .main-info {
            animation: fadeIn 0.6s ease 0.1s backwards;
        }

        .info-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid rgba(46, 125, 50, 0.1);
            padding-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-item {
            background: linear-gradient(135deg, #f8fbf7, #eef5eb);
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #2e7d32;
        }

        .info-label {
            color: #2e7d32;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-value {
            color: #333;
            font-weight: 600;
            font-size: 15px;
            word-break: break-word;
        }

        .info-value.empty {
            color: #999;
            font-style: italic;
            font-weight: 400;
        }

        /* FULL WIDTH ITEMS */
        .info-item.full-width {
            grid-column: 1 / -1;
        }

        /* STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #2e7d32;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
            font-size: 14px;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .content {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .header-actions .btn {
                flex: 1;
                max-width: 150px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .header {
                padding: 15px 20px;
            }

            .header h1 {
                font-size: 22px;
            }

            .profile-box {
                padding: 20px;
            }

            .profile-photo img,
            .profile-photo .avatar-fallback {
                width: 150px;
                height: 150px;
                font-size: 60px;
                border-width: 5px;
            }

            .info-section {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                padding: 10px 16px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>👤 Profil Pribadi</h1>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-back">← Kembali</a>
                <a href="edit_profil.php" class="btn btn-edit">✏️ Edit Profil</a>
            </div>
        </div>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $booking_stats['total'] ?? 0; ?></div>
                <div class="stat-label">Total Booking</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $_SESSION['nama'] ? strlen($_SESSION['nama']) : 0; ?></div>
                <div class="stat-label">Karakter Nama</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo !empty($profil_data['nik']) ? '✓' : '✗'; ?></div>
                <div class="stat-label">Data Lengkap</div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="content">
            <!-- SIDEBAR -->
            <div class="sidebar">
                <div class="profile-box">
                    <div class="profile-photo">
                        <?php if (!empty($profil_data['foto_profil']) && file_exists("../uploads/profil/{$profil_data['foto_profil']}")): ?>
                            <img src="../uploads/profil/<?php echo htmlspecialchars($profil_data['foto_profil']); ?>" alt="Foto Profil">
                        <?php else: ?>
                            <div class="avatar-fallback"><?php echo strtoupper(substr($profil_data['nama'], 0, 1)); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-name"><?php echo htmlspecialchars($profil_data['nama'] ?? '-'); ?></div>
                    <div class="profile-email"><?php echo htmlspecialchars($profil_data['email'] ?? '-'); ?></div>
                    <div class="profile-status">
                        <div class="status-label">Status Akun</div>
                        <div class="status-value" style="color: #43a047;">✓ Aktif</div>
                    </div>
                </div>
            </div>

            <!-- MAIN INFO -->
            <div class="main-info">
                <!-- INFORMASI UMUM -->
                <div class="info-section">
                    <div class="section-title">ℹ️ Informasi Umum</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">🆔 NIK</div>
                            <div class="info-value <?php echo empty($profil_data['nik']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['nik'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">👶 Tempat Lahir</div>
                            <div class="info-value <?php echo empty($profil_data['tempat_lahir']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['tempat_lahir'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">📅 Tanggal Lahir</div>
                            <div class="info-value <?php echo empty($profil_data['tanggal_lahir']) ? 'empty' : ''; ?>">
                                <?php 
                                if (!empty($profil_data['tanggal_lahir'])) {
                                    $date = new DateTime($profil_data['tanggal_lahir']);
                                    echo $date->format('d M Y');
                                } else {
                                    echo 'Belum diisi';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">👥 Jenis Kelamin</div>
                            <div class="info-value <?php echo empty($profil_data['jenis_kelamin']) ? 'empty' : ''; ?>">
                                <?php 
                                $jk = $profil_data['jenis_kelamin'] ?? '';
                                echo $jk === 'L' ? 'Laki-laki' : ($jk === 'P' ? 'Perempuan' : 'Belum diisi');
                                ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🌍 Kewarganegaraan</div>
                            <div class="info-value <?php echo empty($profil_data['kewarganegaraan']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['kewarganegaraan'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ALAMAT -->
                <div class="info-section">
                    <div class="section-title">📍 Alamat</div>
                    <div class="info-grid">
                        <div class="info-item full-width">
                            <div class="info-label">🏠 Alamat Lengkap</div>
                            <div class="info-value <?php echo empty($profil_data['alamat']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['alamat'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🏘️ Kelurahan</div>
                            <div class="info-value <?php echo empty($profil_data['kelurahan']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['kelurahan'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🛣️ Kecamatan</div>
                            <div class="info-value <?php echo empty($profil_data['kecamatan']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['kecamatan'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🏙️ Kabupaten</div>
                            <div class="info-value <?php echo empty($profil_data['kabupaten']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['kabupaten'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🗺️ Provinsi</div>
                            <div class="info-value <?php echo empty($profil_data['provinsi']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['provinsi'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KONTAK -->
                <div class="info-section">
                    <div class="section-title">📞 Kontak & Darurat</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">📱 No. HP</div>
                            <div class="info-value <?php echo empty($profil_data['no_hp']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['no_hp'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🚨 No. Darurat</div>
                            <div class="info-value <?php echo empty($profil_data['no_darurat']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['no_darurat'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">👨‍👩‍👧‍👦 Hubungan Darurat</div>
                            <div class="info-value <?php echo empty($profil_data['hubungan_darurat']) ? 'empty' : ''; ?>">
                                <?php echo htmlspecialchars($profil_data['hubungan_darurat'] ?? 'Belum diisi'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
