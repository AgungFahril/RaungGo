<?php
session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];

// Fetch complete profile data
$stmt = $conn->prepare("
    SELECT u.user_id, u.nama, u.email, u.foto_profil,
           p.nik, p.alamat, p.no_hp, p.no_darurat, p.hubungan_darurat,
           p.provinsi, p.kabupaten, p.kecamatan, p.kelurahan, p.tempat_lahir,
           p.tanggal_lahir, p.jenis_kelamin, p.kewarganegaraan
    FROM users u
    LEFT JOIN pendaki_detail p ON u.user_id = p.user_id
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profil_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profil_data) {
    echo '<p style="color:red;">Data profil tidak ditemukan.</p>';
    exit;
}
?>

<div class="profile-section" style="
    padding: 20px;
    text-align: center;
">
    <!-- Profile Photo -->
    <div style="
        margin-bottom: 20px;
    ">
        <?php if (!empty($profil_data['foto_profil']) && file_exists("../uploads/profil/{$profil_data['foto_profil']}")): ?>
            <img src="../uploads/profil/<?php echo htmlspecialchars($profil_data['foto_profil']); ?>" alt="Foto Profil" style="
                width: 120px;
                height: 120px;
                border-radius: 50%;
                object-fit: cover;
                border: 4px solid #2e7d32;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            ">
        <?php else: ?>
            <div style="
                width: 120px;
                height: 120px;
                border-radius: 50%;
                background: linear-gradient(135deg, #2e7d32, #43a047);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 48px;
                font-weight: 700;
                margin: 0 auto;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            ">
                <?php echo strtoupper(substr($profil_data['nama'], 0, 1)); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Profile Info -->
    <div style="
        background: #f9fbf8;
        border-radius: 10px;
        padding: 20px;
        text-align: left;
    ">
        <div style="
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        ">
            <div style="
                color: #666;
                font-size: 12px;
                margin-bottom: 5px;
            ">👤 Nama</div>
            <div style="
                color: #2e7d32;
                font-weight: 600;
                font-size: 16px;
            ">
                <?php echo htmlspecialchars($profil_data['nama'] ?? '-'); ?>
            </div>
        </div>

        <div style="
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        ">
            <div style="
                color: #666;
                font-size: 12px;
                margin-bottom: 5px;
            ">📧 Email</div>
            <div style="
                color: #333;
                font-size: 14px;
            ">
                <?php echo htmlspecialchars($profil_data['email'] ?? '-'); ?>
            </div>
        </div>

        <div style="
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        ">
            <div style="
                color: #666;
                font-size: 12px;
                margin-bottom: 5px;
            ">🆔 NIK</div>
            <div style="
                color: #333;
                font-size: 14px;
            ">
                <?php echo htmlspecialchars($profil_data['nik'] ?? 'Belum diisi'); ?>
            </div>
        </div>

        <div style="
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        ">
            <div style="
                color: #666;
                font-size: 12px;
                margin-bottom: 5px;
            ">📱 No. HP</div>
            <div style="
                color: #333;
                font-size: 14px;
            ">
                <?php echo htmlspecialchars($profil_data['no_hp'] ?? 'Belum diisi'); ?>
            </div>
        </div>

        <div style="
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        ">
            <div style="
                color: #666;
                font-size: 12px;
                margin-bottom: 5px;
            ">📍 Alamat</div>
            <div style="
                color: #333;
                font-size: 14px;
            ">
                <?php echo htmlspecialchars($profil_data['alamat'] ?? 'Belum diisi'); ?>
            </div>
        </div>

        <div style="
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        ">
            <div style="
                color: #666;
                font-size: 12px;
                margin-bottom: 5px;
            ">🏘️ Kota</div>
            <div style="
                color: #333;
                font-size: 14px;
            ">
                <?php echo htmlspecialchars($profil_data['kabupaten'] ?? 'Belum diisi'); ?>
            </div>
        </div>

        <div>
            <div style="
                color: #666;
                font-size: 12px;
                margin-bottom: 5px;
            ">🗺️ Provinsi</div>
            <div style="
                color: #333;
                font-size: 14px;
            ">
                <?php echo htmlspecialchars($profil_data['provinsi'] ?? 'Belum diisi'); ?>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div style="
        margin-top: 20px;
        display: flex;
        gap: 10px;
        justify-content: center;
    ">
        <a href="profil.php" style="
            display: inline-block;
            background: #2e7d32;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            font-size: 14px;
        " onmouseover="this.style.background='#1b5e20'" onmouseout="this.style.background='#2e7d32'">
            👁️ Lihat Detail
        </a>
        <a href="edit_profil.php" style="
            display: inline-block;
            background: #43a047;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            font-size: 14px;
        " onmouseover="this.style.background='#2e7d32'" onmouseout="this.style.background='#43a047'">
            ✏️ Edit
        </a>
    </div>
</div>
