<?php
$page_title = 'Pesanan';
include '../includes/auth_admin.php';
include '../backend/koneksi.php';

/* ============================================================
   ============== 1. AMBIL FILTER DARI GET =====================
   ============================================================ */
$where = "WHERE 1=1";

// Keyword
if (!empty($_GET['cari'])) {
    $c = $conn->real_escape_string($_GET['cari']);
    $where .= " AND (
        p.kode_token LIKE '%$c%' OR
        p.nama_ketua LIKE '%$c%' OR
        jp.nama_jalur LIKE '%$c%' OR
        g.nama_guide LIKE '%$c%' OR
        pr.nama_porter LIKE '%$c%' OR
        oj.nama_ojek LIKE '%$c%'
    )";
}

// Status Pesanan
if (!empty($_GET['status_pes'])) {
    $st = $conn->real_escape_string($_GET['status_pes']);
    $where .= " AND p.status_pesanan = '$st'";
}

// Status Pembayaran
if (!empty($_GET['status_pay'])) {
    $st2 = $conn->real_escape_string($_GET['status_pay']);
    $where .= " AND pb.status_pembayaran = '$st2'";
}

// Jalur
if (!empty($_GET['jalur'])) {
    $jl = $conn->real_escape_string($_GET['jalur']);
    $where .= " AND jp.jalur_id = '$jl'";
}

// Bulan pesan
if (!empty($_GET['bulan'])) {
    $bl = $conn->real_escape_string($_GET['bulan']);
    $where .= " AND DATE_FORMAT(p.tanggal_pesan,'%Y-%m') = '$bl'";
}

// Range tanggal
$from = $_GET['from'] ?? '';
$to   = $_GET['to']   ?? '';

if ($from !== '' && $to !== '') {
    $where .= " AND p.tanggal_pesan BETWEEN '$from' AND '$to'";
}

/* ============================================================
   ============== 2. QUERY UTAMA (FIXED) =======================
   ============================================================ */

$sql = "
    SELECT 
        p.pesanan_id, p.kode_token, p.nama_ketua, p.jumlah_pendaki,
        p.tanggal_pesan, p.status_pesanan,

        jp.nama_jalur,

        g.nama_guide,
        pr.nama_porter,
        oj.nama_ojek,

        pb.jumlah_bayar,
        pb.status_pembayaran

    FROM pesanan p

    LEFT JOIN pendakian pd ON p.pendakian_id = pd.pendakian_id
    LEFT JOIN jalur_pendakian jp ON pd.jalur_id = jp.jalur_id
    LEFT JOIN guide g ON p.guide_id = g.guide_id
    LEFT JOIN porter pr ON p.porter_id = pr.porter_id
    LEFT JOIN ojek oj ON p.ojek_id = oj.ojek_id
    LEFT JOIN pembayaran pb ON p.pesanan_id = pb.pesanan_id

    $where
    ORDER BY p.tanggal_pesan DESC
