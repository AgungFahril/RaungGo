<?php
$page_title = 'Data Pesanan';
include '../backend/koneksi.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Data Pesanan - Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>
<div class="app-wrap">

  <?php include 'sidebar.php'; ?>

  <div class="main">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid mt-3">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"> Data Pesanan</h3>
        <a href="dashboard_1.php" class="btn btn-success">⬅ Kembali ke Dashboard</a>
      </div>

      <!-- Kotak Pencarian -->
      <form method="GET" class="mb-3">
        <div class="input-group">
          <input type="text" name="cari" class="form-control" placeholder="Cari nama ketua, ID pesanan, no identitas..." value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
          <button type="submit" class="btn btn-success">Cari</button>
          <a href="export_pesanan.php" class="btn btn-danger ms-2">Cetak PDF</a>
        </div>
      </form>

      <!-- Tabel Data Pesanan -->
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <table class="table table-hover align-middle">
            <thead class="table-success">
              <tr>
                <th>ID Pesanan</th>
                <th>Nama Ketua</th>
                <th>Jumlah Pendaki</th>
                <th>Status</th>
                <th>Tanggal Pesan</th>
                <th>Kode Token</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $where = "";
              if (isset($_GET['cari']) && $_GET['cari'] != '') {
                  $cari = $conn->real_escape_string($_GET['cari']);
                  $where = "WHERE p.nama_ketua LIKE '%$cari%' OR p.pesanan_id LIKE '%$cari%' OR p.no_identitas LIKE '%$cari%'";
              }

              $q = $conn->query("SELECT p.pesanan_id, p.nama_ketua, p.jumlah_pendaki, p.status_pesanan, p.tanggal_pesan, p.kode_token
                                 FROM pesanan p $where ORDER BY p.tanggal_pesan DESC");
              if ($q->num_rows > 0) {
                  while ($r = $q->fetch_assoc()) {
                      $statusBadge = '';
                      if ($r['status_pesanan'] == 'lunas') $statusBadge = '<span class="badge bg-success">Lunas</span>';
                      elseif ($r['status_pesanan'] == 'menunggu_pembayaran') $statusBadge = '<span class="badge bg-warning">Menunggu Pembayaran</span>';
                      elseif ($r['status_pesanan'] == 'batal') $statusBadge = '<span class="badge bg-danger">Batal</span>';

                      echo "<tr>
                              <td>{$r['pesanan_id']}</td>
                              <td>{$r['nama_ketua']}</td>
                              <td>{$r['jumlah_pendaki']}</td>
                              <td>{$statusBadge}</td>
                              <td>{$r['tanggal_pesan']}
                               <td>{$r['kode_token']}</td> 
                              </td>
                              <td>
                                <a href='detail_pesanan.php?id={$r['pesanan_id']}' class='btn btn-info btn-sm'>Detail</a>
                              </td>
                            </tr>";
                  }
              } else {
                  echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada data ditemukan.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
