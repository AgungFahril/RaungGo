<?php
// dashboard_dev.php
include '../backend/koneksi.php';
if (isset($_POST['export'])) {
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Admin — Gunung Raung</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Material icons & bootstrap via CDN -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin-style.css">
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-wrap">

  <?php include 'sidebar.php'; ?>

  <div class="main">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid mt-3">

      <?php
// 1. Total Pendaki
$q1 = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM jalur_pendakian
");
$d1 = mysqli_fetch_assoc($q1);


// 2. Total Pesanan
$q2 = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM pesanan
");
$d2 = mysqli_fetch_assoc($q2);

// 3. Total Pending Pembayaran
$q3 = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM pembayaran
    WHERE status_pembayaran = 'pending'
");
$d3 = mysqli_fetch_assoc($q3);

// 4. Total Pendapatan Bulanan (status = terkonfirmasi)
$q4 = mysqli_query($conn, "
    SELECT SUM(jumlah_bayar) AS total 
    FROM pembayaran
    WHERE status_pembayaran = 'terkonfirmasi'
    AND MONTH(tanggal_bayar) = MONTH(CURRENT_DATE())
    AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE())
");
$d4 = mysqli_fetch_assoc($q4);
?>
<div class="card-container">
    <div class="card">
        <h5>Total Jalur</h5>
        <p><?= $d1['total'] ?? 0; ?></p>
    </div>

    <div class="card">
        <h5>Total Pesanan</h5>
        <p><?= $d2['total'] ?? 0; ?></p>
    </div>

    <div class="card">
        <h5>Total Pending</h5>
        <p><?= $d3['total'] ?? 0; ?></p>
    </div>

    <div class="card">
        <h5>Total Pendapatan Bulanan</h5>
        <p>Rp <?= number_format($d4['total'] ?? 0, 0, ',', '.'); ?></p>
    </div>
</div>

      <!-- Charts -->
      <div class="charts">
        <div class="chart-card card">
          <h5>Pesanan per Bulan</h5>
          <canvas id="chartPesanan" height="100"></canvas>
        </div>

        <div class="chart-card card small-grid">
          <div>
            <h6>Pendapatan per Bulan</h6>
            <canvas id="chartPendapatan" height="120"></canvas>
          </div>
          <div>
            <h6>Jalur Populer</h6>
            <canvas id="chartJalur" height="100"></canvas>
          </div>
        </div>
      </div>

      <!-- Search & filters -->
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
              $rj = $conn->query("SELECT nama_jalur FROM jalur_pendakian WHERE status='aktif' OR status='Aktif' ORDER BY nama_jalur ASC");
              while($row = $rj->fetch_assoc()){
                echo '<option value="'.htmlspecialchars($row['nama_jalur']).'">'.htmlspecialchars($row['nama_jalur']).'</option>';
              }
            ?>
          </select>

          <select id="filterBulan">
            <option value="">Semua Bulan</option>
            <?php
              // last 12 months
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

     <button type="submit" name="export" value="excel" class="btn-export excel">
    <i class="fas fa-file-excel"></i> Excel
   </button>
     <button type="submit" name="export" value="pdf" class="btn-export pdf">
    <i class="fas fa-file-pdf"></i> PDF
   </button>
 </form>
</div>

        <table class="search-table" id="resultTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Kode</th>
              <th>Pemesan</th>
              <th>Jalur</th>
              <th>Tgl Pesan</th>
              <th>Total</th>
              <th>Status Pembayaran</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="7" style="text-align:center;color:var(--muted)">Gunakan kotak pencarian atau filter untuk melihat hasil</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // load charts
  fetch('get_data.php').then(r=>r.json()).then(d=>{
    // Pesanan (line)
    new Chart(document.getElementById('chartPesanan'), {
      type:'line',
      data:{
        labels: d.bulan,
        datasets:[{label:'Pesanan',data:d.jumlah_pesanan,borderColor:'#2E7D32',backgroundColor:'rgba(46,125,50,0.12)',fill:true}]
      },
      options:{responsive:true,plugins:{legend:{display:false}}}
    });

    // Pendapatan (bar)
    new Chart(document.getElementById('chartPendapatan'),{
      type:'bar',
      data:{labels:d.bulan,datasets:[{label:'Pendapatan',data:d.total_pendapatan,backgroundColor:'#43A047'}]},
      options:{responsive:true,plugins:{legend:{display:false}}}
    });

    // Jalur (pie)
    new Chart(document.getElementById('chartJalur'),{
      type:'pie',
      data:{labels:d.nama_jalur,datasets:[{data:d.jumlah_jalur,backgroundColor:['#1B5E20','#2E7D32','#66BB6A','#A5D6A7','#C8E6C9']}]},
      options:{responsive:true}
    });
  });

  // advanced search
  const searchInput = document.getElementById('searchInput');
  const filterStatus = document.getElementById('filterStatus');
  const filterJalur = document.getElementById('filterJalur');
  const filterBulan = document.getElementById('filterBulan');
  const resultTbody = document.querySelector('#resultTable tbody');

  function loadSearch(){
    const q = encodeURIComponent(searchInput.value.trim());
    const status = encodeURIComponent(filterStatus.value);
    const jalur = encodeURIComponent(filterJalur.value);
    const bulan = encodeURIComponent(filterBulan.value);

    fetch(`search_data.php?q=${q}&status=${status}&jalur=${jalur}&bulan=${bulan}`)
      .then(r=>r.json())
      .then(rows=>{
        resultTbody.innerHTML = '';
        if(!rows.length){
          resultTbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--muted)">Tidak ada hasil</td></tr>';
          return;
        }
        let i=1;
        rows.forEach(r=>{
          resultTbody.innerHTML += `
            <tr>
              <td>${i++}</td>
              <td>${r.kode_token ?? r.pesanan_id}</td>
              <td>${r.nama_ketua ?? '-'}</td>
              <td>${r.nama_jalur ?? '-'}</td>
              <td>${r.tanggal_pesan ?? '-'}</td>
              <td>Rp ${Number(r.total_bayar||0).toLocaleString('id-ID')}</td>
              <td>${r.status_pembayaran ?? '-'}</td>
            </tr>`;
        });
      });
  }

  // events
  searchInput.addEventListener('keyup', ()=>{ if(searchInput.value.length >= 2 || searchInput.value.length === 0) loadSearch(); });
  filterStatus.addEventListener('change', loadSearch);
  filterJalur.addEventListener('change', loadSearch);
  filterBulan.addEventListener('change', loadSearch);

  

  // Export Excel / PDF (mengirim filter aktif ke hidden form)
const exportForm = document.querySelector('.export-form');
const exp_q = document.getElementById('exp_q');
const exp_status = document.getElementById('exp_status');
const exp_jalur = document.getElementById('exp_jalur');
const exp_bulan = document.getElementById('exp_bulan');

exportForm.addEventListener('submit', () => {
  exp_q.value = searchInput.value.trim();
  exp_status.value = filterStatus.value;
  exp_jalur.value = filterJalur.value;
  exp_bulan.value = filterBulan.value;
});

function syncExportFilters() {
  document.getElementById('exp_q').value = document.getElementById('searchInput').value;
}

</script>
</body>
</html>
