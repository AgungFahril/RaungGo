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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* BASE STYLES */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body{
    font-family:'Poppins',sans-serif; 
    background:#f5faf5; 
    color:#222; 
    margin:0;
    padding:0;
}

/* NAVBAR */
nav{
    background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

nav a img{
    height: 45px;
    width: auto;
}

.nav-left{
    display: flex;
    align-items: center;
    gap: 1rem;
    text-decoration: none;
    color: white;
}

.nav-left span{
    font-weight: 700;
    font-size: 1.1rem;
}

.nav-right{
    display: flex;
    gap: 1rem;
    align-items: center;
}

.nav-link{
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 0.6rem 1.2rem;
    background: rgba(255,255,255,0.2);
    border-radius: 8px;
    transition: all 0.3s ease;
    white-space:nowrap;
}

.nav-link:hover{
    background: rgba(255,255,255,0.3);
}

.nav-user{
    color: rgba(255,255,255,0.9);
    font-weight: 500;
}

.header-space{
    height:80px;
}

/* CONTAINER */
.container-wrap{
    max-width:1000px;
    margin:20px auto;
    padding:28px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 8px 30px rgba(0,0,0,0.08);
}

.header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
}

.h-left{
    display:flex;
    flex-direction:column;
    flex:1;
    min-width:250px;
}

.h1{
    color:#1b5e20;
    font-size:22px;
    margin:0 0 8px;
    font-weight:700;
}

.token{
    font-weight:700;
    color:#37474f;
    font-size:15px;
}

.small{
    font-size:13px;
    color:#666;
    margin-top:4px;
}

#countdown-box{
    margin-top:12px;
    font-weight:600;
    color:#d32f2f;
    font-size:14px;
    padding:10px;
    background:#ffebee;
    border-radius:8px;
}

.status{
    padding:10px 16px;
    border-radius:8px;
    color:#fff;
    font-weight:700;
    white-space:nowrap;
    font-size:14px;
}

