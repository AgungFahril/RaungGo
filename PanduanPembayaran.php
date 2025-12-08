<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Pembayaran - Tahura Raden Soerjo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/responsive-navbar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <?php include 'includes/navbar_user.php'; ?>
</header>

<main class="content-page">
    <div class="page-header">
        <h1>Panduan Pembayaran</h1>
    </div>
    
    <div class="page-content">
        
        <section class="guide-section">
            <h2>Pembayaran Melalui Bank Jatim</h2>
            <h3>1. Melalui Transfer ATM</h3>
            <ul>
                <li>Pada ATM Bank Jatim, pilih menu Pembayaran</li>
                <li>Pilih lainnya</li>
                <li>Pilih Virtual Account</li>
                <li>Masukkan nomor Bank Jatim Virtual Account</li>
                <li>Lakukan konfirmasi pembayaran anda</li>
                <li>Transaksi selesai</li>
            </ul>
            <h3>2. Melalui Mobile Banking Bank Jatim</h3>
            <ul>
                <li>Login ke aplikasi mobile banking JConnect Mobile</li>
                <li>Pilih menu Bayar</li>
                <li>Pilih Virtual Account</li>
                <li>Masukkan Nomor Virtual Account</li>
                <li>Masukkan PIN anda</li>
                <li>Transaksi selesai</li>
            </ul>
        </section>
        
        <section class="guide-section">
            <h2>Pembayaran Melalui Bank Mandiri</h2>
            <h3>1. Melalui Transfer ATM</h3>
            <ul>
                <li>Masukkan kartu di mesin ATM Bank Mandiri</li>
                <li>Masukkan PIN anda</li>
                <li>Pilih Transaksi Lainnya</li>
                <li>Pilih Antar Bank</li>
                <li>Masukkan Kode Bank Jatim 114 + nomor Rekening Virtual Account</li>
                <li>Masukkan jumlah transfer sesuai dengan nominal tagihan</li>
                <li>Lakukan konfirmasi pembayaran</li>
                <li>Transaksi selesai</li>
            </ul>
            <h3>2. Melalui Mobile Banking</h3>
            <ul>
                <li>Login ke aplikasi mobile banking Livin' by Mandiri</li>
                <li>Pilih menu Transfer</li>
                <li>Pilih Transfer ke Penerima Baru</li>
                <li>Pilih Bank BPD Jatim</li>
                <li>Masukkan Nomor Virtual Account sebagai nomor rekening tujuan</li>
                <li>Penerima a.n ketua kelompok pendaki</li>
                <li>Masukkan Nominal Transfer sesuai dengan tagihan</li>
                <li>Pilih metode transfer Transfer Online</li>
                <li>Masukkan PIN anda</li>
                <li>Transaksi selesai</li>
            </ul>
        </section>

        </div>
</main>

<?php include 'includes/footer.php'; ?>


<script src="script.js"></script> 

</body>
</html>