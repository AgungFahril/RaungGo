<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Pendakian Raung Bondowoso - Tahura Raden Soerjo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <?php include 'includes/navbar_user.php'; ?>
</header>

<main>
    <section class="hero">
        <div class="hero-content">
            <h1>Gunung Raung Bondowoso</h1>
            <p>Puncak Gunung Raung dikenal dengan nama Puncak Sejati yang berada di ketinggian 3.344 mdpl.</p>
            <div class="hero-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="pengunjung/booking.php" class="btn btn-primary">BOOKING</a>
                    <a href="StatusBooking.php" class="btn btn-secondary">STATUS BOOKING</a>
                <?php else: ?>          
                    <a href="login.php" class="btn btn-primary">BOOKING</a>
                    <a href="login.php" class="btn btn-secondary">STATUS BOOKING</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="stat-card">
            <p class="stat-number">20,484</p>
            <p class="stat-label">Gunung Raung</p>
            <span>Jumlah pendaki tahun 2025</span>
        </div>
        <div class="stat-card">
            <p class="stat-number">5,000</p>
            <p class="stat-label">Kawah Ijen</p>
            <span>Jumlah pendaki tahun 2025</span>
        </div>
    </section>

    <section class="info-section">
        <h2>Alur Booking</h2>
        <div class="booking-steps">
            <div class="step">
                <h3>1. Portal Booking Pendakian</h3>
                <p>Klik tombol BOOKING. Disarankan menggunakan browser Google Chrome untuk melakukan Booking.</p>
            </div>
            <div class="step">
                <h3>2. SOP Pendakian</h3>
                <p>Pahami dan taati SOP dan peraturan pendakian yang berlaku.</p>
            </div>
            <div class="step">
                <h3>3. Pilih Tujuan dan Jadwal</h3>
                <p>Pilih tujuan Gunung Arjuno-Welirang atau Gunung Pundak serta tentukan tanggal.</p>
            </div>
            <div class="step">
                <h3>4. Mengisi Form</h3>
                <p>Lengkapi semua kolom yang telah disediakan dan pastikan alamat Email dan nomor telepon sudah sesuai.</p>
            </div>
            <div class="step">
                <h3>5. Pembayaran</h3>
                <p>Tagihan akan dikirimkan melalui email dan whatsapp. Batas waktu pembayaran yakni 6 jam.</p>
            </div>
            <div class="step">
                <h3>6. Klik Bayar</h3>
                <p>Setelah melakukan pembayaran, pastikan untuk klik tombol SUDAH BAYAR yang terdapat di menu status booking.</p>
            </div>
        </div>
    </section>
    <section class="info-section faq-section">
    
    <div class="faq-container">
        <div class="faq-header">
            <h3>FAQ</h3>
            <p>Frequently Asked Questions</p>
        </div>
        
        <div class="faq-list">
            
            <div class="faq-item">
                <button class="faq-question">
                    <span>Tarif</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Informasi detail mengenai tarif pendakian, asuransi, dan biaya lainnya dapat dilihat pada halaman SOP Pendaki.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    <span>Jumlah rombongan</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Satu rombongan pendakian minimal terdiri dari 3 orang dan maksimal 10 orang, termasuk ketua rombongan.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    <span>Durasi Pendakian</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Durasi pendakian yang diizinkan adalah maksimal 3 (tiga) hari 2 (dua) malam.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    <span>Jam Pelayanan Administrasi</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Pelayanan administrasi dan verifikasi pembayaran online dibuka pada hari Senin - Jumat, pukul 08.00 - 16.00 WIB.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    <span>Jam Pelayanan Pos Perizinan dan Batas Waktu Trekking</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Pos perizinan (basecamp) buka 24 jam. Namun, batas aman untuk memulai pendakian adalah pukul 17.00 WIB. Pendakian malam tidak disarankan.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    <span>Apakah bisa melakukan pembayaran di pos?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Tidak. Semua pembayaran tiket masuk (SIMAKSI) harus dilakukan secara online melalui transfer Virtual Account setelah booking dikonfirmasi.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    <span>Apakah bisa menambah anggota rombongan ketika booking telah disetujui?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Tidak. Jumlah anggota rombongan yang sudah dibayar tidak dapat ditambah. Pastikan jumlah anggota sudah final sebelum melakukan pembayaran.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    <span>Berapa batas waktu pembayaran?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Batas waktu pembayaran adalah 6 jam setelah kode Virtual Account (invoice) diterbitkan. Jika melewati batas tersebut, booking akan otomatis dibatalkan.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    <span>Masa berlaku surat keterangan sehat?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Surat keterangan sehat dari dokter (klinik atau puskesmas) berlaku maksimal H-2 (dua hari) sebelum tanggal pendakian.</p>
                </div>
            </div>

        </div> </div> </section>

    <section class="info-section">
        <h2>Peta Jalur Pendakian</h2>
        <h3 class="section-subtitle">Deskripsi jalur pendakian resmi Gunung Raung</h3>
        
        <div class="map-feature-container">
            
            <div class="map-image-wrapper">
                <img src="images/peta-raung-thumbnail.jpg" alt="Peta Jalur Pendakian Gunung Raung">
            </div>

            <div class="map-trails-info">
                <h3>Jalur Pendakian Resmi Gunung Raung</h3>
                <p>Terdapat dua jalur pendakian utama untuk mencapai Puncak Sejati (3.332 m) yang tertera di peta:</p>
                
                <div class="trail-desc">
                    <h4>1. Jalur Sumberwringin (via Bondowoso)</h4>
                    <p>Jalur pendakian yang lebih panjang, dimulai dari trailhead Sumberwringin. Jalur ini melewati 9 pos camp (Camp 1 s/d 9) sebelum mencapai bibir kawah.</p>
                </div>
                
                 <div class="trail-desc">
                    <h4>2. Jalur Kalibaru (via Banyuwangi)</h4>
                    <p>Jalur ini dimulai dari trailhead Kalibaru dan dikenal lebih menantang. Jalur ini melewati Pos 1 dan 3 pos camp (Camp 1 s/d 3) sebelum mencapai Puncak Sejati.</p>
                </div>
                
                <a href="pdfs/peta-gunung-raung.pdf" target="_blank" class="btn-download primary" style="margin-top: 2rem;">
                    LIHAT / DOWNLOAD PETA LENGKAP (PDF)
                </a>
            </div>

        </div>
    </section>
    <section class="info-section">
        <h2>Maps</h2>
        <h3 class="section-subtitle">Lokasi pos pendakian interaktif Gunung Raung</h3>
        
        <div class="map-embed-container">
            <iframe src="https://www.google.com/maps/d/u/0/embed?mid=1t7iVhcNs804WT9Fa_XCPuNa_Fb3YzdU&ehbc=2E312F&noprof=1" width="640" height="480"></iframe>" 
         </div>
    </section>
    
    </main>
<script src="script.js"></script>
<footer>
    <p>&copy; 2025 Tahura Raden Soerjo. All Rights Reserved.</p>
</footer>

</body>
</html>
