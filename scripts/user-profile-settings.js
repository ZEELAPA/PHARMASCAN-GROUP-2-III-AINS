document.addEventListener('DOMContentLoaded', function() {
    // --- Image Preview Logic ---
    const profilePicUpload = document.getElementById('profilePicUpload');
    const profilePreview = document.getElementById('profilePreview');

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

    // --- Modal Confirmation Logic ---
    let targetForm = null;

    window.openModal = function(formId) {
        targetForm = document.getElementById(formId);
        const modal = document.getElementById('confirmationModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    window.closeModal = function() {
        const modal = document.getElementById('confirmationModal');
        if (modal) {
            modal.style.display = 'none';
        }
        targetForm = null;
    }

    window.confirmSubmit = function() {
        if (targetForm) {
            targetForm.submit();
        }
    }

    window.onclick = function(event) {
        const modal = document.getElementById('confirmationModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // --- NFC Logic ---
    const nfcModal = document.getElementById('nfcAuthModal');
    const nfcInput = document.getElementById('nfcAuthCode');
    const nfcPassword = document.getElementById('nfcAuthPassword');
    const nfcStatus = document.getElementById('nfcAuthStatus');
    const nfcError = document.getElementById('nfcAuthError');
    const confirmBtn = document.getElementById('confirmNfcBtn');
    const cancelBtn = document.getElementById('cancelNfcBtn');
    
    // Select the link that points to account settings
    const accountSettingsLink = document.querySelector('a[href$="user-account-settings.php"]');

    let nfcBuffer = '';
    let lastKeyTime = Date.now();
    let isListening = false;
    let onSuccessCallback = null;

    // Intercept navigation to Account Settings
    if(accountSettingsLink) {
        accountSettingsLink.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.href;
            
            // Set callback to redirect after successful scan
            onSuccessCallback = function() {
                window.location.href = targetUrl;
            };
            
            openNfcModal();
        });
    }

    // Phone Input Formatting
    const contactInput = document.getElementById('contactNumber');
    if (contactInput) {
        contactInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.startsWith('0')) value = value.substring(1);
            if (value.startsWith('63')) value = value.substring(2);
            this.value = value;
        });
    }

    // --- NFC Modal Functions ---
    function openNfcModal() {
        nfcModal.classList.add('is-open');
        nfcInput.value = '';
        nfcPassword.value = '';
        nfcPassword.disabled = true;
        confirmBtn.disabled = true;
        nfcStatus.textContent = 'Waiting';
        nfcStatus.className = 'nfc-status status-waiting';
        nfcError.textContent = '';
        
        setTimeout(() => nfcInput.focus(), 100);
    }

    function closeNfcModal() {
        nfcModal.classList.remove('is-open');
        isListening = false;
        // This clears the global callback
        onSuccessCallback = null;
    }

    if(cancelBtn) cancelBtn.addEventListener('click', closeNfcModal);

    // --- Scanner Logic ---
    if(nfcInput) {
        nfcInput.addEventListener('focus', () => { 
            isListening = true; 
            nfcStatus.textContent = 'Listening...';
            nfcStatus.className = 'nfc-status status-listening';
        });
        nfcInput.addEventListener('blur', () => { isListening = false; });
    }

    document.addEventListener('keydown', (e) => {
        if (!isListening) return;
        if (e.key === 'Tab') return;
        e.preventDefault();

        const currentTime = Date.now();
        if (currentTime - lastKeyTime > 200) nfcBuffer = ''; 
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

    if(nfcPassword) {
        nfcPassword.addEventListener('input', () => {
            if (/^\d{4}$/.test(nfcPassword.value)) {
                confirmBtn.disabled = false;
                nfcError.textContent = '';
            } else {
                confirmBtn.disabled = true;
            }
        });
    }
    
    // --- Verify NFC with Server ---
    if(confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            const code = nfcInput.value;
            const pass = nfcPassword.value;

            nfcStatus.textContent = 'Verifying...';
            confirmBtn.disabled = true;

            fetch('handlers/verify-current-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `nfcCode=${encodeURIComponent(code)}&nfcPassword=${encodeURIComponent(pass)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // --- FIX IS HERE ---
                    // 1. Capture the callback function BEFORE closing modal wipes it
                    const actionToPerform = onSuccessCallback;

                    // 2. Close the modal (which sets onSuccessCallback = null)
                    closeNfcModal();

                    // 3. Execute the captured function
                    if (actionToPerform) actionToPerform();

                } else {
                    nfcStatus.textContent = 'Invalid Credentials';
                    nfcStatus.className = 'nfc-status status-error';
                    nfcError.textContent = data.message || 'Authentication failed';
                    confirmBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                nfcStatus.textContent = 'Server Error';
                confirmBtn.disabled = false;
            });
        });
    }
    
    // --- SECURITY LOCK LOGIC (Page Unlock) ---
    const securityPassInput = document.getElementById('securityPass');
    const unlockBtn = document.getElementById('unlockBtn');
    const cancelLockBtn = document.getElementById('cancelLockBtn');
    const securityError = document.getElementById('securityError');

    if (securityPassInput && unlockBtn) {
        function verifyProfileAccess() {
            const pass = securityPassInput.value;
            unlockBtn.textContent = 'Checking...';
            unlockBtn.disabled = true;
            securityError.style.display = 'none';

            fetch('handlers/verify-profile-access.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'password=' + encodeURIComponent(pass)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload(); 
                } else {
                    securityError.style.display = 'block';
                    securityError.textContent = data.message || 'Incorrect Password';
                    unlockBtn.textContent = 'Unlock';
                    unlockBtn.disabled = false;
                    securityPassInput.value = '';
                    securityPassInput.focus();
                }
            })
            .catch(err => {
                console.error(err);
                securityError.style.display = 'block';
                securityError.textContent = 'Server Error';
                unlockBtn.textContent = 'Unlock';
                unlockBtn.disabled = false;
            });
        }
        unlockBtn.addEventListener('click', verifyProfileAccess);
        securityPassInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') verifyProfileAccess();
        });
    }

    if(cancelLockBtn) {
        cancelLockBtn.addEventListener('click', function() {
            window.location.href = 'user-dashboard.php';
        });
    }
});