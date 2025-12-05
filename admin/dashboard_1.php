<?php
include '../includes/auth_admin.php';
include '../backend/koneksi.php';

// ------------------------------------------------------
// HANDLE EXPORT (JIKA PERLU)
// ------------------------------------------------------
if (isset($_POST['export'])) {
    // Export diproses di file lain
}

// ------------------------------------------------------
// CARD STATISTICS
// ------------------------------------------------------
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

// ---------- Pendapatan bulan ini ----------
$q_rev_now = mysqli_query($conn, "
  SELECT COALESCE(SUM(jumlah_bayar),0) AS total
  FROM pembayaran
  WHERE status_pembayaran = 'terkonfirmasi'
  AND MONTH(tanggal_bayar) = MONTH(CURRENT_DATE())
  AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE())
");
$d_rev_now = mysqli_fetch_assoc($q_rev_now);

// ---------- Pendapatan bulan lalu ----------
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
$pendapatan_label = number_format($pendapatan_now, 0, ',', '.');

// ------------------------------------------------------
// Growth helper
// ------------------------------------------------------
function percent_change($now, $prev){
    if ($prev == 0){
        if ($now == 0) return 0;
        return null;
    }
    return round((($now - $prev) / $prev) * 100, 1);
}

$rev_change = percent_change($pendapatan_now, $pendapatan_prev);

