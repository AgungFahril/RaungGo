<?php
session_start();
include '../backend/koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔒 Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=booking");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama'] ?? '';

// Ambil data ketua
$qKetua = $conn->prepare("SELECT nik, no_hp, alamat FROM pendaki_detail WHERE user_id = ?");
$qKetua->bind_param("i", $user_id);
$qKetua->execute();
$data_ketua = $qKetua->get_result()->fetch_assoc();
$qKetua->close();

// Ambil ID pendakian & jumlah pendaki dari session
$id_pendakian = $_SESSION['selected_pendakian'] ?? null;
$jumlah_pendaki = $_SESSION['jumlah_pendaki'] ?? ($_POST['jumlah_pendaki'] ?? null);

if (!$id_pendakian || !$jumlah_pendaki) {
    header("Location: kuota.php?error=pilih_jalur");
    exit;
}

// Ambil info jalur
$qJalur = $conn->prepare("
    SELECT p.pendakian_id, j.nama_jalur, j.tarif_tiket, j.deskripsi, p.tanggal_pendakian
    FROM pendakian p
    JOIN jalur_pendakian j ON p.jalur_id = j.jalur_id
    WHERE p.pendakian_id = ?
");
$qJalur->bind_param("i", $id_pendakian);
$qJalur->execute();
$data_jalur = $qJalur->get_result()->fetch_assoc();
$qJalur->close();

if (!$data_jalur) {
    header("Location: kuota.php?error=data_tidak_ditemukan");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Form Booking Pendakian</title>
<link rel="stylesheet" href="../style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Poppins', sans-serif;
    background: #f3f6f3 url('../images/Gunung_Raung.jpg') no-repeat center top;
    background-size: cover;
    color: #333;
    display: flex;
    min-height: 100vh;
    flex-direction: column;
}
.main-container {
    display: flex;
    flex: 1;
    margin-top: 60px;
}
.sidebar {
    width: 250px;
    background: #2e7d32;
    color: white;
    padding: 20px;
    position: fixed;
    left: 0;
    top: 60px;
    height: calc(100vh - 60px);
    overflow-y: auto;
    box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    z-index: 999;
}
.sidebar-header {
    padding-bottom: 20px;
    border-bottom: 2px solid #1b5e20;
    margin-bottom: 20px;
    cursor: pointer;
    transition: 0.3s;
}
.sidebar-header:hover {
    opacity: 0.9;
}
.sidebar-header h3 {
    color: white;
    font-size: 18px;
    margin-bottom: 5px;
}
.sidebar-header p {
    color: #c8e6c9;
    font-size: 12px;
}
.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.nav-item {
    color: white;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 8px;
    transition: 0.3s;
    display: block;
    font-weight: 500;
}
.nav-item:hover {
    background: #1b5e20;
    transform: translateX(5px);
}
.nav-item.active {
    background: #1b5e20;
    border-left: 4px solid #43a047;
    padding-left: 11px;
}
.booking-wrapper {
    max-width: 900px;
    margin: 40px auto 60px calc(250px + 20px);
    background: rgba(255,255,255,0.96);
    border-radius: 15px;
    padding: 35px 45px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    flex: 1;
}
h2 {
    text-align: center;
    color: #2e7d32;
    margin-bottom: 25px;
    font-weight: 700;
}
.info-card {
    background: #e8f5e9;
    border-left: 6px solid #43a047;
    border-radius: 10px;
    padding: 18px 22px;
    margin-bottom: 25px;
    box-shadow: inset 0 0 6px rgba(0,0,0,0.05);
}
fieldset {
    border: 1px solid #c8e6c9;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    background: #fafdf9;
}
legend {
    color: #2e7d32;
    font-weight: 600;
    padding: 0 8px;
}
label { font-weight: 600; margin-top: 10px; display: block; }
input, select, textarea {
    width: 100%;
    padding: 9px;
    margin-top: 5px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
input[readonly], textarea[readonly] { background: #f7f7f7; }
.anggota-group {
    background: #f9fbe7;
    border: 1px solid #dce775;
    border-radius: 8px;
    padding: 15px;
    margin-top: 15px;
    transition: 0.3s;
}
.anggota-group:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.btn-submit {
    width: 100%;
    background: #43a047;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 13px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
.btn-submit:hover { background: #2e7d32; transform: translateY(-1px); }
.btn-back {
    display: inline-block;
    background-color: #9e9e9e;
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}
.btn-back:hover {
    background-color: #616161;
    transform: translateY(-1px);
}
.btn-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 25px;
}
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: static;
        margin-top: 0;
    }
    .main-container {
        flex-direction: column;
    }
    .booking-wrapper {
        margin: 0 auto;
        padding: 20px 15px;
    }
    .nav-item {
        display: inline-block;
        padding: 8px 12px;
        margin-right: 5px;
        font-size: 12px;
    }
    .sidebar-header {
        display: none;
    }
    .sidebar-nav {
        flex-direction: row;
        gap: 5px;
        flex-wrap: wrap;
    }
}
</style>
</head>
<body>

<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

<div class="main-container">
    <aside class="sidebar">
        <div class="sidebar-header" onclick="openProfilModal()">
            <h3>👤 Profil</h3>
            <p><?= htmlspecialchars($user_name); ?></p>
        </div>
        <nav class="sidebar-nav">
            <a href="profil.php" class="nav-item">👤 Profil Pribadi</a>
            <a href="booking.php" class="nav-item active">✏️ Booking Pendakian</a>
            <a href="pembayaran.php" class="nav-item">💳 Pembayaran</a>
            <a href="detail_transaksi.php" class="nav-item">📋 Detail Transaksi</a>
            <a href="sop.php" class="nav-item">📖 SOP Pendakian</a>
            <a href="lengkapi_data.php" class="nav-item">✏️ Edit Profil</a>
        </nav>
        <a href="../backend/logout.php" class="nav-item" style="border-top:1px solid rgba(255,255,255,0.15); background:#e53935; margin-top:auto;">🚪 Logout</a>
    </aside>

    <main class="booking-wrapper">
    <h2>🧗 Form Booking Pendakian</h2>

    <!-- Tombol kembali -->
    <div style="margin-bottom: 20px;">
        <a href="kuota.php" class="btn-back">⬅ Kembali ke Cek Kuota</a>
    </div>

    <div class="info-card">
        <p><strong>Jalur:</strong> <?= htmlspecialchars($data_jalur['nama_jalur']); ?></p>
        <p><strong>Tanggal Pendakian:</strong> <?= htmlspecialchars($data_jalur['tanggal_pendakian']); ?></p>
        <p><strong>Deskripsi Jalur:</strong><br><?= nl2br(htmlspecialchars($data_jalur['deskripsi'])); ?></p>
        <hr style="border:none; border-top:1px solid #ccc; margin:10px 0;">
        <p><strong>Jumlah Pendaki:</strong> <?= $jumlah_pendaki; ?> orang</p>
        <p><strong>Tarif per Orang:</strong> Rp<?= number_format($data_jalur['tarif_tiket'],0,',','.'); ?></p>
        <p><strong>Total Bayar:</strong> 
           <span style="color:#e91e63; font-weight:600;">
               Rp<?= number_format($jumlah_pendaki * $data_jalur['tarif_tiket'],0,',','.'); ?>
           </span></p>
    </div>

    <form method="POST" action="../backend/proses_booking.php">
        <input type="hidden" name="pendakian_id" value="<?= $id_pendakian; ?>">
        <input type="hidden" name="jumlah_pendaki" value="<?= $jumlah_pendaki; ?>">

        <fieldset>
            <legend>👤 Data Ketua Pendaki</legend>
            <label>Nama Ketua</label>
            <input type="text" name="nama_ketua" value="<?= htmlspecialchars($user_name); ?>" readonly>

            <label>NIK</label>
            <input type="text" name="no_identitas_ketua" value="<?= htmlspecialchars($data_ketua['nik'] ?? '-'); ?>" readonly>

            <label>No. HP</label>
            <input type="text" name="telepon_ketua" value="<?= htmlspecialchars($data_ketua['no_hp'] ?? '-'); ?>" readonly>

            <label>Alamat</label>
            <textarea name="alamat_ketua" rows="2" readonly><?= htmlspecialchars($data_ketua['alamat'] ?? '-'); ?></textarea>
        </fieldset>

        <?php if ($jumlah_pendaki > 1): ?>
        <fieldset>
            <legend>🧍 Anggota Tim Pendaki (<?= $jumlah_pendaki - 1; ?> orang)</legend>
            <div id="anggotaContainer"></div>
        </fieldset>
        <?php endif; ?>

        <div class="btn-row">
            <button type="submit" name="submit_booking" class="btn-submit">Kirim Booking</button>
        </div>
    </form>
</main>
</div>

<!-- Modal Profil -->
<div id="profilModal" class="modal-profil" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease-in-out;
">
    <div style="
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 450px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 15px 50px rgba(0,0,0,0.3);
        animation: slideInUp 0.4s ease-out;
    ">
        <div style="
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            padding: 25px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        ">
            <h2 style="margin: 0; font-size: 24px;">👤 Profil Saya</h2>
            <button onclick="closeProfilModal()" style="
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
            ">✕</button>
        </div>
        <div style="padding: 25px;" id="profilContent">
            <p>Memuat...</p>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.modal-profil.show {
    display: flex !important;
}
</style>

<script>
// Otomatis generate form anggota
const jumlahPendaki = <?= intval($jumlah_pendaki); ?>;
const container = document.getElementById('anggotaContainer');
if (container && jumlahPendaki > 1) {
    for (let i = 2; i <= jumlahPendaki; i++) {
        const div = document.createElement('div');
        div.className = 'anggota-group';
        div.innerHTML = `
            <h4>Anggota ${i}</h4>
            <label>Nama Lengkap:</label>
            <input type="text" name="anggota[${i}][nama]" required>
            <label>NIK:</label>
            <input type="text" name="anggota[${i}][nik]" required>
            <label>No. HP:</label>
            <input type="text" name="anggota[${i}][hp]" required>
            <label>Kewarganegaraan:</label>
            <input type="text" name="anggota[${i}][kewarganegaraan]" value="WNI" required>
            <label>Jenis Kelamin:</label>
            <select name="anggota[${i}][jenis_kelamin]" required>
                <option value="">-- Pilih --</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>`;
        container.appendChild(div);
    }
}

// 👤 Modal Profil Functions
function openProfilModal() {
    const modal = document.getElementById('profilModal');
    const content = document.getElementById('profilContent');
    modal.classList.add('show');
    
    // Fetch profil data
    fetch('../backend/get_profil_modal.php')
        .then(resp => resp.text())
        .then(data => {
            content.innerHTML = data;
        })
        .catch(err => {
            content.innerHTML = '<p style="color:red;">Gagal memuat profil.</p>';
        });
}

function closeProfilModal() {
    const modal = document.getElementById('profilModal');
    modal.classList.remove('show');
}

// Close modal when clicking backdrop
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('profilModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfilModal();
            }
        });
    }
});
</script>

<footer style="text-align:center; padding:25px; color:#555;">
    &copy; 2025 Tahura Raden Soerjo
</footer>
</body>
</html>
