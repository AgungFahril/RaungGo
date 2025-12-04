<?php
$page_title = 'Layanan Ojek';
include '../includes/auth_admin.php'; 
include '../backend/koneksi.php';

// Pencarian
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$where = "";
if ($q !== '') {
    $e = $conn->real_escape_string($q);
    $where = "WHERE o.nama_ojek LIKE '%$e%' OR o.no_hp LIKE '%$e%' OR j.nama_jalur LIKE '%$e%'";
}

$data = $conn->query("
    SELECT o.*, j.nama_jalur 
    FROM ojek o
    LEFT JOIN jalur_pendakian j ON o.jalur_id = j.jalur_id
    $where 
    ORDER BY o.nama_ojek ASC
");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Layanan Ojek - Admin</title>
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

      <!-- HEADER -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success">Layanan Ojek</h3>

        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
          + Tambah Ojek
        </button>
      </div>

      <!-- SEARCH -->
      <form method="GET" class="mb-3">
        <div class="input-group">
          <input type="text" name="q" class="form-control"
                 placeholder="Cari nama ojek, jalur, atau no HP..."
                 value="<?= htmlspecialchars($q) ?>">

          <button type="submit" class="btn btn-success">Cari</button>
          <a href="ojek.php" class="btn btn-secondary ms-2">Reset</a>
        </div>
      </form>

      <!-- TABEL -->
      <div class="card shadow-sm border-0">
        <div class="card-body">

          <table class="table table-hover align-middle">
            <thead class="table-success">
  <tr>
    <th width="40">#</th>
    <th>Nama Ojek</th>
    <th>No HP</th>
    <th>Tarif</th>
    <th>Jalur</th>
    <th>Status</th>
    <th width="160" class="text-center">Aksi</th>
  </tr>
</thead>


            <tbody>
<?php
$no = 1;
$modal_list = "";

if ($data->num_rows > 0):
  while ($r = $data->fetch_assoc()):
?>
<tr>
  <td><?= $no++ ?></td>

  <td class="fw-semibold"><?= htmlspecialchars($r['nama_ojek']) ?></td>

  <td><?= htmlspecialchars($r['no_hp']) ?></td>

  <!-- TARIF (dipindah ke atas, sama seperti Porter & Guide) -->
  <td class="fw-bold text-success">
      Rp <?= number_format($r['tarif']) ?>
  </td>

  <!-- JALUR -->
  <td>
    <?= $r['nama_jalur'] ?: 'Tidak Ada Jalur' ?>
</td>


  <td>
    <?= $r['available']
        ? "<span class='badge bg-success'>Aktif</span>"
        : "<span class='badge bg-secondary'>Nonaktif</span>" ?>
  </td>

  <td class="text-center">

    <button class="btn btn-info btn-sm px-3"
            data-bs-toggle="modal"
            data-bs-target="#modalEdit<?= $r['ojek_id'] ?>">
      Edit
    </button>

    <a onclick="return confirm('Hapus data ini?')"
       href="../backend/ojek_hapus.php?id=<?= $r['ojek_id'] ?>"
       class="btn btn-danger btn-sm px-3">
      Hapus
    </a>

  </td>
</tr>
<?php endwhile; else: ?>
<tr>
  <td colspan="7" class="text-center py-3 text-muted">Tidak ada data ditemukan.</td>
</tr>
<?php endif; ?>
</tbody>

          </table>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- =============== MODALS EDIT (Semua disatukan) ================= -->
<?= $modal_list ?>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <form action="../backend/ojek_tambah.php" method="POST" class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Tambah Ojek Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <label class="fw-semibold">Nama Ojek</label>
        <input type="text" name="nama_ojek" class="form-control mb-2" required>

        <label class="fw-semibold">No HP</label>
        <input type="text" name="no_hp" class="form-control mb-2" required>

        <label class="fw-semibold">Tarif</label>
        <input type="number" name="tarif" class="form-control mb-2" required>

        <label class="fw-semibold">Jalur Ojek</label>
        <select name="jalur_id" class="form-select mb-2">
          <?php
          $jalur2 = $conn->query("SELECT * FROM jalur_pendakian ORDER BY nama_jalur ASC");
          while ($j2 = $jalur2->fetch_assoc()):
          ?>
            <option value="<?= $j2['jalur_id'] ?>"><?= $j2['nama_jalur'] ?></option>
          <?php endwhile; ?>
        </select>

      </div>

      <div class="modal-footer">
        <button name="simpan" class="btn btn-success">Simpan</button>
      </div>

    </form>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
