<!-- Footer Modern & Responsive -->
<footer style="background:linear-gradient(135deg,#2e7d32,#1b5e20);color:#fff;padding:40px 20px 25px;margin-top:50px;width:100%;box-shadow:0 -4px 15px rgba(0,0,0,0.1)">
    <div style="max-width:1200px;margin:0 auto">
        <!-- Footer Content -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:30px;margin-bottom:25px">
            
            <!-- Column 1: About -->
            <div>
                <h3 style="font-size:20px;margin-bottom:12px;font-weight:700;color:#FFD700">
                    🏔️ Gunung Raung
                </h3>
                <p style="font-size:13px;line-height:1.7;opacity:0.9;margin:0">
                    Sistem booking pendakian resmi. Nikmati pengalaman mendaki yang aman dan terorganisir.
                </p>
            </div>
            
            <!-- Column 2: Quick Links -->
            <div>
                <h4 style="font-size:16px;margin-bottom:12px;font-weight:600;color:#FFD700">Link Cepat</h4>
                <ul style="list-style:none;padding:0;margin:0">
                    <li style="margin-bottom:8px">
                        <a href="<?= strpos($_SERVER['PHP_SELF'], '/pengunjung/') !== false ? '../index.php' : 'index.php' ?>" 
                           style="color:#fff;text-decoration:none;opacity:0.9;transition:.3s;font-size:13px">
                            🏠 Beranda
                        </a>
                    </li>
                    <li style="margin-bottom:8px">
                        <a href="<?= strpos($_SERVER['PHP_SELF'], '/pengunjung/') !== false ? '../jalur.php' : 'jalur.php' ?>" 
                           style="color:#fff;text-decoration:none;opacity:0.9;transition:.3s;font-size:13px">
                            🗺️ Jalur Pendakian
                        </a>
                    </li>
                    <li style="margin-bottom:8px">
                        <a href="<?= strpos($_SERVER['PHP_SELF'], '/pengunjung/') !== false ? '../StatusBooking.php' : 'StatusBooking.php' ?>" 
                           style="color:#fff;text-decoration:none;opacity:0.9;transition:.3s;font-size:13px">
                            📋 Status Booking
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Column 3: Contact -->
            <div>
                <h4 style="font-size:16px;margin-bottom:12px;font-weight:600;color:#FFD700">Kontak</h4>
                <p style="font-size:13px;line-height:1.8;opacity:0.9;margin:0">
                    📍 Banyuwangi, Jawa Timur<br>
                    📞 (0333) 123-456<br>
                    📧 info@gunungraung.com
                </p>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div style="border-top:1px solid rgba(255,255,255,0.2);padding-top:20px;text-align:center">
            <p style="font-size:13px;opacity:0.85;margin:0">
                &copy; <?= date('Y') ?> <strong>Gunung Raung</strong>. All Rights Reserved.
            </p>
        </div>
    </div>
</footer>

<style>
footer a:hover {
    opacity: 1 !important;
    transform: translateX(3px);
}

/* Responsive Mobile */
@media(max-width:768px){
    footer {
        padding: 30px 20px 20px;
        margin-top: 40px;
    }
    
    footer > div > div:first-child {
        grid-template-columns: 1fr;
        gap: 25px;
        text-align: center;
    }
    
    footer h3,
    footer h4 {
        font-size: 17px;
    }
    
    footer p,
    footer li {
        font-size: 12px;
    }
    
    footer ul {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
}

@media(max-width:480px){
    footer {
        padding: 25px 15px 18px;
    }
    
    footer h3 {
        font-size: 16px;
    }
    
    footer h4 {
        font-size: 15px;
    }
}
</style>
