<?php
$page_title = 'Pembayaran';
include '../backend/koneksi.php';

// Query pencarian
$where = "";
if (isset($_GET['cari']) && $_GET['cari'] != '') {
    $cari = $conn->real_escape_string($_GET['cari']);
    $where = "WHERE 
                pembayaran_id LIKE '%$cari%' OR 
                pesanan_id LIKE '%$cari%' OR 
                metode LIKE '%$cari%' OR 
                status_pembayaran LIKE '%$cari%'";
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pembayaran - Admin</title>
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
        <h3 class="fw-bold text-success">Pembayaran</h3>
        <a href="dashboard_1.php" class="btn btn-success">⬅ Kembali ke Dashboard</a>
      </div>

      <!-- SEARCH -->
      <form method="GET" class="mb-3">
        <div class="input-group">
          <input type="text" name="cari" class="form-control"
                 placeholder="Cari ID pembayaran, ID pesanan, metode, status..."
                 value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
          <button type="submit" class="btn btn-success">Cari</button>
          <a href="pembayaran.php" class="btn btn-danger ms-2">Reset</a>
        </div>
      </form>

      <!-- TABEL PEMBAYARAN -->
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <table class="table table-hover align-middle">
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
            $q = $conn->query("
                SELECT * FROM pembayaran
                $where 
                ORDER BY tanggal_bayar DESC
            ");

            if ($q->num_rows > 0) {
                while ($r = $q->fetch_assoc()) {

                    // Badge warna
                    if ($r['status_pembayaran'] == 'pending')     $status = '<span class="badge bg-warning">Pending</span>';
                    elseif ($r['status_pembayaran'] == 'terkonfirmasi') $status = '<span class="badge bg-success">Terkonfirmasi</span>';
                    elseif ($r['status_pembayaran'] == 'ditolak')  $status = '<span class="badge bg-danger">Ditolak</span>';
                    else $status = '<span class="badge bg-secondary">Tidak Diketahui</span>';

                    // Bukti bayar
                    $bukti = $r['bukti_bayar'] != '' 
                            ? "<a href='../uploads/{$r['bukti_bayar']}' target='_blank' class='btn btn-info btn-sm'>Lihat</a>"
                            : "<span class='text-muted'>Tidak ada</span>";

                    echo "
                    <tr>
                        <td>{$r['pembayaran_id']}</td>
                        <td>{$r['pesanan_id']}</td>
                        <td>{$r['metode']}</td>
                        <td>Rp " . number_format($r['jumlah_bayar'], 0, ',', '.') . "</td>
                        <td>{$r['tanggal_bayar']}</td>
                        <td>$status</td>
                        <td>$bukti</td>
                        <td>
                            <a href='edit_pembayaran.php?id={$r['pembayaran_id']}' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='hapus_pembayaran.php?id={$r['pembayaran_id']}' 
                               class='btn btn-danger btn-sm'
                               onclick='return confirm(\"Yakin ingin menghapus data pembayaran ini?\")'>
                               Hapus
                            </a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='text-center text-muted'>Tidak ada data ditemukan.</td></tr>";
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
</body>
</html>
