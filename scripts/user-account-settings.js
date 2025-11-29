document.addEventListener('DOMContentLoaded', function() {
    // --- Variables ---
    let currentFormId = null; // Stores 'accountForm' when button is clicked

    const confirmModal = document.getElementById('confirmationModal');
    
    // NFC Modal Elements
    const nfcModal = document.getElementById('nfcAuthModal');
    const nfcInput = document.getElementById('nfcAuthCode');
    const nfcPassword = document.getElementById('nfcAuthPassword');
    const nfcStatus = document.getElementById('nfcAuthStatus');
    const nfcError = document.getElementById('nfcAuthError');
    const confirmNfcBtn = document.getElementById('confirmNfcBtn');
    const cancelNfcBtn = document.getElementById('cancelNfcBtn');

    // Scanner Logic Variables
    let nfcBuffer = '';
    let lastKeyTime = Date.now();
    let isListening = false;

    // --- 1. Functions called by HTML onclick attributes ---
    
    window.openModal = function(formId) {
        currentFormId = formId;
        confirmModal.style.display = 'flex';
        confirmModal.classList.add('is-open');
    };

    window.closeModal = function() {
        confirmModal.style.display = 'none';
        confirmModal.classList.remove('is-open');
    };

    window.confirmSubmit = function() {
        // 1. Close the text confirmation modal
        window.closeModal();
        // 2. Open the NFC authentication modal
        openNfcModal();
    };

    // --- 2. NFC Modal Logic ---

    function openNfcModal() {
        nfcModal.classList.add('is-open');
        nfcModal.style.display = 'flex';
        
        // Reset fields
        nfcInput.value = '';
        nfcPassword.value = '';
        nfcPassword.disabled = true;
        confirmNfcBtn.disabled = true;
        nfcStatus.textContent = 'Waiting';
        nfcStatus.className = 'nfc-status status-waiting';
        nfcError.textContent = '';
        
        // Focus the scanner input
        setTimeout(() => nfcInput.focus(), 100);
    }

    function closeNfcModal() {
        nfcModal.classList.remove('is-open');
        nfcModal.style.display = 'none';
        isListening = false;
    }

    if(cancelNfcBtn) cancelNfcBtn.addEventListener('click', closeNfcModal);

    // --- 3. Scanner Detection Logic ---

    if(nfcInput) {
        nfcInput.addEventListener('focus', () => { 
            isListening = true; 
            nfcStatus.textContent = 'Listening...';
            nfcStatus.className = 'nfc-status status-listening';
        });
        
        nfcInput.addEventListener('blur', () => { isListening = false; });
    }

    // Listen for rapid keystrokes (Scanner Emulation)
    document.addEventListener('keydown', (e) => {
        if (!isListening) return;
        if (e.key === 'Tab') return; // Allow tabbing

        e.preventDefault(); 

        const currentTime = Date.now();
        if (currentTime - lastKeyTime > 200) {
            nfcBuffer = ''; 
        }
        lastKeyTime = currentTime;

        if (e.key === 'Enter') {
            if (nfcBuffer.length > 3) {
                nfcInput.value = nfcBuffer;
                nfcStatus.textContent = 'Card Scanned';
                nfcStatus.className = 'nfc-status status-success';
                
                nfcPassword.disabled = false;
                nfcPassword.focus();
            } else {
                nfcStatus.textContent = 'Scan Failed';
                nfcStatus.className = 'nfc-status status-error';
                nfcInput.value = '';
            }
            nfcBuffer = ''; 
        } else if (e.key.length === 1) {
            nfcBuffer += e.key;
        }
    });

    // --- 4. Password Validation & Verification ---

    if(nfcPassword) {
        nfcPassword.addEventListener('input', () => {
            if (/^\d{4}$/.test(nfcPassword.value)) {
                confirmNfcBtn.disabled = false;
                nfcError.textContent = '';
            } else {
                confirmNfcBtn.disabled = true;
            }
        });
    }

    if(confirmNfcBtn) {
        confirmNfcBtn.addEventListener('click', () => {
            const code = nfcInput.value;
            const pass = nfcPassword.value;

            nfcStatus.textContent = 'Verifying...';
            confirmNfcBtn.disabled = true;

            fetch('handlers/verify-current-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `nfcCode=${encodeURIComponent(code)}&nfcPassword=${encodeURIComponent(pass)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    nfcStatus.textContent = 'Verified!';
                    nfcStatus.className = 'nfc-status status-success';
                    
                    // Submit the actual form after a short delay
                    setTimeout(() => {
                        closeNfcModal(); // Close modal visually
                        const formToSubmit = document.getElementById(currentFormId);
                        if (formToSubmit) {
                            formToSubmit.submit();
                        } else {
                            console.error('Form not found: ' + currentFormId);
                        }
                    }, 500);
                } else {
                    nfcStatus.textContent = 'Invalid Credentials';
                    nfcStatus.className = 'nfc-status status-error';
                    nfcError.textContent = data.message || 'Authentication failed.';
                    confirmNfcBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                nfcStatus.textContent = 'Server Error';
                nfcStatus.className = 'nfc-status status-error';
                confirmNfcBtn.disabled = false;
            });
        });
    }

    // --- Password Toggle Logic ---
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');

    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function () {
            const passwordInput = this.previousElementSibling;
            const eyeIcon = this.querySelector('.icon-eye');
            const eyeSlashIcon = this.querySelector('.icon-eye-slash');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeSlashIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeSlashIcon.style.display = 'none';
            }
        });
    });
});