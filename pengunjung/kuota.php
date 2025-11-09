<?php
session_start();
include '../backend/koneksi.php';

// 🔒 Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=pengunjung/kuota.php");
    exit;
}

$data_kuota = null;
$total_harga = null;
$deskripsi = null;
$jumlah_pendaki = null;

// 🧮 Jika user menekan tombol CEK KUOTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cek_kuota'])) {
    $jalur_id = $_POST['jalur_id'] ?? '';
    $tanggal_naik = $_POST['tanggal_naik'] ?? '';
    $tanggal_turun = $_POST['tanggal_turun'] ?? '';
    $jumlah_pendaki = intval($_POST['jumlah_pendaki'] ?? 0);

    if ($jalur_id && $tanggal_naik && $tanggal_turun && $jumlah_pendaki > 0) {
        // 🔹 Cek apakah pendakian pada tanggal & jalur itu sudah ada
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
            // 🔹 Ambil kuota harian default dari tabel jalur_pendakian
            $qKuota = $conn->prepare("SELECT kuota_harian FROM jalur_pendakian WHERE jalur_id=?");
            $qKuota->bind_param("i", $jalur_id);
            $qKuota->execute();
            $kuotaData = $qKuota->get_result()->fetch_assoc();
            $qKuota->close();
            $kuota_awal = $kuotaData['kuota_harian'] ?? 0;

            // 🔹 Buat data pendakian baru
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

        // 🔹 Simpan ke session untuk halaman booking.php
        $_SESSION['selected_pendakian'] = $pendakian_id;
        $_SESSION['jumlah_pendaki'] = $jumlah_pendaki;
        $_SESSION['tanggal_naik'] = $tanggal_naik;
        $_SESSION['tanggal_turun'] = $tanggal_turun;
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
    background: #f3f6f3 url('../images/Gunung_Raung.jpg') no-repeat center top;
    background-size: cover;
    margin: 0;
}
.container {
    width: 90%;
    max-width: 1100px;
    margin: 120px auto 60px auto;
    background: rgba(255,255,255,0.96);
    border-radius: 15px;
    padding: 30px 40px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}
h2 {
    text-align: center;
    color: #2e7d32;
    margin-bottom: 25px;
    font-weight: 700;
}
form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}
input, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
.btn {
    background-color: #43a047;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px;
    cursor: pointer;
    font-weight: 600;
}
.btn:hover { background-color: #2e7d32; }
.result {
    margin-top: 30px;
    background: #e8f5e9;
    border-left: 6px solid #43a047;
    padding: 20px;
    border-radius: 10px;
    animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.result h3 { color: #2e7d32; margin-bottom: 10px; }
.total { font-weight: bold; color: #e91e63; }
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
                    <option value="<?= $j['jalur_id']; ?>" <?= (isset($jalur_id) && $jalur_id==$j['jalur_id'])?'selected':''; ?>>
                        <?= htmlspecialchars($j['nama_jalur']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label>Jumlah Pendaki</label>
            <input type="number" name="jumlah_pendaki" min="1" max="15" required value="<?= htmlspecialchars($jumlah_pendaki ?? ''); ?>">
        </div>
        <div>
            <label>Tanggal Naik</label>
            <input type="date" name="tanggal_naik" required value="<?= htmlspecialchars($_POST['tanggal_naik'] ?? ''); ?>">
        </div>
        <div>
            <label>Tanggal Turun</label>
            <input type="date" name="tanggal_turun" required value="<?= htmlspecialchars($_POST['tanggal_turun'] ?? ''); ?>">
        </div>
        <div>
            <button type="submit" name="cek_kuota" class="btn">Cek Kuota</button>
        </div>
    </form>

    <?php if ($data_kuota): ?>
    <div class="result">
        <h3><?= htmlspecialchars($data_kuota['nama_jalur']); ?></h3>
        <p><strong>Deskripsi Jalur:</strong><br><?= nl2br(htmlspecialchars($deskripsi)); ?></p>
        <p><strong>Kuota Tersisa:</strong> <?= max(0, $data_kuota['sisa_kuota']); ?> / <?= $data_kuota['kuota_harian']; ?></p>
        <p><strong>Tarif per Orang:</strong> Rp<?= number_format($data_kuota['tarif_tiket'], 0, ',', '.'); ?></p>
        <p><strong>Total Bayar:</strong> <span class="total">Rp<?= number_format($total_harga, 0, ',', '.'); ?></span></p>
        <form action="booking.php" method="POST" style="margin-top:15px;text-align:right;">
            <input type="hidden" name="id_pendakian" value="<?= $_SESSION['selected_pendakian']; ?>">
            <input type="hidden" name="jumlah_pendaki" value="<?= htmlspecialchars($jumlah_pendaki); ?>">
            <button type="submit" class="btn">Lanjut Booking</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<footer style="text-align:center; margin:30px 0; color:#555;">
    &copy; 2025 Tahura Raden Soerjo. All Rights Reserved.
</footer>
</body>
</html>
