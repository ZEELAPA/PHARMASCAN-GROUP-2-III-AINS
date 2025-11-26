document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const profilePicUpload = document.getElementById('profilePicUpload');
    const profilePreview = document.getElementById('profilePreview');

    if (profileForm) {
        profileForm.addEventListener('submit', function(event) {
            const confirmed = confirm('Are you sure you want to save these changes?');
            if (!confirmed) {
                event.preventDefault(); // Stop form submission if user cancels
            }
        });
    }

    if (profilePicUpload && profilePreview) {
        profilePicUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                }
                
                reader.readAsDataURL(file);
            }
        });
    }


    // --- NFC Logic Variables ---
    const nfcModal = document.getElementById('nfcAuthModal');
    const nfcInput = document.getElementById('nfcAuthCode');
    const nfcPassword = document.getElementById('nfcAuthPassword');
    const nfcStatus = document.getElementById('nfcAuthStatus');
    const nfcError = document.getElementById('nfcAuthError');
    const confirmBtn = document.getElementById('confirmNfcBtn');
    const cancelBtn = document.getElementById('cancelNfcBtn');
    
    // Select the Account Settings link (adjust selector if needed based on exact HTML structure)
    // Looking at your PHP, the link contains 'account-settings.php'
    const accountSettingsLink = document.querySelector('a[href$="account-settings.php"]');

    let nfcBuffer = '';
    let lastKeyTime = Date.now();
    let isListening = false;
    let onSuccessCallback = null;

    // --- Event Listener for Link ---
    if(accountSettingsLink) {
        accountSettingsLink.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.href;
            
            // Define what happens on success
            onSuccessCallback = function() {
                window.location.href = targetUrl;
            };
            
            openNfcModal();
        });
    }

    // --- Modal Functions ---
    function openNfcModal() {
        nfcModal.classList.add('is-open');
        nfcInput.value = '';
        nfcPassword.value = '';
        nfcPassword.disabled = true;
        confirmBtn.disabled = true;
        nfcStatus.textContent = 'Waiting';
        nfcStatus.className = 'nfc-status status-waiting';
        nfcError.textContent = '';
        
        // Slight delay to focus
        setTimeout(() => nfcInput.focus(), 100);
    }

    function closeNfcModal() {
        nfcModal.classList.remove('is-open');
        isListening = false;
    }

    cancelBtn.addEventListener('click', closeNfcModal);

    // --- Scanner Logic (Extracted from user-management.js) ---
    nfcInput.addEventListener('focus', () => { 
        isListening = true; 
        nfcStatus.textContent = 'Listening...';
        nfcStatus.className = 'nfc-status status-listening';
    });
    
    nfcInput.addEventListener('blur', () => { isListening = false; });

    // --- Scanner Logic ---
    nfcInput.addEventListener('focus', () => { 
        isListening = true; 
        nfcStatus.textContent = 'Listening...';
        nfcStatus.className = 'nfc-status status-listening';
    });
    
    nfcInput.addEventListener('blur', () => { isListening = false; });

    document.addEventListener('keydown', (e) => {
        if (!isListening) return;
        
        // Allow Tab to escape focus
        if (e.key === 'Tab') return;

        // Prevent manual typing from appearing visually
        e.preventDefault();

        const currentTime = Date.now();
        if (currentTime - lastKeyTime > 200) {
            nfcBuffer = ''; // Reset buffer if typing is too slow
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

    // --- Password Validation ---
    nfcPassword.addEventListener('input', () => {
        if (/^\d{4}$/.test(nfcPassword.value)) {
            confirmBtn.disabled = false;
            nfcError.textContent = '';
        } else {
            confirmBtn.disabled = true;
        }
    });

    // --- Server Verification ---
    confirmBtn.addEventListener('click', () => {
        const code = nfcInput.value; // In a real scanner, this might need to come from the buffer if input is cleared
        const pass = nfcPassword.value;

        nfcStatus.textContent = 'Verifying...';
        
        // IMPORTANT: Point this to your actual auth handler
        fetch('verify-current-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `nfcCode=${encodeURIComponent(code)}&nfcPassword=${encodeURIComponent(pass)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeNfcModal();
                if (onSuccessCallback) onSuccessCallback();
            } else {
                nfcStatus.textContent = 'Invalid Credentials';
                nfcStatus.className = 'nfc-status status-error';
                nfcError.textContent = data.message || 'Authentication failed';
            }
        })
        .catch(err => {
            console.error(err);
            nfcError.textContent = 'Server Error';
        });
    });

});