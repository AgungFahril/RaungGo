<?php
// DETAIL TRANSAKSI — versi final fix logic saja

date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../backend/koneksi.php';

// Flash message
$success_message = $_SESSION['success_message'] ?? null;
$error_message   = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil parameter
$pesanan_id = isset($_GET['pesanan_id']) ? intval($_GET['pesanan_id']) : null;
$kode_token = isset($_GET['kode_token']) ? trim($_GET['kode_token']) : null;

if (!$pesanan_id && !$kode_token) {
    echo "<script>alert('Parameter pesanan tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}

// Ambil data pesanan
$sql = "
    SELECT ps.*, p.tanggal_pendakian, p.tanggal_turun,
           jp.nama_jalur, jp.tarif_tiket, jp.deskripsi AS jalur_deskripsi,
           g.nama_guide, g.tarif AS guide_tarif,
           pr.nama_porter AS porter_nama, pr.tarif AS porter_tarif,
           oj.nama_ojek AS ojek_nama, oj.tarif AS ojek_tarif
    FROM pesanan ps
    LEFT JOIN pendakian p ON ps.pendakian_id = p.pendakian_id
    LEFT JOIN jalur_pendakian jp ON p.jalur_id = jp.jalur_id
    LEFT JOIN guide g ON ps.guide_id = g.guide_id
    LEFT JOIN porter pr ON ps.porter_id = pr.porter_id
    LEFT JOIN ojek oj ON ps.ojek_id = oj.ojek_id
    WHERE " . ($pesanan_id ? "ps.pesanan_id = ?" : "ps.kode_token = ?") . " LIMIT 1
";
$stmt = $conn->prepare($sql);
if ($pesanan_id) $stmt->bind_param("i", $pesanan_id);
else $stmt->bind_param("s", $kode_token);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pesanan) {
    echo "<script>alert('Data pesanan tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}
$pesanan_id = intval($pesanan['pesanan_id']);

// Ambil anggota
$anggota = [];
$stA = $conn->prepare("SELECT * FROM pesanan_anggota WHERE pesanan_id = ? ORDER BY anggota_id ASC");
$stA->bind_param("i", $pesanan_id);
$stA->execute();
$resA = $stA->get_result();
while ($r = $resA->fetch_assoc()) $anggota[] = $r;
$stA->close();

// Ambil histori pembayaran
$payments = [];
$stP = $conn->prepare("SELECT * FROM pembayaran WHERE pesanan_id = ? ORDER BY tanggal_bayar DESC, pembayaran_id DESC");
$stP->bind_param("i", $pesanan_id);
$stP->execute();
$resP = $stP->get_result();
while ($p = $resP->fetch_assoc()) $payments[] = $p;
$stP->close();
$payment = $payments[0] ?? null;

// Ambil status pembayaran terbaru
$status_bayar = $payment['status_pembayaran'] ?? null;

/* -----------------------------------------------------------------
   ⚠️ FIX LOGIKA BLOKIR YANG BERMASALAH
   -----------------------------------------------------------------
   detail_transaksi TIDAK BOLEH memblokir user ke pembayaran.php
   pembayaran.php SENDIRI yang menentukan boleh tidaknya upload ulang
------------------------------------------------------------------- */

// Tidak ada redirect-loop lagi di sini.

// helper waktu
function formatWaktu($dt){
    if (!$dt) return '-';
    try {
        $t = new DateTime($dt, new DateTimeZone('Asia/Jakarta'));
        return $t->format('d M Y, H:i') . ' WIB';
    } catch (Exception $e) {
        return $dt;
    }
}

// map status tampil
function map_status($ps_status, $pb_status){
    if ($ps_status === 'berhasil' || $ps_status === 'lunas') return ['Pembayaran Dikonfirmasi / Berhasil', 'sukses'];
    if ($ps_status === 'gagal' || $ps_status === 'dibatalkan') return ['Pembayaran Ditolak / Gagal', 'gagal'];

    if (in_array($pb_status, ['terkonfirmasi', 'lunas'])) return ['Pembayaran Dikonfirmasi / Berhasil', 'sukses'];
    if ($pb_status === 'ditolak') return ['Pembayaran Ditolak / Dibatalkan', 'gagal'];
    if ($pb_status === 'pending') return ['Menunggu Konfirmasi Admin', 'verifikasi'];

    if ($ps_status === 'menunggu_pembayaran') return ['Menunggu Pembayaran', 'pending'];
    if ($ps_status === 'menunggu_konfirmasi') return ['Menunggu Konfirmasi Admin', 'verifikasi'];

    return ['Menunggu Proses', 'pending'];
}
[$statusText, $statusClass] = map_status($pesanan['status_pesanan'] ?? null, $payment['status_pembayaran'] ?? null);

// ringkasan layanan
$guideText = !empty($pesanan['nama_guide']) ? ($pesanan['nama_guide'] . " (Rp " . number_format($pesanan['guide_tarif'] ?? 0,0,',','.') . ")") : '-';
$porterText = !empty($pesanan['porter_nama']) ? ($pesanan['porter_nama'] . " (Rp " . number_format($pesanan['porter_tarif'] ?? 0,0,',','.') . ")") : '-';
$ojekText   = !empty($pesanan['ojek_nama']) ? ($pesanan['ojek_nama'] . " (Rp " . number_format($pesanan['ojek_tarif'] ?? 0,0,',','.') . ")") : '-';

$alasanDitolak = $payment['alasan_ditolak'] ?? ($payment['catatan'] ?? null);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Detail Transaksi - <?= htmlspecialchars($pesanan['kode_token']); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ——————————————————————
   TAMPILAN TIDAK DIUBAH
—————————————————————— */
body{font-family:'Poppins',system-ui,Arial; background:#f5faf5; color:#222; margin:0;}
.header-space{height:72px}
.container-wrap{max-width:1000px;margin:20px auto;padding:28px;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.08);}
.header{display:flex;align-items:center;justify-content:space-between;gap:12px}
.h-left{display:flex;flex-direction:column}
.h1{color:#1b5e20;font-size:20px;margin:0 0 6px}
.token{font-weight:700;color:#37474f}
.status{padding:8px 12px;border-radius:8px;color:#fff;font-weight:700}
.status.pending{background:#f39c12}.status.verifikasi{background:#3498db}.status.sukses{background:#43a047}.status.gagal{background:#e53935}
.grid{display:grid;grid-template-columns:1fr 360px;gap:20px;margin-top:18px}
.card{background:#fafafa;padding:16px;border-radius:10px;border:1px solid #eee}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:8px 6px;border-bottom:1px dashed #eee;text-align:left}
.kv{font-weight:600;color:#444}
.controls{display:flex;gap:10px;justify-content:flex-end;margin-top:10px}
.btn{display:inline-block;padding:10px 14px;border-radius:8px;text-decoration:none;color:#fff;background:#2e7d32;font-weight:700}
.btn.light{background:#607d8b}
.btn.danger{background:#d32f2f}
.bukti img{max-width:100%;border-radius:10px;border:1px solid #ddd;cursor:pointer}
.modal{display:none;position:fixed;z-index:1000;padding-top:40px;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.9)}
.modal-content{margin:auto;display:block;width:90%;max-width:900px}
.close-modal{position:absolute;top:18px;right:28px;color:#fff;font-size:38px;cursor:pointer}
</style>
</head>
<body>

<nav style="
    background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
">
    <a href="../index.php" style="display: flex; align-items: center; gap: 1rem; text-decoration: none; color: white;">
        <img src="../images/RaungGo.png" alt="RaungGo Logo" style="height: 45px; width: auto;">
        <span style="font-weight: 700; font-size: 1.1rem;">Detail Transaksi</span>
    </a>
    
    <div style="display: flex; gap: 1rem; align-items: center;">
        <a href="../StatusBooking.php" style="
            color: white; 
            text-decoration: none; 
            font-weight: 600; 
            padding: 0.6rem 1.2rem; 
            background: rgba(255,255,255,0.2); 
            border-radius: 8px;
            transition: all 0.3s ease;
        " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            ← Status Booking
        </a>
        <span style="color: rgba(255,255,255,0.9); font-weight: 500;">
            👋 Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?>
        </span>
    </div>
</nav>
<div class="header-space"></div>

<div class="container-wrap">
    <div class="header">
        <div class="h-left">
            <?php if ($pesanan['status_pesanan'] === 'menunggu_pembayaran'): ?>
    <?php
        $createdTime = strtotime($pesanan['created_at']);
        $deadline = $createdTime + (24 * 60 * 60); // 24 jam
    ?>
    <div id="countdown-box" style="margin-top:10px; font-weight:600; color:#d32f2f;">
        <span id="countdown-text">Menghitung waktu...</span>
    </div>

    <script>
    var deadline = <?= $deadline * 1000 ?>; // convert to ms

    function updateCountdown() {
        var now = new Date().getTime();
        var distance = deadline - now;

        if (distance <= 0) {
            document.getElementById("countdown-text").innerHTML =
                "⛔ Waktu pembayaran telah habis — pesanan akan otomatis gagal.";
            return;
        }

        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdown-text").innerHTML =
            "⏳ Sisa waktu pembayaran: " + 
            hours + " jam " + minutes + " menit " + seconds + " detik";
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
    </script>
<?php endif; ?>

            <div class="h1">🧾 Detail Transaksi Pendakian</div>
            <div class="token">Kode Token: <?= htmlspecialchars($pesanan['kode_token']); ?></div>
            <div class="small">Tanggal Pesan: <?= formatWaktu($pesanan['tanggal_pesan']); ?></div>
        </div>
        <div>
            <div class="status <?= $statusClass; ?>"><?= $statusText; ?></div>

            <?php
            $ps_final = in_array($pesanan['status_pesanan'], ['berhasil','lunas']);
            $pb_ok = in_array($payment['status_pembayaran'] ?? '', ['terkonfirmasi','lunas']);
            if ($ps_final || $pb_ok): ?>
                <div style="margin-top:8px">
                    <a class="btn" href="cetak_bukti.php?pesanan_id=<?= $pesanan_id ?>" target="_blank">📄 Cetak Bukti (PDF)</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid">
        <!-- LEFT INFO — tidak diubah -->
        <div>
            <div class="card">
                <h3>Info Pendakian</h3>
                <table class="table">
                    <tr><th class="kv">Jalur</th><td><?= $pesanan['nama_jalur']; ?></td></tr>
                    <tr><th class="kv">Tarif Tiket / org</th><td>Rp <?= number_format($pesanan['tarif_tiket'],0,',','.'); ?></td></tr>
                    <tr><th class="kv">Tanggal Naik</th><td><?= date('d M Y', strtotime($pesanan['tanggal_pendakian'])); ?></td></tr>
                    <tr><th class="kv">Tanggal Turun</th><td><?= date('d M Y', strtotime($pesanan['tanggal_turun'])); ?></td></tr>
                    <tr><th class="kv">Jumlah Pendaki</th><td><?= intval($pesanan['jumlah_pendaki']); ?> orang</td></tr>
                </table>
            </div>

            <div class="card" style="margin-top:12px">
                <h3>Layanan & Harga</h3>
                <table class="table">
                    <tr><th class="kv">Guide</th><td><?= $guideText ?></td></tr>
                    <tr><th class="kv">Porter</th><td><?= $porterText ?></td></tr>
                    <tr><th class="kv">Ojek</th><td><?= $ojekText ?></td></tr>
                    <tr><th class="kv">Total</th><td><strong>Rp <?= number_format($pesanan['total_bayar'],0,',','.'); ?></strong></td></tr>
                </table>
            </div>

            <div class="card" style="margin-top:12px">
                <h3>Ketua Tim</h3>
                <table class="table">
                    <tr><th class="kv">Nama</th><td><?= $pesanan['nama_ketua'] ?></td></tr>
                    <tr><th class="kv">HP</th><td><?= $pesanan['telepon_ketua'] ?></td></tr>
                    <tr><th class="kv">Alamat</th><td><?= nl2br($pesanan['alamat_ketua']) ?></td></tr>
                    <tr><th class="kv">NIK</th><td><?= $pesanan['no_identitas'] ?></td></tr>
                </table>
            </div>

            <div class="card" style="margin-top:12px">
                <h3>Anggota Tim</h3>
                <?php if (count($anggota) == 0): ?>
                    <div>- Tidak ada anggota -</div>
                <?php else: foreach ($anggota as $a): ?>
                    <div class="anggota-item">
                        <strong><?= $a['nama'] ?></strong><br>
                        NIK: <?= $a['nik'] ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- RIGHT PAYMENT -->
        <div>
            <div class="card">
                <h3>Informasi Pembayaran</h3>
                <div>Status: <strong><?= $payment['status_pembayaran'] ?? 'Belum Ada Pembayaran'; ?></strong></div>

                <table class="table" style="margin-top:8px">
                    <tr><th class="kv">Metode</th><td><?= $payment['metode'] ?? '-'; ?></td></tr>
                    <tr><th class="kv">Jumlah</th><td><?= isset($payment['jumlah_bayar']) ? 'Rp ' . number_format($payment['jumlah_bayar'],0,',','.') : '-'; ?></td></tr>
                    <tr><th class="kv">Tanggal</th><td><?= formatWaktu($payment['tanggal_bayar'] ?? null); ?></td></tr>
                </table>

                <?php if (!empty($payment['bukti_bayar']) && file_exists("../uploads/bukti/" . $payment['bukti_bayar'])): ?>
                    <div class="bukti" style="margin-top:12px">
                        <div><strong>Bukti Pembayaran:</strong></div>
                        <img src="../uploads/bukti/<?= $payment['bukti_bayar']; ?>" onclick="zoomImage(this.src)">
                    </div>
                <?php endif; ?>

                <?php if (!empty($alasanDitolak) && ($payment['status_pembayaran'] ?? '') === 'ditolak'): ?>
                    <div style="margin-top:16px;color:#b71c1c;background:#ffebee;padding:12px;border-radius:8px">
                        <strong>Alasan Penolakan:</strong><br>
                        <?= nl2br(htmlspecialchars($alasanDitolak)); ?>
                    </div>
                <?php endif; ?>

                <!-- TOMBOL AKSI -->
                <div class="controls">
                    <a class="btn light" href="../StatusBooking.php">⬅ Kembali</a>

                    <?php
                    $ps = $pesanan['status_pesanan'];
                    $pb = $payment['status_pembayaran'] ?? '';

                    // Belum bayar sama sekali
                    if ($ps === 'menunggu_pembayaran'): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">Bayar Sekarang</a>

                    <?php
                    // Ditolak → upload ulang
                    elseif ($ps === 'menunggu_konfirmasi' && $pb === 'ditolak'): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">🔁 Upload Bukti Ulang</a>

                    <?php
                    // Menunggu konfirmasi tapi tidak ada payment
                    elseif ($ps === 'menunggu_konfirmasi' && !$payment): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">Bayar Sekarang</a>
                    <?php endif; ?>

                    <?php if (!in_array($ps, ['berhasil','dibatalkan','gagal'])): ?>
                        <a class="btn danger" href="#" onclick="confirmCancel(<?= $pesanan_id ?>)">Batalkan</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success_message): ?>
        <script>Swal.fire({icon:'success',title:'Berhasil',text:<?= json_encode($success_message) ?>});</script>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <script>Swal.fire({icon:'error',title:'Gagal',text:<?= json_encode($error_message) ?>});</script>
    <?php endif; ?>

</div>

<!-- Modal zoom -->
<div id="imageModal" class="modal" onclick="closeModal()">
    <span class="close-modal">&times;</span>
    <img class="modal-content" id="img01">
</div>

<script>
function zoomImage(src){
    document.getElementById("imageModal").style.display = "block";
    document.getElementById("img01").src = src;
    document.body.style.overflow = "hidden";
}
function closeModal(){
    document.getElementById("imageModal").style.display = "none";
    document.body.style.overflow = "";
}
// Batalkan pesanan
function confirmCancel(id){
    Swal.fire({
        icon:'warning',
        title:'Batalkan Pesanan?',
        text:'Pesanan yang dibatalkan tidak bisa dipulihkan.',
        showCancelButton:true,
        confirmButtonColor:'#d32f2f'
    }).then(res=>{
        if(res.isConfirmed){
            window.location='../backend/proses_batal.php?pesanan_id='+id;
        }
    });
}
</script>

</body>
</html>