// ---------- Jumlah pesanan bulan ini ----------
$q_ord_now = mysqli_query($conn, "
  SELECT COUNT(*) AS total
  FROM pesanan
  WHERE MONTH(tanggal_pesan) = MONTH(CURRENT_DATE())
  AND YEAR(tanggal_pesan) = YEAR(CURRENT_DATE())
");
$ord_now = mysqli_fetch_assoc($q_ord_now)['total'];

// ---------- Jumlah pesanan bulan lalu ----------
$q_ord_prev = mysqli_query($conn, "
  SELECT COUNT(*) AS total
  FROM pesanan
  WHERE MONTH(tanggal_pesan) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
  AND YEAR(tanggal_pesan) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
");
$ord_prev = mysqli_fetch_assoc($q_ord_prev)['total'];

$ord_change = percent_change($ord_now, $ord_prev);

// ------------------------------------------------------
// DATA GRAFIK — 6 bulan terakhir
// ------------------------------------------------------
$labels = [];
$orders_per_month = [];

for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $labels[] = $m;

    $q = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM pesanan
        WHERE DATE_FORMAT(tanggal_pesan, '%Y-%m') = '$m'
    ");
    $orders_per_month[] = mysqli_fetch_assoc($q)['total'];
}

// ------------------------------------------------------
// PIE CHART — PERSENTASE JALUR (JOIN BENAR)
// ------------------------------------------------------
$pie_labels = [];
$pie_data = [];

$qpie = mysqli_query($conn, "
    SELECT 
        j.nama_jalur AS jalur,
        COUNT(*) AS cnt
    FROM pesanan p
    LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
    LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
    GROUP BY j.nama_jalur
    ORDER BY cnt DESC
");

while ($rp = mysqli_fetch_assoc($qpie)) {
    $pie_labels[] = $rp['jalur'] ?? '-';
    $pie_data[] = (int)$rp['cnt'];
}

// ------------------------------------------------------
// RECENT ORDERS (JOIN BENAR)
// ------------------------------------------------------
$recent = [];
$qrecent = mysqli_query($conn, "
  SELECT 
      p.pesanan_id,
      p.kode_token,
      p.nama_ketua,
      p.tanggal_pesan,
      p.total_bayar,
      j.nama_jalur,
      pay.status_pembayaran
  FROM pesanan p
  LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
  LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
  LEFT JOIN pembayaran pay ON pay.pesanan_id = p.pesanan_id
  ORDER BY p.tanggal_pesan DESC
  LIMIT 5
");

while ($rr = mysqli_fetch_assoc($qrecent)) {
    $recent[] = $rr;
}

// ------------------------------------------------------
// INSIGHT OTOMATIS (JOIN BENAR)
// ------------------------------------------------------
$insight = [];

// Jalur paling populer bulan ini
$q_pop = mysqli_query($conn, "
  SELECT 
      j.nama_jalur AS jalur,
      COUNT(*) AS cnt
  FROM pesanan p
  LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
  LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
  WHERE DATE_FORMAT(p.tanggal_pesan, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
  GROUP BY j.nama_jalur
  ORDER BY cnt DESC
  LIMIT 1
");
if ($rpop = mysqli_fetch_assoc($q_pop)) {
    $insight[] = "Jalur paling populer bulan ini: <strong>{$rpop['jalur']}</strong> ({$rpop['cnt']} pesanan).";
} else {
    $insight[] = "Belum ada pesanan bulan ini.";
}

// Hari terpopuler
$q_day = mysqli_query($conn, "
  SELECT DAYNAME(tanggal_pesan) AS hari, COUNT(*) AS cnt
  FROM pesanan
  GROUP BY DAYNAME(tanggal_pesan)
  ORDER BY cnt DESC
  LIMIT 1
");
if ($rday = mysqli_fetch_assoc($q_day)) {
    $insight[] = "Hari terpopuler: <strong>{$rday['hari']}</strong> ({$rday['cnt']} pesanan total).";
}

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

<style>
    :root{
      --accent: #1e8a57;
      --accent-2: #2ea06a;
      --muted: #7a8b83;
      --card-bg: #ffffff;
      --panel-bg: linear-gradient(180deg,#f6fcf8,#eaf7ee);
      --icon-bg: rgba(30,138,87,0.12);
    }
    body{ background: var(--panel-bg); }
    .stat-card{ border-radius:12px; background:var(--card-bg); color: #0b2b1f; }
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
    .insight-box{ background: linear-gradient(90deg,#eafbf0,#f7fff9); border-radius:12px; padding:12px; color:#11462d; }
    .badge-status { padding:6px 10px; border-radius:999px; font-size:0.85rem; }
    .badge-lunas{ background:#dff6e8; color:#0b6b3d; }
    .badge-pending{ background:#fff5d9; color:#7a5a00; }
    .badge-ditolak{ background:#fdecec; color:#8b2b2b; }
</style>

<script>
const chartLabels = <?= json_encode($labels); ?>;
const chartOrders = <?= json_encode($orders_per_month); ?>;
const pieLabels = <?= json_encode($pie_labels); ?>;
const pieData = <?= json_encode($pie_data); ?>;
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // LINE CHART
    new Chart(document.getElementById("lineChart"), {
        type:'line',
        data:{
            labels: chartLabels,
            datasets:[{
                data:chartOrders,
                label:"Jumlah Pesanan",
                borderWidth:3,
                borderColor:"#1e8a57",
                backgroundColor:"rgba(30,138,87,0.12)",
                tension:0.35
            }]
        },
        options:{ responsive:true, maintainAspectRatio:false }
    });

    // PIE CHART
    new Chart(document.getElementById("pieChart"), {
        type:'pie',
        data:{
            labels:pieLabels,
            datasets:[{
                data:pieData,
                backgroundColor:['#1f8a57','#2ea06a','#69c18f','#b2e6c7','#14734f','#3da36f']
            }]
        }
    });
});
</script>

</head>
<body>

<div class="app-wrap">
<?php include 'sidebar.php'; ?>
<div class="main">

<?php include 'navbar.php'; ?>

<div class="container-fluid mt-3">

<!-- ========================================================
   STAT CARDS 
========================================================= -->
<div class="row g-3 mb-4">

    <!-- Total Jalur -->
    <div class="col-md-3">
        <div class="card stat-card shadow-sm p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon"><span class="material-icons">terrain</span></div>
                <div>
                    <div class="small-muted">Total Jalur</div>
                    <div class="h4 fw-bold mb-0"><?= $d1['total'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pesanan -->
    <div class="col-md-3">
        <div class="card stat-card shadow-sm p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon"><span class="material-icons">shopping_cart</span></div>
                <div>
                    <div class="small-muted">Total Pesanan</div>
                    <div class="h4 fw-bold mb-0"><?= $d2['total'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending -->
    <div class="col-md-3">
        <div class="card stat-card shadow-sm p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon"><span class="material-icons">hourglass_top</span></div>
                <div>
                    <div class="small-muted">Pending</div>
                    <div class="h4 fw-bold mb-0"><?= $d3['total'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pendapatan -->
    <div class="col-md-3">
        <div class="card stat-card shadow-sm p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon"><span class="material-icons">attach_money</span></div>
                <div>
                    <div class="small-muted">Pendapatan Bulanan</div>
                    <div class="h4 fw-bold mb-0">Rp <?= $pendapatan_label ?></div>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- ========================================================
   CHARTS
========================================================= -->
<div class="row g-4">

    <!-- Line Chart -->
    <div class="col-lg-8">
        <div class="card shadow-sm p-3" style="height:380px;">
            <div class="card-header bg-success">Pesanan 6 Bulan Terakhir</div>
            <canvas id="lineChart" style="height:260px;"></canvas>
        </div>
    </div>

    <!-- PIE + INSIGHT -->
    <div class="col-lg-4">
        
        <div class="card shadow-sm p-3 mb-3" style="height:220px;">
            <div class="card-header bg-success">Persentase Jalur</div>
            <canvas id="pieChart"></canvas>
        </div>

        <div class="insight-box">
            <div class="fw-bold mb-2">Insight</div>
            <?php foreach ($insight as $ins) echo "<div>$ins</div>"; ?>
        </div>

    </div>

</div>


<!-- ========================================================
    SEARCH SECTION (AJAX)
========================================================= -->
<div class="search-section mt-4">

    <h5>Pencarian & Filter</h5>

    <div class="filter-container">
        <input id="searchInput" placeholder="Cari nama, kode, jalur ...">

        <select id="filterStatus">
            <option value="">Semua Status</option>
            <option value="terkonfirmasi">Lunas</option>
            <option value="pending">Pending</option>
            <option value="ditolak">Ditolak</option>
        </select>

        <select id="filterJalur">
            <option value="">Semua Jalur</option>
            <?php
            $rr = mysqli_query($conn, "SELECT nama_jalur FROM jalur_pendakian ORDER BY nama_jalur");
            while($r = mysqli_fetch_assoc($rr)){
                echo '<option value="'.$r['nama_jalur'].'">'.$r['nama_jalur'].'</option>';
            }
            ?>
        </select>

        <select id="filterBulan">
            <option value="">Semua Bulan</option>
            <?php
            for($i=0;$i<12;$i++){
                $m = date('Y-m', strtotime("-$i months"));
                echo "<option value='$m'>".date("F Y", strtotime("$m-01"))."</option>";
            }
            ?>
        </select>
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
            <tr><td colspan="7" class="text-center small-muted">Gunakan filter untuk memulai...</td></tr>
        </tbody>
    </table>

</div>


<!-- ========================================================
    RECENT ORDERS
========================================================= -->
<div class="row mt-4 g-3">
    <div class="col-lg-6">
        <div class="card p-3">
            <div class="fw-bold mb-2">Pesanan Terbaru</div>
            <table class="table">
                <thead><tr><th>No</th><th>Kode</th><th>Pemesan</th><th>Jalur</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php
                if (!count($recent)) {
                    echo "<tr><td colspan='6' class='text-center small-muted'>Belum ada pesanan</td></tr>";
                } else {
                    $i=1;
                    foreach ($recent as $r) {
                        $badge = ($r['status_pembayaran']=='terkonfirmasi')
                                ? "<span class='badge-status badge-lunas'>Lunas</span>"
                                : (($r['status_pembayaran']=='pending')
                                    ? "<span class='badge-status badge-pending'>Pending</span>"
                                    : "<span class='badge-status badge-ditolak'>Ditolak</span>"
                                );

                        echo "
                        <tr>
                            <td>{$i}</td>
                            <td>{$r['kode_token']}</td>
                            <td>{$r['nama_ketua']}</td>
                            <td>{$r['nama_jalur']}</td>
                            <td>Rp ".number_format($r['total_bayar'],0,',','.')."</td>
                            <td>$badge</td>
                        </tr>";
                        $i++;
                    }
                }
                ?>
                </tbody>
            </table>
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
function loadSearch(){
  const q = encodeURIComponent(document.getElementById("searchInput").value);
  const st = encodeURIComponent(document.getElementById("filterStatus").value);
  const jl = encodeURIComponent(document.getElementById("filterJalur").value);
  const bl = encodeURIComponent(document.getElementById("filterBulan").value);

  fetch(`search_data.php?q=${q}&status=${st}&jalur=${jl}&bulan=${bl}`)
    .then(r=>r.json())
    .then(rows=>{
      const tbody = document.querySelector("#resultTable tbody");
      tbody.innerHTML = "";

      if(!rows.length){
        tbody.innerHTML = "<tr><td colspan='7' class='text-center small-muted'>Tidak ada hasil</td></tr>";
        return;
      }

      let i=1;
      rows.forEach(r=>{
        const badge =
          r.status_pembayaran == 'terkonfirmasi'
            ? '<span class="badge-status badge-lunas">Lunas</span>'
            : (r.status_pembayaran == 'pending'
                ? '<span class="badge-status badge-pending">Pending</span>'
                : '<span class="badge-status badge-ditolak">Ditolak</span>');

        tbody.innerHTML += `
          <tr>
            <td>${i++}</td>
            <td>${r.kode_token}</td>
            <td>${r.nama_ketua}</td>
            <td>${r.nama_jalur}</td>
            <td>${r.tanggal_pesan}</td>
            <td>Rp ${Number(r.total_bayar).toLocaleString('id-ID')}</td>
            <td>${badge}</td>
          </tr>
        `;
      });

    });
}

document.getElementById("searchInput").onkeyup = loadSearch;
document.getElementById("filterStatus").onchange = loadSearch;
document.getElementById("filterJalur").onchange = loadSearch;
document.getElementById("filterBulan").onchange = loadSearch;
</script>

</body>
</html>
