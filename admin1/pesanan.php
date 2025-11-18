<?php
// file: admin/pesanan.php
session_start();
include '../backend/koneksi.php';

 include 'sidebar.php'; ?>
  <div class="main">
 <?php include 'navbar.php'; ?>

<div class="container-fluid mt-3">

      <?php

// --- PARAMETER FILTER / SEARCH ---
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$date_from = isset($_GET['from']) ? $conn->real_escape_string($_GET['from']) : '';
$date_to   = isset($_GET['to']) ? $conn->real_escape_string($_GET['to']) : '';

// Build WHERE
$where = "WHERE 1=1";
if ($search !== '') {
    $s = "%{$search}%";
    $where .= " AND (
        p.pesanan_id LIKE '{$s}' OR
        p.nama_ketua LIKE '{$s}' OR
        u.nama LIKE '{$s}' OR
        j.nama_jalur LIKE '{$s}'
    )";
}
if ($status !== '') {
    $where .= " AND p.status_pesanan = '{$status}' ";
}
if ($date_from !== '' && $date_to !== '') {
    $where .= " AND DATE(p.tanggal_pesan) BETWEEN '{$date_from}' AND '{$date_to}' ";
}

// Main query: pesanan + user + pendakian + jalur + pembayaran (last payment)
$sql = "
SELECT p.*, u.nama AS nama_user, u.email,
       d.tanggal_pendakian, d.tanggal_turun,
       j.nama_jalur,
       pay.status_pembayaran, pay.bukti_bayar, pay.jumlah_bayar AS bayar_jumlah
FROM pesanan p
LEFT JOIN users u ON p.user_id = u.user_id
LEFT JOIN pendakian d ON p.pendakian_id = d.pendakian_id
LEFT JOIN jalur_pendakian j ON d.jalur_id = j.jalur_id
LEFT JOIN pembayaran pay ON pay.pesanan_id = p.pesanan_id
    AND pay.tanggal_bayar = (
        SELECT MAX(tanggal_bayar) FROM pembayaran WHERE pesanan_id = p.pesanan_id
    )
{$where}
GROUP BY p.pesanan_id
ORDER BY p.pesanan_id DESC
";
$res = $conn->query($sql);

