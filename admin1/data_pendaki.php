<?php
include '../backend/koneksi.php';
include 'navbar.php';
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Pendaki</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin-style.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body style="background:#f8f9fa;">

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-success">📊 Data Pendaki</h3>
    <a href="dashboard_dev.php" class="btn btn-success">⬅ Kembali ke Dashboard</a>
  </div>

  <!-- Kotak Pencarian -->
  <form method="GET" class="mb-3">
    <div class="input-group">
      <input type="text" name="cari" class="form-control" placeholder="Cari nama, email, atau no HP..." value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
      <button type="submit" class="btn btn-success">Cari</button>
      <a href="export_pendaki.php" class="btn btn-danger ms-2">Cetak PDF</a>
    </div>
  </form>

  <!-- Tabel Data Pendaki -->
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <table class="table table-hover align-middle">
        <thead class="table-success">
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No HP</th>
            <th>Alamat</th>
            <th>Tanggal Daftar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $where = "";
          if (isset($_GET['cari']) && $_GET['cari'] != '') {
              $cari = $conn->real_escape_string($_GET['cari']);
              $where = "WHERE nama LIKE '%$cari%' OR email LIKE '%$cari%' OR no_hp LIKE '%$cari%'";
          }

          $q = $conn->query("SELECT * FROM users WHERE role='pendaki' $where ORDER BY tanggal_daftar DESC");
          if ($q->num_rows > 0) {
              while ($r = $q->fetch_assoc()) {
                  echo "<tr>
                          <td>{$r['user_id']}</td>
                          <td>{$r['nama']}</td>
                          <td>{$r['email']}</td>
                          <td>" . (isset($r['no_hp']) ? $r['no_hp'] : '-') . "</td>
                          <td>" . (isset($r['alamat']) ? $r['alamat'] : '-') . "</td>
                          <td>{$r['tanggal_daftar']}</td>
                          <td>
                            <a href='edit_pendaki.php?user_id={$r['user_id']}' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='hapus_pendaki.php?user_id={$r['user_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
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

</body>
</html>