.status.pending{background:#f39c12}
.status.verifikasi{background:#3498db}
.status.sukses{background:#43a047}
.status.gagal{background:#e53935}

.grid{
    display:grid;
    grid-template-columns:1fr 380px;
    gap:20px;
    margin-top:20px;
}

.card{
    background:#fafafa;
    padding:18px;
    border-radius:10px;
    border:1px solid #e0e0e0;
    margin-bottom:16px;
}

.card h3{
    margin:0 0 14px;
    color:#2e7d32;
    font-size:17px;
    font-weight:700;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th,.table td{
    padding:10px 8px;
    border-bottom:1px dashed #ddd;
    text-align:left;
    font-size:14px;
}

.table th{
    width:40%;
    font-weight:600;
    color:#555;
}

.table tr:last-child th,
.table tr:last-child td{
    border-bottom:none;
}

.anggota-item{
    padding:12px;
    background:#fff;
    border-radius:8px;
    margin-bottom:10px;
    border:1px solid #e0e0e0;
}

.anggota-item strong{
    color:#2e7d32;
}

.bukti{
    margin-top:12px;
}

.bukti img{
    max-width:100%;
    border-radius:10px;
    border:1px solid #ddd;
    cursor:pointer;
    margin-top:8px;
    transition:transform 0.3s ease;
}

.bukti img:hover{
    transform:scale(1.02);
}

/* CONTROLS & BUTTONS */
.controls{
    display:flex;
    gap:12px;
    justify-content:flex-end;
    margin-top:18px;
    flex-wrap:wrap;
}

.btn{
    display:inline-block;
    padding:14px 20px;
    border-radius:10px;
    text-decoration:none;
    color:#fff;
    background:#2e7d32;
    font-weight:700;
    font-size:15px;
    text-align:center;
    white-space:nowrap;
    transition:all 0.3s ease;
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
    min-width:140px;
}

.btn:hover{
    background:#1b5e20;
    transform:translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.18);
}

.btn.light{
    background:#607d8b;
}

.btn.light:hover{
    background:#455a64;
}

.btn.danger{
    background:#d32f2f;
}

.btn.danger:hover{
    background:#b71c1c;
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    z-index:1000;
    padding-top:40px;
    left:0;
    top:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.9);
}

.modal-content{
    margin:auto;
    display:block;
    width:90%;
    max-width:900px;
}

.close-modal{
    position:absolute;
    top:18px;
    right:28px;
    color:#fff;
    font-size:38px;
    cursor:pointer;
    transition:transform 0.3s ease;
}

.close-modal:hover{
    transform:scale(1.1);
}

/* RESPONSIVE MOBILE */
@media screen and (max-width: 968px) {
    nav{
        padding: 0.8rem 1rem;
        flex-wrap:wrap;
    }
    
    nav a img{
        height: 35px;
    }
    
    .nav-left span{
        font-size: 0.95rem;
    }
    
    .nav-right{
        gap: 0.6rem;
        flex-wrap:wrap;
    }
    
    .nav-link{
        padding: 0.5rem 0.8rem;
        font-size: 13px;
    }
    
    .nav-user{
        font-size: 13px;
    }
    
    .header-space{
        height:70px;
    }
    
    .container-wrap{
        margin:15px;
        padding:20px;
    }
    
    .header{
        flex-direction:column;
        align-items:flex-start;
    }
    
    .h-left{
        min-width:100%;
    }
    
    .h1{
        font-size:19px;
    }
    
    .token{
        font-size:14px;
    }
    
    #countdown-box{
        font-size:13px;
        padding:10px;
    }
    
    /* GRID 1 kolom */
    .grid{
        grid-template-columns:1fr;
        gap:0;
    }
    
    .card{
        padding:16px;
        margin-bottom:14px;
    }
    
    .card h3{
        font-size:16px;
    }
    
    .table th,.table td{
        padding:8px 6px;
        font-size:13px;
    }
    
    .table th{
        width:38%;
    }
    
    /* BUTTON CENTERED FULL WIDTH */
    .controls{
        flex-direction:column;
        justify-content:center;
        align-items:stretch;
        gap:10px;
        margin-top:16px;
        padding:0;
    }
    
    .btn{
        width:100%;
        padding:16px 20px;
        font-size:15px;
        min-height:54px;
        display:block;
    }
}

@media screen and (max-width: 480px) {
    nav{
        padding: 0.7rem 0.8rem;
    }
    
    .nav-left{
        gap: 0.6rem;
    }
    
    .nav-left span{
        display:none;
    }
    
    nav a img{
        height: 32px;
    }
    
    .container-wrap{
        margin:10px;
        padding:16px;
    }
    
    .h1{
        font-size:17px;
    }
    
    .token{
        font-size:13px;
    }
    
    .small{
        font-size:12px;
    }
    
    #countdown-box{
        font-size:12px;
    }
    
    .status{
        font-size:13px;
        padding:8px 12px;
    }
    
    .card{
        padding:14px;
    }
    
    .table th,.table td{
        padding:7px 5px;
        font-size:12px;
    }
    
    .btn{
        font-size:14px;
        min-height:52px;
    }
}
</style>

</head>
<body>

<nav>
    <a href="../index.php" class="nav-left">
        <img src="../images/RaungGo.png" alt="RaungGo Logo">
        <span>Detail Transaksi</span>
    </a>
    
    <div class="nav-right">
        <a href="../StatusBooking.php" class="nav-link">
            ← Status Booking
        </a>
        <span class="nav-user">
            👋 Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?>
        </span>
    </div>
</nav>

<div class="header-space"></div>

<div class="container-wrap">
    <div class="header">
        <div class="h-left">
            <div class="h1">🧾 Detail Transaksi Pendakian</div>
            <div class="token">Kode Token: <?= htmlspecialchars($pesanan['kode_token']); ?></div>
            <div class="small">Tanggal Pesan: <?= formatWaktu($pesanan['tanggal_pesan']); ?></div>
            
            <?php if ($pesanan['status_pesanan'] === 'menunggu_pembayaran'): ?>
                <?php
                    $createdTime = strtotime($pesanan['created_at']);
                    $deadline = $createdTime + (24 * 60 * 60);
                ?>
                <div id="countdown-box">
                    <span id="countdown-text">Menghitung waktu...</span>
                </div>

                <script>
                var deadline = <?= $deadline * 1000 ?>;

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
        </div>
        <div>
            <div class="status <?= $statusClass; ?>"><?= $statusText; ?></div>

            <?php
            $ps_final = in_array($pesanan['status_pesanan'], ['berhasil','lunas']);
            $pb_ok = in_array($payment['status_pembayaran'] ?? '', ['terkonfirmasi','lunas']);
            if ($ps_final || $pb_ok): ?>
                <div style="margin-top:10px">
                    <a class="btn" href="cetak_bukti.php?pesanan_id=<?= $pesanan_id ?>" target="_blank">📄 Cetak Bukti (PDF)</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid">
        <!-- LEFT INFO -->
        <div>
            <div class="card">
                <h3>Info Pendakian</h3>
                <table class="table">
                    <tr><th>Jalur</th><td><?= $pesanan['nama_jalur']; ?></td></tr>
                    <tr><th>Tarif Tiket / org</th><td>Rp <?= number_format($pesanan['tarif_tiket'],0,',','.'); ?></td></tr>
                    <tr><th>Tanggal Naik</th><td><?= date('d M Y', strtotime($pesanan['tanggal_pendakian'])); ?></td></tr>
                    <tr><th>Tanggal Turun</th><td><?= date('d M Y', strtotime($pesanan['tanggal_turun'])); ?></td></tr>
                    <tr><th>Jumlah Pendaki</th><td><?= intval($pesanan['jumlah_pendaki']); ?> orang</td></tr>
                </table>
            </div>

            <div class="card">
                <h3>Layanan & Harga</h3>
                <table class="table">
                    <tr><th>Guide</th><td><?= $guideText ?></td></tr>
                    <tr><th>Porter</th><td><?= $porterText ?></td></tr>
                    <tr><th>Ojek</th><td><?= $ojekText ?></td></tr>
                    <tr><th>Total</th><td><strong>Rp <?= number_format($pesanan['total_bayar'],0,',','.'); ?></strong></td></tr>
                </table>
            </div>

            <div class="card">
                <h3>Ketua Tim</h3>
                <table class="table">
                    <tr><th>Nama</th><td><?= $pesanan['nama_ketua'] ?></td></tr>
                    <tr><th>HP</th><td><?= $pesanan['telepon_ketua'] ?></td></tr>
                    <tr><th>Alamat</th><td><?= nl2br($pesanan['alamat_ketua']) ?></td></tr>
                    <tr><th>NIK</th><td><?= $pesanan['no_identitas'] ?></td></tr>
                </table>
            </div>

            <div class="card">
                <h3>Anggota Tim</h3>
                <?php if (count($anggota) == 0): ?>
                    <div style="color:#999">- Tidak ada anggota -</div>
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
                <div style="margin-bottom:10px">
                    Status: <strong><?= $payment['status_pembayaran'] ?? 'Belum Ada Pembayaran'; ?></strong>
                </div>

                <table class="table">
                    <tr><th>Metode</th><td><?= $payment['metode'] ?? '-'; ?></td></tr>
                    <tr><th>Jumlah</th><td><?= isset($payment['jumlah_bayar']) ? 'Rp ' . number_format($payment['jumlah_bayar'],0,',','.') : '-'; ?></td></tr>
                    <tr><th>Tanggal</th><td><?= formatWaktu($payment['tanggal_bayar'] ?? null); ?></td></tr>
                </table>

                <?php if (!empty($payment['bukti_bayar']) && file_exists("../uploads/bukti/" . $payment['bukti_bayar'])): ?>
                    <div class="bukti">
                        <div style="font-weight:600;margin-top:12px;color:#555">Bukti Pembayaran:</div>
                        <img src="../uploads/bukti/<?= $payment['bukti_bayar']; ?>" alt="Bukti Pembayaran" onclick="zoomImage(this.src)">
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

                    if ($ps === 'menunggu_pembayaran'): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">💳 Bayar Sekarang</a>

                    <?php elseif ($ps === 'menunggu_konfirmasi' && $pb === 'ditolak'): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">🔁 Upload Bukti Ulang</a>

                    <?php elseif ($ps === 'menunggu_konfirmasi' && !$payment): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">💳 Bayar Sekarang</a>
                    <?php endif; ?>

                    <?php if (!in_array($ps, ['berhasil','dibatalkan','gagal'])): ?>
                        <a class="btn danger" href="#" onclick="confirmCancel(<?= $pesanan_id ?>); return false;">🗑️ Batalkan Pesanan</a>
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
    <img class="modal-content" id="img01" alt="Zoom Image">
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

function confirmCancel(id){
    Swal.fire({
        icon:'warning',
        title:'Batalkan Pesanan?',
        text:'Pesanan yang dibatalkan tidak bisa dipulihkan.',
        showCancelButton:true,
        confirmButtonText:'Ya, Batalkan',
        cancelButtonText:'Tidak',
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
