<?php
$page_title = 'Data Jalur Pendakian';
include '../backend/koneksi.php';

// SEARCH
$where = "";
if (isset($_GET['cari']) && $_GET['cari'] != '') {
    $cari = $conn->real_escape_string($_GET['cari']);
    $where = "WHERE nama_jalur LIKE '%$cari%'";
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Data Jalur Pendakian - Admin</title>
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
        <h3 class="fw-bold text-success">Data Jalur Pendakian</h3>
       
      </div>

      <!-- FORM PENCARIAN -->
      <form method="GET" class="mb-3">
        <div class="input-group">
          <input type="text" name="cari" class="form-control"
                 placeholder="Cari nama jalur..."
                 value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
          <button type="submit" class="btn btn-success">Cari</button>
          <a href="jalur_pendakian.php" class="btn btn-danger ms-2">Reset</a>
        </div>
      </form>

      <!-- TABLE DATA JALUR -->
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <table class="table table-hover align-middle">
            <thead class="table-success">
              <tr>
                <th>ID</th>
                <th>Nama Jalur</th>
                <th>Kuota Harian</th>
                <th>Tarif Tiket</th>
                <th>Status</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
              </tr>
            </thead>

            <tbody>
              <?php
              $q = $conn->query("SELECT * FROM jalur_pendakian $where ORDER BY jalur_id DESC");

              if ($q->num_rows > 0) {
                  while ($r = $q->fetch_assoc()) {

                      $badge = $r['status'] == 'aktif'
                          ? "<span class='badge bg-success'>Aktif</span>"
                          : "<span class='badge bg-danger'>Ditutup</span>";

                      echo "
                        <tr>
                          <td>{$r['jalur_id']}</td>
                          <td>{$r['nama_jalur']}</td>
                          <td>{$r['kuota_harian']}</td>
                          <td>Rp " . number_format($r['tarif_tiket'], 0, ',', '.') . "</td>
                          <td>$badge</td>
                          <td width='30%'>{$r['deskripsi']}</td>
                          <td>
                            <a href='edit_jalur.php?id={$r['jalur_id']}' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='hapus_jalur.php?id={$r['jalur_id']}'
                               onclick='return confirm(\"Hapus jalur ini?\")'
                               class='btn btn-danger btn-sm'>Hapus</a>
                          </td>
                        </tr>
                      ";
                  }
              } else {
                  echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada data jalur.</td></tr>";
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
