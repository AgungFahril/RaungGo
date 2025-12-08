<?php
session_start();
include '../backend/koneksi.php';
header("Cache-Control: no-cache, must-revalidate");

// ============================================
// AJAX FETCH KUOTA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fetch') {
    $jalur_id = intval($_POST['jalur_id'] ?? 0);
    $tanggal_naik = $_POST['tanggal_naik'] ?? '';
    $tanggal_turun = $_POST['tanggal_turun'] ?? '';
    $jumlah_pendaki = intval($_POST['jumlah_pendaki'] ?? 0);

    $resp = ['success' => false, 'errors' => [], 'data' => null];

    $today = date('Y-m-d');
    if (!$jalur_id) $resp['errors'][] = "Pilih jalur terlebih dahulu.";
    if (!$tanggal_naik) $resp['errors'][] = "Tanggal naik diperlukan.";
    if (!$tanggal_turun) $resp['errors'][] = "Tanggal turun diperlukan.";
    if ($tanggal_naik && strtotime($tanggal_naik) < strtotime($today)) $resp['errors'][] = "Tanggal naik tidak boleh sebelum hari ini.";
    if ($tanggal_naik && $tanggal_turun && strtotime($tanggal_turun) < strtotime($tanggal_naik)) $resp['errors'][] = "Tanggal turun harus sama atau setelah tanggal naik.";
    if ($jumlah_pendaki <= 0) $resp['errors'][] = "Masukkan jumlah pendaki minimal 1.";

    if (empty($resp['errors'])) {
        $qj = $conn->prepare("SELECT nama_jalur, kuota_harian, tarif_tiket, deskripsi FROM jalur_pendakian WHERE jalur_id = ? LIMIT 1");
        $qj->bind_param("i", $jalur_id);
        $qj->execute();
        $rj = $qj->get_result()->fetch_assoc();
        $qj->close();

        if (!$rj) {
            $resp['errors'][] = "Data jalur tidak ditemukan.";
        } else {
            $nama_jalur = $rj['nama_jalur'];
            $kuota_harian = intval($rj['kuota_harian'] ?? 0);
            $tarif = intval($rj['tarif_tiket'] ?? 0);
            $deskripsi = $rj['deskripsi'] ?? '';

            $min_required = 1;
            if (strtolower($nama_jalur) === 'kalibaru') $min_required = 6;
            elseif (strtolower($nama_jalur) === 'sumberwringin') $min_required = 2;

            if ($jumlah_pendaki < $min_required) {
                $resp['errors'][] = "Minimal pendaki untuk jalur {$nama_jalur} adalah {$min_required} orang.";
            } else {
                $cek = $conn->prepare("SELECT pendakian_id, kuota_tersedia FROM pendakian WHERE jalur_id = ? AND tanggal_pendakian = ? AND tanggal_turun = ? LIMIT 1");
                $cek->bind_param("iss", $jalur_id, $tanggal_naik, $tanggal_turun);
                $cek->execute();
                $res = $cek->get_result();

                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $pendakian_id = intval($row['pendakian_id']);
                    $kuota_tersedia = intval($row['kuota_tersedia']);
                } else {
                    $pendakian_id = 0;
                    $kuota_tersedia = $kuota_harian;
                }
                $cek->close();

                $jumlah_terpesan = 0;
                if ($pendakian_id) {
                    $qp = $conn->prepare("SELECT IFNULL(SUM(jumlah_pendaki),0) as booked FROM pesanan WHERE pendakian_id = ? AND status_pesanan NOT IN ('dibatalkan','cancelled')");
                    $qp->bind_param("i", $pendakian_id);
                    $qp->execute();
                    $jumlah_terpesan = intval($qp->get_result()->fetch_assoc()['booked'] ?? 0);
                    $qp->close();
                }

                $sisa = max(0, $kuota_tersedia - $jumlah_terpesan);
                $total_harga = $tarif * max(1, $jumlah_pendaki);
                $persen = $kuota_harian > 0 ? round(($sisa / $kuota_harian) * 100) : 0;
                $warna = ($persen > 60) ? '#4caf50' : (($persen > 30) ? '#fbc02d' : '#e53935');

                $resp['success'] = true;
                $resp['data'] = [
                    'pendakian_id' => $pendakian_id,
                    'nama_jalur' => $nama_jalur,
                    'kuota_harian' => $kuota_harian,
                    'kuota_tersedia' => $kuota_tersedia,
                    'jumlah_terpesan' => $jumlah_terpesan,
                    'sisa' => $sisa,
                    'persen' => $persen,
                    'warna' => $warna,
                    'tarif' => $tarif,
                    'deskripsi' => $deskripsi,
                    'total_harga' => $total_harga,
                    'min_required' => $min_required,
                ];
            }
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($resp);
    exit;
}

