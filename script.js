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
// --- SCRIPT UNTUK ACCORDION FAQ ---
document.addEventListener("DOMContentLoaded", function() {
    const faqQuestions = document.querySelectorAll(".faq-question");

    faqQuestions.forEach(button => {
        button.addEventListener("click", () => {
            const item = button.parentElement;
            const answer = item.querySelector(".faq-answer");
            const wasActive = item.classList.contains("active");

            // Tutup semua item lain (jika kamu mau)
            document.querySelectorAll(".faq-item").forEach(otherItem => {
                 if (otherItem !== item && otherItem.classList.contains("active")) {
                    otherItem.classList.remove("active");
                    otherItem.querySelector(".faq-answer").style.maxHeight = null;
                 }
             });

            // Buka atau tutup item yang diklik
            item.classList.toggle("active");
            if (item.classList.contains("active")) {
                // Atur max-height agar pas dengan kontennya
                answer.style.maxHeight = answer.scrollHeight + "px";
            } else {
                answer.style.maxHeight = null;
            }
        });
    });
});