";
$q = $conn->query($sql);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pesanan - Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin-style.css">

  <style>
    th.sortable { cursor:pointer; }
    th.sortable:hover { color:#198754; }
  </style>
</head>

<body>
<div class="app-wrap">

<?php include 'sidebar.php'; ?>
<div class="main">
<?php include 'navbar.php'; ?>

<div class="container-fluid mt-3">

<h3 class="fw-bold text-success mb-3">Data Pesanan</h3>

<!-- ========================= FILTER ========================= -->
<div class="card shadow-sm mb-4">
<div class="card-body">

<form method="GET">
<div class="row g-2">

    <div class="col-md-3">
        <input type="text" name="cari" class="form-control"
               placeholder="Cari ketua / kode / jalur..."
               value="<?= htmlspecialchars($_GET['cari'] ?? '') ?>">
    </div>

    <div class="col-md-2">
        <select name="status_pes" class="form-select">
            <option value="">Status Pesanan</option>
            <option value="menunggu_konfirmasi" <?= (@$_GET['status_pes']=='menunggu_konfirmasi')?'selected':'' ?>>Menunggu</option>
            <option value="berhasil" <?= (@$_GET['status_pes']=='berhasil')?'selected':'' ?>>Berhasil</option>
            <option value="dibatalkan" <?= (@$_GET['status_pes']=='dibatalkan')?'selected':'' ?>>Dibatalkan</option>
        </select>
    </div>

    <div class="col-md-2">
        <select name="status_pay" class="form-select">
            <option value="">Status Pembayaran</option>
            <option value="pending" <?= (@$_GET['status_pay']=='pending')?'selected':'' ?>>Pending</option>
            <option value="terkonfirmasi" <?= (@$_GET['status_pay']=='terkonfirmasi')?'selected':'' ?>>Terkonfirmasi</option>
            <option value="ditolak" <?= (@$_GET['status_pay']=='ditolak')?'selected':'' ?>>Ditolak</option>
        </select>
    </div>

    <div class="col-md-2">
        <select name="jalur" class="form-select">
            <option value="">Semua Jalur</option>
            <?php
            $rj = $conn->query("SELECT jalur_id,nama_jalur FROM jalur_pendakian");
            while ($jl = $rj->fetch_assoc()):
            ?>
            <option value="<?= $jl['jalur_id'] ?>" <?= (@$_GET['jalur']==$jl['jalur_id'])?'selected':'' ?>>
                <?= $jl['nama_jalur'] ?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-2">
        <input type="month" name="bulan" class="form-control"
               value="<?= htmlspecialchars($_GET['bulan'] ?? '') ?>">
    </div>

    <div class="col-md-2">
        <input type="date" name="from" class="form-control"
               value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
    </div>

    <div class="col-md-2">
        <input type="date" name="to" class="form-control"
               value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
    </div>

    <div class="col-md-2">
        <button class="btn btn-success w-100">Filter</button>
    </div>

    <div class="col-md-2">
        <a href="pesanan.php" class="btn btn-danger w-100">Reset</a>
    </div>

</div>
</form>

</div>
</div>


<!-- ========================= TABLE ========================= -->
<div class="card shadow-sm">
<div class="card-body">

<div class="table-responsive">
<table class="table table-hover align-middle" id="dataTable">

<thead class="table-success">
<tr>
    <th class="sortable">No</th>
    <th class="sortable">Kode Token</th>
    <th class="sortable">Ketua</th>
    <th class="sortable">Jalur</th>
    <th class="sortable">Layanan</th>
    <th class="sortable">Pendaki</th>
    <th class="sortable">Status</th>
    <th class="sortable">Total Bayar</th>
    <th class="sortable">Tanggal Pesan</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php
$i=1;
while($r = $q->fetch_assoc()):

// Status Pesanan
if ($r['status_pesanan']=='berhasil') $pes = "<span class='badge bg-success'>Berhasil</span>";
elseif ($r['status_pesanan']=='menunggu_konfirmasi') $pes = "<span class='badge bg-warning text-dark'>Menunggu</span>";
else $pes = "<span class='badge bg-danger'>Batal</span>";

// Status Pembayaran
$st = $r['status_pembayaran'] ?? 'pending';
if ($st=='terkonfirmasi') $pay = "<span class='badge bg-success'>Lunas</span>";
elseif ($st=='pending') $pay = "<span class='badge bg-warning text-dark'>Pending</span>";
else $pay = "<span class='badge bg-danger'>Ditolak</span>";

$layanan = "
Guide: ".($r['nama_guide'] ?: '-')."<br>
Porter: ".($r['nama_porter'] ?: '-')."<br>
Ojek: ".($r['nama_ojek'] ?: '-')."
";

?>
<tr>
    <td><?= $i++ ?></td>
    <td class="fw-bold"><?= $r['kode_token'] ?></td>
    <td><?= $r['nama_ketua'] ?></td>
    <td><?= $r['nama_jalur'] ?: '-' ?></td>
    <td><?= $layanan ?></td>
    <td><?= $r['jumlah_pendaki'] ?></td>
    <td><?= $pes ?> <br> <?= $pay ?></td>
    <td>Rp <?= number_format($r['jumlah_bayar'] ?: 0,0,',','.') ?></td>
    <td><?= $r['tanggal_pesan'] ?></td>
    <td>
        <a href="detail_pesanan.php?id=<?= $r['pesanan_id'] ?>" 
           class="btn btn-info btn-sm">Detail</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

</div>
</div>

</div></div>
</div>

<!-- SORTING -->
<script>
document.querySelectorAll("th.sortable").forEach((th, idx) => {
    th.addEventListener("click", () => {
        const table = th.closest("table");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        const asc = th.classList.toggle("asc");

        rows.sort((a,b)=>{
            let A = a.children[idx].innerText.toLowerCase();
            let B = b.children[idx].innerText.toLowerCase();
            return asc ? A.localeCompare(B) : B.localeCompare(A);
        });

        rows.forEach(r => tbody.appendChild(r));
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
