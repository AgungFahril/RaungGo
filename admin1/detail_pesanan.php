<?php 
$page_title = 'Detail Booking';
include '../backend/koneksi.php';

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
    .img-thumb { width: 80px; border-radius: 6px; border: 1px solid #fbfffcff; }
  
        body {
    position: relative;
    background: url('Gunung_Raung1.jpg') no-repeat center center fixed;
    background-size: cover;
    overflow-x: hidden;
}

/* Overlay blur + warna hijau transparan */
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(6px);       /* Blur background */
    background: rgba(0, 128, 0, 0.25); /* Sentuhan hijau (0.25 = tingkat transparansi) */
    pointer-events: none;
    z-index: -1; /* Pastikan tidak menutupi konten */
}


    .card {
        background: #b2d7baee;
        backdrop-filter: blur(2px);
    }
@media print {
    /* Hilangkan tombol & background saat print */
    a.btn, button.btn {
        display: none !important;
    }

    body {
        background: #9db0a5ff !important;
    }

    body::before {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        background: #b9c7beff !important;
    }

    img.img-thumb {
        width: 120px !important;
        border: 1px solid #777;
    }
}


  </style>
</head>
<body>

<div class="container mt-4">

    <a href="pesanan.php" class="btn btn-success mb-3">← Kembali</a>
    
    <button onclick="window.print()" class="btn btn-primary mb-3 ms-2">🖨 Cetak</button>


    <!-- HEADER PESANAN -->
    <div class="card p-4 mb-4">
        <h4 class="fw-bold text-success mb-3">📌 Informasi Pesanan</h4>

        <div class="row">
            <div class="col-md-6">
                <p><b>ID Pesanan:</b> <?= $header['pesanan_id']; ?></p>
                <p><b>Kode Token:</b> <?= $header['kode_token']; ?></p>
                <p><b>Total Bayar:</b> Rp <?= number_format($header['total_bayar'],0,',','.'); ?></p>
            </div>
            <div class="col-md-6">
                <p><b>Tgl Pesan:</b> <?= $header['tanggal_pesan']; ?></p>
                <p><b>Tgl Naik:</b> <?= $header['tanggal_pendakian']; ?></p>
                <p><b>Tgl Turun:</b> <?= $header['tanggal_turun']; ?></p>
            </div>
        </div>
    </div>

    <!-- LAYANAN -->
    <div class="card p-4 mb-4">
        <h4 class="fw-bold text-primary mb-3">🛒 Layanan</h4>
        <p><b>Porter:</b> <?= $header['nama_porter'] ?? '-'; ?></p>
        <p><b>Ojek:</b> <?= $header['nama_ojek'] ?? '-'; ?></p>
    </div>

    <!-- BIODATA KETUA -->
    <div class="card p-4 mb-4">
        <h4 class="fw-bold text-warning mb-3">👤 Biodata Ketua</h4>
        <p><b>Nama Ketua:</b> <?= $header['nama_ketua']; ?></p>
        <p><b>No HP:</b> <?= $header['telepon_ketua']; ?></p>
        <p><b>Alamat:</b> <?= $header['alamat_ketua']; ?></p>
    </div>

    <!-- TABEL ANGGOTA -->
    <div class="card p-4">
        <h4 class="fw-bold text-info mb-3">👥 Daftar Anggota Pendaki</h4>

        <table class="table table-bordered table-hover">
            <thead class="table-success">
                <tr>
                    <th>ID Anggota</th>
                    <th>Nama</th>
                    <th>JK</th>
                    <th>NIK</th>
                    <th>KTP</th>
                    <th>Surat Sehat</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $anggota->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['anggota_id']; ?></td>
                        <td><?= $r['nama_anggota']; ?></td>
                        <td><?= $r['jenis_kelamin']; ?></td>
                        <td><?= $r['nik']; ?></td>

                        <td><?= $r['ktp'] ? "<img src='../uploads/{$r['ktp']}' class='img-thumb'>" : "-"; ?></td>
                        <td><?= $r['surat_sehat'] ? "<img src='../uploads/{$r['surat_sehat']}' class='img-thumb'>" : "-"; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
