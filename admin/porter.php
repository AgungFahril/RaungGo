<?php
$page_title = 'Layanan Porter';
include '../includes/auth_admin.php';
include '../backend/koneksi.php';

// Pencarian
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$where = "";
if ($q !== '') {
    $e = $conn->real_escape_string($q);
    $where = "WHERE p.nama_porter LIKE '%$e%' OR p.no_hp LIKE '%$e%'";
}

$data = $conn->query("
   SELECT p.*, j.nama_jalur 
   FROM porter p
   LEFT JOIN jalur_pendakian j ON j.jalur_id = p.jalur_id
   $where
   ORDER BY p.porter_id DESC
");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Layanan Porter - Admin</title>
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
        <h3 class="fw-bold text-success">Layanan Porter</h3>

        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
          + Tambah Porter
        </button>
      </div>

      <!-- SEARCH -->
      <form method="GET" class="mb-3">
        <div class="input-group">
          <input type="text" name="q" class="form-control"
                 placeholder="Cari nama porter atau nomor HP..."
                 value="<?= htmlspecialchars($q) ?>">

          <button type="submit" class="btn btn-success">Cari</button>
          <a href="porter.php" class="btn btn-secondary ms-2">Reset</a>
        </div>
      </form>

      <!-- LIST TABLE -->
      <div class="card shadow-sm border-0">
        <div class="card-body">

          <table class="table table-hover align-middle">
            <thead class="table-success">
              <tr>
                <th>#</th>
                <th>Nama Porter</th>
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
$rows = [];
if ($data->num_rows > 0):
  while ($r = $data->fetch_assoc()):
    $rows[] = $r; // simpan dulu agar modal bisa dibuat di bawah tabel
?>
<tr>
  <td><?= $no++ ?></td>
  <td class="fw-semibold"><?= $r['nama_porter'] ?></td>
  <td><?= $r['no_hp'] ?></td>
  <td class="fw-bold text-success">Rp <?= number_format($r['tarif']) ?></td>
  <td><?= $r['nama_jalur'] ?: '-' ?></td>

  <td>
    <?= $r['available']
      ? "<span class='badge bg-success'>Aktif</span>"
      : "<span class='badge bg-secondary'>Nonaktif</span>" ?>
  </td>

  <td class="text-center">
    <button class="btn btn-info btn-sm px-3"
            data-bs-toggle="modal"
            data-bs-target="#modalEdit<?= $r['porter_id'] ?>">
      Edit
    </button>

    <a onclick="return confirm('Hapus data ini?')"
       href="../backend/porter_hapus.php?id=<?= $r['porter_id'] ?>"
       class="btn btn-danger btn-sm px-3">
      Hapus
    </a>
  </td>
</tr>

<?php
  endwhile;
else:
  echo "<tr><td colspan='7' class='text-center py-3 text-muted'>Tidak ada data.</td></tr>";
endif;
?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- === MODAL TAMBAH === -->
      <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
          <form action="../backend/porter_tambah.php" method="POST" class="modal-content">

            <div class="modal-header bg-success text-white">
              <h5 class="modal-title">Tambah Porter</h5>
              <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

              <label>Nama Porter</label>
              <input type="text" name="nama_porter" class="form-control mb-2" required>

              <label>No HP</label>
              <input type="text" name="no_hp" class="form-control mb-2" required>

              <label>Tarif</label>
              <input type="number" name="tarif" class="form-control mb-2" required>

              <label>Jalur</label>
              <select name="jalur_id" class="form-select mb-2" required>
                <option value="">- Pilih Jalur -</option>
                <?php
                $jalur = $conn->query("SELECT * FROM jalur_pendakian ORDER BY nama_jalur ASC");
                while ($j = $jalur->fetch_assoc()):
                ?>
                  <option value="<?= $j['jalur_id'] ?>"><?= $j['nama_jalur'] ?></option>
                <?php endwhile; ?>
              </select>

            </div>

            <div class="modal-footer">
              <button class="btn btn-success">Simpan</button>
            </div>

          </form>
        </div>
      </div>

      <!-- === MODAL EDIT (DILUAR TABLE) === -->
<?php foreach ($rows as $r): ?>
<div class="modal fade" id="modalEdit<?= $r['porter_id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form action="../backend/porter_update.php" method="POST" class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Edit Porter</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" name="id" value="<?= $r['porter_id'] ?>">

        <label>Nama Porter</label>
        <input type="text" name="nama_porter" class="form-control mb-2"
               value="<?= $r['nama_porter'] ?>" required>

        <label>No HP</label>
        <input type="text" name="no_hp" class="form-control mb-2"
               value="<?= $r['no_hp'] ?>" required>

        <label>Tarif</label>
        <input type="number" name="tarif" class="form-control mb-2"
               value="<?= $r['tarif'] ?>" required>

        <label>Jalur</label>
        <select name="jalur_id" class="form-select mb-2" required>
          <?php
          $jalur = $conn->query("SELECT * FROM jalur_pendakian ORDER BY nama_jalur ASC");
          while ($j = $jalur->fetch_assoc()):
          ?>
            <option value="<?= $j['jalur_id'] ?>"
              <?= $j['jalur_id']==$r['jalur_id']?'selected':'' ?>>
              <?= $j['nama_jalur'] ?>
            </option>
          <?php endwhile; ?>
        </select>

        <label>Status</label>
        <select name="available" class="form-select">
          <option value="1" <?= $r['available']==1?'selected':'' ?>>Aktif</option>
          <option value="0" <?= $r['available']==0?'selected':'' ?>>Nonaktif</option>
        </select>

      </div>

      <div class="modal-footer">
        <button class="btn btn-success">Simpan Perubahan</button>
      </div>

    </form>
  </div>
</div>
<?php endforeach; ?>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