// ============================================
// KONFIRMASI BOOKING (FIXED UNTUK MOBILE)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {
    $jalur_id = intval($_POST['jalur_id'] ?? 0);
    $jumlah_pendaki = intval($_POST['jumlah_pendaki'] ?? 0);
    $tanggal_naik = $_POST['tanggal_naik'] ?? '';
    $tanggal_turun = $_POST['tanggal_turun'] ?? '';
    $pendakian_id = intval($_POST['pendakian_id'] ?? 0);

    // Validasi ulang
    if (!$jalur_id || !$jumlah_pendaki || !$tanggal_naik || !$tanggal_turun) {
        $_SESSION['error'] = "Data booking tidak lengkap. Silakan ulangi dari awal.";
        header("Location: kuota.php");
        exit;
    }

    // Jika pendakian_id = 0, buat baru
    if (!$pendakian_id) {
        $qk = $conn->prepare("SELECT kuota_harian FROM jalur_pendakian WHERE jalur_id = ? LIMIT 1");
        $qk->bind_param("i", $jalur_id);
        $qk->execute();
        $r = $qk->get_result()->fetch_assoc();
        $qk->close();
        $kuota_awal = intval($r['kuota_harian'] ?? 0);

        $ins = $conn->prepare("INSERT INTO pendakian (jalur_id, tanggal_pendakian, tanggal_turun, kuota_tersedia, status) VALUES (?, ?, ?, ?, 'tersedia')");
        $ins->bind_param("issi", $jalur_id, $tanggal_naik, $tanggal_turun, $kuota_awal);
        $ins->execute();
        $pendakian_id = $conn->insert_id;
        $ins->close();
    }

    // Set session LENGKAP (penting untuk mobile)
    $_SESSION['selected_pendakian'] = $pendakian_id;
    $_SESSION['jumlah_pendaki'] = $jumlah_pendaki;
    $_SESSION['tanggal_naik'] = $tanggal_naik;
    $_SESSION['tanggal_turun'] = $tanggal_turun;
    $_SESSION['jalur_id'] = $jalur_id;

    // Redirect dengan GET parameter sebagai backup untuk mobile
    header("Location: booking.php?pid={$pendakian_id}&jp={$jumlah_pendaki}");
    exit;
}

// ============================================
// RENDER HALAMAN
// ============================================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=pengunjung/kuota.php");
    exit;
}

