<?php
$page_title = 'Data Jalur Pendakian';
include '../includes/auth_admin.php';   // proteksi admin
include '../backend/koneksi.php';       // koneksi database

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

  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahJalur">
    + Tambah Jalur
  </button>
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
                            
                            <!-- TOMBOL DETAIL -->
                            <button 
                              class='btn btn-info btn-sm text-white detailJalurBtn'
                              data-id='{$r['jalur_id']}'
                              data-nama='{$r['nama_jalur']}'
                              data-kuota='{$r['kuota_harian']}'
                              data-tarif='" . number_format($r['tarif_tiket'], 0, ',', '.') . "'
                              data-status='{$r['status']}'
                              data-deskripsi=\"" . htmlspecialchars($r['deskripsi']) . "\"
                            >Detail</button>

                            <!-- TOMBOL EDIT -->
                            <button 
                              class='btn btn-warning btn-sm editJalurBtn'
                              data-id='{$r['jalur_id']}'
                              data-nama='{$r['nama_jalur']}'
                              data-kuota='{$r['kuota_harian']}'
                              data-tarif='{$r['tarif_tiket']}'
                              data-status='{$r['status']}'
                              data-deskripsi=\"" . htmlspecialchars($r['deskripsi']) . "\"
                            >Edit</button>

                            <!-- TOMBOL HAPUS -->
                            <a href='../backend/hapus_jalur.php?id={$r['jalur_id']}'
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

<!-- ====================== MODAL DETAIL ====================== -->
<div class="modal fade" id="modalDetailJalur" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Detail Jalur Pendakian</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <table class="table table-bordered">
          <tr>
            <th width="25%">ID</th>
            <td id="detail_id"></td>
          </tr>
          <tr>
            <th>Nama Jalur</th>
            <td id="detail_nama"></td>
          </tr>
          <tr>
            <th>Kuota Harian</th>
            <td id="detail_kuota"></td>
          </tr>
          <tr>
            <th>Tarif Tiket</th>
            <td id="detail_tarif"></td>
          </tr>
          <tr>
            <th>Status</th>
            <td id="detail_status"></td>
          </tr>
          <tr>
            <th>Deskripsi</th>
            <td id="detail_deskripsi"></td>
          </tr>
        </table>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

<!-- ====================== MODAL EDIT ====================== -->
<div class="modal fade" id="modalEditJalur" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Edit Jalur Pendakian</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form action="../backend/update_jalur.php" method="POST">
        <div class="modal-body">
          
          <input type="hidden" name="jalur_id" id="edit_jalur_id">

          <div class="mb-3">
            <label class="form-label">Nama Jalur</label>
            <input type="text" name="nama_jalur" id="edit_nama_jalur" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Kuota Harian</label>
            <input type="number" name="kuota_harian" id="edit_kuota" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Tarif Tiket</label>
            <input type="number" name="tarif_tiket" id="edit_tarif" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="edit_status" class="form-select" required>
              <option value="aktif">Aktif</option>
              <option value="ditutup">Ditutup</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- MODAL TAMBAH JALUR -->
<div class="modal fade" id="modalTambahJalur" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Tambah Jalur Pendakian</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form action="../backend/tambah_jalur.php" method="POST">
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Nama Jalur</label>
            <input type="text" name="nama_jalur" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Kuota Harian</label>
            <input type="number" name="kuota_harian" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Tarif Tiket</label>
            <input type="number" name="tarif_tiket" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              <option value="aktif">Aktif</option>
              <option value="ditutup">Ditutup</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Tambah Jalur</button>
        </div>
      </form>

    </div>
  </div>
</div>


<!-- ====================== SCRIPT ====================== -->
<script>
// MODAL EDIT
document.querySelectorAll('.editJalurBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('edit_jalur_id').value = this.dataset.id;
        document.getElementById('edit_nama_jalur').value = this.dataset.nama;
        document.getElementById('edit_kuota').value = this.dataset.kuota;
        document.getElementById('edit_tarif').value = this.dataset.tarif;
        document.getElementById('edit_status').value = this.dataset.status;
        document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;

        new bootstrap.Modal(document.getElementById('modalEditJalur')).show();
    });
});

// MODAL DETAIL
document.querySelectorAll('.detailJalurBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('detail_id').innerText = this.dataset.id;
        document.getElementById('detail_nama').innerText = this.dataset.nama;
        document.getElementById('detail_kuota').innerText = this.dataset.kuota;
        document.getElementById('detail_tarif').innerText = "Rp " + this.dataset.tarif;
        document.getElementById('detail_status').innerText = this.dataset.status === "aktif" ? "Aktif" : "Ditutup";
        document.getElementById('detail_deskripsi').innerText = this.dataset.deskripsi;

        new bootstrap.Modal(document.getElementById('modalDetailJalur')).show();
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
