<?php
$page_title = 'Data Pesanan';
include '../includes/auth_admin.php';   // proteksi admin
include '../backend/koneksi.php';       // koneksi database

// pastikan $where selalu terdefinisi sebelum dipakai
$where = "";

// jika ada parameter pencarian, buat klausa WHERE
if (!empty($_GET['cari'])) {
    $c = $conn->real_escape_string($_GET['cari']);
    $where = "WHERE nama_ketua LIKE '%$c%' OR pesanan_id LIKE '%$c%' OR kode_token LIKE '%$c%'";
}

// query nanti memakai $where yang sudah pasti ada (bisa kosong)
$q = $conn->query("SELECT * FROM pesanan $where ORDER BY tanggal_pesan DESC");
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
        <h3 class="fw-bold text-success">Data Pesanan</h3>
        <a href="dashboard_1.php" class="btn btn-success">⬅ Kembali ke Dashboard</a>
      </div>

      <!-- Pencarian -->
      <form method="GET" class="mb-3">
        <div class="input-group">
          <input type="text" name="cari" class="form-control" placeholder="Cari nama ketua, ID pesanan, kode token..."
                 value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
          <button type="submit" class="btn btn-success">Cari</button>
          <a href="export_pesanan.php" class="btn btn-danger ms-2">Cetak PDF</a>
        </div>
      </form>

      <!-- Tabel -->
      <div class="card shadow-sm border-0">
        <div class="card-body">

          <table class="table table-hover align-middle">
            <thead class="table-success">
  <tr>
    <th>No</th>
    <th>Kode Token</th>
    <th>Nama Ketua</th>
    <th>Jumlah Pendaki</th>
    <th>Status</th>
    <th>Tanggal Pesan</th>
    <th>Aksi</th>
  </tr>
</thead>


            <tbody>
<?php
$q = $conn->query("SELECT * FROM pesanan $where ORDER BY tanggal_pesan DESC");

$no = 1;

if ($q->num_rows > 0):
    while ($r = $q->fetch_assoc()):

        $status = $r['status_pesanan'];

        switch ($status) {
            case 'menunggu_konfirmasi':
                $badge = "<span class='badge bg-warning text-dark'>Menunggu Konfirmasi</span>";
                break;
            case 'berhasil':
                $badge = "<span class='badge bg-success'>Berhasil</span>";
                break;
            case 'dibatalkan':
                $badge = "<span class='badge bg-danger'>Dibatalkan</span>";
                break;
            default:
                $badge = "<span class='badge bg-secondary'>$status</span>";
                break;
        }
?>
<tr>
    <!-- NO URUT -->
    <td><?= $no++; ?></td>

    <!-- KODE TOKEN -->
    <td class="fw-bold"><?= $r['kode_token'] ?></td>

    <!-- NAMA KETUA -->
    <td><?= $r['nama_ketua'] ?></td>

    <!-- JUMLAH -->
    <td><?= $r['jumlah_pendaki'] ?></td>

    <!-- STATUS -->
    <td><?= $badge ?></td>

    <!-- TANGGAL -->
    <td><?= $r['tanggal_pesan'] ?></td>

    <!-- AKSI -->
    <td>
        <a href="detail_pesanan.php?id=<?= $r['pesanan_id'] ?>" 
           class="btn btn-info btn-sm btn-aksi">Detail</a>

        <?php if ($status == 'menunggu_konfirmasi'): ?>
            <a href="../backend/proses_konfirmasi.php?id=<?= $r['pesanan_id'] ?>" 
               class="btn btn-success btn-sm btn-aksi">Konfirmasi</a>

            <a href="../backend/proses_tolak.php?id=<?= $r['pesanan_id'] ?>" 
               class="btn btn-danger btn-sm btn-aksi">Tolak</a>
        <?php endif; ?>
    </td>
</tr>
<?php
    endwhile;
else:
    echo "<tr><td colspan='7' class='text-center text-muted py-3'>Tidak ada data ditemukan.</td></tr>";
endif;
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
