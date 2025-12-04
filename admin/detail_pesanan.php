<?php 
$page_title = 'Detail Booking';
include '../includes/auth_admin.php';   // proteksi admin
include '../backend/koneksi.php';       // koneksi database

// Ambil ID pesanan
$id = $_GET['id'] ?? 0;
$id = mysqli_real_escape_string($conn, $id);

// Ambil data header pesanan
$header = mysqli_query($conn, "
    SELECT 
        p.pesanan_id,
        p.nama_ketua,
        p.telepon_ketua,
        p.alamat_ketua,
        p.tanggal_pesan,
        p.kode_token,
        p.total_bayar,
        pd.tanggal_pendakian,
        pd.tanggal_turun,
        pr.nama_porter,
        oj.nama_ojek
    FROM pesanan p
    LEFT JOIN pendakian pd ON p.pendakian_id = pd.pendakian_id
    LEFT JOIN porter pr ON p.porter_id = pr.porter_id
    LEFT JOIN ojek oj ON p.ojek_id = oj.ojek_id
    WHERE p.pesanan_id = '$id'
")->fetch_assoc();

// Ambil anggota
$anggota = mysqli_query($conn, "
    SELECT 
        pa.anggota_id,
        ap.nama_anggota,
        ap.jenis_kelamin,
        pa.nik,
        pa.ktp,
        pa.surat_sehat
    FROM pesanan_anggota pa
    LEFT JOIN anggota_pendaki ap ON pa.anggota_id = ap.id_anggota
    WHERE pa.pesanan_id = '$id'
");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Detail Booking - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .img-thumb { 
        width: 90px; 
        border-radius: 8px; 
        border: 2px solid #d8e8dc; 
        transition: .2s;
    }
    .img-thumb:hover {
        transform: scale(1.15);
        border-color: #1d7f4c;
    }

    body {
        background: url('Gunung_Raung1.jpg') no-repeat center center fixed;
        background-size: cover;
        backdrop-filter: blur(4px);
    }

    body::before {
        content: "";
        position: fixed;
        inset: 0;
        backdrop-filter: blur(6px);
        background: rgba(0, 64, 0, 0.25);
        z-index: -1;
    }

    .card {
        background: #cfe8d6dd;
        border-radius: 12px;
    }

    .title-icon {
        font-size: 26px;
        margin-right: 6px;
    }

    @media print {
        a.btn, button.btn { display: none !important; }
        body::before { display: none !important; }
        body { background: white !important; }
        .card { background: white !important; box-shadow: none !important; }
    }
  </style>
</head>
<body>

<div class="container mt-4">

    <!-- Tombol -->
    <a href="pesanan.php" class="btn btn-success mb-3">← Kembali</a>
    <button onclick="window.print()" class="btn btn-primary mb-3 ms-2">🖨 Cetak</button>

    <!-- Ringkasan Singkat -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3 text-center shadow-sm">
                <h6>Total Anggota</h6>
                <h3 class="fw-bold text-success">
                    <?= mysqli_num_rows($anggota); ?>
                </h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 text-center shadow-sm">
                <h6>Layanan Dipilih</h6>
                <p class="m-0">
                    Porter: <b><?= $header['nama_porter'] ?: '-'; ?></b><br>
                    Ojek: <b><?= $header['nama_ojek'] ?: '-'; ?></b>
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 text-center shadow-sm">
                <h6>Tanggal Pendakian</h6>
                <p class="m-0 fw-bold">
                    <?= $header['tanggal_pendakian']; ?> → <?= $header['tanggal_turun']; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- HEADER PESANAN -->
    <div class="card p-4 mb-4">
        <h4 class="fw-bold text-success mb-3">
            <span class="title-icon">📌</span> Informasi Pesanan
        </h4>

        <div class="row">
            <div class="col-md-6">
                <p><b>ID Pesanan:</b> <?= $header['pesanan_id']; ?></p>
                <p><b>Kode Token:</b> <?= $header['kode_token']; ?></p>
                <p><b>Total Bayar:</b> Rp <?= number_format($header['total_bayar'], 0, ',', '.'); ?></p>
            </div>
            <div class="col-md-6">
                <p><b>Tgl Pesan:</b> <?= $header['tanggal_pesan']; ?></p>
                <p><b>Tgl Naik:</b> <?= $header['tanggal_pendakian']; ?></p>
                <p><b>Tgl Turun:</b> <?= $header['tanggal_turun']; ?></p>
            </div>
        </div>
    </div>

    <!-- BIODATA KETUA -->
    <div class="card p-4 mb-4">
        <h4 class="fw-bold text-warning mb-3">
            <span class="title-icon">👤</span> Biodata Ketua
        </h4>

        <div class="row">
            <div class="col-md-6">
                <p><b>Nama Ketua:</b> <?= $header['nama_ketua']; ?></p>
                <p><b>No HP:</b> <?= $header['telepon_ketua']; ?></p>
            </div>
            <div class="col-md-6">
                <p><b>Alamat:</b> <?= $header['alamat_ketua']; ?></p>
            </div>
        </div>
    </div>

    <!-- LAYANAN -->
    <div class="card p-4 mb-4">
        <h4 class="fw-bold text-primary mb-3">
            <span class="title-icon">🛒</span> Layanan
        </h4>
        <p><b>Porter:</b> <?= $header['nama_porter'] ?: '-'; ?></p>
        <p><b>Ojek:</b> <?= $header['nama_ojek'] ?: '-'; ?></p>
    </div>

    <!-- TABEL ANGGOTA -->
    <div class="card p-4">
        <h4 class="fw-bold text-info mb-3">
            <span class="title-icon">👥</span> Daftar Anggota Pendaki
        </h4>

        <table class="table table-bordered table-hover">
            <thead class="table-success">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>JK</th>
                    <th>NIK</th>
                    <th>KTP</th>
                    <th>Surat Sehat</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                mysqli_data_seek($anggota, 0); 
                while ($r = $anggota->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $r['nama_anggota']; ?></td>
                        <td><?= $r['jenis_kelamin']; ?></td>
                        <td><?= $r['nik']; ?></td>

                        <td>
                            <?php if ($r['ktp']): ?>
                                <a href="../uploads/ktp/<?= $r['ktp']; ?>" target="_blank">
                                    <img src="../uploads/ktp/<?= $r['ktp']; ?>" class="img-thumb">
                                </a><br>
                                <small class="text-muted">Klik untuk perbesar</small>
                            <?php else: echo "-"; endif; ?>
                        </td>

                        <td>
                            <?php if ($r['surat_sehat']): ?>
                                <a href="../uploads/surat_sehat/<?= $r['surat_sehat']; ?>" target="_blank">
                                    <img src="../uploads/surat_sehat/<?= $r['surat_sehat']; ?>" class="img-thumb">
                                </a><br>
                                <small class="text-muted">Klik untuk perbesar</small>
                            <?php else: echo "-"; endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
