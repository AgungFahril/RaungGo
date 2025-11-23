<?php
include '../backend/koneksi.php';

$id = $_GET['id'];

$p = $conn->query("
    SELECT p.*, u.nama, j.nama_jalur
    FROM pesanan p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN jalur_pendakian j ON p.jalur_id = j.jalur_id
    WHERE p.pesanan_id='$id'
")->fetch_assoc();

$anggota = $conn->query("
    SELECT * FROM anggota_pendaki WHERE pesanan_id='$id'
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Detail Pesanan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h3 class="text-success fw-bold mb-3">Detail Pesanan</h3>

    <div class="card mb-4">
        <div class="card-body">
            <p><b>Pemesan:</b> <?= $p['nama'] ?></p>
            <p><b>Jalur:</b> <?= $p['nama_jalur'] ?></p>
            <p><b>Tgl Naik:</b> <?= $p['tanggal_naik'] ?></p>
            <p><b>Tgl Turun:</b> <?= $p['tanggal_turun'] ?></p>
            <p><b>Jumlah Pendaki:</b> <?= $p['jumlah_pendaki'] ?></p>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Daftar Anggota Pendaki</h5>

    <table class="table table-bordered">
        <thead class="table-success">
            <tr>
                <th>Nama</th>
                <th>NIK</th>
                <th>Alamat</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($a = $anggota->fetch_assoc()): ?>
            <tr>
                <td><?= $a['nama'] ?></td>
                <td><?= $a['nik'] ?></td>
                <td><?= $a['alamat'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <a href="data_pesanan.php" class="btn btn-success mt-3">Kembali</a>

</div>

</body>
</html>
