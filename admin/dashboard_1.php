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

  <script>
// ------------------ FIXED CHART SCRIPT ------------------

// LINE CHART
document.addEventListener("DOMContentLoaded", () => {

  new Chart(document.getElementById("lineChart"), {
      type: 'line',
      data: {
          labels: ["2025-01","2025-02","2025-03","2025-04","2025-05","2025-06"],
          datasets: [{
              label: "Jumlah Pesanan",
              data: [1,3,2,5,6,4],
              borderWidth: 3,
              borderColor: "white",
              backgroundColor: "rgba(255,255,255,0.25)",
              tension: 0.35
          }]
      },
      options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { labels:{ color:"white" } } },
          scales: {
              x: { ticks:{ color:"white" } },
              y: { ticks:{ color:"white" } }
          }
      }
  });

  // PIE CHART
  new Chart(document.getElementById("pieChart"), {
      type: 'pie',
      data: {
          labels: ["Jalur A","Jalur B","Jalur C"],
          datasets: [{
              data: [40, 35, 25],
              backgroundColor: ["#1f5f3f","#2b7a52","#3aa669"]
          }]
      },
      options: { responsive: true, maintainAspectRatio: false }
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

<?php

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

$q4 = mysqli_query($conn, "
  SELECT SUM(jumlah_bayar) AS total 
  FROM pembayaran
  WHERE status_pembayaran = 'terkonfirmasi'
  AND MONTH(tanggal_bayar) = MONTH(CURRENT_DATE())
  AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE())
");
$d4 = mysqli_fetch_assoc($q4);

$pendapatan = $d4['total'] ? number_format($d4['total'],0,',','.') : "0";
?>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">

  <div class="col-md-3">
    <div class="card shadow-sm stat-card bg-success text-white">
      <div class="card-body text-center">
        <h5 class="fw-bold">Total Jalur</h5>
        <h2 class="fw-bold"><?= $d1['total']; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm stat-card bg-success text-white">
      <div class="card-body text-center">
        <h5 class="fw-bold">Total Pesanan</h5>
        <h2 class="fw-bold"><?= $d2['total']; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm stat-card bg-success text-white">
      <div class="card-body text-center">
        <h5 class="fw-bold">Total Pending</h5>
        <h2 class="fw-bold"><?= $d3['total']; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm stat-card bg-success text-white">
      <div class="card-body text-center">
        <h5 class="fw-bold">Total Pendapatan Bulanan</h5>
        <h2 class="fw-bold">Rp <?= $pendapatan; ?></h2>
      </div>
    </div>
  </div>

</div>

<!-- CHARTS -->
<div class="row g-4">

  <div class="col-lg-8">
    <div class="card shadow-sm border-0" style="height: 360px;">
      <div class="card-header bg-success text-white fw-bold">
        Pesanan per Bulan
      </div>
      <div class="card-body p-3">
        <canvas id="lineChart"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card shadow-sm border-0" style="height: 360px;">
      <div class="card-header bg-success text-white fw-bold">
        Persentase Jalur
      </div>
      <div class="card-body d-flex justify-content-center align-items-center">
        <canvas id="pieChart" style="max-width: 250px;"></canvas>
      </div>
    </div>
  </div>

</div>

<!-- SEARCH / FILTER -->
<div class="search-section mt-4">
  <h5>Pencarian & Filter</h5>

  <div class="filter-container">
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
      $rj = $conn->query("SELECT nama_jalur FROM jalur_pendakian WHERE status='aktif' OR status='Aktif'");
      while($row = $rj->fetch_assoc()){
        echo '<option value="'.$row['nama_jalur'].'">'.$row['nama_jalur'].'</option>';
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

      <button type="submit" name="export" value="excel" class="btn btn-success">Excel</button>
      <button type="submit" name="export" value="pdf" class="btn btn-danger">PDF</button>
    </form>

  </div>

  <table class="search-table" id="resultTable">
    <thead>
      <tr>
       
        <th>Kode</th>
        <th>Pemesan</th>
        <th>Jalur</th>
        <th>Tgl Pesan</th>
        <th>Total</th>
        <th>Status Pembayaran</th>
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

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// -------------- SEARCH AJAX ---------------
const sInput = document.getElementById('searchInput');
const sStatus = document.getElementById('filterStatus');
const sJalur = document.getElementById('filterJalur');
const sBulan = document.getElementById('filterBulan');
const result = document.querySelector('#resultTable tbody');

function loadSearch(){
  let q = encodeURIComponent(sInput.value.trim());
  let st = encodeURIComponent(sStatus.value);
  let ja = encodeURIComponent(sJalur.value);
  let bl = encodeURIComponent(sBulan.value);

  fetch(`search_data.php?q=${q}&status=${st}&jalur=${ja}&bulan=${bl}`)
    .then(r=>r.json())
    .then(rows=>{
      result.innerHTML = '';
      if(!rows.length){
        result.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--muted)">Tidak ada hasil</td></tr>';
        return;
      }
      let i=1;
      rows.forEach(r=>{
        result.innerHTML += `
          <tr>
            <td>${i++}</td>
            <td>${r.kode_token ?? r.pesanan_id}</td>
            <td>${r.nama_ketua ?? '-'}</td>
            <td>${r.nama_jalur ?? '-'}</td>
            <td>${r.tanggal_pesan ?? '-'}</td>
            <td>Rp ${Number(r.total_bayar||0).toLocaleString('id-ID')}</td>
            <td>${r.status_pembayaran ?? '-'}</td>
          </tr>
        `;
      });
    });
}

sInput.addEventListener('keyup', ()=>{ if(sInput.value.length>=2 || sInput.value.length===0) loadSearch(); });
sStatus.addEventListener('change', loadSearch);
sJalur.addEventListener('change', loadSearch);
sBulan.addEventListener('change', loadSearch);

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
