<?php
session_start();

// Jika belum login, arahkan ke login dengan redirect
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'sop';
    header("Location: login.php?redirect=sop");
    exit;
}

// Sudah login
$nama_pengguna = htmlspecialchars($_SESSION['nama'] ?? 'Pendaki');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOP Pendakian Gunung Raung</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include 'includes/navbar_user.php'; ?>
    </header>

    <div class="page-container">
        <aside class="sidebar">
            <ul class="sidebar-nav">
                <li class="active"><a href="#ketentuan-umum"><i class="fa-solid fa-file-lines"></i> Ketentuan Umum</a></li>
                <li><a href="#tarif"><i class="fa-solid fa-tags"></i> Tarif</a></li>
                <li><a href="#pelaksanaan"><i class="fa-solid fa-person-hiking"></i> Pelaksanaan</a></li>
                <li><a href="#larangan"><i class="fa-solid fa-ban"></i> Larangan</a></li>
                <li><a href="#booking"><i class="fa-solid fa-calendar-check"></i> Booking</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <section id="ketentuan-umum" class="content-block">
                <div class="content-header">
                    <h2>Pendaftaran Pendakian</h2>
                    <p>Syarat dan ketentuan umum pendaftaran pendakian Gunung Raung.</p>
                </div>
                <div class="content-body">
                    <ul>
                        <li>Pendaftaran SIMAKSI hanya melalui website resmi pengelola.</li>
                        <li>Booking dapat dilakukan hingga maksimal H-2 sebelum keberangkatan.</li>
                        <li>Durasi pendakian maksimal 3 hari 2 malam.</li>
                        <li>Setiap kelompok wajib beranggotakan minimal 3 orang.</li>
                        <li>Manipulasi data akan menyebabkan pembatalan dan blacklist pendaki.</li>
                    </ul>
                </div>
            </section>
            
            <section id="tarif" class="content-block">
                <div class="content-header">
                    <h2>Tarif dan Pembayaran</h2>
                </div>
                <div class="content-body">
                    <ol>
                        <li><strong>Gunung Raung via Bondowoso</strong>:
                            <ul>
                                <li>WNI: Rp15.000 / orang / hari</li>
                                <li>WNA: Rp250.000 / orang / hari</li>
                            </ul>
                        </li>
                        <li>Asuransi (pos) Rp5.000 / orang</li>
                        <li>Porter/Guide (opsional)</li>
                        <li>Pembayaran hanya melalui transfer non-tunai.</li>
                    </ol>
                </div>
            </section>

            <section id="pelaksanaan" class="content-block">
                <div class="content-header">
                    <h2>Pelaksanaan Pendakian</h2>
                </div>
                <div class="content-body">
                    <ol>
                        <li>Setiap pendaki wajib membawa identitas asli dan surat izin.</li>
                        <li>Pendaki usia < 10 tahun dilarang mendaki.</li>
                        <li>Briefing wajib diikuti sebelum pendakian.</li>
                        <li>Pendaki wajib melapor setelah turun gunung.</li>
                    </ol>
                </div>
            </section>

            <section id="larangan" class="content-block">
                <div class="content-header">
                    <h2>Larangan</h2>
                </div>
                <div class="content-body">
                    <ul>
                        <li>Dilarang membawa minuman keras, narkoba, atau senjata tajam.</li>
                        <li>Dilarang membuat api unggun dari kayu.</li>
                        <li>Dilarang menggunakan drone tanpa izin petugas.</li>
                        <li>Dilarang membuang sampah sembarangan.</li>
                    </ul>
                </div>
            </section>
            
            <!-- Bagian konfirmasi booking -->
            <section id="booking" class="checklist-form content-block">
                <h2>Persetujuan Pendaki</h2>
                <form action="pengunjung/booking.php" method="GET" id="formSOP">
                    <div class="checklist-items">
                        <label class="checkbox-container">
                            Saya telah membaca dan menyetujui semua peraturan dan ketentuan di atas.
                            <input type="checkbox" id="setuju" required>
                            <span class="checkmark"></span>
                        </label>

                        <p><strong>Wajib dibawa:</strong></p>
                        <ol>
                            <li>eSIMAKSI (Surat Izin Masuk Kawasan Konservasi)</li>
                            <li>KTP/SIM/Paspor yang masih berlaku</li>
                            <li>Surat keterangan sehat</li>
                            <li>Trash bag / kantong sampah</li>
                        </ol>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" id="btnBooking" class="btn btn-primary" disabled>LANJUT BOOKING</button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <footer>
        <p>&copy; 2025 Tahura Raden Soerjo. All Rights Reserved.</p>
    </footer>

    <script>
    // Aktifkan tombol booking hanya jika checkbox dicentang
    const checkbox = document.getElementById('setuju');
    const tombol = document.getElementById('btnBooking');
    checkbox.addEventListener('change', function() {
        tombol.disabled = !this.checked;
    });
    </script>
</body>
</html>
