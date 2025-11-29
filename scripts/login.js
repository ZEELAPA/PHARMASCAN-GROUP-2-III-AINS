document.addEventListener('DOMContentLoaded', function() {
    // --- Get All Necessary Elements ---
    const loginForm = document.querySelector('form.login');
    
    // Views
    const credentialView = document.getElementById('credential-login-view');
    const nfcView = document.getElementById('nfc-login-view');

    // Buttons
    const switchToNfcBtn = document.getElementById('switchToNfcBtn');
    const switchToCredentialBtn = document.getElementById('switchToCredentialBtn');
    
    // Inputs
    const usernameInput = document.querySelector('input[name="username"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const nfcCodeInput = document.getElementById('nfcCode');
    const nfcPasswordInput = document.getElementById('nfcPassword'); // Added back

    // NFC Status
    const nfcStatusEl = document.getElementById('nfcScanStatus');

    // --- Initial State Setup ---
    // On page load, all NFC inputs are disabled.
    nfcCodeInput.disabled = true;
    nfcPasswordInput.disabled = true; // Added back


    // --- View Switching Logic ---
    if (switchToNfcBtn) {
        switchToNfcBtn.addEventListener('click', () => {
            credentialView.style.display = 'none';
            nfcView.style.display = 'block';

            // Disable credential inputs, enable NFC inputs
            usernameInput.disabled = true;
            passwordInput.disabled = true;
            nfcCodeInput.disabled = false;
            nfcPasswordInput.disabled = false; // Added back

            // Clear any previous values
            nfcCodeInput.value = ''; 
            nfcPasswordInput.value = ''; // Added back
            
            setTimeout(() => nfcCodeInput.focus(), 50);
        });
    }

    if (switchToCredentialBtn) {
        switchToCredentialBtn.addEventListener('click', () => {
            nfcView.style.display = 'none';
            credentialView.style.display = 'block';

            // Disable NFC inputs, enable credential inputs
            nfcCodeInput.disabled = true;
            nfcPasswordInput.disabled = true; // Added back
            usernameInput.disabled = false;
            passwordInput.disabled = false;
        });
    }

    // --- NFC Scan Simulation Logic ---
    const SCAN_SPEED_THRESHOLD = 300; 
    let isNfcListening = false;
    let nfcBuffer = '';
    let firstCharTimestamp = null;
    let bufferClearTimer = null;

    nfcCodeInput.addEventListener('focus', () => {
        isNfcListening = true;
        nfcStatusEl.textContent = 'Listening...';
        nfcStatusEl.className = 'status-listening';
    });

    nfcCodeInput.addEventListener('blur', () => {
        isNfcListening = false;
        nfcStatusEl.textContent = 'Waiting';
        nfcStatusEl.className = 'status-waiting';
    });

    document.addEventListener('keydown', (event) => {
        if (!isNfcListening) return;
        if (event.key === 'Tab') return;
        event.preventDefault(); 

        clearTimeout(bufferClearTimer);
        bufferClearTimer = setTimeout(() => { nfcBuffer = ''; firstCharTimestamp = null; }, 500);

        if (event.key.length === 1) { 
            if (nfcBuffer.length === 0) {
                firstCharTimestamp = Date.now();
            }
            nfcBuffer += event.key;
        } else if (event.key === 'Enter') { 
            if (nfcBuffer.length > 0 && firstCharTimestamp) {
                const scanDuration = Date.now() - firstCharTimestamp;

                if (scanDuration < SCAN_SPEED_THRESHOLD) {
                    nfcCodeInput.value = nfcBuffer;
                    nfcStatusEl.textContent = 'Success!';
                    nfcStatusEl.className = 'status-success';
                    
                    // --- KEY CHANGE ---
                    // On successful scan, move focus to the password field.
                    nfcPasswordInput.focus();

                } else {
                    nfcCodeInput.value = '';
                    nfcStatusEl.textContent = 'Too Slow!';
                    nfcStatusEl.className = 'status-error';
                }
            }
            nfcBuffer = '';
            firstCharTimestamp = null;
        }
    });
    
    // --- Password Toggle Logic ---
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');

    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Get the input field (sibling of the button)
            const passwordInput = this.previousElementSibling;

            // Get the icons
            const eyeIcon = this.querySelector('.icon-eye');
            const eyeSlashIcon = this.querySelector('.icon-eye-slash');

            // Toggle type
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