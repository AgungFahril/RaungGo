<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOP Pendakian Gunung Raung</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <?php include '../includes/navbar_user.php'; ?>
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
                    <li>Pendaftaran SIMAKSI secara online hanya melalui website resmi pengelola atau aplikasi tiket pendakian.</li>
                    <li>Booking dapat dilakukan untuk 30 hari ke depan sampai maksimal H-2 khusus tujuan pendakian Gunung Raung.</li>
                    <li>Batas durasi pendakian maksimal 3 (tiga) hari 2 (dua) malam.</li>
                    <li>Verifikasi Pembayaran dilakukan pada hari Senin s/d Jumat jam 08.00–15.30 WIB. Proses maksimal 1x24 jam hari kerja setelah pembayaran.</li>
                    <li>eSIMAKSI diberikan pada kelompok dengan jumlah minimal 3 orang dan menunjuk 1 ketua kelompok.</li>
                    <li>Pendaftaran berlaku bagi pendaki domestik dan mancanegara.</li>
                    <li>Pergantian anggota hanya 1x dan maksimal H-2 sebelum pendakian.</li>
                    <li>Booking yang sudah dibayar tidak dapat menambah anggota.</li>
                    <li>Manipulasi data akan menyebabkan pembatalan dan dimasukkan ke daftar hitam (blacklist).</li>
                </ul>
            </div>
        </section>
        
        <section id="tarif" class="content-block">
            <div class="content-header">
                <h2>Tarif dan Pembayaran</h2>
            </div>
            <div class="content-body">
                <p>Setiap pendaki dikenakan tarif sesuai ketentuan terbaru.</p>
                <ol>
                    <li><strong>Tarif Pendakian Gunung Raung via Bondowoso</strong>
                        <ul>
                            <li>Pendaki WNI: Rp 15.000 / orang / hari</li>
                            <li>Pendaki WNA: Rp 250.000 / orang / hari</li>
                        </ul>
                    </li>
                    <li><strong>Asuransi</strong>: Rp 5.000 / orang (dibayar di pos)</li>
                    <li><strong>Jasa Porter/Guide (opsional)</strong>: sesuai kesepakatan.</li>
                    <li>Pembayaran hanya non-tunai melalui transfer Virtual Account.</li>
                </ol>
            </div>
        </section>

        <section id="pelaksanaan" class="content-block">
            <div class="content-header">
                <h2>Pelaksanaan Pendakian</h2>
            </div>
            <div class="content-body">
                <ol>
                    <li>Bukti konfirmasi berupa QR Code pada eSIMAKSI adalah bukti masuk kawasan.</li>
                    <li>Persyaratan berkas meliputi:
                        <ul>
                            <li>eSIMAKSI (Surat Izin Masuk Kawasan Konservasi)</li>
                            <li>Identitas asli (KTP/SIM/Paspor) wajib diserahkan ke petugas.</li>
                            <li>Usia 10–18 tahun wajib menyertakan surat izin orang tua/wali.</li>
                            <li>Anak di bawah 10 tahun dilarang mendaki.</li>
                            <li>Pendaki wajib membawa perlengkapan, administrasi, dan logistik cukup.</li>
                        </ul>
                    </li>
                    <li>Semua pendaki wajib mengikuti briefing sebelum pendakian.</li>
                    <li>Jaga keselamatan, kebersihan, dan norma selama pendakian.</li>
                    <li>Gunakan jalur resmi yang ditentukan.</li>
                    <li>Wajib lapor kembali ke pos setelah pendakian selesai.</li>
                </ol>
            </div>
        </section>

        <section id="larangan" class="content-block">
            <div class="content-header">
                <h2>Larangan</h2>
            </div>
            <div class="content-body">
                <ul>
                    <li>Dilarang memakai sandal gunung — wajib sepatu trekking.</li>
                    <li>Dilarang membawa/menggunakan drone.</li>
                    <li>Dilarang membuat api unggun dari kayu atau sampah; hanya boleh pakai kompor.</li>
                    <li>Dilarang merusak flora/fauna dan vandalisme.</li>
                    <li>Dilarang melanggar norma agama, susila, dan adat.</li>
                    <li>Dilarang membawa minuman keras atau narkoba.</li>
                    <li>Dilarang membawa senjata tajam/senjata api tidak sesuai peruntukan.</li>
                    <li>Dilarang membuang sampah sembarangan — wajib bawa turun kembali.</li>
                </ul>
            </div>
        </section>
        
        <section id="booking" class="checklist-form content-block">
            <h2>Checklist</h2>
            <form>
                <div class="checklist-items">
                    <label class="checkbox-container">Saya telah membaca dan menyetujui semua ketentuan di atas
                        <input type="checkbox" required>
                        <span class="checkmark"></span>
                    </label>
                    <p><strong>Wajib dibawa:</strong></p>
                    <ol>
                        <li>eSIMAKSI</li>
                        <li>KTP/KTM/SIM/Paspor</li>
                        <li>Surat keterangan sehat (khusus Raung)</li>
                        <li>Trash bag/kantong sampah</li>
                    </ol>
                </div>

                <div class="form-buttons" style="text-align:center; margin-top:25px;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="kuota.php" class="btn btn-primary">Lanjut Booking ➜</a>
                    <?php else: ?>
                        <p style="margin-bottom:10px;">Ingin melakukan booking pendakian?</p>
                        <a href="../login.php?redirect=pengunjung/sop.php" class="btn btn-secondary">Login untuk Booking</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </main>
</div>

<footer>
    <p>&copy; 2025 Tahura Raden Soerjo. All Rights Reserved.</p>
</footer>
<script src="../script.js"></script>
</body>
</html>
