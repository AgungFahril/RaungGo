<?php
$page_title = 'Data Pesanan';
include '../includes/auth_admin.php';
include '../backend/koneksi.php';

// Ambil filter
$cari       = $_GET['cari']       ?? '';
$statusPes  = $_GET['status_pes'] ?? '';
$statusPay  = $_GET['status_pay'] ?? '';
$jalur      = $_GET['jalur']      ?? '';
$bulan      = $_GET['bulan']      ?? '';
$from       = $_GET['from']       ?? '';
$to         = $_GET['to']         ?? '';

// Build WHERE
$where = "WHERE 1=1";

if ($cari !== '') {
    $c = $conn->real_escape_string($cari);
    $where .= " AND (p.kode_token LIKE '%$c%' 
                OR p.nama_ketua LIKE '%$c%' 
                OR p.pesanan_id LIKE '%$c%')";
}

if ($statusPes !== '') {
    $where .= " AND p.status_pesanan='$statusPes'";
}

if ($statusPay !== '') {
    $where .= " AND pb.status_pembayaran='$statusPay'";
}

if ($jalur !== '') {
    $where .= " AND jp.jalur_id='$jalur'";
}

if ($bulan !== '') {
    $where .= " AND DATE_FORMAT(p.tanggal_pesan,'%Y-%m')='$bulan'";
}

if ($from !== '' && $to !== '') {
    $where .= " AND p.tanggal_pesan BETWEEN '$from' AND '$to'";
}

// Query utama FIXED
$sql = "
    SELECT 
        p.pesanan_id, p.kode_token, p.nama_ketua, p.jumlah_pendaki,
        p.tanggal_pesan, p.status_pesanan,

        jp.nama_jalur,

        g.nama_guide,
        pr.nama_porter,
        oj.nama_ojek,

        pb.jumlah_bayar, pb.tanggal_bayar, pb.status_pembayaran

    FROM pesanan p

    LEFT JOIN pendakian pd 
        ON p.pendakian_id = pd.pendakian_id

    LEFT JOIN jalur_pendakian jp 
        ON pd.jalur_id = jp.jalur_id

    LEFT JOIN guide g 
        ON p.guide_id = g.guide_id

    LEFT JOIN porter pr 
        ON p.porter_id = pr.porter_id

    LEFT JOIN ojek oj 
        ON p.ojek_id = oj.ojek_id

    LEFT JOIN pembayaran pb 
        ON p.pesanan_id = pb.pesanan_id

    $where
    ORDER BY p.tanggal_pesan DESC
";



$q = $conn->query($sql);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Data Pesanan - Admin</title>

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

<!-- FILTER -->
<div class="card shadow-sm mb-4">
    <div class="card-body">

<form method="GET">
<div class="row g-2">

    <div class="col-md-3">
        <input type="text" name="cari" class="form-control"
               placeholder="Cari ketua / kode"
               value="<?= htmlspecialchars($cari) ?>">
    </div>

    <div class="col-md-2">
        <select name="status_pes" class="form-select">
            <option value="">Status Pesanan</option>
            <option value="menunggu_konfirmasi" <?= $statusPes=='menunggu_konfirmasi'?'selected':''?>>Menunggu</option>
            <option value="berhasil" <?= $statusPes=='berhasil'?'selected':''?>>Berhasil</option>
            <option value="dibatalkan" <?= $statusPes=='dibatalkan'?'selected':''?>>Dibatalkan</option>
        </select>
    </div>

    <div class="col-md-2">
        <select name="status_pay" class="form-select">
            <option value="">Status Pembayaran</option>
            <option value="pending" <?= $statusPay=='pending'?'selected':''?>>Pending</option>
            <option value="terkonfirmasi" <?= $statusPay=='terkonfirmasi'?'selected':''?>>Terkonfirmasi</option>
            <option value="ditolak" <?= $statusPay=='ditolak'?'selected':''?>>Ditolak</option>
        </select>
    </div>

    <div class="col-md-2">
        <select name="jalur" class="form-select">
            <option value="">Jalur Pendakian</option>
            <?php
            $rj = $conn->query("SELECT jalur_id, nama_jalur FROM jalur_pendakian");
            while($jj = $rj->fetch_assoc()):
            ?>
            <option value="<?= $jj['jalur_id'] ?>" 
                <?= $jalur==$jj['jalur_id']?'selected':''?>>
                <?= $jj['nama_jalur'] ?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-2">
        <input type="month" name="bulan" class="form-control"
               value="<?= htmlspecialchars($bulan) ?>">
    </div>

    <div class="col-md-2">
        <input type="date" name="from" class="form-control"
               value="<?= htmlspecialchars($from) ?>">
    </div>

    <div class="col-md-2">
        <input type="date" name="to" class="form-control"
               value="<?= htmlspecialchars($to) ?>">
    </div>

    <div class="col-md-2">
        <button class="btn btn-success w-100">Filter</button>
    </div>

</div>
</form>

    </div>
</div>


<!-- TABEL -->
<div class="card shadow-sm">
<div class="card-body">

<div class="table-responsive">
<table class="table table-hover align-middle" id="dataTable">

<thead class="table-success">
<tr>
    <th class="sortable">Kode Token</th>
    <th class="sortable">Ketua</th>
    <th class="sortable">Jalur</th>
    <th class="sortable">Layanan</th>
    <th class="sortable">Pendaki</th>
    <th class="sortable">Status</th>
    <th class="sortable">Total Bayar</th>
    <th class="sortable">Tgl Pesan</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php while($r = $q->fetch_assoc()): ?>

<?php
// badge status pesanan
$stPes = $r['status_pesanan'];
if ($stPes == 'berhasil') $b1 = "<span class='badge bg-success'>Berhasil</span>";
elseif ($stPes == 'menunggu_konfirmasi') $b1 = "<span class='badge bg-warning text-dark'>Menunggu</span>";
else $b1 = "<span class='badge bg-danger'>Batal</span>";

// badge status bayar
$stPay = $r['status_pembayaran'] ?? 'pending';
if ($stPay == 'terkonfirmasi') $b2 = "<span class='badge bg-success'>Lunas</span>";
elseif ($stPay == 'pending') $b2 = "<span class='badge bg-warning text-dark'>Pending</span>";
else $b2 = "<span class='badge bg-danger'>Ditolak</span>";

// gabungan layanan
$layanan = "
Guide: ".($r['nama_guide'] ?? '-')."<br>
Porter: ".($r['nama_porter'] ?? '-')."<br>
Ojek: ".($r['nama_ojek'] ?? '-')."
";
?>

<tr>
    <td class="fw-bold"><?= $r['kode_token'] ?></td>
    <td><?= $r['nama_ketua'] ?></td>
    <td><?= $r['nama_jalur'] ?? '-' ?></td>
    <td><?= $layanan ?></td>
    <td><?= $r['jumlah_pendaki'] ?></td>
    <td><?= $b1 ?> <br><?= $b2 ?></td>
    <td>Rp <?= number_format($r['jumlah_bayar'] ?? 0,0,',','.') ?></td>
    <td><?= $r['tanggal_pesan'] ?></td>
    <td>
        <a href="detail_pesanan.php?id=<?= $r['pesanan_id'] ?>" class="btn btn-info btn-sm">Detail</a>
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

</body>
</html>
