<?php
session_start();
include '../backend/koneksi.php';
include 'check_data_lengkap.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=booking");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama'] ?? '';

$qKetua = $conn->prepare("SELECT nik, no_hp, alamat FROM pendaki_detail WHERE user_id = ?");
$qKetua->bind_param("i", $user_id);
$qKetua->execute();
$data_ketua = $qKetua->get_result()->fetch_assoc();
$qKetua->close();

// FIXED: Triple fallback untuk mobile (GET > SESSION > Error)
$id_pendakian = intval($_GET['pid'] ?? $_SESSION['selected_pendakian'] ?? 0);
$jumlah_pendaki = intval($_GET['jp'] ?? $_SESSION['jumlah_pendaki'] ?? 0);

if (!$id_pendakian || !$jumlah_pendaki) {
    header("Location: kuota.php?error=pilih_jalur");
    exit;
}

// Update session dari GET (backup mobile)
$_SESSION['selected_pendakian'] = $id_pendakian;
$_SESSION['jumlah_pendaki'] = $jumlah_pendaki;

$qJalur = $conn->prepare("
    SELECT p.pendakian_id, j.jalur_id, j.nama_jalur, j.tarif_tiket, j.deskripsi, p.tanggal_pendakian
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
$jalur_id = $data_jalur['jalur_id'];

$guide = [];
$porter = [];
$ojek = [];

$stmt = $conn->prepare("SELECT guide_id, nama_guide, IFNULL(tarif,0) AS tarif FROM guide WHERE jalur_id = ? AND available = 1");
if ($stmt) {
    $stmt->bind_param("i", $jalur_id);
    $stmt->execute();
    $guide = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$stmt = $conn->prepare("SELECT porter_id, nama_porter, IFNULL(tarif,0) AS tarif FROM porter WHERE jalur_id = ? AND available = 1");
if ($stmt) {
    $stmt->bind_param("i", $jalur_id);
    $stmt->execute();
    $porter = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$stmt = $conn->prepare("SELECT ojek_id, nama_ojek, IFNULL(tarif,0) AS tarif FROM ojek WHERE jalur_id = ? AND available = 1");
if ($stmt) {
    $stmt->bind_param("i", $jalur_id);
    $stmt->execute();
    $ojek = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$tarif_per_orang = intval($data_jalur['tarif_tiket']);
$total_tiket = $tarif_per_orang * $jumlah_pendaki;

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
body{font-family:'Poppins',sans-serif;background:#f3f6f3 url('../images/Gunung_Raung.jpg') no-repeat center top;background-size:cover;color:#333}
.booking-wrapper{max-width:980px;margin:90px auto;background:rgba(255,255,255,0.98);border-radius:12px;padding:24px 26px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
.info-card{background:#e8f5e9;border-left:6px solid #43a047;border-radius:10px;padding:14px 18px;margin-bottom:18px}
fieldset{border:1px solid #c8e6c9;border-radius:10px;padding:14px;margin-bottom:14px;background:#fbfff9}
legend{font-weight:700;color:#2e7d32}
label{font-weight:600;margin-top:8px;display:block}
input,select,textarea{width:100%;padding:9px;border-radius:6px;border:1px solid #ccc;box-sizing:border-box}
.btn-submit{width:100%;background:#43a047;color:#fff;border:none;border-radius:8px;padding:12px;font-weight:700;cursor:pointer}
.small{font-size:.9rem;color:#666}
.breakdown{background:#fff;border-radius:8px;padding:12px;border:1px solid #eee;margin-top:12px}
.row{display:flex;gap:12px}
.col{flex:1}
.file-input{display:block;margin-top:8px}
.note{font-size:0.95rem;color:#555}
.error{color:#e53935;font-weight:700}
.anggota-group{margin-bottom:12px;padding:10px;border-radius:8px;border:1px solid #eee;background:#fff}
.kembali{display:inline-block;margin-bottom:12px;background:#9e9e9e;color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none}
.kembali:hover{background:#7e7e7e}
.card{background:#fff;border-radius:8px;border:1px solid #eee;padding:12px}

/* === RESPONSIVE MOBILE === */
@media screen and (max-width: 768px) {
    /* Booking wrapper */
    .booking-wrapper {
        padding: 18px 14px !important;
        margin: 70px auto 20px !important;
    }
    
    /* Row flexbox jadi vertical */
    .row {
        flex-direction: column !important;
        gap: 0 !important;
    }
    
    .col {
        width: 100% !important;
        margin-bottom: 12px;
    }
    
    /* Fieldset */
    fieldset {
        padding: 12px !important;
        margin-bottom: 12px;
    }
    
    legend {
        font-size: 15px;
    }
    
    /* Labels */
    label {
        font-size: 14px !important;
        margin-top: 8px;
        margin-bottom: 6px !important;
    }
    
    /* Input fields */
    input[type="text"],
    input[type="number"],
    input[type="date"],
    select,
    textarea {
        width: 100% !important;
        padding: 12px 15px !important;
        font-size: 14px !important;
        border: 2px solid #e0e0e0 !important;
        border-radius: 8px !important;
        margin-bottom: 5px !important;
    }
    
    input:focus,
    select:focus,
    textarea:focus {
        border-color: #2e7d32 !important;
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1) !important;
        outline: none !important;
    }
    
    /* File upload */
    input[type="file"] {
        padding: 10px !important;
        font-size: 13px !important;
        background: #f5f5f5 !important;
        border: 2px dashed #2e7d32 !important;
        border-radius: 8px !important;
    }
    
    input[type="file"]::file-selector-button {
        background: #2e7d32 !important;
        color: white !important;
        border: none !important;
        padding: 10px 20px !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        margin-right: 10px !important;
    }
    
    /* Anggota group - Override inline styles */
    .anggota-group > div[style*="display:flex"] {
        display: flex !important;
        flex-direction: column !important;
        gap: 0 !important;
    }
    
    .anggota-group > div[style*="display:flex"] > div {
        flex: 1 !important;
        width: 100% !important;
        margin-bottom: 12px !important;
    }
    
    .anggota-group input,
    .anggota-group select {
        width: 100% !important;
    }
    
    .anggota-group h4 {
        font-size: 15px !important;
        color: #2e7d32 !important;
        margin-bottom: 12px;
    }
    
    /* Breakdown (Rincian Harga) */
    .breakdown {
        background: #f9fdf9 !important;
        border: 2px solid #e8f5e9 !important;
        border-radius: 12px !important;
        padding: 16px !important;
        margin: 20px 0 !important;
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.1) !important;
    }
    
    .breakdown > div {
        flex-wrap: wrap !important;
        font-size: 14px;
        padding: 8px 0;
    }
    
    /* Submit button */
    .btn-submit {
        background: linear-gradient(135deg, #2e7d32, #1b5e20) !important;
        font-size: 16px !important;
        padding: 16px 24px !important;
        border-radius: 10px !important;
        margin-top: 20px !important;
        box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }
    
    .btn-submit:active {
        transform: translateY(2px) !important;
        box-shadow: 0 3px 10px rgba(46, 125, 50, 0.3) !important;
    }
    
    /* Info card */
    .info-card {
        padding: 12px 14px !important;
        font-size: 14px !important;
    }
    
    /* Kembali button */
    .kembali {
        font-size: 14px;
        padding: 10px 14px;
    }
    
    /* Helper text */
    .small, .note {
        font-size: 12px !important;
    }
}

/* Small mobile */
@media screen and (max-width: 375px) {
    .booking-wrapper {
        padding: 14px 10px !important;
        margin: 60px auto 15px !important;
    }
    
    input, select, textarea {
        padding: 10px 12px !important;
        font-size: 13px !important;
    }
    
    .btn-submit {
        padding: 14px 20px !important;
        font-size: 15px !important;
    }
}
</style>

</head>
<body>
<header><?php include '../includes/navbar_user.php'; ?></header>

<main class="booking-wrapper">
    <a class="kembali" href="kuota.php">⬅ Kembali ke Cek Kuota</a>
    <h2 style="text-align:center;color:#2e7d32;margin-bottom:10px">🧗 Form Booking Pendakian</h2>

    <div class="info-card">
        <p><strong>Jalur:</strong> <?= htmlspecialchars($data_jalur['nama_jalur']); ?></p>
        <p><strong>Tanggal Pendakian:</strong> <?= htmlspecialchars($data_jalur['tanggal_pendakian']); ?></p>
        <p class="small"><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($data_jalur['deskripsi'])); ?></p>
        <hr style="border:none;border-top:1px solid #ddd;margin:8px 0;">
        <p><strong>Jumlah Pendaki (terdaftar):</strong> <?= $jumlah_pendaki; ?> orang</p>
        <p><strong>Tarif per Orang:</strong> Rp<?= number_format($tarif_per_orang,0,',','.'); ?></p>
    </div>

    <form method="POST" action="../backend/proses_booking.php" enctype="multipart/form-data" id="bookingForm" onsubmit="return validateBeforeSubmit();">
        <!-- FIXED: Hidden inputs dengan backup untuk mobile -->
        <input type="hidden" name="pendakian_id" value="<?= htmlspecialchars($id_pendakian); ?>">
        <input type="hidden" name="pendakian_id_backup" value="<?= htmlspecialchars($id_pendakian); ?>">
        <input type="hidden" id="jumlah_pendaki" name="jumlah_pendaki" value="<?= $jumlah_pendaki; ?>">
        <input type="hidden" name="jumlah_pendaki_backup" value="<?= $jumlah_pendaki; ?>">
        <input type="hidden" id="hidden_total" name="total_bayar" value="<?= $total_tiket; ?>">

        <!-- DATA KETUA -->
        <fieldset>
            <legend>👤 Data Ketua Pendaki</legend>
            <label>Nama Ketua</label>
            <input type="text" name="nama_ketua" value="<?= htmlspecialchars($user_name); ?>" readonly>

            <div class="row">
                <div class="col">
                    <label>NIK (16 digit)</label>
                    <input type="text" name="no_identitas_ketua" id="nik_ketua" pattern="\d{16}" minlength="16" maxlength="16" value="<?= htmlspecialchars($data_ketua['nik'] ?? ''); ?>" required placeholder="16 digit angka">
                </div>
                <div class="col">
                    <label>No. HP</label>
                    <input type="text" name="telepon_ketua" value="<?= htmlspecialchars($data_ketua['no_hp'] ?? ''); ?>" required>
                </div>
            </div>

            <label>Alamat</label>
            <textarea name="alamat_ketua" rows="2" required><?= htmlspecialchars($data_ketua['alamat'] ?? ''); ?></textarea>

            <div class="row">
                <div class="col">
                    <label>Upload KTP Ketua (jpg/png/pdf) <span class="small">*wajib</span></label>
                    <input type="file" name="ktp_ketua" id="ktp_ketua" accept=".jpg,.jpeg,.png,.pdf" class="file-input" required>
                </div>
                <div class="col">
                    <label>Upload Surat Keterangan Sehat Ketua (jpg/png/pdf) <span class="small">*wajib</span></label>
                    <input type="file" name="sehat_ketua" id="sehat_ketua" accept=".jpg,.jpeg,.png,.pdf" class="file-input" required>
                </div>
            </div>

            <div class="row" style="margin-top:10px">
                <div class="col">
                    <label>Pilih Porter untuk Ketua (opsional)</label>
                    <select name="porter_id" id="ketua_porter_id">
                        <option value="" data-price="0">-- Tidak Menggunakan Porter --</option>
                        <?php foreach ($porter as $p): ?>
                            <option value="<?= $p['porter_id'] ?>" data-price="<?= intval($p['tarif']) ?>">
                                <?= htmlspecialchars($p['nama_porter']) ?> – Rp<?= number_format(intval($p['tarif']),0,',','.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <label>Pilih Ojek untuk Ketua (opsional)</label>
                    <select name="ojek_id" id="ketua_ojek_id">
                        <option value="" data-price="0">-- Tidak Menggunakan Ojek --</option>
                        <?php foreach ($ojek as $o): ?>
                            <option value="<?= $o['ojek_id'] ?>" data-price="<?= intval($o['tarif']) ?>">
                                <?= htmlspecialchars($o['nama_ojek']) ?> – Rp<?= number_format(intval($o['tarif']),0,',','.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>🧭 Guide (layanan kelompok)</legend>
            <p class="note">Guide bersifat per-grup. Jika jalur <strong>Kalibaru</strong>, memilih guide <span class="error">WAJIB</span>.</p>
            <label>Pilih Guide</label>
            <select name="guide_id" id="guideSelect" <?= (strtolower($data_jalur['nama_jalur']) === 'kalibaru') ? 'required' : '' ?>>
                <option value="" data-price="0">-- Tanpa Guide --</option>
                <?php foreach ($guide as $g): ?>
                    <option value="<?= $g['guide_id'] ?>" data-price="<?= intval($g['tarif']) ?>">
                        <?= htmlspecialchars($g['nama_guide']) ?> – Rp<?= number_format(intval($g['tarif']),0,',','.') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </fieldset>

        <?php if ($jumlah_pendaki > 1): ?>
        <fieldset>
            <legend>🧑 Anggota Tim Pendaki (<?= $jumlah_pendaki - 1; ?> orang)</legend>
            <div id="anggotaContainer"></div>
        </fieldset>
        <?php endif; ?>

        <div class="breakdown" id="breakdown">
            <div class="note"><strong>Rincian Harga</strong></div>

            <div style="display:flex;justify-content:space-between;margin-top:8px">
                <div>Tarif per Orang</div><div id="bd_tarif">Rp<?= number_format($tarif_per_orang,0,',','.'); ?></div>
            </div>

            <div style="display:flex;justify-content:space-between">
                <div>Total Tiket (<?= $jumlah_pendaki ?> x)</div><div id="bd_total_tiket">Rp<?= number_format($total_tiket,0,',','.'); ?></div>
            </div>

            <div style="display:flex;justify-content:space-between">
                <div>Harga Guide (grup)</div><div id="bd_guide">Rp0</div>
            </div>

            <div style="display:flex;justify-content:space-between">
                <div>Total Porter (semua orang)</div><div id="bd_porter">Rp0</div>
            </div>

            <div style="display:flex;justify-content:space-between">
                <div>Total Ojek (semua orang)</div><div id="bd_ojek">Rp0</div>
            </div>

            <hr style="margin:8px 0">
            <div style="display:flex;justify-content:space-between;font-weight:700">
                <div>Total Bayar</div><div id="bd_total_akhir" style="color:#e91e63">Rp<?= number_format($total_tiket,0,',','.'); ?></div>
            </div>
        </div>

        <div style="margin-top:12px">
            <button type="button" class="btn-submit" onclick="konfirmasiBooking()">Kirim Booking</button>
        </div>
    </form>
</main>

<script>
function numberFormat(n){return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,".")}
function pickPrice(selectEl){
    if(!selectEl)return 0;
    const opt=selectEl.options[selectEl.selectedIndex];
    return parseInt(opt?.dataset?.price||0);
}

const tarifPerOrang=<?=json_encode($tarif_per_orang)?>;
const jumlahPendaki=<?=json_encode($jumlah_pendaki)?>;
const jalurName=<?=json_encode($data_jalur['nama_jalur'])?>;

document.addEventListener('DOMContentLoaded',()=>{
    const guideSelect=document.getElementById('guideSelect');
    const bd_tarif=document.getElementById('bd_tarif');
    const bd_total_tiket=document.getElementById('bd_total_tiket');
    const bd_guide=document.getElementById('bd_guide');
    const bd_porter=document.getElementById('bd_porter');
    const bd_ojek=document.getElementById('bd_ojek');
    const bd_total_akhir=document.getElementById('bd_total_akhir');
    const hiddenTotal=document.getElementById('hidden_total');

    bd_tarif.textContent='Rp'+numberFormat(tarifPerOrang);
    bd_total_tiket.textContent='Rp'+numberFormat(tarifPerOrang*jumlahPendaki);

    const anggotaContainer=document.getElementById('anggotaContainer');

    if(anggotaContainer&&jumlahPendaki>1){
        for(let idx=0;idx<jumlahPendaki-1;idx++){
            const wrapper=document.createElement('div');
            wrapper.className='anggota-group card';
            wrapper.innerHTML=`
                <h4>Anggota ${idx+1}</h4>

                <div style="display:flex;gap:12px;align-items:center">
                    <div style="flex:1">
                        <label>Nama Lengkap</label>
                        <input type="text" name="anggota[${idx}][nama]" required>
                    </div>
                    <div style="width:220px">
                        <label>NIK (16 digit)</label>
                        <input type="text" name="anggota[${idx}][nik]" pattern="\\d{16}" minlength="16" maxlength="16" required>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px">
                    <div style="flex:1">
                        <label>No. HP</label>
                        <input type="text" name="anggota[${idx}][hp]" required>
                    </div>
                    <div style="width:200px">
                        <label>Kewarganegaraan</label>
                        <input type="text" name="anggota[${idx}][kewarganegaraan]" value="WNI" required>
                    </div>
                    <div style="width:140px">
                        <label>Jenis Kelamin</label>
                        <select name="anggota[${idx}][jenis_kelamin]" required>
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px">
                    <div style="flex:1">
                        <label>Upload KTP Anggota</label>
                        <input type="file" name="anggota[${idx}][ktp]" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <div style="flex:1">
                        <label>Upload Surat Sehat</label>
                        <input type="file" name="anggota[${idx}][sehat]" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px">
                    <div style="flex:1">
                        <label>Pilih Porter</label>
                        <select name="anggota[${idx}][porter_id]" class="anggota-porter">
                            <option value="" data-price="0">-- Tidak --</option>
                            <?php foreach($porter as $p):?>
                                <option value="<?=$p['porter_id']?>" data-price="<?=intval($p['tarif'])?>">
                                    <?=htmlspecialchars($p['nama_porter'])?> – Rp<?=number_format(intval($p['tarif']),0,',','.')?>
                                </option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div style="flex:1">
                        <label>Pilih Ojek</label>
                        <select name="anggota[${idx}][ojek_id]" class="anggota-ojek">
                            <option value="" data-price="0">-- Tidak --</option>
                            <?php foreach($ojek as $o):?>
                                <option value="<?=$o['ojek_id']?>" data-price="<?=intval($o['tarif'])?>">
                                    <?=htmlspecialchars($o['nama_ojek'])?> – Rp<?=number_format(intval($o['tarif']),0,',','.')?>
                                </option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
            `;
            anggotaContainer.appendChild(wrapper);
        }
    }

    function getAllPorterSelects(){
        const arr=Array.from(document.querySelectorAll('.anggota-porter'));
        const ket=document.getElementById('ketua_porter_id');
        if(ket)arr.unshift(ket);
        return arr;
    }
    function getAllOjekSelects(){
        const arr=Array.from(document.querySelectorAll('.anggota-ojek'));
        const ket=document.getElementById('ketua_ojek_id');
        if(ket)arr.unshift(ket);
        return arr;
    }

    function recalc(){
        const hargaGuide=pickPrice(guideSelect)||0;
        const porterSelects=getAllPorterSelects();
        const ojekSelects=getAllOjekSelects();

        let totalPorter=0;
        porterSelects.forEach(s=>totalPorter+=pickPrice(s)||0);

        let totalOjek=0;
        ojekSelects.forEach(s=>totalOjek+=pickPrice(s)||0);

        const totalTiket=tarifPerOrang*jumlahPendaki;
        const totalAkhir=totalTiket+hargaGuide+totalPorter+totalOjek;

        bd_total_tiket.textContent='Rp'+numberFormat(totalTiket);
        bd_guide.textContent='Rp'+numberFormat(hargaGuide);
        bd_porter.textContent='Rp'+numberFormat(totalPorter);
        bd_ojek.textContent='Rp'+numberFormat(totalOjek);
        bd_total_akhir.textContent='Rp'+numberFormat(totalAkhir);

        hiddenTotal.value=totalAkhir;
    }

    if(guideSelect)guideSelect.addEventListener('change',recalc);
    document.addEventListener('change',(e)=>{
        if(e.target&&
           (e.target.classList.contains('anggota-porter')||
            e.target.classList.contains('anggota-ojek')||
            e.target.id==='ketua_porter_id'||
            e.target.id==='ketua_ojek_id'))
        {
            recalc();
        }
    });

    recalc();
});

function validateBeforeSubmit(){
    const nikKetua=document.getElementById('nik_ketua').value.trim();
    if(!/^\d{16}$/.test(nikKetua)){
        Swal.fire('Validasi','NIK Ketua harus 16 digit.','warning');
        return false;
    }

    const ktpKetua=document.getElementById('ktp_ketua').files.length;
    const sehatKetua=document.getElementById('sehat_ketua').files.length;
    if(!ktpKetua||!sehatKetua){
        Swal.fire('Validasi','KTP & Surat Sehat Ketua wajib diupload.','warning');
        return false;
    }

    const anggotaNikInputs=document.querySelectorAll('input[name^="anggota"][name$="[nik]"]');
    for(let i=0;i<anggotaNikInputs.length;i++){
        if(!/^\d{16}$/.test(anggotaNikInputs[i].value.trim())){
            Swal.fire('Validasi','Semua NIK anggota harus 16 digit.','warning');
            return false;
        }
    }

    const anggotaKtpFiles=document.querySelectorAll('input[name^="anggota"][name$="[ktp]"]');
    for(let a of anggotaKtpFiles){
        if(a.files.length===0){
            Swal.fire('Validasi','Semua anggota wajib upload KTP.','warning');
            return false;
        }
    }

    const anggotaSehatFiles=document.querySelectorAll('input[name^="anggota"][name$="[sehat]"]');
    for(let a of anggotaSehatFiles){
        if(a.files.length===0){
            Swal.fire('Validasi','Semua anggota wajib upload surat sehat.','warning');
            return false;
        }
    }

    if((jalurName||'').toLowerCase().includes('kalibaru')){
        const g=document.getElementById('guideSelect');
        if(!g.value){
            Swal.fire('Validasi','Jalur Kalibaru wajib memilih Guide.','warning');
            return false;
        }
    }

    return true;
}

function konfirmasiBooking(){
    if(!validateBeforeSubmit())return false;

    const namaKetua=document.querySelector('input[name="nama_ketua"]').value;
    const nikKetua=document.getElementById('nik_ketua').value;
    const jumlahPendaki=document.getElementById('jumlah_pendaki').value;

    const jalur="<?=addslashes($data_jalur['nama_jalur'])?>";
    const tanggal="<?=addslashes($data_jalur['tanggal_pendakian'])?>";

    const guideSelect=document.getElementById('guideSelect');
    const guideText=guideSelect.value?guideSelect.options[guideSelect.selectedIndex].text:"Tidak menggunakan guide";

    const pket=document.getElementById('ketua_porter_id');
    const porterKetuaText=pket.value?pket.options[pket.selectedIndex].text:"Tidak memakai porter";

    const oket=document.getElementById('ketua_ojek_id');
    const ojekKetuaText=oket.value?oket.options[oket.selectedIndex].text:"Tidak memakai ojek";

    const totalAkhir=document.getElementById('bd_total_akhir').textContent;

    function createFilePreview(inputEl){
        const f=inputEl.files[0];
        if(!f)return"<i>Tidak ada file</i>";
        if(f.type.includes("pdf"))return`<a style="color:blue">File PDF: ${f.name}</a>`;
        const url=URL.createObjectURL(f);
        return`<img src="${url}" style="width:100px;height:auto;border-radius:8px;margin:4px 0">`;
    }

    let previewKetuaKTP=createFilePreview(document.getElementById('ktp_ketua'));
    let previewKetuaSehat=createFilePreview(document.getElementById('sehat_ketua'));

    const anggotaGroups=document.querySelectorAll('#anggotaContainer .anggota-group');
    let anggotaHTML="";

    anggotaGroups.forEach((g,idx)=>{
        const nama=g.querySelector(`input[name="anggota[${idx}][nama]"]`).value;
        const nik=g.querySelector(`input[name="anggota[${idx}][nik]"]`).value;

        const ktp=createFilePreview(g.querySelector(`input[name="anggota[${idx}][ktp]"]`));
        const sehat=createFilePreview(g.querySelector(`input[name="anggota[${idx}][sehat]"]`));

        anggotaHTML+=`
        <div style="margin-bottom:12px">
            <strong>${idx+1}. ${nama}</strong><br>
            NIK: ${nik}<br>
            <small>KTP:</small><br>${ktp}
            <small>Surat Sehat:</small><br>${sehat}
        </div>`;
    });

    return Swal.fire({
        title:"Konfirmasi Data Booking",
        width:700,
        html:`
            <div style="text-align:left;font-size:15px;line-height:1.6">

                <h3>👤 Data Ketua</h3>
                <strong>Nama:</strong> ${namaKetua}<br>
                <strong>NIK:</strong> ${nikKetua}<br><br>
                <strong>KTP Ketua:</strong><br>${previewKetuaKTP}<br>
                <strong>Surat Sehat Ketua:</strong><br>${previewKetuaSehat}<br>

                <hr>

                <h3>🧑‍🤝‍🧑 Informasi Pendakian</h3>
                <strong>Jumlah Pendaki:</strong> ${jumlahPendaki}<br>
                <strong>Jalur:</strong> ${jalur}<br>
                <strong>Tanggal:</strong> ${tanggal}<br>

                <hr>

                <h3>🛠 Layanan</h3>
                <strong>Guide:</strong> ${guideText}<br>
                <strong>Porter Ketua:</strong> ${porterKetuaText}<br>
                <strong>Ojek Ketua:</strong> ${ojekKetuaText}<br>

                <hr>

                <h3>👥 Daftar Anggota</h3>
                ${anggotaHTML}

                <hr>

                <h3>💰 Total Pembayaran</h3>
                <span style="color:#d81b60;font-size:18px;font-weight:bold">${totalAkhir}</span>

            </div>
        `,
        icon:"question",
        showCancelButton:true,
        confirmButtonText:"Ya, data sudah benar",
        cancelButtonText:"Periksa Lagi",
        confirmButtonColor:"#43a047",
        cancelButtonColor:"#d33",
    }).then((res)=>{
        if(res.isConfirmed){
            document.getElementById('bookingForm').submit();
        }
    });
}
</script>

<footer style="text-align:center;padding:18px;color:#666;margin-top:20px">&copy; 2025 Tahura Raden Soerjo</footer>
</body>
</html>