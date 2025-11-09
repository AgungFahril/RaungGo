<?php
session_start();

// 🔒 Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'pengunjung/sop.php';
    header("Location: ../login.php?redirect=pengunjung/sop.php");
    exit;
}

// ✅ Jika user menekan tombol "Lanjut Booking"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setuju'])) {
    $_SESSION['setuju_sop'] = true; // simpan status persetujuan
    header("Location: kuota.php");
    exit;
}

$nama_pengguna = htmlspecialchars($_SESSION['nama'] ?? 'Pendaki');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOP Pendakian Gunung Raung</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8faf8;
        }
        .main-content {
            margin: 40px auto;
            width: 85%;
            background: #fff;
            border-radius: 10px;
            padding: 25px 40px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 15px;
        }
        ul, ol { line-height: 1.7; }
        .checklist-form {
            margin-top: 30px;
            text-align: center;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 25px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-primary:disabled {
            background-color: #9e9e9e;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

<main class="main-content">
    <h2>📜 SOP Pendakian Gunung Raung</h2>
    <p style="text-align:center;color:#444;">Harap baca dan pahami seluruh peraturan sebelum melanjutkan proses booking pendakian.</p>

    <section class="content-block">
        <h3>Ketentuan Umum</h3>
        <ul>
            <li>Pendaftaran SIMAKSI hanya melalui website resmi pengelola.</li>
            <li>Booking dapat dilakukan hingga maksimal H-2 sebelum keberangkatan.</li>
            <li>Durasi pendakian maksimal 3 hari 2 malam.</li>
            <li>Setiap kelompok wajib beranggotakan minimal 3 orang.</li>
            <li>Manipulasi data akan menyebabkan pembatalan dan blacklist pendaki.</li>
        </ul>

        <h3>Larangan</h3>
        <ul>
            <li>Dilarang membawa minuman keras, narkoba, atau senjata tajam.</li>
            <li>Dilarang membuat api unggun dari kayu di kawasan hutan.</li>
            <li>Dilarang membuang sampah sembarangan.</li>
            <li>Wajib membawa trashbag pribadi dan surat keterangan sehat.</li>
        </ul>
    </section>

    <!-- ✅ Form Persetujuan -->
    <section class="checklist-form">
        <form method="POST" action="">
            <label>
                <input type="checkbox" name="setuju" value="1" id="setuju" required>
                Saya telah membaca dan menyetujui seluruh peraturan di atas.
            </label>
            <br>
            <button type="submit" class="btn-primary" id="btnBooking" disabled>LANJUT KE PEMILIHAN KUOTA</button>
        </form>
    </section>
</main>

<footer style="text-align:center; margin:25px 0; color:#555;">
    &copy; 2025 Tahura Raden Soerjo. All Rights Reserved.
</footer>

<script>
const checkbox = document.getElementById('setuju');
const tombol = document.getElementById('btnBooking');
checkbox.addEventListener('change', function() {
    tombol.disabled = !this.checked;
});
</script>

</body>
</html>
