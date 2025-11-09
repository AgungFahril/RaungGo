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
body {
    font-family: 'Poppins', sans-serif;
    background: #f3f6f3 url('../images/Gunung_Raung.jpg') no-repeat center top;
    background-size: cover;
    color: #333;
}
.booking-wrapper {
    max-width: 900px;
    margin: 120px auto 60px auto;
    background: rgba(255,255,255,0.96);
    border-radius: 12px;
    padding: 35px 45px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}
h2 {
    text-align: center;
    color: #2e7d32;
    margin-bottom: 25px;
    font-weight: 700;
}
.info-card {
    background: #e8f5e9;
    border-left: 5px solid #4caf50;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 25px;
}
fieldset {
    border: 1px solid #c8e6c9;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
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
}
.btn-submit {
    width: 100%;
    background: #43a047;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}
.btn-submit:hover { background: #2e7d32; }
</style>
</head>
<body>

<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

<main class="booking-wrapper">
    <h2>🧗 Form Booking Pendakian</h2>

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

    <!-- Form booking mengarah ke backend/proses_booking.php -->
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

        <button type="submit" name="submit_booking" class="btn-submit">Kirim Booking</button>
    </form>
</main>

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
</script>

<footer style="text-align:center; padding:25px; color:#555;">&copy; 2025 Tahura Raden Soerjo</footer>
</body>
</html>
