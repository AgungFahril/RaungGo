<?php
include '../backend/koneksi.php';

// Query ambil semua jalur pendakian
$query = mysqli_query($conn, "SELECT * FROM jalur_pendakian");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jalur Pendakian</title>
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
                <i class="fa-solid fa-map-location-dot me-2"></i> Data Jalur Pendakian
            </h1>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Nama Jalur</th>
                                    <th class="text-center">Kuota Harian</th>
                                    <th class="text-center">Tarif Tiket</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($data = mysqli_fetch_assoc($query)) :
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($data['nama_jalur']); ?></td>
                                        <td class="text-center"><?= $data['kuota_harian']; ?></td>
                                        <td class="text-center">Rp <?= number_format($data['tarif_tiket'], 0, ',', '.'); ?></td>
                                        <td><?= htmlspecialchars($data['deskripsi']); ?></td>
                                        <td class="text-center">
                                            <?php if (strtolower($data['status']) == 'aktif') : ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else : ?>
                                                <span class="badge bg-danger">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="edit_jalur.php?id=<?= $data['jalur_id']; ?>" class="btn btn-sm btn-primary me-1">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </a>
                                            <a href="hapus_jalur.php?id=<?= $data['jalur_id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Yakin hapus jalur ini?');">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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
