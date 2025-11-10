<?php
session_start();
include '../backend/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=pengunjung/kuota.php");
    exit;
}

$data_kuota = $_SESSION['kuota_data'] ?? null;
$total_harga = $_SESSION['kuota_total'] ?? null;
$deskripsi = $_SESSION['kuota_deskripsi'] ?? null;
$jumlah_pendaki = $_SESSION['kuota_jumlah'] ?? null;
$jalur_id = $_SESSION['kuota_jalur_id'] ?? null;
$tanggal_naik = $_SESSION['kuota_tanggal_naik'] ?? null;
$tanggal_turun = $_SESSION['kuota_tanggal_turun'] ?? null;
$error_message = null;

// Tombol reset
if (isset($_POST['reset_data'])) {
    unset(
        $_SESSION['kuota_data'],
        $_SESSION['kuota_total'],
        $_SESSION['kuota_deskripsi'],
        $_SESSION['kuota_jumlah'],
        $_SESSION['kuota_jalur_id'],
        $_SESSION['kuota_tanggal_naik'],
        $_SESSION['kuota_tanggal_turun']
    );
    header("Location: kuota.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cek_kuota'])) {
    $jalur_id = $_POST['jalur_id'] ?? '';
    $tanggal_naik = $_POST['tanggal_naik'] ?? '';
    $tanggal_turun = $_POST['tanggal_turun'] ?? '';
    $jumlah_pendaki = intval($_POST['jumlah_pendaki'] ?? 0);

    // 🧩 Validasi dasar
    if ($jumlah_pendaki < 2) {
        $error_message = "Minimal pendaki adalah 2 orang!";
    } elseif (strtotime($tanggal_naik) <= strtotime('today')) {
        $error_message = "Tanggal pendakian harus minimal H-1 dari hari ini!";
    } elseif ($tanggal_turun <= $tanggal_naik) {
        $error_message = "Tanggal turun harus lebih besar dari tanggal naik!";
    } elseif ($jalur_id && $tanggal_naik && $tanggal_turun) {
        // 🔹 Cek atau buat data pendakian
        $cek = $conn->prepare("
            SELECT pendakian_id, kuota_tersedia 
            FROM pendakian 
            WHERE jalur_id=? AND tanggal_pendakian=? AND tanggal_turun=? LIMIT 1
        ");
        $cek->bind_param("iss", $jalur_id, $tanggal_naik, $tanggal_turun);
        $cek->execute();
        $res = $cek->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $pendakian_id = $row['pendakian_id'];
        } else {
            $qKuota = $conn->prepare("SELECT kuota_harian FROM jalur_pendakian WHERE jalur_id=?");
            $qKuota->bind_param("i", $jalur_id);
            $qKuota->execute();
            $kuotaData = $qKuota->get_result()->fetch_assoc();
            $qKuota->close();
            $kuota_awal = $kuotaData['kuota_harian'] ?? 0;

            $insert = $conn->prepare("
                INSERT INTO pendakian (jalur_id, tanggal_pendakian, tanggal_turun, kuota_tersedia, status)
                VALUES (?, ?, ?, ?, 'tersedia')
            ");
            $insert->bind_param("issi", $jalur_id, $tanggal_naik, $tanggal_turun, $kuota_awal);
            $insert->execute();
            $pendakian_id = $conn->insert_id;
            $insert->close();
        }
        $cek->close();

        // 🔹 Ambil info jalur dan sisa kuota
        $q = $conn->prepare("
            SELECT jp.nama_jalur, jp.kuota_harian, jp.tarif_tiket, jp.deskripsi,
                   (p.kuota_tersedia - IFNULL(SUM(ps.jumlah_pendaki),0)) AS sisa_kuota
            FROM jalur_pendakian jp
            JOIN pendakian p ON jp.jalur_id = p.jalur_id
            LEFT JOIN pesanan ps ON p.pendakian_id = ps.pendakian_id
            WHERE p.pendakian_id = ?
            GROUP BY p.pendakian_id
        ");
        $q->bind_param("i", $pendakian_id);
        $q->execute();
        $data_kuota = $q->get_result()->fetch_assoc();
        $q->close();

        if ($data_kuota) {
            $total_harga = $jumlah_pendaki * $data_kuota['tarif_tiket'];
            $deskripsi = $data_kuota['deskripsi'];
        }

        // Simpan ke session
        $_SESSION['selected_pendakian'] = $pendakian_id;
        $_SESSION['jumlah_pendaki'] = $jumlah_pendaki;
        $_SESSION['tanggal_naik'] = $tanggal_naik;
        $_SESSION['tanggal_turun'] = $tanggal_turun;

        $_SESSION['kuota_data'] = $data_kuota;
        $_SESSION['kuota_total'] = $total_harga;
        $_SESSION['kuota_deskripsi'] = $deskripsi;
        $_SESSION['kuota_jumlah'] = $jumlah_pendaki;
        $_SESSION['kuota_jalur_id'] = $jalur_id;
        $_SESSION['kuota_tanggal_naik'] = $tanggal_naik;
        $_SESSION['kuota_tanggal_turun'] = $tanggal_turun;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek Kuota Pendakian</title>
<link rel="stylesheet" href="../style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to bottom right, #e8f5e9, #c8e6c9);
    margin: 0;
}
.container {
    width: 90%;
    max-width: 1050px;
    margin: 130px auto 80px auto;
    background: #fff;
    border-radius: 18px;
    padding: 40px 45px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #2e7d32;
    margin-bottom: 35px;
    font-weight: 700;
    font-size: 1.8rem;
}
form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    margin-bottom: 20px;
}
label {
    font-weight: 600;
    color: #2e7d32;
}
input, select {
    width: 100%;
    padding: 11px;
    border: 2px solid #c8e6c9;
    border-radius: 10px;
    transition: 0.3s;
}
input:focus, select:focus {
    border-color: #43a047;
    outline: none;
    box-shadow: 0 0 6px rgba(67,160,71,0.3);
}
.btn {
    background-color: #43a047;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 13px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}
.btn:hover { background-color: #2e7d32; transform: translateY(-1px); }

.result-card {
    margin-top: 35px;
    background: #f9fff9;
    border-radius: 16px;
    border: 2px solid #dcedc8;
    padding: 30px 35px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.result-card h3 {
    color: #2e7d32;
    margin-bottom: 8px;
    font-size: 1.4rem;
}
.deskripsi {
    background: #f1f8e9;
    border-left: 5px solid #8bc34a;
    padding: 10px 15px;
    border-radius: 10px;
    color: #444;
    margin-bottom: 15px;
}
.total {
    font-weight: bold;
    color: #e91e63;
    font-size: 1.1rem;
}
.progress-container {
    margin: 15px 0;
    background: #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    height: 20px;
}
.progress-bar {
    height: 100%;
    width: 0%;
    transition: width 0.5s ease-in-out;
    border-radius: 10px;
}
footer {
    text-align: center;
    color: #555;
    margin: 40px 0;
}
</style>
</head>
<body>
<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

<div class="container">
    <h2>🧭 Cek Kuota & Tarif Pendakian</h2>

    <form method="POST">
        <div>
            <label>Jalur Pendakian</label>
            <select name="jalur_id" required>
                <option value="">-- Pilih Jalur --</option>
                <?php
                $jalur = $conn->query("SELECT jalur_id, nama_jalur FROM jalur_pendakian WHERE status='aktif' ORDER BY nama_jalur ASC");
                while ($j = $jalur->fetch_assoc()):
                ?>
                    <option value="<?= $j['jalur_id']; ?>" <?= ($jalur_id == $j['jalur_id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($j['nama_jalur']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label>Jumlah Pendaki</label>
            <input type="number" name="jumlah_pendaki" min="2" max="15" required value="<?= htmlspecialchars($jumlah_pendaki ?? ''); ?>">
        </div>
        <div>
            <label>Tanggal Naik</label>
            <input type="date" name="tanggal_naik" required value="<?= htmlspecialchars($tanggal_naik ?? ''); ?>">
        </div>
        <div>
            <label>Tanggal Turun</label>
            <input type="date" name="tanggal_turun" required value="<?= htmlspecialchars($tanggal_turun ?? ''); ?>">
        </div>
        <div style="align-self:end;">
            <button type="submit" name="cek_kuota" class="btn">Cek Kuota</button>
        </div>
        <?php if ($data_kuota): ?>
        <div style="align-self:end;">
            <button type="submit" name="reset_data" class="btn" style="background:#757575;">Reset</button>
        </div>
        <?php endif; ?>
    </form>

    <?php if ($error_message): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            text: '<?= addslashes($error_message) ?>',
            confirmButtonColor: '#e53935'
        });
    </script>
    <?php endif; ?>

    <?php if ($data_kuota):
        $total_kuota = max(1, $data_kuota['kuota_harian']);
        $sisa = max(0, $data_kuota['sisa_kuota']);
        $persen = round(($sisa / $total_kuota) * 100);
        $warna = ($persen > 60) ? '#4caf50' : (($persen > 30) ? '#fbc02d' : '#e53935');
    ?>
    <div class="result-card">
        <h3><?= htmlspecialchars($data_kuota['nama_jalur']); ?></h3>
        <div class="deskripsi"><?= nl2br(htmlspecialchars($deskripsi)); ?></div>
        <p><strong>Kuota Tersisa:</strong> <?= $sisa ?> / <?= $total_kuota; ?></p>
        <div class="progress-container">
            <div class="progress-bar" style="width: <?= $persen; ?>%; background: <?= $warna; ?>;"></div>
        </div>
        <p><strong>Tarif per Orang:</strong> Rp<?= number_format($data_kuota['tarif_tiket'], 0, ',', '.'); ?></p>
        <p><strong>Total Bayar:</strong> <span class="total">Rp<?= number_format($total_harga, 0, ',', '.'); ?></span></p>
        <form action="booking.php" method="POST" style="margin-top:20px; text-align:right;">
            <input type="hidden" name="id_pendakian" value="<?= $_SESSION['selected_pendakian']; ?>">
            <input type="hidden" name="jumlah_pendaki" value="<?= htmlspecialchars($jumlah_pendaki); ?>">
            <button type="submit" class="btn">Lanjut Booking ➜</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<footer>
    &copy; 2025 Tahura Raden Soerjo. All Rights Reserved.
</footer>
</body>
</html>
