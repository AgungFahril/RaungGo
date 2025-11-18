<?php
include '../backend/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Konfirmasi Pembayaran</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
</head>

<body>
<div class="app-wrap">
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <?php include 'navbar.php'; ?>

        <div class="container-fluid mt-3">
            <h1 class="mb-4 d-flex align-items-center">
                <i class="fa-solid fa-spinner me-2"></i> Menunggu Konfirmasi Pembayaran
            </h1>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">ID Bayar</th>
                                    <th class="text-center">ID Pesanan</th>
                                    <th>Pemesan</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-center">Bukti</th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q = mysqli_query($conn, "
                                    SELECT p.*, u.nama AS nama_pemesan
                                    FROM pembayaran p
                                    LEFT JOIN pesanan ps ON p.pesanan_id = ps.pesanan_id
                                    LEFT JOIN users u ON ps.user_id = u.user_id
                                    WHERE p.status_pembayaran='pending'
                                    ORDER BY p.pembayaran_id DESC
                                ");

                                if (mysqli_num_rows($q) == 0) {
                                    echo "<tr><td colspan='7' class='text-center'>Tidak ada data.</td></tr>";
                                } else {
                                    while ($row = mysqli_fetch_assoc($q)) {
                                        echo "
                                        <tr>
                                            <td class='text-center'>{$row['pembayaran_id']}</td>
                                            <td class='text-center'>{$row['pesanan_id']}</td>
                                            <td>" . htmlspecialchars($row['nama_pemesan']) . "</td>
                                            <td class='text-center'>Rp " . number_format($row['jumlah_bayar'], 0, ',', '.') . "</td>
                                            <td class='text-center'>
                                                <a href='../uploads/bukti/{$row['bukti_bayar']}' target='_blank' class='btn btn-sm btn-info'>Lihat Bukti</a>
                                            </td>
                                            <td class='text-center'>{$row['tanggal_bayar']}</td>
                                            <td class='text-center'>
                                                <a href='konfirmasi.php?id={$row['pembayaran_id']}' class='btn btn-sm btn-success me-1'>Konfirmasi</a>
                                                <a href='tolak.php?id={$row['pembayaran_id']}' class='btn btn-sm btn-danger'>Tolak</a>
                                            </td>
                                        </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
