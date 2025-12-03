<?php
$page_title = 'Pembayaran';
include '../backend/koneksi.php';

// Query pencarian
$where = "WHERE 1=1";

if (!empty($_GET['cari'])) {
    $cari = $conn->real_escape_string($_GET['cari']);
    $where .= " AND (
        ps.nama_ketua LIKE '%$cari%' OR 
        p.metode LIKE '%$cari%' OR 
        p.jumlah_bayar LIKE '%$cari%' OR 
        p.status_pembayaran LIKE '%$cari%'
    )";
}

// Filter status
if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $where .= " AND p.status_pembayaran = '$status'";
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

  <style>
    .thumbnail-bukti {
        width: 70px;
        height: 70px;
        object-fit: cover;
        cursor: pointer;
        border-radius: 6px;
    }
  </style>
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

      <!-- ========================= SEARCH + FILTER ========================= -->
      <form method="GET" class="mb-3 row g-2">

        <div class="col-md-6">
            <div class="input-group">
                <input type="text" name="cari" class="form-control"
                       placeholder="Cari nama pemesanan, status"
                       value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
                <button type="submit" class="btn btn-success">Cari</button>
                <a href="pembayaran.php" class="btn btn-danger">Reset</a>
            </div>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending"       <?= (($_GET['status'] ?? '') == 'pending') ? 'selected':'' ?>>Pending</option>
                <option value="terkonfirmasi" <?= (($_GET['status'] ?? '') == 'terkonfirmasi') ? 'selected':'' ?>>Terkonfirmasi</option>
                <option value="ditolak"       <?= (($_GET['status'] ?? '') == 'ditolak') ? 'selected':'' ?>>Ditolak</option>
            </select>
        </div>

      </form>

      <!-- ========================= TABEL PEMBAYARAN ========================= -->
      <div class="card shadow-sm border-0">
        <div class="card-body">

          <table class="table table-hover align-middle">
            <thead class="table-success">
              <tr>
                <th>No</th>
                <th style="display:none;">ID Bayar</th>
                <th>Nama Pemesan</th>
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
                SELECT 
                    p.pembayaran_id,
                    p.pesanan_id,
                    ps.nama_ketua AS nama_pemesan,
                    p.metode,
                    p.jumlah_bayar,
                    p.tanggal_bayar,
                    p.status_pembayaran,
                    p.bukti_bayar
                FROM pembayaran AS p
                INNER JOIN pesanan AS ps 
                    ON p.pesanan_id = ps.pesanan_id
                $where
                ORDER BY p.tanggal_bayar DESC
            ");

            $i = 1; // NOMOR URUT

            if ($q->num_rows > 0) {
                while ($r = $q->fetch_assoc()) {

                    // Badge status
                    $badge = [
                      'pending'        => 'warning',
                      'terkonfirmasi'  => 'success',
                      'ditolak'        => 'danger'
                    ];

                    $status = "<span class='badge bg-".$badge[$r['status_pembayaran']]."'>"
                               .ucfirst($r['status_pembayaran'])."</span>";

                    // Thumbnail bukti
                    $buktiPath = "../uploads/bukti/" . $r['bukti_bayar'];

                    if (!empty($r['bukti_bayar']) && file_exists($buktiPath)) {
                        $bukti = "
                            <img src='$buktiPath' 
                                 class='thumbnail-bukti'
                                 data-bs-toggle='modal'
                                 data-bs-target='#previewModal'
                                 onclick=\"previewBukti('$buktiPath')\">
                        ";
                    } else {
                        $bukti = "<span class='text-muted'>Tidak ada</span>";
                    }

                    echo "
<tr>
    <td>$i</td>
    <td style='display:none;'>{$r['pembayaran_id']}</td>
    <td>{$r['nama_pemesan']}</td>
    <td>{$r['metode']}</td>
    <td>Rp " . number_format($r['jumlah_bayar'], 0, ',', '.') . "</td>
    <td>{$r['tanggal_bayar']}</td>
    <td>$status</td>
    <td>$bukti</td>
    <td>
        <button class='btn btn-success btn-sm'
            onclick=\"updateStatus('{$r['pembayaran_id']}', 'terkonfirmasi')\">
            Terima
        </button>

        <button class='btn btn-danger btn-sm'
            onclick=\"updateStatus('{$r['pembayaran_id']}', 'ditolak')\">
            Tolak
        </button>
    </td>
</tr>";

                    $i++;
                }
            } else {
                echo "<tr><td colspan='10' class='text-center text-muted'>Tidak ada data ditemukan.</td></tr>";
            }
            ?>
            </tbody>
          </table>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- ========================= MODAL PREVIEW FOTO ========================= -->
<div class="modal fade" id="previewModal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0">
            <div class="modal-body text-center">
                <img id="previewImg" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<script>
function previewBukti(src) {
    document.getElementById('previewImg').src = src;
}

function updateStatus(id, status) {
    if (!confirm("Yakin ingin mengubah status pembayaran?")) return;

    fetch('update_status_pembayaran.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}&status=${status}`
    })
    .then(res => res.text())
    .then(data => {
        alert(data);
        location.reload();
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
