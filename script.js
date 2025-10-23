// Ambil elemen ikon dan input password dari HTML
const togglePassword = document.querySelector('#togglePassword');
const passwordInput = document.querySelector('#password');

// Jalankan kode hanya jika kedua elemen ditemukan di halaman
if (togglePassword && passwordInput) {

    togglePassword.addEventListener('click', function () {
        // Cek tipe input saat ini, lalu ganti
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Ganti ikon mata (dari terbuka menjadi tertutup, atau sebaliknya)
        this.classList.toggle('fa-eye-slash');
    });

}