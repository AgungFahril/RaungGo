<?php
include '../backend/koneksi.php';
include 'navbar.php';
include 'sidebar.php'; 

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pembayaran - Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .sidebar { width:240px; position:fixed; top:0; left:0; bottom:0; background:linear-gradient(180deg,#1f6f2e,#114d15); color:#fff; padding:20px; }
    .content { margin-left:260px; padding:24px; }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="d-flex align-items-center mb-4">
      <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:52px;height:52px">GR</div>
      <div class="ms-3">
        <div style="font-weight:700;">Gunung Raung</div>
        <div style="font-size:12px;opacity:.9">Admin Panel</div>
      </div>
    </div>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
      <li class="nav-item mb-2"><a href="data_pendaki.php" class="nav-link text-white">Data Pendaki</a></li>
      <li class="nav-item mb-2"><a href="pesanan.php" class="nav-link text-white">Pesanan</a></li>
      <li class="nav-item mb-2"><a href="pembayaran.php" class="nav-link text-white fw-bold">Pembayaran</a></li>
      <li class="nav-item mb-2"><a href="pendaki_aktif.php" class="nav-link text-white">Pendaki Aktif</a></li>
    </ul>
  </div>

  <div class="content">
    <div class="d-flex justify-content-between mb-3">
      <h3 class="text-success">Pembayaran</h3>
      <a href="dashboard.php" class="btn btn-success">← Kembali ke Dashboard</a>
    </div>

    <div class="card p-3 mb-4">
      <form method="GET" class="row g-2">
        <div class="col-md-8">
          <input name="q" class="form-control" placeholder="Cari ID pembayaran, ID pesanan, metode, status..." value="<?php echo isset($_GET['q'])?htmlspecialchars($_GET['q']):''; ?>">
        </div>
        <div class="col-auto"><button class="btn btn-success">Cari</button></div>
        <div class="col-auto"><a href="pembayaran.php" class="btn btn-outline-secondary">Reset</a></div>
      </form>
    </div>

    <div class="card p-3 shadow-sm">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead class="table-success">
            <tr>
              <th>ID Bayar</th>
              <th>ID Pesanan</th>
              <th>Metode</th>
              <th>Jumlah</th>
              <th>Tanggal Bayar</th>
              <th>Status</th>
              <th>Bukti</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
<?php
$q = '';
if (!empty($_GET['q'])) $q = $conn->real_escape_string($_GET['q']);
$sql = "SELECT * FROM pembayaran WHERE 1";
if ($q !== '') {
  $sql .= " AND (pembayaran_id LIKE '%$q%' OR pesanan_id LIKE '%$q%' OR metode LIKE '%$q%' OR status_pembayaran LIKE '%$q%')";
}
$sql .= " ORDER BY tanggal_bayar DESC LIMIT 200";
$res = $conn->query($sql);
if ($res && $res->num_rows) {
  while($r = $res->fetch_assoc()){
    echo "<tr>";
    echo "<td>".$r['pembayaran_id']."</td>";
    echo "<td>".$r['pesanan_id']."</td>";
    echo "<td>".htmlspecialchars($r['metode'])."</td>";
    echo "<td>Rp ".number_format($r['jumlah_bayar'],0,',','.')."</td>";
    echo "<td>".$r['tanggal_bayar']."</td>";
    echo "<td>".htmlspecialchars($r['status_pembayaran'])."</td>";
    $bukti = !empty($r['bukti_bayar']) ? "<a target='_blank' href='../uploads/".$r['bukti_bayar']."'>Lihat</a>" : "Tidak ada";
    echo "<td>$bukti</td>";
    echo "<td>
            <a href='edit_pembayaran.php?id=".$r['pembayaran_id']."' class='btn btn-sm btn-primary'>Edit</a>
            <a href=\"delete.php?type=pembayaran&id=".$r['pembayaran_id']."\" onclick=\"return confirm('Hapus pembayaran?')\" class='btn btn-sm btn-danger'>Hapus</a>
          </td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='8' class='text-center'>Tidak ada data pembayaran.</td></tr>";
}
?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
