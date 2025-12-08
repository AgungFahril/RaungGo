<!DOCTYPE html>
<html lang="id">
<!DOCTYPE html>
<html lang="id">
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOP Pendakian Gunung Raung</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="pengunjung/css/responsive-navbar.css">
    <link rel="stylesheet" href="pengunjung/css/sop-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- TEST CSS INLINE -->
    <style>
@media screen and (max-width: 968px) {
    .page-container {
        flex-direction: column !important;
        margin: 80px auto 30px !important;
        padding: 0 15px !important;
    }
    
    .sidebar {
        width: 100% !important;
        position: sticky !important;
        top: 70px !important;
        z-index: 100 !important;
        padding: 15px !important;
        margin-bottom: 20px !important;
        background: white !important; /* Ganti dari yellow */
    }
    
    .sidebar-nav {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    
    .sidebar-nav li {
        margin: 0 !important;
    }
    
    .sidebar-nav a {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        padding: 16px 12px !important;
        border: 2px solid #e0e0e0 !important;
        border-radius: 10px !important;
        gap: 8px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        background: white !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08) !important;
        transition: all 0.3s ease !important;
        color: #555 !important;
        text-decoration: none !important;
    }
    
    .sidebar-nav a:hover {
        border-color: #43a047 !important;
        background: #f9fdf9 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 10px rgba(67, 160, 71, 0.2) !important;
    }
    
    .sidebar-nav li.active a {
        background: linear-gradient(135deg, #43a047, #2e7d32) !important;
        color: white !important;
        border-color: #2e7d32 !important;
        box-shadow: 0 4px 12px rgba(67, 160, 71, 0.4) !important;
    }
    
    .sidebar-nav i {
        font-size: 24px !important;
        margin-bottom: 4px !important;
    }
    
    .btn-back {
        display: block !important;
        width: 100% !important;
        padding: 12px 20px !important;
        margin-top: 12px !important;
        background: linear-gradient(135deg, #e53935, #c62828) !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        text-align: center !important;
        text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(229, 57, 53, 0.3) !important;
    }
    
    .main-content {
        padding: 25px 20px !important;
    }
}

@media screen and (max-width: 480px) {
    .sidebar-nav {
        grid-template-columns: 1fr !important;
    }
}
</style>

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
    <a href="dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
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
                        <li>Booking dapat dilakukan untuk 30 hari kedepan sampai dengan maksimal H-2 khusus tujuan pendakian Gunung Raung.</li>
                        <li>Batas durasi pendakian yang diizinkan maksimal 3 (tiga) hari 2 (dua) malam.</li>
                        <li>Verifikasi Pembayaran dilakukan pada hari Senin s/d Jumat jam 08.00 s/d 15.30 WIB. Proses verifikasi maksimal 1x24 jam hari kerja setelah melakukan pembayaran.</li>
                        <li>Surat Izin Masuk Kawasan Konservasi (eSIMAKSI) khusus pendakian Gunung Raung diberikan pada kelompok dengan jumlah anggota minimal 3 orang dan menunjuk 1 orang sebagai ketua kelompok yang bertanggung jawab.</li>
                        <li>Pendaftaran diberlakukan bagi calon pendaki, baik nusantara maupun mancanegara.</li>
                        <li>Calon pendaki dapat melakukan pergantian anggota maksimal 1 (satu) kali, dengan batasan waktu 2 (dua) hari (H-2) sebelum keberangkatan.</li>
                        <li>Pendaki yang sudah melakukan booking tidak dapat menambahkan anggota.</li>
                        <li>Bagi yang terbukti melakukan manipulasi dan/atau pemalsuan data, maka booking akan dibatalkan dan pendaki masuk dalam **daftar hitam (blacklist)**.</li>
                    </ul>
                </div>
            </section>
            
            <section id="tarif" class="content-block">
                <div class="content-header">
                    <h2>Tarif dan Pembayaran</h2>
                </div>
                <div class="content-body">
                    <p>Setiap pendaki di kawasan Gunung Raung dikenakan tarif karcis masuk sesuai dengan ketentuan terbaru.</p>
                    <ol>
                        <li><strong>Tarif Pendakian Gunung Raung via Bondowoso</strong>
                            <ul>
                                <li>Pendaki WNI: Rp 15.000,- / orang / hari</li>
                                <li>Pendaki WNA: Rp 250.000,- / orang / hari</li>
                            </ul>
                        </li>
                        <li><strong>Asuransi (pembayaran di pos)</strong>: Rp 5.000,- / orang</li>
                        <li><strong>Jasa Porter/Guide (opsional)</strong>: Tarif bervariasi sesuai kesepakatan.</li>
                        <li>Pembayaran hanya non tunai dengan transfer ke nomor rekening virtual account yang tertera pada invoice booking.</li>
                    </ol>
                </div>
            </section>

            <section id="pelaksanaan" class="content-block">
                <div class="content-header">
                    <h2>Pelaksanaan Pendakian</h2>
                </div>
                <div class="content-body">
                    <ol>
                        <li>Bukti konfirmasi berupa QR Code yang ada pada eSIMAKSI menjadi alat bukti masuk ke dalam kawasan.</li>
                        <li>Persyaratan memberkas meliputi:
                            <ul>
                                <li>eSIMAKSI (Surat Izin Masuk Kawasan Konservasi)</li>
                                <li>Identitas asli (KTP/SIM/Paspor) wajib diserahkan kepada petugas selama pendakian.</li>
                                <li>Remaja usia 10-18 tahun wajib menyertakan Surat Keterangan Izin Orang Tua/Wali.</li>
                                <li>Anak-anak usia di bawah 10 tahun tidak diperkenankan melakukan pendakian.</li>
                                <li>Setiap pendaki diharuskan membawa perlengkapan pendakian standar (personal & kelompok), administrasi, dan logistik yang cukup.</li>
                            </ul>
                        </li>
                        <li>Semua calon pendaki wajib mengikuti pengarahan (briefing) dari petugas sebelum melakukan pendakian.</li>
                        <li>Setiap pendaki harus menjaga keselamatan, kebersihan, dan norma yang berlaku selama di jalur pendakian.</li>
                        <li>Pendaki wajib menggunakan jalur pendakian resmi yang telah ditentukan.</li>
                        <li>Pendaki wajib lapor kembali ke pos pendakian setelah selesai melakukan pendakian.</li>
                    </ol>
                </div>
            </section>

            <section id="larangan" class="content-block">
                <div class="content-header">
                    <h2>Larangan</h2>
                </div>
                <div class="content-body">
                    <ul>
                        <li>DILARANG MEMAKAI SANDAL GUNUNG. Sepatu trekking adalah wajib.</li>
                        <li>Dilarang membawa dan menggunakan Drone selama berada di kawasan Gunung Raung.</li>
                        <li>Dilarang membuat api unggun dari kayu atau sampah untuk tujuan apapun; api hanya boleh dari kompor.</li>
                        <li>Dilarang melakukan tindakan yang mengakibatkan kerusakan flora/fauna serta vandalisme.</li>
                        <li>Dilarang melanggar norma agama, norma susila, norma budaya dan nilai-nilai adat istiadat masyarakat setempat.</li>
                        <li>Dilarang membawa dan minum-minuman keras (beralkohol) serta menggunakan obat-obat terlarang (narkoba).</li>
                        <li>Dilarang membawa senjata tajam dan senjata api yang tidak sesuai peruntukannya.</li>
                        <li>Dilarang membuang sampah sembarangan dan wajib membawa sampah anda turun kembali.</li>
                    </ul>
                </div>
            </section>
            
            <section id="booking" class="checklist-form content-block">
                <h2>Checklist</h2>
                <form>
                    <div class="checklist-items">
                        <label class="checkbox-container"> Saya telah membaca, menyetujui, dan mengikuti semua peraturan dan ketentuan diatas
                            <input type="checkbox" required>
                            <span class="checkmark"></span>
                        </label>
                        <p><strong>Wajib untuk dibawa:</strong></p>
                        <ol>
                            <li>eSIMAKSI (Surat Izin Masuk Kawasan Konservasi)</li>
                            <li>Membawa KTP/KTM/SIM/Paspor yang masih berlaku</li>
                            <li>Surat keterangan sehat (khusus pendakian Gunung Raung)</li>
                            <li>Membawa trash bag/kantong sampah</li>
                        </ol>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">BOOKING SEKARANG</button>
                        <a href="login.html" class="btn-secondary-solid">LOGIN</a>
                    </div>
                </form>
            </section>

        </main>
    </div>

   <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>