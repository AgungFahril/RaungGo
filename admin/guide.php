<?php
$page_title = 'Layanan Guide';
include '../includes/auth_admin.php';
include '../backend/koneksi.php';

// pencarian
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$where = "";
if ($q !== '') {
    $e = $conn->real_escape_string($q);
    $where = "WHERE g.nama_guide LIKE '%$e%' OR g.no_hp LIKE '%$e%'";
}

// ambil data guide + nama jalur
$data = $conn->query("
   SELECT g.*, jp.nama_jalur 
   FROM guide g
   LEFT JOIN jalur_pendakian jp ON jp.jalur_id = g.jalur_id
   $where
   ORDER BY g.guide_id DESC
");

// ambil daftar jalur untuk dropdown (dipakai di modal tambah & edit)
$jalurList = [];
$jr = $conn->query("SELECT * FROM jalur_pendakian ORDER BY nama_jalur ASC");
while ($j = $jr->fetch_assoc()) $jalurList[] = $j;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Layanan Guide - Admin</title>
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
        <h3 class="fw-bold text-success">Layanan Guide</h3>

        <!-- tombol tambah harus type="button" agar tidak submit form lain -->
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
          + Tambah Guide
        </button>
      </div>

      <!-- SEARCH -->
      <form method="GET" class="mb-3" onsubmit="">
        <div class="input-group">
          <input type="text" name="q" class="form-control"
                 placeholder="Cari nama guide atau no HP..."
                 value="<?= htmlspecialchars($q) ?>">

          <button type="submit" class="btn btn-success">Cari</button>
          <a href="guide.php" class="btn btn-secondary ms-2">Reset</a>
        </div>
      </form>

      <!-- TABLE -->
      <div class="card shadow-sm border-0">
        <div class="card-body">

          <table class="table table-hover align-middle">
            <thead class="table-success">
              <tr>
                <th>#</th>
                <th>Nama Guide</th>
                <th>No HP</th>
                <th>Tarif</th>
                <th>Jalur</th>
                <th>Status</th>
                <th width="160" class="text-center">Aksi</th>
              </tr>
            </thead>

            <tbody>
<?php
$rows = [];
$no = 1;
if ($data && $data->num_rows > 0):
  while ($r = $data->fetch_assoc()):
    $rows[] = $r; // simpan untuk modal edit di bawah tabel
?>
<tr>
  <td><?= $no++ ?></td>
  <td class="fw-semibold"><?= htmlspecialchars($r['nama_guide']) ?></td>
  <td><?= htmlspecialchars($r['no_hp']) ?></td>
  <td class="fw-bold text-success">Rp <?= number_format($r['tarif']) ?></td>
  <td><?= htmlspecialchars($r['nama_jalur'] ?: '-') ?></td>
  <td>
    <?= $r['available'] ? "<span class='badge bg-success'>Aktif</span>" : "<span class='badge bg-secondary'>Nonaktif</span>" ?>
  </td>
  <td class="text-center">
    <!-- tombol edit type button supaya tidak submit form -->
    <button type="button" class="btn btn-info btn-sm px-3"
            data-bs-toggle="modal"
            data-bs-target="#modalEdit<?= $r['guide_id'] ?>">
      Edit
    </button>

    <a onclick="return confirm('Hapus data ini?')"
       href="../backend/guide_hapus.php?id=<?= $r['guide_id'] ?>"
       class="btn btn-danger btn-sm px-3">
      Hapus
    </a>
  </td>
</tr>
<?php
  endwhile;
else:
  echo "<tr><td colspan='7' class='text-center py-3 text-muted'>Tidak ada data ditemukan.</td></tr>";
endif;
?>
            </tbody>
          </table>

        </div>
      </div>

      <!-- MODAL TAMBAH (DILUAR TABLE) -->
      <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
          <form action="../backend/guide_tambah.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title">Tambah Guide</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="mb-2">
                <label class="form-label">Nama Guide</label>
                <input type="text" name="nama_guide" class="form-control" required>
              </div>

              <div class="mb-2">
                <label class="form-label">No HP</label>
                <input type="text" name="no_hp" class="form-control" required>
              </div>

              <div class="mb-2">
                <label class="form-label">Tarif</label>
                <input type="number" name="tarif" class="form-control" required>
              </div>

              <div class="mb-2">
                <label class="form-label">Jalur</label>
                <select name="jalur_id" class="form-select" required>
                  <option value="">- Pilih Jalur -</option>
                  <?php foreach ($jalurList as $j): ?>
                    <option value="<?= $j['jalur_id'] ?>"><?= htmlspecialchars($j['nama_jalur']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- MODAL EDITS (semua modal edit diletakkan di luar table, dibuat dari $rows) -->
<?php foreach ($rows as $r): ?>
<div class="modal fade" id="modalEdit<?= $r['guide_id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form action="../backend/guide_update.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Edit Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" value="<?= $r['guide_id'] ?>">

        <div class="mb-2">
          <label class="form-label">Nama Guide</label>
          <input type="text" name="nama_guide" class="form-control" value="<?= htmlspecialchars($r['nama_guide']) ?>" required>
        </div>

        <div class="mb-2">
          <label class="form-label">No HP</label>
          <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($r['no_hp']) ?>" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Tarif</label>
          <input type="number" name="tarif" class="form-control" value="<?= intval($r['tarif']) ?>" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Jalur</label>
          <select name="jalur_id" class="form-select" required>
            <?php foreach ($jalurList as $j): ?>
              <option value="<?= $j['jalur_id'] ?>" <?= $j['jalur_id']==$r['jalur_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($j['nama_jalur']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Status</label>
          <select name="available" class="form-select">
            <option value="1" <?= $r['available']==1 ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= $r['available']==0 ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
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
