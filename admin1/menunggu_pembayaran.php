<?php
$page_title = 'Menunggu Konfirmasi Pembayaran';
include '../backend/koneksi.php';

// Query pencarian
$where = "WHERE status_pembayaran = 'pending'";
if (isset($_GET['cari']) && $_GET['cari'] != '') {
    $cari = $conn->real_escape_string($_GET['cari']);
    $where .= " AND (
        pembayaran_id LIKE '%$cari%' OR 
        pesanan_id LIKE '%$cari%' OR 
        metode LIKE '%$cari%'
    )";
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Menunggu Konfirmasi Pembayaran - Admin</title>
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
        <h3 class="fw-bold text-warning">⏳ Menunggu Konfirmasi Pembayaran</h3>

        <a href="pembayaran.php" class="btn btn-success">⬅ Kembali ke Pembayaran</a>
      </div>

      <!-- SEARCH -->
      <form method="GET" class="mb-3">
        <div class="input-group">
          <input type="text" name="cari" class="form-control"
                 placeholder="Cari ID pembayaran, ID pesanan, metode..."
                 value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
          <button type="submit" class="btn btn-warning">Cari</button>
          <a href="menunggu_konfirmasi.php" class="btn btn-danger ms-2">Reset</a>
        </div>
      </form>

      <!-- TABEL PEMBAYARAN PENDING -->
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <table class="table table-hover align-middle">
            <thead class="table-warning">
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

                    // Status pending
                    $status = '<span class="badge bg-warning text-dark">Pending</span>';

                    // Bukti transfer
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
                            <a href='konfirmasi_pembayaran.php?id={$r['pembayaran_id']}'
                               class='btn btn-success btn-sm'>Konfirmasi</a>

                            <a href='tolak_pembayaran.php?id={$r['pembayaran_id']}'
                               onclick='return confirm(\"Yakin tolak pembayaran ini?\")'
                               class='btn btn-danger btn-sm'>Tolak</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='text-center text-muted'>
                        Tidak ada pembayaran pending.
                      </td></tr>";
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
