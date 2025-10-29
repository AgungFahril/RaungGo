<?php
// Pengaturan error reporting (opsional, bagus untuk development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect ke halaman login jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Arahkan ke login.php
    exit();
}

// Ambil ID pengguna dari session (akan digunakan nanti untuk query database)
$user_id = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Booking - Tahura Raden Soerjo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .status-table-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2.5rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .status-table-container h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        .status-table th, .status-table td {
            border: 1px solid #ddd;
            padding: 0.8rem 1rem;
            text-align: left;
            font-size: 0.95rem;
        }
        .status-table th {
            background-color: #f8f8f8;
            font-weight: 600;
        }
        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #fff;
        }
        .status-pending { background-color: #f39c12; } /* Oranye */
        .status-approved { background-color: #2ecc71; } /* Hijau */
        .status-rejected { background-color: #e74c3c; } /* Merah */
        .status-completed { background-color: #3498db; } /* Biru */
        .no-booking {
            text-align: center;
            color: #777;
            margin-top: 2rem;
        }
    </style>
</head>
<body>

<header>
    <?php include 'includes/navbar_user.php'; ?>
</header>

<main class="content-page">
    <div class="page-header">
        <h1>Status Booking Pendakian</h1>
    </div>

    <div class="status-table-container">
        <h2>Riwayat Booking Anda</h2>

        <?php
        // --- TEMPAT QUERY DATABASE NANTI ---
        // Di sini nanti kamu akan query database untuk mengambil data booking
        // berdasarkan $user_id. Contoh:
        // $sql = "SELECT * FROM bookings WHERE user_id = ? ORDER BY tanggal_booking DESC";
        // $stmt = $koneksi->prepare($sql);
        // $stmt->bind_param("i", $user_id);
        // $stmt->execute();
        // $result = $stmt->get_result();

        // Untuk sekarang, kita buat data contoh
        $bookings = [
            [
                'id' => 'BK001', 
                'tanggal_naik' => '2025-11-15', 
                'tanggal_turun' => '2025-11-17', 
                'jumlah_pendaki' => 4, 
                'status' => 'approved', // approved, pending, rejected, completed
                'tanggal_booking' => '2025-10-28 10:00:00'
            ],
            [
                'id' => 'BK002', 
                'tanggal_naik' => '2025-12-01', 
                'tanggal_turun' => '2025-12-03', 
                'jumlah_pendaki' => 3, 
                'status' => 'pending',
                'tanggal_booking' => '2025-10-29 11:30:00'
            ]
        ]; // Anggap ini hasil dari database

        if (count($bookings) > 0): // Ganti ini dengan: if ($result->num_rows > 0): 
        ?>
            <table class="status-table">
                <thead>
                    <tr>
                        <th>ID Booking</th>
                        <th>Tgl Naik</th>
                        <th>Tgl Turun</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Ganti ini dengan: while($row = $result->fetch_assoc()):
                    foreach ($bookings as $row): 
                        $status_text = ucfirst($row['status']); // Jadi: Approved, Pending, dll.
                        $status_class = '';
                        switch ($row['status']) {
                            case 'pending': $status_class = 'status-pending'; break;
                            case 'approved': $status_class = 'status-approved'; break;
                            case 'rejected': $status_class = 'status-rejected'; break;
                            case 'completed': $status_class = 'status-completed'; break;
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']); ?></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($row['tanggal_naik']))); ?></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($row['tanggal_turun']))); ?></td>
                        <td><?= htmlspecialchars($row['jumlah_pendaki']); ?> org</td>
                        <td><span class="status-badge <?= $status_class; ?>"><?= $status_text; ?></span></td>
                        <td>
                            <a href="detail_booking.php?id=<?= $row['id']; ?>" style="font-size: 0.9rem;">Detail</a>
                            <?php if($row['status'] == 'pending'): ?>
                                | <a href="pembayaran_booking.php?id=<?= $row['id']; ?>" style="font-size: 0.9rem;">Bayar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; // Ganti dengan: endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-booking">Anda belum memiliki riwayat booking.</p>
        <?php endif; ?>
        <?php 
        // Jangan lupa tutup koneksi database nanti:
        // $stmt->close();
        // $koneksi->close(); 
        ?>
    </div>
</main>

<footer>
    <p>&copy; 2025 Tahura Raden Soerjo. All Rights Reserved.</p>
</footer>

<script src="script.js"></script> 

</body>
</html>