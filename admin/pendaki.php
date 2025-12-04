<?php
// admin/pendaki.php
include '../includes/auth_admin.php';   // proteksi admin
include '../backend/koneksi.php';       // koneksi database

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Hitung total users dan pendaki
$total_users = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM users"))['total'];
$total_pendaki = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM users WHERE role='pendaki'"))['total'];

// Filter pencarian
$where = "WHERE u.role = 'pendaki'";
if ($q !== '') {
    $e = mysqli_real_escape_string($conn, $q);
    $where .= " AND (u.nama LIKE '%$e%' OR u.email LIKE '%$e%' OR pd.no_hp LIKE '%$e%' OR pd.nik LIKE '%$e%')";
}

// Query utama
$sql = "
    SELECT 
        u.user_id,
        u.nama,
        u.email,
        pd.no_hp,
        pd.nik,
        pd.jenis_kelamin,
        pd.alamat,
        pd.tanggal_lahir
    FROM users u
    LEFT JOIN pendaki_detail pd ON pd.user_id = u.user_id
    $where
    ORDER BY u.nama ASC
";

$result = mysqli_query($conn, $sql);
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Data Pendaki</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>

<div class="app-wrap">

    <?php include 'sidebar.php'; ?>   <!-- SAMA DENGAN PESANAN -->
    <div class="main">
    <?php include 'navbar.php'; ?>    <!-- SAMA DENGAN PESANAN -->

    <div class="container-fluid mt-3">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-success">Data Pendaki / User</h3>
            <a href="dashboard_1.php" class="btn btn-success">⬅ Kembali</a>
        </div>

        <!-- SEARCH -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Cari nama, email, NIK, atau no HP..."
                       value="<?= htmlspecialchars($q) ?>">
                <button class="btn btn-success">Cari</button>
                <a href="pendaki.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <!-- TABLE -->
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <table class="table table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>NIK</th>
                            <th>JK</th>
                            <th>Tgl Lahir</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

<?php
$no = 1;
if ($result->num_rows > 0):
    while ($r = $result->fetch_assoc()):
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $r['nama'] ?></td>
    <td><?= $r['email'] ?></td>
    <td><?= $r['no_hp'] ?: '-' ?></td>
    <td><?= $r['nik'] ?: '-' ?></td>
    <td><?= $r['jenis_kelamin'] ?: '-' ?></td>
    <td><?= $r['tanggal_lahir'] ?: '-' ?></td>
    <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
        <?= $r['alamat'] ?: '-' ?>
    </td>
    <td>
        <a href="edit_pendaki.php?id=<?= $r['user_id'] ?>" class="btn btn-info btn-sm">Edit</a>
        <a onclick="return confirm('Hapus user ini?')"
           href="hapus_pendaki.php?id=<?= $r['user_id'] ?>" 
           class="btn btn-danger btn-sm">Hapus</a>
    </td>
</tr>
<?php
    endwhile;
else:
    echo "<tr><td colspan='9' class='text-center py-3 text-muted'>Tidak ada data.</td></tr>";
endif;
?>

                    </tbody>
                </table>

            </div>
        </div>

    </div>
    </div>
</div>

</body>
</html>
