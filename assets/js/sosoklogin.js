document.addEventListener('DOMContentLoaded', function() {
    
    // --- BARU: Logika Pindah Form (Slide Overlay) ---
    const showRegisterButton = document.getElementById('btn-show-register');
    const showLoginButton = document.getElementById('btn-show-login');
    const mainContainer = document.getElementById('main-container');

    if (showRegisterButton && showLoginButton && mainContainer) {
        
        showRegisterButton.addEventListener('click', () => {
            mainContainer.classList.add('active');
        });

        showLoginButton.addEventListener('click', () => {
            mainContainer.classList.remove('active');
        });

    } else {
        console.error("Elemen tombol overlay (btn-show-register / btn-show-login) tidak ditemukan.");
    }

    // --- TETAP SAMA: Logika Show/Hide Password ---

    /**
     * Fungsi untuk mengatur toggle show/hide password
     * @param {string} inputId - ID dari input password
     * @param {string} toggleId - ID dari elemen <span> yang berisi ikon mata
     */
    function setupPasswordToggle(inputId, toggleId) {
        const passwordInput = document.getElementById(inputId);
        const toggleElement = document.getElementById(toggleId);

        if (!passwordInput || !toggleElement) {
            console.warn(`Elemen password toggle tidak ditemukan. Cek ID: ${inputId} atau ${toggleId}`);
            return;
        }

        const icon = toggleElement.querySelector('i'); 

        toggleElement.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    // Terapkan fungsi ke kedua form
    setupPasswordToggle('login-password', 'toggle-login-password');
    setupPasswordToggle('register-password', 'toggle-register-password');
});