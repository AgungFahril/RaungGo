<?php
session_start();

// ✅ Jika user menekan tombol "Lanjut Booking"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setuju'])) {
    $_SESSION['setuju_sop'] = true;
    header("Location: kuota.php");
    exit;
}

$logged_in = isset($_SESSION['user_id']);
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
            background: linear-gradient(to bottom right, #f9fff9, #e8f5e9);
            margin: 0;
        }
        .page-container {
            display: flex;
            max-width: 1300px;
            margin: 100px auto;
            gap: 25px;
            padding: 20px;
        }
        .sidebar {
            flex: 1;
            max-width: 250px;
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        .sidebar-nav li {
            list-style: none;
            margin-bottom: 12px;
        }
        .sidebar-nav a {
            text-decoration: none;
            color: #2e7d32;
            font-weight: 500;
            display: block;
            padding: 8px;
            border-radius: 6px;
            transition: background 0.3s, color 0.3s;
        }
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: #e8f5e9;
            color: #1b5e20;
            font-weight: 600;
        }
        .main-content {
            flex: 3;
            background: #fff;
            border-radius: 12px;
            padding: 35px 40px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        ul, ol { line-height: 1.7; }
        .content-block { margin-bottom: 40px; scroll-margin-top: 120px; }
        .content-block h2 { border-bottom: 2px solid #c8e6c9; padding-bottom: 5px; }
        .notice-box {
            background: #f1f8e9;
            border-left: 6px solid #81c784;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
        }
        .checklist-form {
            text-align: center;
            background: #f1f8e9;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #c8e6c9;
        }
        .btn-primary, .btn-secondary {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-primary { background-color: #43a047; color: white; }
        .btn-primary:hover { background-color: #2e7d32; transform: translateY(-2px); }
        .btn-secondary { background-color: #eee; color: #333; }
        .btn-secondary:hover { background-color: #ddd; }
        footer { text-align: center; margin: 40px 0; color: #555; }

        /* 🌿 Animasi smooth scroll */
        html { scroll-behavior: smooth; }
    </style>
</head>
<body>

<header>
    <?php include '../includes/navbar_user.php'; ?>
</header>

<div class="page-container">
    <aside class="sidebar">
        <ul class="sidebar-nav" id="sidebar-nav">
            <li><a href="#ketentuan-umum" class="active"><i class="fa-solid fa-file-lines"></i> Ketentuan Umum</a></li>
            <li><a href="#tarif"><i class="fa-solid fa-tags"></i> Tarif</a></li>
            <li><a href="#pelaksanaan"><i class="fa-solid fa-person-hiking"></i> Pelaksanaan</a></li>
            <li><a href="#larangan"><i class="fa-solid fa-ban"></i> Larangan</a></li>
            <li><a href="#booking"><i class="fa-solid fa-calendar-check"></i> Booking</a></li>
        </ul>
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <a href="../pengunjung/dashboard.php" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #e53935; color: white; text-decoration: none; padding: 12px 16px; border-radius: 8px; font-weight: 600; transition: 0.3s;">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </aside>

    <main class="main-content">
        <section id="ketentuan-umum" class="content-block">
            <h2>Ketentuan Umum</h2>
            <ul>
                <li>Pendaftaran SIMAKSI hanya melalui website resmi pengelola atau aplikasi booking.</li>
                <li>Booking dapat dilakukan maksimal H-2 sebelum keberangkatan.</li>
                <li>Durasi pendakian maksimal 3 hari 2 malam.</li>
                <li>Kelompok wajib terdiri minimal 3 orang dan 1 ketua.</li>
                <li>Manipulasi data akan menyebabkan pembatalan dan blacklist pendaki.</li>
            </ul>
        </section>

        <section id="tarif" class="content-block">
            <h2>Tarif dan Pembayaran</h2>
            <ol>
                <li><strong>WNI:</strong> Rp 15.000 / orang / hari</li>
                <li><strong>WNA:</strong> Rp 250.000 / orang / hari</li>
                <li><strong>Asuransi:</strong> Rp 5.000 / orang</li>
                <li>Pembayaran hanya non-tunai via Virtual Account.</li>
            </ol>
        </section>

        <section id="pelaksanaan" class="content-block">
            <h2>Pelaksanaan Pendakian</h2>
            <ol>
                <li>Setiap pendaki wajib membawa eSIMAKSI dan identitas asli (KTP/SIM/Paspor).</li>
                <li>Remaja usia 10–18 tahun wajib menyertakan izin orang tua.</li>
                <li>Wajib mengikuti briefing sebelum pendakian dimulai.</li>
                <li>Gunakan jalur resmi dan lapor kembali ke pos setelah turun.</li>
            </ol>
        </section>

        <section id="larangan" class="content-block">
            <h2>Larangan</h2>
            <ul>
                <li>Dilarang membawa minuman keras, narkoba, atau senjata tajam.</li>
                <li>Dilarang membuat api unggun dari kayu.</li>
                <li>Dilarang membuang sampah sembarangan.</li>
                <li>Wajib menjaga etika, lingkungan, dan budaya setempat.</li>
            </ul>
        </section>

        <section id="booking" class="content-block">
            <?php if ($logged_in): ?>
                <div class="checklist-form">
                    <h2>Checklist</h2>
                    <form method="POST">
                        <label>
                            <input type="checkbox" name="setuju" id="setuju" required>
                            Saya telah membaca, memahami, dan menyetujui seluruh ketentuan di atas.
                        </label><br>
                        <button type="submit" class="btn-primary" id="btnBooking" disabled>LANJUT KE PEMILIHAN KUOTA</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="notice-box">
                    <h3>Ingin Melakukan Booking Pendakian?</h3>
                    <p>Silakan login terlebih dahulu untuk melanjutkan proses pemesanan pendakian.</p>
                    <a href="../login.php?redirect=pengunjung/sop.php" class="btn-primary">LOGIN UNTUK MELANJUTKAN</a>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php include 'includes/footer.php'; ?>


<script>
// 🌿 Enable tombol setelah centang
const checkbox = document.getElementById('setuju');
const tombol = document.getElementById('btnBooking');
if (checkbox && tombol) {
    checkbox.addEventListener('change', function() {
        tombol.disabled = !this.checked;
    });
}

// 🌿 Highlight otomatis di sidebar saat scroll
const sections = document.querySelectorAll('.content-block');
const navLinks = document.querySelectorAll('#sidebar-nav a');
window.addEventListener('scroll', () => {
    let current = "";
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 120;
        if (scrollY >= sectionTop) current = section.getAttribute("id");
    });
    navLinks.forEach(link => {
        link.classList.remove("active");
        if (link.getAttribute("href") === "#" + current) {
            link.classList.add("active");
        }
    });
});
</script>

</body>
</html>