// Chart data: jumlah pesanan & total_bayar per tanggal (within filter if provided)
$chart_where = "WHERE 1=1";
if ($date_from !== '' && $date_to !== '') {
    $chart_where .= " AND DATE(tanggal_pesan) BETWEEN '{$date_from}' AND '{$date_to}' ";
}
$chart_q = $conn->query("
    SELECT DATE(tanggal_pesan) AS tgl, COUNT(*) AS total_pesanan, SUM(total_bayar) AS total_bayar
    FROM pesanan
    {$chart_where}
    GROUP BY DATE(tanggal_pesan)
    ORDER BY DATE(tanggal_pesan) ASC
");
$chart_labels = [];
$chart_counts = [];
$chart_revenue = [];
while ($c = $chart_q->fetch_assoc()) {
    $chart_labels[] = $c['tgl'];
    $chart_counts[] = (int)$c['total_pesanan'];
    $chart_revenue[] = (float)$c['total_bayar'];
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Data Pesanan - Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    body { background: #f6fff6; }
    .container-custom { margin-left: 260px; padding: 24px; }
    .card { border-radius: 10px; }
    .table thead th { vertical-align: middle; }
    .badge-status { min-width: 110px; text-align:center; font-weight:600; }
    .thumb { width:72px; height:auto; border-radius:6px; border:1px solid #ddd; }
    .chart-card { height:300px; }
    @media (max-width: 991px) {
      .container-custom { margin-left: 0; padding: 12px; }
    }
  </style>
</head>
<body>
<div class="app-wrap">
<body>
<div class="app-wrap">

  
<div class="container-custom">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold text-success">📦 Data Pesanan</h3>
    <div>
      <a href="pesanan.php" class="btn btn-outline-secondary me-2">Refresh</a>
      <a href="export_pesanan.php?<?= http_build_query($_GET) ?>" class="btn btn-danger me-2">Cetak Semua</a>
      <a href="tambah_pesanan.php" class="btn btn-success">Tambah Pesanan</a>
    </div>
  </div>

  <!-- FILTER & SEARCH -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <form class="row g-2 align-items-center" method="get" action="">
        <div class="col-md-4">
          <div class="input-group">
            <input name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Cari ID / Nama Ketua / Nama User / Jalur...">
            <button class="btn btn-success" type="submit">Cari</button>
            <a class="btn btn-outline-secondary" href="pesanan.php">Reset</a>
          </div>
        </div>

        <div class="col-md-2">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="pending" <?= $status=='pending'?'selected':'' ?>>pending</option>
            <option value="dibayar" <?= $status=='dibayar'?'selected':'' ?>>dibayar</option>
            <option value="ditolak" <?= $status=='ditolak'?'selected':'' ?>>ditolak</option>
            <option value="selesai" <?= $status=='selesai'?'selected':'' ?>>selesai</option>
          </select>
        </div>

        <div class="col-md-2">
          <input type="date" name="from" value="<?= htmlspecialchars($date_from) ?>" class="form-control" />
        </div>
        <div class="col-md-2">
          <input type="date" name="to" value="<?= htmlspecialchars($date_to) ?>" class="form-control" />
        </div>

        <div class="col-md-2 text-end">
          <button class="btn btn-outline-success">Terapkan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- TABLE & CHART -->
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body p-3">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-success">
                <tr>
                  <th>ID</th>
                  <th>Pemesan / Ketua</th>
                  <th>Jalur</th>
                  <th>Tgl Pendakian</th>
                  <th>Jumlah</th>
                  <th>Total Bayar</th>
                  <th>Status Pesanan</th>
                  <th>Status Bayar</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
              <?php if ($res && $res->num_rows>0): while($r = $res->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($r['pesanan_id']) ?></td>
                  <td>
                    <div class="fw-semibold"><?= htmlspecialchars($r['nama_ketua'] ?: $r['nama_user']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($r['nama_user'] . ' · ' . $r['email']) ?></div>
                  </td>
                  <td><?= htmlspecialchars($r['nama_jalur'] ?: '-') ?></td>
                  <td>
                    <?= htmlspecialchars($r['tanggal_pendakian'] ?: '-') ?>
                    <?php if ($r['tanggal_turun']): ?><div class="text-muted small">s/d <?= $r['tanggal_turun'] ?></div><?php endif; ?>
                  </td>
                  <td><?= (int)$r['jumlah_pendaki'] ?></td>
                  <td>Rp <?= number_format($r['total_bayar'] ?? 0,0,',','.') ?></td>
                  <td>
                    <?php
                      $s = $r['status_pesanan'] ?? 'pending';
                      $col = ($s=='pending'?'warning':($s=='dibayar'?'success':($s=='ditolak'?'danger':'secondary')));
                    ?>
                    <span class="badge bg-<?= $col ?> badge-status"><?= htmlspecialchars($s) ?></span>
                  </td>
                  <td>
                    <?php
                      $ps = $r['status_pembayaran'] ?? '-';
                      $col2 = ($ps=='valid'?'success':($ps=='menunggu'?'warning':($ps=='invalid'?'danger':'secondary')));
                    ?>
                    <span class="badge bg-<?= $col2 ?> badge-status"><?= htmlspecialchars($ps) ?></span>
                  </td>
                  <td>
                    <a href="#" class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#detailModal" data-id="<?= $r['pesanan_id'] ?>">Detail</a>
                    <a href="edit_pesanan.php?id=<?= urlencode($r['pesanan_id']) ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                    <a href="pesanan_proses.php?action=delete&id=<?= urlencode($r['pesanan_id']) ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin hapus pesanan <?= htmlspecialchars($r['pesanan_id']) ?> ?')">Hapus</a>
                    <a href="cetak_pesanan.php?id=<?= urlencode($r['pesanan_id']) ?>" class="btn btn-sm btn-info mb-1" target="_blank">Cetak</a>
                  </td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="9" class="text-center text-muted">Tidak ada data pesanan.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- CHART -->
    <div class="col-lg-4">
      <div class="card shadow-sm chart-card">
        <div class="card-body">
          <h6 class="fw-bold text-success">Grafik Pesanan & Pendapatan</h6>
          <canvas id="areaChart" style="width:100%;height:220px"></canvas>
          <div class="mt-3 text-muted small">
            Jumlah pesanan (bar) & total pendapatan (area gradasi).
          </div>
        </div>
      </div>

      <div class="card mt-3 shadow-sm">
        <div class="card-body text-center">
          <h6 class="text-success fw-bold">Total Pesanan</h6>
          <h3 class="fw-bold">
            <?php
              $totQ = $conn->query("SELECT COUNT(*) AS c FROM pesanan")->fetch_assoc();
              echo number_format($totQ['c']);
            ?>
          </h3>
          <small class="text-muted">Semua waktu</small>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- DETAIL MODAL -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Pesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- konten di-load via AJAX -->
        <div id="detailContent">
          <div class="text-center py-5 text-muted">Memuat...</div>
        </div>
      </div>
      <div class="modal-footer">
        <a id="modalCetak" href="#" target="_blank" class="btn btn-info">Cetak</a>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Chart: area + bar with gradient
const labels = <?= json_encode($chart_labels) ?>;
const counts = <?= json_encode($chart_counts) ?>;
const revenue = <?= json_encode($chart_revenue) ?>;

const ctx = document.getElementById('areaChart').getContext('2d');
// create gradient
const gradient = ctx.createLinearGradient(0,0,0,220);
gradient.addColorStop(0, 'rgba(25,135,84,0.6)');
gradient.addColorStop(1, 'rgba(25,135,84,0.05)');

new Chart(ctx, {
  data: {
    labels: labels,
    datasets: [
      {
        type: 'bar',
        label: 'Jumlah Pesanan',
        data: counts,
        yAxisID: 'y',
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      },
      {
        type: 'line',
        label: 'Pendapatan (Rp)',
        data: revenue,
        yAxisID: 'y1',
        fill: true,
        backgroundColor: gradient,
        borderColor: 'rgba(25,135,84,1)',
        tension: 0.3,
        pointRadius: 2
      }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    scales: {
      y: { type: 'linear', position: 'left', beginAtZero: true },
      y1: {
        type: 'linear',
        position: 'right',
        beginAtZero: true,
        grid: { drawOnChartArea: false },
        ticks: { callback: function(v){ return 'Rp ' + v.toLocaleString(); } }
      }
    }
  }
});

// Modal: load detail via fetch
const detailModal = document.getElementById('detailModal');
detailModal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  const id = button.getAttribute('data-id');
  const content = document.getElementById('detailContent');
  const modalCetak = document.getElementById('modalCetak');
  content.innerHTML = '<div class="text-center py-5 text-muted">Memuat...</div>';
  fetch('detail_pesanan_ajax.php?id=' + encodeURIComponent(id))
    .then(r => r.text())
    .then(html => {
      content.innerHTML = html;
      modalCetak.href = 'cetak_pesanan.php?id=' + encodeURIComponent(id);
    })
    .catch(err => {
      content.innerHTML = '<div class="alert alert-danger">Gagal memuat detail.</div>';
      console.error(err);
    });
});
</script>

</body>
</html>
