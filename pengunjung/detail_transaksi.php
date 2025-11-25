<?php
// pengunjung/detail_transaksi.php — DETAIL TRANSAKSI (VERSI FINAL)
// Menampilkan: pendakian (naik/turun), jalur, layanan, ketua, anggota, pembayaran + tombol cetak PDF (jika berhasil)
// + Logika: jika pembayaran ditolak & pesanan masih menunggu_konfirmasi -> tampilkan tombol upload ulang

date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../backend/koneksi.php';

// Ambil pesan PRG dari session
$success_message = $_SESSION['success_message'] ?? null;
$error_message   = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil parameter (id atau token)
$pesanan_id = isset($_GET['pesanan_id']) ? intval($_GET['pesanan_id']) : null;
$kode_token = isset($_GET['kode_token']) ? trim($_GET['kode_token']) : null;
if (!$pesanan_id && !$kode_token) {
    echo "<script>alert('Parameter pesanan tidak ditemukan.'); window.location='../StatusBooking.php';</script>";
    exit;
}

// Ambil data pesanan + pendakian + jalur + layanan (guide/porter/ojek)
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
$pesanan_id = intval($pesanan['pesanan_id']); // pastikan

// Ambil anggota dari pesanan_anggota (jika ada)
$anggota = [];
$stA = $conn->prepare("SELECT * FROM pesanan_anggota WHERE pesanan_id = ? ORDER BY anggota_id ASC");
$stA->bind_param("i", $pesanan_id);
$stA->execute();
$resA = $stA->get_result();
while ($r = $resA->fetch_assoc()) $anggota[] = $r;
$stA->close();

// Ambil histori pembayaran (terbaru dulu)
$payments = [];
$stP = $conn->prepare("SELECT * FROM pembayaran WHERE pesanan_id = ? ORDER BY tanggal_bayar DESC, pembayaran_id DESC");
$stP->bind_param("i", $pesanan_id);
$stP->execute();
$resP = $stP->get_result();
while ($p = $resP->fetch_assoc()) $payments[] = $p;
$stP->close();
$payment = $payments[0] ?? null;

// helper format waktu
function formatWaktu($dt){
    if (!$dt) return '-';
    try {
        $t = new DateTime($dt, new DateTimeZone('Asia/Jakarta'));
        return $t->format('d M Y, H:i') . ' WIB';
    } catch (Exception $e) {
        return $dt;
    }
}

// improved map status -> label + class
function map_status($ps_status, $pb_status){
    // Prioritaskan status pesanan (final)
    if ($ps_status === 'berhasil') return ['Pembayaran Dikonfirmasi / Berhasil', 'sukses'];
    if ($ps_status === 'gagal' || $ps_status === 'dibatalkan') return ['Pembayaran Ditolak / Gagal', 'gagal'];

    // Jika pesanan belum final, lihat status pembayaran terakhir
    if ($pb_status === 'terkonfirmasi') return ['Pembayaran Dikonfirmasi / Berhasil', 'sukses'];
    if ($pb_status === 'ditolak') return ['Pembayaran Ditolak / Dibatalkan', 'gagal'];
    if ($pb_status === 'pending') return ['Menunggu Konfirmasi Admin', 'verifikasi'];

    // Fallback berdasarkan pesanan
    if ($ps_status === 'menunggu_pembayaran') return ['Menunggu Pembayaran', 'pending'];
    if ($ps_status === 'menunggu_konfirmasi') return ['Menunggu Konfirmasi Admin', 'verifikasi'];

    return ['Menunggu Proses', 'pending'];
}
[$statusText, $statusClass] = map_status($pesanan['status_pesanan'] ?? null, $payment['status_pembayaran'] ?? null);

// total ringkasan layanan per-orang / per-grup
$guideText = !empty($pesanan['nama_guide']) ? ($pesanan['nama_guide'] . " (Rp " . number_format($pesanan['guide_tarif'] ?? 0,0,',','.') . ")") : '-';
$porterText = !empty($pesanan['porter_nama']) ? ($pesanan['porter_nama'] . " (Rp " . number_format($pesanan['porter_tarif'] ?? 0,0,',','.') . ")") : '-';
$ojekText   = !empty($pesanan['ojek_nama']) ? ($pesanan['ojek_nama'] . " (Rp " . number_format($pesanan['ojek_tarif'] ?? 0,0,',','.') . ")") : '-';

