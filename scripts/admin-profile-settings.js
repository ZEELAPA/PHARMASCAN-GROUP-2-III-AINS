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

    // Define globally so inline onclick works
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

    // Close modal if user clicks outside the modal box
    window.onclick = function(event) {
        const modal = document.getElementById('confirmationModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // --- NFC Logic Variables & Setup ---
    const nfcModal = document.getElementById('nfcAuthModal');
    const nfcInput = document.getElementById('nfcAuthCode');
    const nfcPassword = document.getElementById('nfcAuthPassword');
    const nfcStatus = document.getElementById('nfcAuthStatus');
    const nfcError = document.getElementById('nfcAuthError');
    const confirmBtn = document.getElementById('confirmNfcBtn');
    const cancelBtn = document.getElementById('cancelNfcBtn');
    
    const accountSettingsLink = document.querySelector('a[href$="admin-account-settings.php"]');

    let nfcBuffer = '';
    let lastKeyTime = Date.now();
    let isListening = false;
    let onSuccessCallback = null;

    // --- Event Listener for Account Settings Link ---
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

    const contactInput = document.getElementById('contactNumber');
    if (contactInput) {
        contactInput.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            let value = this.value.replace(/[^0-9]/g, '');
            
            // Optional: Prevent starting with 0 or 63 if user tries to type them
            // If they type '09', remove the 0
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            // If they paste '639', remove the 63
            if (value.startsWith('63')) {
                value = value.substring(2);
            }

            this.value = value;
        });

        // Prevent pasting non-numeric content
        contactInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text');
            const numericData = pastedData.replace(/[^0-9]/g, '');
            document.execCommand('insertText', false, numericData);
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
        
        setTimeout(() => nfcInput.focus(), 100);
    }

    function closeNfcModal() {
        nfcModal.classList.remove('is-open');
        isListening = false;
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

    // --- Password Validation ---
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

    // --- Server Verification ---
    if(confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            const code = nfcInput.value;
            const pass = nfcPassword.value;

            nfcStatus.textContent = 'Verifying...';
            
            fetch('handlers/verify-current-user.php', {
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
    }

    
    // --- SECURITY LOCK LOGIC ---
    const securityPassInput = document.getElementById('securityPass');
    const unlockBtn = document.getElementById('unlockBtn');
    const cancelLockBtn = document.getElementById('cancelLockBtn');
    const securityError = document.getElementById('securityError');

    if (securityPassInput && unlockBtn) {
        // Function to handle verification
        function verifyProfileAccess() {
            const pass = securityPassInput.value;
            
            // UI Feedback
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
                    location.reload(); // Reload page to fetch data
                } else {
                    securityError.style.display = 'block';
                    securityError.textContent = data.message || 'Incorrect Password';
                    unlockBtn.textContent = 'Unlock';
                    unlockBtn.disabled = false;
                    securityPassInput.value = ''; // Clear input
                    securityPassInput.focus();
                }
            })
            .catch(err => {
                console.error(err);
                securityError.style.display = 'block';
                securityError.textContent = 'Server Error. Please try again.';
                unlockBtn.textContent = 'Unlock';
                unlockBtn.disabled = false;
            });
        }

        // Event Listeners
        unlockBtn.addEventListener('click', verifyProfileAccess);

        // Allow Enter key to submit
        securityPassInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') { verifyProfileAccess(); }
        });

        // Redirect to Dashboard on Cancel
        if(cancelLockBtn) {
            cancelLockBtn.addEventListener('click', function() {
                window.location.href = 'user-dashboard.php';
            });
        }
    }

    if(cancelLockBtn) {
        cancelLockBtn.addEventListener('click', function() {
            window.location.href = 'admin-dashboard.php';
        });
    }
});