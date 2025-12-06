<?php
include '../includes/auth_admin.php';   // proteksi admin
include '../backend/koneksi.php';       // koneksi database
if (isset($_POST['export'])) {}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Admin — Gunung Raung</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin-style.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
// ------------------- DATA UNTUK DASHBOARD -------------------

// STAT CARDS
$q1 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jalur_pendakian");
$d1 = mysqli_fetch_assoc($q1);

$q2 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan");
$d2 = mysqli_fetch_assoc($q2);

$q3 = mysqli_query($conn, "
  SELECT COUNT(*) AS total 
  FROM pembayaran
  WHERE status_pembayaran = 'pending'
");
$d3 = mysqli_fetch_assoc($q3);

// Pendapatan bulan ini & bulan lalu (terkonfirmasi)
$q_rev_now = mysqli_query($conn, "
  SELECT COALESCE(SUM(jumlah_bayar),0) AS total 
  FROM pembayaran
  WHERE status_pembayaran = 'terkonfirmasi'
  AND MONTH(tanggal_bayar) = MONTH(CURRENT_DATE())
  AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE())
");
$d_rev_now = mysqli_fetch_assoc($q_rev_now);

$q_rev_prev = mysqli_query($conn, "
  SELECT COALESCE(SUM(jumlah_bayar),0) AS total 
  FROM pembayaran
  WHERE status_pembayaran = 'terkonfirmasi'
  AND MONTH(tanggal_bayar) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
  AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
");
$d_rev_prev = mysqli_fetch_assoc($q_rev_prev);

$pendapatan_now = (float)$d_rev_now['total'];
$pendapatan_prev = (float)$d_rev_prev['total'];
$pendapatan_label = $pendapatan_now ? number_format($pendapatan_now,0,',','.') : "0";

// Growth percentage helper
function percent_change($now, $prev){
  if($prev == 0){
    if($now == 0) return 0;
    return null; // cannot compute percentage growth from zero (we'll display "—")
  }
  return round((($now - $prev) / $prev) * 100, 1);
}

$rev_change = percent_change($pendapatan_now, $pendapatan_prev);

// Pesanan this month vs prev
$q_ord_now = mysqli_query($conn, "
  SELECT COUNT(*) AS total 
  FROM pesanan
  WHERE MONTH(tanggal_pesan) = MONTH(CURRENT_DATE())
  AND YEAR(tanggal_pesan) = YEAR(CURRENT_DATE())
");
$d_ord_now = mysqli_fetch_assoc($q_ord_now);
$ord_now = (int)$d_ord_now['total'];

$q_ord_prev = mysqli_query($conn, "
  SELECT COUNT(*) AS total 
  FROM pesanan
  WHERE MONTH(tanggal_pesan) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
  AND YEAR(tanggal_pesan) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
");
$d_ord_prev = mysqli_fetch_assoc($q_ord_prev);
$ord_prev = (int)$d_ord_prev['total'];

$ord_change = percent_change($ord_now, $ord_prev);

// ------- DATA GRAFIK (6 bulan terakhir) -------
$labels = [];
$orders_per_month = [];
for($i=5;$i>=0;$i--){
  $m = date('Y-m', strtotime("-$i months"));
  $labels[] = $m;
  $q = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM pesanan
    WHERE DATE_FORMAT(tanggal_pesan, '%Y-%m') = '$m'
  ");
  $r = mysqli_fetch_assoc($q);
  $orders_per_month[] = (int)$r['total'];
}

// ------- DATA PIE (persentase jalur) -------
$pie_labels = [];
$pie_data = [];
$qpie = mysqli_query($conn, "
  SELECT COALESCE(j.nama_jalur,'--Undefined--') AS jalur, COUNT(*) AS cnt
  FROM pesanan p
  LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
  LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
  GROUP BY COALESCE(j.nama_jalur,'--Undefined--')
  ORDER BY cnt DESC
");
while($rp = mysqli_fetch_assoc($qpie)){
  $pie_labels[] = $rp['jalur'];
  $pie_data[] = (int)$rp['cnt'];
}

// ------- RECENT ORDERS (5 terakhir) -------
$recent = [];
$qrecent = mysqli_query($conn, "
  SELECT p.pesanan_id, p.kode_token, p.nama_ketua, COALESCE(j.nama_jalur,'-') AS nama_jalur, p.tanggal_pesan, COALESCE(p.total_bayar,0) AS total_bayar, pay.status_pembayaran
  FROM pesanan p
  LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
  LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
  LEFT JOIN pembayaran pay ON pay.pesanan_id = p.pesanan_id
  ORDER BY p.tanggal_pesan DESC
  LIMIT 5
");
while($rr = mysqli_fetch_assoc($qrecent)){
  $recent[] = $rr;
}

// ------- INSIGHT SINGKAT (insight otomatis) -------
$insight = [];
$q_pop = mysqli_query($conn, "
  SELECT COALESCE(j.nama_jalur,'-') AS jalur, COUNT(*) AS cnt
  FROM pesanan p
  LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
  LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
  WHERE DATE_FORMAT(p.tanggal_pesan, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
  GROUP BY COALESCE(j.nama_jalur,'-')
  ORDER BY cnt DESC
  LIMIT 1
");
if($rpop = mysqli_fetch_assoc($q_pop)){
  if($rpop['cnt'] > 0){
    $insight[] = "Jalur paling populer bulan ini: <strong>{$rpop['jalur']}</strong> ({$rpop['cnt']} pesanan).";
  } else {
    $insight[] = "Belum ada pesanan pada bulan ini.";
  }
} else {
  $insight[] = "Belum cukup data untuk insight jalur bulan ini.";
}

$q_day = mysqli_query($conn, "
  SELECT DAYNAME(tanggal_pesan) AS hari, COUNT(*) AS cnt
  FROM pesanan
  GROUP BY DAYNAME(tanggal_pesan)
  ORDER BY cnt DESC
  LIMIT 1
");
if($rday = mysqli_fetch_assoc($q_day)){
  $insight[] = "Hari terpopuler: <strong>{$rday['hari']}</strong> ({$rday['cnt']} pesanan total).";
}
?>

  <style>
    /* --- visual polish & animations patch --- */
    :root{
      --accent: #1e8a57;
      --accent-2: #2ea06a;
      --muted: #7a8b83;
      --card-bg: #ffffff;
      --panel-bg: linear-gradient(180deg,#f6fcf8,#eaf7ee);
      --icon-bg: rgba(30,138,87,0.12);
    }

    /* stat card entrance animation */
    .stat-card{
      border-radius:12px;
      background:var(--card-bg);
      color: #0b2b1f;
      opacity: 0;
      transform: translateY(12px);
      animation: cardIn .6s ease-out forwards;
      box-shadow: 0 6px 18px rgba(11,43,31,0.03);
    }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.12s; }
    .stat-card:nth-child(3) { animation-delay: 0.20s; }
    .stat-card:nth-child(4) { animation-delay: 0.28s; }

    @keyframes cardIn {
      to { opacity: 1; transform: translateY(0); }
    }

    .stat-icon{ width:48px;height:48px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:var(--icon-bg); color:var(--accent); margin-right:12px; }
    .small-muted{ color:var(--muted); font-size:0.9rem; }
    .card-header.bg-success{ background: linear-gradient(90deg,var(--accent),var(--accent-2)); color: #fff; border-radius: .5rem; }

    .search-section{ background: #fff; padding:18px; border-radius:12px; box-shadow: 0 6px 18px rgba(0,0,0,0.03); }
    .filter-container{ display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:12px; }
    .filter-container input, .filter-container select{ padding:10px;border-radius:8px;border:1px solid #e6efe8; min-width:160px; }
    .export-form { display:flex; gap:8px; align-items:center; }
    .search-table{ width:100%; border-collapse:collapse; margin-top:16px; background:#fff; border-radius:8px; overflow:hidden; }
    .search-table thead{ background:#f1faf4; color: #1b4b37; }
    .search-table th, .search-table td{ padding:12px 14px; border-bottom:1px solid #f6f6f6; text-align:left; }

    .insight-box{
      background: linear-gradient(90deg,#eafbf0,#f7fff9);
      border-radius:12px; padding:12px; color:#11462d;
      transition: all .25s ease;
      border-left: 6px solid rgba(30,138,87,0.12);
    }
    .insight-box:hover{
      transform: translateY(-4px);
      box-shadow: 0 10px 24px rgba(11,43,31,0.06);
      background: linear-gradient(90deg,#e9fff2,#f8fff9);
    }

    .badge-status { padding:6px 10px; border-radius:999px; font-size:0.85rem; }
    .badge-lunas{ background:#dff6e8; color:#0b6b3d; }
    .badge-pending{ background:#fff5d9; color:#7a5a00; }
    .badge-ditolak{ background:#fdecec; color:#8b2b2b; }

    /* subtle chart container polish */
    .card .chart-wrap { padding: 6px; }
    .chart-legend { display:flex; gap:8px; align-items:center; font-size:0.9rem; color:#2b6b4f; }

    /* responsive tweak */
    @media(max-width:991px){
      .filter-container{ flex-direction:column; align-items:stretch; }
    }
  </style>

<script>
  // Pass PHP arrays to JS for charts
  const chartLabels = <?= json_encode($labels); ?>;
  const chartOrders = <?= json_encode($orders_per_month); ?>;
  const pieLabels = <?= json_encode($pie_labels); ?>;
  const pieData = <?= json_encode($pie_data); ?>;
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // ------- LINE CHART (smooth, animated) -------
    const lineCtx = document.getElementById("lineChart");
    if(lineCtx){
      new Chart(lineCtx, {
        type: 'line',
        data: {
          labels: chartLabels,
          datasets: [{
            label: "Jumlah Pesanan",
            data: chartOrders,
            borderWidth: 3,
            borderColor: "#0f8a4f",
            backgroundColor: "rgba(15,138,79,0.16)",
            tension: 0.36,
            pointBackgroundColor: "#fff",
            pointBorderColor: "#0f8a4f",
            pointRadius: 5,
            pointHoverRadius: 8,
            fill: true,
            hoverBorderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: 1100,
            easing: 'easeOutQuart'
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              mode: 'index',
              intersect: false,
              backgroundColor: '#0f8a4f',
              titleColor: '#fff',
              bodyColor: '#fff',
              padding: 10,
              cornerRadius: 8
            }
          },
          scales: {
            x: {
              ticks: { color: '#2e6b52' },
              grid: { display: false }
            },
            y: {
              beginAtZero: true,
              ticks: { color: '#2e6b52' },
              grid: { color: 'rgba(15,138,79,0.06)' }
            }
          }
        }
      });
    }

    // ------- PIE CHART (animate rotate + scale) -------
    const pieCtx = document.getElementById("pieChart");
    if(pieCtx){
      new Chart(pieCtx, {
        type: 'pie',
        data: {
          labels: pieLabels,
          datasets: [{
            data: pieData,
            backgroundColor: ['#1e8a57','#2ea06a','#3fca84','#7ce6b5','#c6f7de','#0f7241'],
            hoverOffset: 8,
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            animateRotate: true,
            animateScale: true,
            duration: 1100,
            easing: 'easeOutBack'
          },
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth:12, padding:8 } },
            tooltip: {
              padding: 8,
              cornerRadius: 6
            }
          }
        }
      });
    }
});
</script>

</head>
<body>

<div class="app-wrap">

<?php include 'sidebar.php'; ?>
<div class="main">
<?php include 'navbar.php'; ?>

<div class="container-fluid mt-3">

<?php
// STAT CARDS (reused values)
?>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">

  <div class="col-md-3">
    <div class="card shadow-sm stat-card">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon">
          <span class="material-icons">terrain</span>
        </div>
        <div>
          <div class="small-muted">Total Jalur</div>
          <div class="h4 fw-bold mb-0"><?= $d1['total']; ?></div>
          <div class="small-muted">Jumlah jalur</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm stat-card">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon">
          <span class="material-icons">shopping_cart</span>
        </div>
        <div style="flex:1">
          <div class="small-muted">Total Pesanan</div>
          <div class="h4 fw-bold mb-0"><?= $d2['total']; ?></div>
          <div class="small-muted">
            <?php
              if($ord_change === null) echo '<span style="color:#6a945f">+' . $ord_now . ' dari bulan lalu</span>';
              else echo ($ord_change >= 0 ? '<span style="color:#2a8a4a">+' . abs($ord_change) . '%</span>' : '<span style="color:#c94a4a">' . abs($ord_change) . '%</span>') . " dari bulan lalu";
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm stat-card">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon">
          <span class="material-icons">hourglass_top</span>
        </div>
        <div style="flex:1">
          <div class="small-muted">Total Pending</div>
          <div class="h4 fw-bold mb-0"><?= $d3['total']; ?></div>
          <div class="small-muted">Menunggu verifikasi pembayaran</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm stat-card">
      <div class="card-body d-flex align-items-center">
        <div class="stat-icon">
          <span class="material-icons">attach_money</span>
        </div>
        <div style="flex:1">
          <div class="small-muted">Pendapatan Bulanan</div>
          <div class="h4 fw-bold mb-0">Rp <?= $pendapatan_label; ?></div>
          <div class="small-muted">
            <?php
              if($rev_change === null) echo '<span style="color:#2a8a4a">+' . number_format($pendapatan_now,0,',','.') . '</span> dari bulan lalu';
              else echo ($rev_change >= 0 ? '<span style="color:#2a8a4a">+' . abs($rev_change) . '%</span>' : '<span style="color:#c94a4a">' . abs($rev_change) . '%</span>') . " dari bulan lalu";
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- CHARTS -->
<div class="row g-4">

  <div class="col-lg-8">
    <div class="card shadow-sm border-0" style="height: 420px;">
      <div class="card-header bg-success">
        Pesanan per Bulan (6 bulan terakhir)
      </div>
      <div class="card-body p-3 chart-wrap">
        <canvas id="lineChart"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-4 d-flex flex-column gap-3">

    <div class="card shadow-sm border-0" style="height: 220px;">
      <div class="card-header bg-success">Persentase Jalur</div>
      <div class="card-body d-flex justify-content-center align-items-center">
        <canvas id="pieChart" style="max-width: 240px; max-height:200px;"></canvas>
      </div>
    </div>

    <div class="insight-box p-3">
      <div class="fw-bold mb-2">Insight Cepat</div>
      <div class="small-muted">
        <?php foreach($insight as $ins) echo "<div style='margin-bottom:6px;'>$ins</div>"; ?>
      </div>
    </div>

  </div>

</div>

<!-- SEARCH / FILTER -->
<div class="search-section mt-4">
  <div class="d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Pencarian & Filter</h5>
    <div class="small-muted">Quick export & filter results</div>
  </div>

  <div class="filter-container mt-3">
    <input id="searchInput" placeholder="Cari nama, kode pesanan, atau jalur..." />

    <select id="filterStatus">
      <option value="">Semua Status</option>
      <option value="terkonfirmasi">Lunas</option>
      <option value="pending">Pending</option>
      <option value="ditolak">Ditolak</option>
    </select>

    <select id="filterJalur">
      <option value="">Semua Jalur</option>
      <?php
      $rj = $conn->query("SELECT nama_jalur FROM jalur_pendakian WHERE status='aktif' OR status='Aktif' ORDER BY nama_jalur");
      while($row = $rj->fetch_assoc()){
        echo '<option value="'.htmlspecialchars($row['nama_jalur']).'">'.htmlspecialchars($row['nama_jalur']).'</option>';
      }
      ?>
    </select>

    <select id="filterBulan">
      <option value="">Semua Bulan</option>
      <?php
      for($i=0;$i<12;$i++){
        $m = date('Y-m', strtotime("-$i months"));
        echo '<option value="'.$m.'">'.date('F Y', strtotime($m.'-01')).'</option>';
      }
      ?>
    </select>

    <form method="POST" class="export-form">
      <input type="hidden" name="q" id="exp_q">
      <input type="hidden" name="status" id="exp_status">
      <input type="hidden" name="jalur" id="exp_jalur">
      <input type="hidden" name="bulan" id="exp_bulan">

      <button type="submit" name="export" value="excel" class="btn btn-outline-success btn-sm">Excel</button>
      <button type="submit" name="export" value="pdf" class="btn btn-outline-danger btn-sm">PDF</button>
    </form>

  </div>

  <table class="search-table" id="resultTable">
    <thead>
      <tr>
        <th>No</th>
        <th>Kode</th>
        <th>Pemesan</th>
        <th>Jalur</th>
        <th>Tgl Pesan</th>
        <th>Total</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="7" style="text-align:center;color:var(--muted)">
          Gunakan kotak pencarian atau filter untuk melihat hasil
        </td>
      </tr>
    </tbody>
  </table>

</div>

<!-- RECENT ORDERS -->
<div class="row mt-4 g-3">
  <div class="col-lg-6">
    <div class="card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div><strong>Pesanan Terbaru</strong></div>
        <div class="small-muted">5 item terakhir</div>
      </div>
      <div class="table-responsive">
        <table class="table table-borderless mb-0">
          <thead>
            <tr>
              <th>No</th>
              <th>Kode</th>
              <th>Pemesan</th>
              <th>Jalur</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!count($recent)): ?>
              <tr><td colspan="6" class="text-center small-muted">Belum ada pesanan</td></tr>
            <?php else: $i=1; foreach($recent as $r): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= htmlspecialchars($r['kode_token'] ?: $r['pesanan_id']); ?></td>
                <td><?= htmlspecialchars($r['nama_ketua'] ?: '-'); ?></td>
                <td><?= htmlspecialchars($r['nama_jalur']); ?></td>
                <td>Rp <?= number_format($r['total_bayar']?:0,0,',','.'); ?></td>
                <td>
                  <?php
                    $st = $r['status_pembayaran'] ?? 'pending';
                    if($st == 'terkonfirmasi' || $st == 'lunas') echo '<span class="badge-status badge-lunas">Lunas</span>';
                    elseif($st == 'pending') echo '<span class="badge-status badge-pending">Pending</span>';
                    else echo '<span class="badge-status badge-ditolak">Ditolak</span>';
                  ?>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS / ANALYTICS -->
  <div class="col-lg-6">
    <div class="card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div><strong>Analitik Cepat</strong></div>
        <div class="small-muted">Ringkasan</div>
      </div>
      <div class="small-muted mb-3">
        <?= implode('', array_map(fn($s)=>"<div style='margin-bottom:6px;'>$s</div>", $insight)); ?>
      </div>

      <div class="d-flex gap-2">
        <a href="pesanan.php" class="btn btn-success btn-sm">Lihat Semua Pesanan</a>
        <a href="pembayaran.php" class="btn btn-outline-success btn-sm">Verifikasi Pembayaran</a>
        <a href="jalur_pendakian.php" class="btn btn-outline-secondary btn-sm">Kelola Jalur</a>
      </div>
    </div>
  </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ================================
// AJAX SEARCH
// ================================
const sInput = document.getElementById('searchInput');
const sStatus = document.getElementById('filterStatus');
const sJalur = document.getElementById('filterJalur');
const sBulan = document.getElementById('filterBulan');
const result = document.querySelector('#resultTable tbody');

let searchTimer = null;

function loadSearch(){
  const q = encodeURIComponent(sInput.value.trim());
  const st = encodeURIComponent(sStatus.value);
  const ja = encodeURIComponent(sJalur.value);
  const bl = encodeURIComponent(sBulan.value);

  fetch(`search_data.php?q=${q}&status=${st}&jalur=${ja}&bulan=${bl}`)
    .then(r=>r.json())
    .then(rows=>{
      result.innerHTML = '';
      if(!rows || !rows.length){
        result.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--muted)">Tidak ada hasil</td></tr>';
        return;
      }
      let i=1;
      rows.forEach(r=>{
        const kode = r.kode_token ?? r.pesanan_id ?? '-';
        const nama = r.nama_ketua ?? r.nama_pemesan ?? '-';
        const jal = r.nama_jalur ?? '-';
        const tgl = r.tanggal_pesan ?? '-';
        const total = r.total_bayar ? Number(r.total_bayar).toLocaleString('id-ID') : '0';
        const stp = r.status_pembayaran ?? '-';
        let badge = stp == 'terkonfirmasi' || stp == 'lunas' ? '<span class="badge-status badge-lunas">Lunas</span>' : (stp=='pending' ? '<span class="badge-status badge-pending">Pending</span>' : '<span class="badge-status badge-ditolak">Ditolak</span>');
        result.innerHTML += `
          <tr>
            <td>${i++}</td>
            <td class="fw-bold">${kode}</td>
            <td>${nama}</td>
            <td>${jal}</td>
            <td>${tgl}</td>
            <td>Rp ${total}</td>
            <td>${badge}</td>
          </tr>
        `;
      });
    })
    .catch(err=>{
      console.error(err);
      result.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--muted)">Terjadi kesalahan saat mengambil data</td></tr>';
    });
}

// Debounce search
sInput.addEventListener('keyup', ()=>{
  clearTimeout(searchTimer);
  searchTimer = setTimeout(()=> {
    if(sInput.value.length>=2 || sInput.value.length===0) loadSearch();
  }, 300);
});
sStatus.addEventListener('change', loadSearch);
sJalur.addEventListener('change', loadSearch);
sBulan.addEventListener('change', loadSearch);

// initial load (small)
window.addEventListener('load', ()=> {
  // load initial stats or leave blank
});

// Export filter sync
document.querySelector('.export-form').addEventListener('submit', ()=>{
  document.getElementById('exp_q').value = sInput.value.trim();
  document.getElementById('exp_status').value = sStatus.value;
  document.getElementById('exp_jalur').value = sJalur.value;
  document.getElementById('exp_bulan').value = sBulan.value;
});
</script>

</body>
</html>