// tampilkan catatan alasan penolakan jika tersedia di pembayaran
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
/* clean premium styling */
body{font-family:'Poppins',system-ui,Arial; background:#f5faf5; color:#222; margin:0;}
/* ensure navbar included above content - keep consistent with other pages */
.header-space{height:72px} /* adjust to your navbar height */
.wrap{max-width:1000px;margin:20px auto;padding:28px;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.08);}
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
.anggota-list{margin-top:8px}
.anggota-item{padding:10px;border-radius:8px;background:#fff;border:1px solid #eee;margin-bottom:8px}
.controls{display:flex;gap:10px;justify-content:flex-end;margin-top:10px}
.btn{display:inline-block;padding:10px 14px;border-radius:8px;text-decoration:none;color:#fff;background:#2e7d32;font-weight:700}
.btn.light{background:#607d8b}
.btn.danger{background:#d32f2f}
.pay-card .muted{color:#666;font-size:0.95rem}
.bukti img{max-width:100%;border-radius:10px;border:1px solid #ddd;cursor:pointer}
.note{font-size:0.95rem;color:#666}
.small{font-size:0.9rem;color:#666}
@media(max-width:900px){.grid{grid-template-columns:1fr;}.h-left{margin-bottom:12px}}

/* Tambahkan kode ini ke dalam tag <style> yang sudah ada */
/* --- STYLING TAMBAHAN UNTUK MODAL (POPUP) --- */
.modal {
    display: none; /* Penting: Sembunyikan secara default */
    position: fixed; /* Terapkan pada seluruh viewport */
    z-index: 1000; /* Pastikan di atas konten lain */
    padding-top: 50px; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.9); /* Latar belakang hitam transparan */
}
.modal-content {
    margin: auto;
    display: block;
    width: 90%; 
    max-width: 900px; 
    /* Animasi zoom (opsional, tapi bagus) */
    animation-name: zoom;
    animation-duration: 0.6s;
}
.close-modal {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    transition: 0.3s;
    cursor: pointer;
}
.close-modal:hover, .close-modal:focus {
    color: #bbb;
    text-decoration: none;
    cursor: pointer;
}
@keyframes zoom {
  from {transform: scale(0.1)} 
  to {transform: scale(1)}
}
</style>
</head>
<body>

<!-- include same navbar used by other pages so header looks identical -->
<?php if (file_exists(__DIR__ . '/../includes/navbar_user.php')) include __DIR__ . '/../includes/navbar_user.php'; ?>

<!-- spacer so content not overlapped by navbar -->
<div class="header-space" aria-hidden="true"></div>

<div class="wrap">
    <div class="header">
        <div class="h-left">
            <div class="h1">🧾 Detail Transaksi Pendakian</div>
            <div class="token">Kode Token: <?= htmlspecialchars($pesanan['kode_token']); ?></div>
            <div class="small" style="margin-top:6px">Tanggal Pesan: <?= formatWaktu($pesanan['tanggal_pesan']); ?></div>
        </div>
        <div>
            <div class="status <?= $statusClass; ?>"><?= $statusText; ?></div>

            <!-- Cetak Bukti hanya tampil ketika status final sukses -->
            <?php if ($statusClass === 'sukses'): ?>
                <div style="margin-top:8px">
                    <a class="btn" href="cetak_bukti.php?pesanan_id=<?= $pesanan_id ?>" target="_blank">📄 Cetak Bukti (PDF)</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid">
        <!-- LEFT: detail utama -->
        <div>
            <div class="card">
                <h3 style="margin:0 0 8px">Info Pendakian</h3>
                <table class="table">
                    <tr><th class="kv">Jalur</th><td><?= htmlspecialchars($pesanan['nama_jalur'] ?? '-'); ?></td></tr>
                    <tr><th class="kv">Tarif Tiket / org</th><td>Rp <?= number_format($pesanan['tarif_tiket'] ?? 0,0,',','.'); ?></td></tr>
                    <tr><th class="kv">Tanggal Naik</th><td><?= htmlspecialchars($pesanan['tanggal_pendakian'] ? date('d M Y', strtotime($pesanan['tanggal_pendakian'])) : '-'); ?></td></tr>
                    <tr><th class="kv">Tanggal Turun</th><td><?= htmlspecialchars($pesanan['tanggal_turun'] ? date('d M Y', strtotime($pesanan['tanggal_turun'])) : '-'); ?></td></tr>
                    <tr><th class="kv">Jumlah Pendaki</th><td><?= intval($pesanan['jumlah_pendaki']); ?> orang</td></tr>
                </table>
                <?php if (!empty($pesanan['jalur_deskripsi'])): ?>
                    <div style="margin-top:10px" class="small"><?= nl2br(htmlspecialchars($pesanan['jalur_deskripsi'])); ?></div>
                <?php endif; ?>
            </div>

            <div class="card" style="margin-top:12px">
                <h3 style="margin:0 0 8px">Layanan & Rincian Harga</h3>
                <table class="table">
                    <tr><th class="kv">Guide (grup)</th><td><?= $guideText; ?></td></tr>
                    <tr><th class="kv">Porter (pilihan)</th><td><?= $porterText; ?></td></tr>
                    <tr><th class="kv">Ojek (pilihan)</th><td><?= $ojekText; ?></td></tr>
                    <tr><th class="kv">Total Bayar</th><td><strong>Rp <?= number_format($pesanan['total_bayar'] ?? 0,0,',','.'); ?></strong></td></tr>
                </table>
            </div>

            <div class="card" style="margin-top:12px">
                <h3 style="margin:0 0 8px">Ketua Tim</h3>
                <table class="table">
                    <tr><th class="kv">Nama</th><td><?= htmlspecialchars($pesanan['nama_ketua'] ?? '-'); ?></td></tr>
                    <tr><th class="kv">No. HP</th><td><?= htmlspecialchars($pesanan['telepon_ketua'] ?? '-'); ?></td></tr>
                    <tr><th class="kv">Alamat</th><td><?= nl2br(htmlspecialchars($pesanan['alamat_ketua'] ?? '-')); ?></td></tr>
                    <tr><th class="kv">NIK</th><td><?= htmlspecialchars($pesanan['no_identitas'] ?? '-'); ?></td></tr>
                </table>
            </div>

            <div class="card" style="margin-top:12px">
                <h3 style="margin:0 0 8px">Anggota Tim (<?= count($anggota); ?>)</h3>
                <div class="anggota-list">
                    <?php if (count($anggota)===0): ?>
                        <div class="anggota-item">- Tidak ada data anggota / hanya ketua terdaftar -</div>
                    <?php else: foreach ($anggota as $a): ?>
                        <div class="anggota-item">
                            <div style="font-weight:700"><?= htmlspecialchars($a['nama']); ?></div>
                            <div style="font-size:0.95rem;color:#555">
                                NIK: <?= htmlspecialchars($a['nik']); ?>
                                <?php if (isset($a['porter_id'])): ?> — Porter: <?= $a['porter_id'] ? 'Ya' : 'Tidak'; ?><?php endif; ?>
                                <?php if (isset($a['ojek_id'])): ?> — Ojek: <?= $a['ojek_id'] ? 'Ya' : 'Tidak'; ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: pembayaran & bukti -->
        <div>
            <div class="card pay-card">
                <h3 style="margin:0 0 8px">Informasi Pembayaran</h3>
                <div class="muted">Status pembayaran: <strong><?= htmlspecialchars($payment['status_pembayaran'] ?? 'Belum Ada Pembayaran'); ?></strong></div>

                <table class="table" style="margin-top:8px">
                    <tr><th class="kv">Metode</th><td><?= htmlspecialchars($payment['metode'] ?? '-'); ?></td></tr>
                    <tr><th class="kv">Jumlah Bayar</th><td><?= isset($payment['jumlah_bayar']) ? 'Rp ' . number_format($payment['jumlah_bayar'],0,',','.') : '-'; ?></td></tr>
                    <tr><th class="kv">Tanggal Bayar</th><td><?= formatWaktu($payment['tanggal_bayar'] ?? null); ?></td></tr>
                </table>

              // KODE BARU (GANTI SELURUH BLOK DI ATAS)
<?php if (!empty($payment['bukti_bayar']) && file_exists(__DIR__ . '/../uploads/bukti/' . $payment['bukti_bayar'])): ?>
    <div class="bukti" style="margin-top:12px;">
        <div style="font-weight:700;margin-bottom:6px">Bukti Pembayaran</div>
        <img 
            src="../uploads/bukti/<?= htmlspecialchars($payment['bukti_bayar']); ?>" 
            alt="Bukti Pembayaran"
            onclick="zoomImage(this.src)" 
            style="max-width:100%;border-radius:10px;border:1px solid #ddd;cursor:pointer"
        >
        <div style="font-size:0.9rem;color:#666;margin-top:6px">Klik gambar untuk memperbesar.</div>
    </div>
<?php else: ?>
                    <div style="margin-top:12px;color:#777">Belum ada bukti pembayaran yang diunggah.</div>
                <?php endif; ?>

                <?php if (!empty($alasanDitolak) && ($payment['status_pembayaran'] ?? '') === 'ditolak'): ?>
                    <div style="margin-top:12px;color:#a94442;background:#fbeaea;padding:10px;border-radius:8px;border:1px solid #f2c7c7">
                        <strong>Alasan penolakan:</strong>
                        <div class="small"><?= nl2br(htmlspecialchars($alasanDitolak)); ?></div>
                    </div>
                <?php endif; ?>

                <div class="controls" style="margin-top:14px">
                    <a class="btn light" href="../StatusBooking.php">⬅️ Kembali</a>

                    <?php
                    // Determine which action button to show:
                    $ps = $pesanan['status_pesanan'] ?? '';
                    $pb = $payment['status_pembayaran'] ?? '';

                    // Case: belum bayar sama sekali
                    if ($ps === 'menunggu_pembayaran'): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">Bayar Sekarang</a>

                    <?php
                    // Case: pembayaran ditolak, pesanan masih menunggu konfirmasi -> allow upload ulang
                    elseif ($ps === 'menunggu_konfirmasi' && $pb === 'ditolak'): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">🔁 Upload Bukti Ulang</a>

                    <?php
                    // Case: pesanan menunggu konfirmasi but no payment record -> prompt to pay
                    elseif ($ps === 'menunggu_konfirmasi' && !$payment): ?>
                        <a class="btn" href="pembayaran.php?pesanan_id=<?= $pesanan_id ?>">Bayar Sekarang</a>

                    <?php endif; ?>
                </div>
            </div>

            <?php if (count($payments) > 0): ?>
            <div class="card" style="margin-top:12px">
                <h3 style="margin:0 0 8px">Riwayat Pembayaran</h3>
                <table class="table">
                    <tr><th class="kv">Waktu</th><th class="kv">Jumlah</th><th class="kv">Status</th></tr>
                    <?php foreach ($payments as $pp): ?>
                        <tr>
                            <td><?= formatWaktu($pp['tanggal_bayar']); ?></td>
                            <td><?= 'Rp ' . number_format($pp['jumlah_bayar'],0,',','.'); ?></td>
                            <td><?= htmlspecialchars($pp['status_pembayaran']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <script>Swal.fire({icon:'success',title:'Berhasil',text:<?= json_encode($success_message) ?>,confirmButtonColor:'#43a047'});</script>
    <?php elseif (!empty($error_message)): ?>
        <script>Swal.fire({icon:'error',title:'Gagal',text:<?= json_encode($error_message) ?>,confirmButtonColor:'#e53935'});</script>
    <?php endif; ?>

</div>
<div id="imageModal" class="modal">
    <span class="close-modal" onclick="document.getElementById('imageModal').style.display='none'">&times;</span>

    <img class="modal-content" id="img01">
</div>

<footer style="text-align:center;margin-top:20px;color:#888">© 2025 Tahura Raden Soerjo</footer>

<script>
    // Dapatkan elemen modal
    var modal = document.getElementById("imageModal");

    // Dapatkan elemen gambar di dalam modal
    var modalImg = document.getElementById("img01");

    // Fungsi yang dipanggil saat gambar bukti pembayaran diklik
    function zoomImage(imageSrc) {
        // Tampilkan modal
        modal.style.display = "block"; 
        // Atur sumber gambar modal ke gambar yang diklik
        modalImg.src = imageSrc; 
    }
    
    // Ketika pengguna mengklik di latar belakang modal, tutup modal
    window.onclick = function(event) {
        // Cek apakah target klik adalah elemen modal itu sendiri (latar belakang)
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

<footer style="text-align:center;margin-top:20px;color:#888">© 2025 Tahura Raden Soerjo</footer>

</body>
</html>
