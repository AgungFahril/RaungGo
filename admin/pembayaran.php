<?php
$page_title = 'Pembayaran';
include '../includes/auth_admin.php';
include '../backend/koneksi.php';

// ===================== FILTER =====================
$where = "WHERE 1=1";

// Keyword search
if (!empty($_GET['cari'])) {
    $c = $conn->real_escape_string($_GET['cari']);
    $where .= " AND (
        ps.kode_token LIKE '%$c%' OR
        ps.nama_ketua LIKE '%$c%' OR
        p.metode LIKE '%$c%' OR
        p.status_pembayaran LIKE '%$c%'
    )";
}

// Filter status pembayaran
if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $where .= " AND p.status_pembayaran = '$status'";
}

// Filter metode pembayaran
if (!empty($_GET['metode'])) {
    $met = $conn->real_escape_string($_GET['metode']);
    $where .= " AND p.metode = '$met'";
}

// Filter bulan
if (!empty($_GET['bulan'])) {
    $bulan = $conn->real_escape_string($_GET['bulan']);
    $where .= " AND DATE_FORMAT(p.tanggal_bayar,'%Y-%m') = '$bulan'";
}

// Filter range tanggal
$from = $_GET['from'] ?? '';
$to   = $_GET['to']   ?? '';

if ($from !== '' && $to !== '') {
    $where .= " AND p.tanggal_bayar BETWEEN '$from' AND '$to'";
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pembayaran - Admin</title>

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

<h3 class="fw-bold text-success mb-3">Pembayaran</h3>

<!-- ========================= FILTER ========================= -->
<div class="card shadow-sm mb-4">
<div class="card-body">

<form method="GET">
<div class="row g-2">

    <div class="col-md-3">
        <input type="text" name="cari" class="form-control"
               placeholder="Cari kode / nama / metode..."
               value="<?= htmlspecialchars($_GET['cari'] ?? '') ?>">
    </div>

    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="pending"       <?= ($_GET['status'] ?? '')=='pending' ? 'selected':'' ?>>Pending</option>
            <option value="terkonfirmasi" <?= ($_GET['status'] ?? '')=='terkonfirmasi' ? 'selected':'' ?>>Terkonfirmasi</option>
            <option value="ditolak"       <?= ($_GET['status'] ?? '')=='ditolak' ? 'selected':'' ?>>Ditolak</option>
        </select>
    </div>

    <div class="col-md-2">
        <select name="metode" class="form-select">
            <option value="">Semua Metode</option>
            <option value="transfer" <?= ($_GET['metode'] ?? '')=='transfer' ? 'selected':'' ?>>Transfer</option>
            <option value="cash"     <?= ($_GET['metode'] ?? '')=='cash' ? 'selected':'' ?>>Cash</option>
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
        <a href="pembayaran.php" class="btn btn-danger w-100">Reset</a>
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
    <th class="sortable">Nama Pemesan</th>
    <th class="sortable">Metode</th>
    <th class="sortable">Jumlah</th>
    <th class="sortable">Tanggal Bayar</th>
    <th class="sortable">Status</th>
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
        ps.kode_token,
        ps.nama_ketua AS nama_pemesan,
        p.metode,
        p.jumlah_bayar,
        p.tanggal_bayar,
        p.status_pembayaran,
        p.bukti_bayar
    FROM pembayaran p
    INNER JOIN pesanan ps ON ps.pesanan_id = p.pesanan_id
    $where
    ORDER BY p.tanggal_bayar DESC
");

$i = 1;

while ($r = $q->fetch_assoc()):
    $badgeClass = [
        'pending' => 'warning text-dark',
        'terkonfirmasi' => 'success',
        'ditolak' => 'danger'
    ];
    $badge = "<span class='badge bg-".$badgeClass[$r['status_pembayaran']]."'>".ucfirst($r['status_pembayaran'])."</span>";

    $path = "../uploads/bukti/".$r['bukti_bayar'];
    $bukti = (file_exists($path) && !empty($r['bukti_bayar']))
        ? "<img src='$path' class='thumbnail-bukti' data-bs-toggle='modal' data-bs-target='#previewModal' onclick=\"previewBukti('$path')\">"
        : "<span class='text-muted'>Tidak ada</span>";

    $aksi = "";
    if ($r['status_pembayaran'] == 'pending') {
        $aksi = "
            <button class='btn btn-success btn-sm' onclick=\"updateStatus('{$r['pembayaran_id']}', 'terkonfirmasi')\">✔ Terima</button>
            <button class='btn btn-danger btn-sm' onclick=\"updateStatus('{$r['pembayaran_id']}', 'ditolak')\">✖ Tolak</button>
        ";
    } else {
        $aksi = "<b class='text-".($r['status_pembayaran']=='terkonfirmasi'?'success':'danger')."'>".ucfirst($r['status_pembayaran'])."</b>";
    }
?>
<tr>
    <td><?= $i++ ?></td>
    <td class="fw-bold"><?= $r['kode_token'] ?></td>
    <td><?= $r['nama_pemesan'] ?></td>
    <td><?= $r['metode'] ?></td>
    <td>Rp <?= number_format($r['jumlah_bayar'],0,',','.') ?></td>
    <td><?= $r['tanggal_bayar'] ?></td>
    <td><?= $badge ?></td>
    <td><?= $bukti ?></td>
    <td><?= $aksi ?></td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

</div>
</div>

</div></div>
</div>

<!-- ========================= MODAL PREVIEW ========================= -->
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

// Sorting
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
