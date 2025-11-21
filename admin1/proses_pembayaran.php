<?php
include '../backend/koneksi.php';

$id  = $_GET['id'];
$aksi = $_GET['aksi'];

if ($aksi == "konfirmasi") {
    $conn->query("UPDATE pembayaran SET status_pembayaran='terkonfirmasi' WHERE pembayaran_id='$id'");
}

if ($aksi == "tolak") {
    $conn->query("UPDATE pembayaran SET status_pembayaran='ditolak' WHERE pembayaran_id='$id'");
}

header("Location: data_pembayaran.php");
exit;
?>
<?php
include '../backend/koneksi.php';
$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $conn->real_escape_string($_POST['pembayaran_id']);
  $status = $conn->real_escape_string($_POST['status_pembayaran']);
  $metode = $conn->real_escape_string($_POST['metode']);
  $jumlah = (float)$_POST['jumlah_bayar'];
  $tgl = $conn->real_escape_string($_POST['tanggal_bayar']);
  $update = $conn->query("UPDATE pembayaran SET status_pembayaran='$status', metode='$metode', jumlah_bayar=$jumlah, tanggal_bayar='$tgl' WHERE pembayaran_id='$id'");
  if ($update) header("Location: pembayaran.php");
  else $error = $conn->error;
}
$row = $conn->query("SELECT * FROM pembayaran WHERE pembayaran_id='$id'")->fetch_assoc();
?>
<!doctype html><html><head>
  <meta charset="utf-8"><title>Edit Pembayaran</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-4">
  <div class="container">
    <a href="pembayaran.php" class="btn btn-secondary mb-3">← Kembali</a>
    <div class="card p-3">
      <h4>Edit Pembayaran #<?php echo htmlspecialchars($id); ?></h4>
      <?php if(!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
      <form method="post">
        <input type="hidden" name="pembayaran_id" value="<?php echo htmlspecialchars($row['pembayaran_id']); ?>">
        <div class="mb-3">
          <label>ID Pesanan</label>
          <input class="form-control" value="<?php echo htmlspecialchars($row['pesanan_id']); ?>" readonly>
        </div>
        <div class="mb-3">
          <label>Metode</label>
          <input name="metode" class="form-control" value="<?php echo htmlspecialchars($row['metode']); ?>">
        </div>
        <div class="mb-3">
          <label>Jumlah Bayar</label>
          <input name="jumlah_bayar" class="form-control" value="<?php echo htmlspecialchars($row['jumlah_bayar']); ?>">
        </div>
        <div class="mb-3">
          <label>Tanggal Bayar</label>
          <input name="tanggal_bayar" class="form-control" value="<?php echo htmlspecialchars($row['tanggal_bayar']); ?>">
        </div>
        <div class="mb-3">
          <label>Status Pembayaran</label>
          <select name="status_pembayaran" class="form-control">
            <?php $s = $row['status_pembayaran']; ?>
            <option <?php if($s=='pending') echo 'selected'; ?> value="pending">pending</option>
            <option <?php if($s=='paid') echo 'selected'; ?> value="paid">paid</option>
            <option <?php if($s=='rejected') echo 'selected'; ?> value="rejected">rejected</option>
          </select>
        </div>
        <button class="btn btn-primary">Simpan</button>
      </form>
    </div>
  </div>
</body></html>