$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cek Kuota Pendakian</title>
<link rel="stylesheet" href="../style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {font-family:'Poppins',sans-serif;background:linear-gradient(to bottom right,#e8f5e9,#c8e6c9);margin:0}
.container {width:90%;max-width:1050px;margin:130px auto 80px;background:#fff;border-radius:18px;padding:40px 45px;box-shadow:0 10px 30px rgba(0,0,0,0.1)}
h2 {text-align:center;color:#2e7d32;margin-bottom:35px;font-weight:700;font-size:1.8rem}
form.grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:25px;margin-bottom:20px}
label {font-weight:600;color:#2e7d32}
input,select {width:100%;padding:11px;border:2px solid #c8e6c9;border-radius:10px;transition:0.3s}
input:focus,select:focus {border-color:#43a047;outline:none;box-shadow:0 0 6px rgba(67,160,71,0.3)}
.btn {background-color:#43a047;color:white;border:none;border-radius:10px;padding:13px;cursor:pointer;font-weight:600;transition:all 0.3s}
.btn:hover {background-color:#2e7d32;transform:translateY(-1px)}
.result-card {margin-top:35px;background:#f9fff9;border-radius:16px;border:2px solid #dcedc8;padding:30px 35px;box-shadow:0 5px 15px rgba(0,0,0,0.05);animation:fadeIn 0.4s ease-in-out}
@keyframes fadeIn {from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.result-card h3 {color:#2e7d32;margin-bottom:8px;font-size:1.4rem}
.deskripsi {background:#f1f8e9;border-left:5px solid #8bc34a;padding:10px 15px;border-radius:10px;color:#444;margin-bottom:15px}
.total {font-weight:bold;color:#e91e63;font-size:1.1rem}
.progress-container {margin:15px 0;background:#e0e0e0;border-radius:10px;overflow:hidden;height:20px}
.progress-bar {height:100%;width:0%;transition:width 0.5s ease-in-out;border-radius:10px}
footer {text-align:center;color:#555;margin:40px 0}
.note {font-size:0.95rem;color:#666;margin-top:8px}
.small {font-size:0.9rem;color:#777}
.error-alert {background:#ffebee;border-left:4px solid #e53935;padding:12px;border-radius:8px;margin-bottom:20px;color:#c62828}
</style>
</head>
<body>
<header><?php include '../includes/navbar_user.php'; ?></header>

<div class="container">
    <h2>🧭 Cek Kuota & Tarif Pendakian</h2>
    
    <?php if($error_message): ?>
    <div class="error-alert">⚠️ <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form class="grid" id="kuotaForm" onsubmit="return false;">
        <div>
            <label>Jalur Pendakian</label>
            <select name="jalur_id" id="jalur_id" required>
                <option value="">-- Pilih Jalur --</option>
                <?php
                $jalur = $conn->query("SELECT jalur_id, nama_jalur FROM jalur_pendakian WHERE status='aktif' ORDER BY nama_jalur ASC");
                while ($j = $jalur->fetch_assoc()):
                ?>
                    <option value="<?= $j['jalur_id']; ?>"><?= htmlspecialchars($j['nama_jalur']); ?></option>
                <?php endwhile; ?>
            </select>
            <div class="note small" id="jalurNote">Catatan: Kalibaru minimal 6 orang (Guide wajib).</div>
        </div>

        <div>
            <label>Jumlah Pendaki</label>
            <input type="number" id="jumlah_pendaki" name="jumlah_pendaki" min="1" max="50" required>
            <div class="note small">Masukkan total anggota termasuk ketua.</div>
        </div>

        <div>
            <label>Tanggal Naik</label>
            <input type="date" id="tanggal_naik" name="tanggal_naik" required>
        </div>

        <div>
            <label>Tanggal Turun</label>
            <input type="date" id="tanggal_turun" name="tanggal_turun" required>
        </div>

        <div style="align-self:end">
            <button id="resetBtn" class="btn" type="button" style="background:#757575">Reset</button>
        </div>
    </form>

    <div id="resultArea"></div>
</div>

<?php include 'includes/footer.php'; ?>


<script>
(function(){
    const jalurEl=document.getElementById('jalur_id');
    const jumlahEl=document.getElementById('jumlah_pendaki');
    const naikEl=document.getElementById('tanggal_naik');
    const turunEl=document.getElementById('tanggal_turun');
    const resultArea=document.getElementById('resultArea');
    const resetBtn=document.getElementById('resetBtn');

    const today=new Date();
    const pad=d=>d.toString().padStart(2,'0');
    const todayStr=`${today.getFullYear()}-${pad(today.getMonth()+1)}-${pad(today.getDate())}`;
    naikEl.setAttribute('min',todayStr);

    naikEl.addEventListener('change',()=>{
        if(naikEl.value){
            turunEl.setAttribute('min',naikEl.value);
            if(turunEl.value&&turunEl.value<naikEl.value)turunEl.value=naikEl.value;
        }else{turunEl.removeAttribute('min')}
        triggerFetch();
    });

    turunEl.addEventListener('change',()=>triggerFetch());

    jalurEl.addEventListener('change',()=>{
        const selText=jalurEl.options[jalurEl.selectedIndex]?.text||'';
        const note=document.getElementById('jalurNote');
        if(selText.toLowerCase().includes('kalibaru')){
            note.innerHTML='Catatan: Kalibaru minimal 6 orang (Guide wajib).';
            jumlahEl.min=6;
        }else if(selText.toLowerCase().includes('sumberwringin')){
            note.innerHTML='Catatan: Sumberwringin minimal 2 orang.';
            jumlahEl.min=2;
        }else{
            note.innerHTML='Catatan: Isi data lengkap untuk melihat kuota dan tarif.';
            jumlahEl.min=1;
        }
        triggerFetch();
    });

    jumlahEl.addEventListener('input',()=>triggerFetch());
    resetBtn.addEventListener('click',()=>{
        jalurEl.value='';jumlahEl.value='';naikEl.value='';turunEl.value='';
        turunEl.removeAttribute('min');resultArea.innerHTML='';
    });

    let debounceTimer=null;
    function triggerFetch(){
        clearTimeout(debounceTimer);
        debounceTimer=setTimeout(fetchKuota,350);
    }

    async function fetchKuota(){
        const jalur_id=jalurEl.value;
        const tanggal_naik=naikEl.value;
        const tanggal_turun=turunEl.value;
        const jumlah_pendaki=jumlahEl.value?parseInt(jumlahEl.value):0;

        if(!jalur_id||!tanggal_naik||!tanggal_turun||!jumlah_pendaki){
            resultArea.innerHTML='';return;
        }

        resultArea.innerHTML='<div class="result-card"><h3>Memeriksa kuota...</h3><p class="small">Mohon tunggu...</p></div>';

        const formData=new FormData();
        formData.append('action','fetch');
        formData.append('jalur_id',jalur_id);
        formData.append('tanggal_naik',tanggal_naik);
        formData.append('tanggal_turun',tanggal_turun);
        formData.append('jumlah_pendaki',jumlah_pendaki);

        try{
            const res=await fetch('',{method:'POST',body:formData});
            const data=await res.json();

            if(!data.success){
                const err=data.errors?.length?data.errors[0]:'Gagal memeriksa kuota';
                resultArea.innerHTML=`<div class="result-card"><h3>Validasi Gagal</h3><p class="small">${escapeHtml(err)}</p></div>`;
                Swal.fire('Error',err,'error');
                return;
            }

            const d=data.data;
            const namaJalur=escapeHtml(d.nama_jalur||'');
            const deskripsi=escapeHtml(d.deskripsi||'');
            const sisa=d.sisa??0;
            const kuota=d.kuota_harian??0;
            const persen=d.persen??0;
            const warna=d.warna||'#4caf50';
            const tarif=numberFormat(d.tarif||0);
            const total=numberFormat(d.total_harga||0);
            const pendakianID=d.pendakian_id??0;
            const minReq=d.min_required??null;

            const bookingForm=`
                <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="confirm">
                    <input type="hidden" name="pendakian_id" value="${pendakianID}">
                    <input type="hidden" name="jalur_id" value="${jalur_id}">
                    <input type="hidden" name="jumlah_pendaki" value="${jumlah_pendaki}">
                    <input type="hidden" name="tanggal_naik" value="${tanggal_naik}">
                    <input type="hidden" name="tanggal_turun" value="${tanggal_turun}">
                    <button type="submit" class="btn" ${sisa<jumlah_pendaki?'disabled':''}>Lanjut Booking ➜</button>
                </form>
            `;

            const catatan=`${minReq?'Minimal '+minReq+' orang untuk jalur ini.':''}${sisa<jumlah_pendaki?'<br><span style="color:#e53935">Kuota tidak mencukupi.</span>':''}`;

            resultArea.innerHTML=`
                <div class="result-card">
                    <h3>${namaJalur}</h3>
                    <div class="deskripsi">${deskripsi}</div>
                    <p><strong>Kuota Tersisa:</strong> ${sisa}/${kuota}</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width:${persen}%;background:${warna}"></div>
                    </div>
                    <p><strong>Tarif per Orang:</strong> Rp${tarif}</p>
                    <p><strong>Total Bayar:</strong> <span class="total">Rp${total}</span></p>
                    <div style="text-align:right;margin-top:18px">${bookingForm}</div>
                    <div class="note small" style="margin-top:12px"><strong>Catatan:</strong> ${catatan}</div>
                </div>
            `;
        }catch(err){
            console.error(err);
            resultArea.innerHTML='<div class="result-card"><h3>Error</h3><p class="small">Terjadi kesalahan saat memeriksa kuota.</p></div>';
        }
    }

    function numberFormat(n){return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,".")}
    function escapeHtml(text){
        if(!text)return'';
        return text.replace(/[&<>"'`=\/]/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'}[s]))
    }
})();
</script>
</body>
</html>