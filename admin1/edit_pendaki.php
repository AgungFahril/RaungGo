<?php
include '../backend/koneksi.php';
$id = intval($_GET['pendaki_id'] ?? 0);
if(!$id){ header('Location: data_pendaki.php'); exit; }

if($_SERVER['REQUEST_METHOD'] == 'POST'){
  // ambil dan update
  $nik = $conn->real_escape_string($_POST['nik']);
  $tempat = $conn->real_escape_string($_POST['tempat_lahir']);
  $tgl = $conn->real_escape_string($_POST['tanggal_lahir']);
  $jk = $conn->real_escape_string($_POST['jenis_kelamin']);
  $alamat = $conn->real_escape_string($_POST['alamat']);
  $nohp = $conn->real_escape_string($_POST['no_hp']);
  $conn->query("UPDATE pendaki_detail SET nik='$nik', tempat_lahir='$tempat', tanggal_lahir='$tgl', jenis_kelamin='$jk', alamat='$alamat', no_hp='$nohp', tanggal_update=NOW() WHERE pendaki_id=$id");
  header('Location: data_pendaki.php'); exit;
}

$r = $conn->query("SELECT * FROM pendaki_detail WHERE pendaki_id=$id")->fetch_assoc();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Edit Pendaki</title><link rel="stylesheet" href="admin-style.css"></head><body>
<main class="main">
  <div class="card"><h3>Edit Pendaki</h3>
    <form method="post">
      <label>NIK</label><input class="input" name="nik" value="<?= htmlspecialchars($r['nik']); ?>"><br><br>
      <label>Tempat Lahir</label><input class="input" name="tempat_lahir" value="<?= htmlspecialchars($r['tempat_lahir']); ?>"><br><br>
      <label>Tanggal Lahir</label><input class="input" name="tanggal_lahir" value="<?= htmlspecialchars($r['tanggal_lahir']); ?>"><br><br>
      <label>Jenis Kelamin</label>
      <select name="jenis_kelamin" class="input">
        <option <?= $r['jenis_kelamin']=='L'?'selected':''; ?>>L</option>
        <option <?= $r['jenis_kelamin']=='P'?'selected':''; ?>>P</option>
      </select><br><br>
      <label>No HP</label><input class="input" name="no_hp" value="<?= htmlspecialchars($r['no_hp']); ?>"><br><br>
      <label>Alamat</label><textarea class="input" name="alamat"><?= htmlspecialchars($r['alamat']); ?></textarea><br><br>
      <button class="btn" type="submit">Simpan</button>
      <a href="data_pendaki.php" class="btn secondary" style="text-decoration:none">Batal</a>
    </form>
  </div>
</main></body></html>
