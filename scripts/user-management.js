document.addEventListener('DOMContentLoaded', function() {
    
    // --- Get Modal Elements ---
    const modal = document.getElementById('editEmployeeModal');
    // Assume you have a button to open the modal, e.g., with class "edit-employee-btn"
    const openModalBtn = document.querySelector('.edit-employee-btn'); 
    const closeModalBtn = modal.querySelector('.close-btn');
    const profileItems = document.querySelectorAll('.profile-item');
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const dateEmployedInput = document.getElementById('dateEmployed');
    const vacationLeaveInput = document.getElementById('vacationLeave');
    const sickLeaveInput = document.getElementById('sickLeave');

    // --- Get Tab Elements ---
    const tabLinks = modal.querySelectorAll('.tab-link');
    const tabContents = modal.querySelectorAll('.tab-content');

    const editEmployeeIDInput = document.getElementById('editEmployeeID');
    const displayEmployeeIDInput = document.getElementById('displayEmployeeID');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const genderSelect = document.getElementById('gender');
    const ageInput = document.getElementById('age');
    const contactNumberInput = document.getElementById('contactNumber');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const departmentSelect = document.getElementById('department');
    const positionInput = document.getElementById('position');
    const employmentStatusSelect = document.getElementById('employmentStatus');
    const roleSelect = document.getElementById('role');
    const nfcCodeInput = document.getElementById('nfcCode');
    const nfcPasswordInput = document.getElementById('nfcPassword'); 
    const nfcPasswordError = document.getElementById('nfcPasswordError');

    const nfcStatusEl = document.getElementById('nfcScanStatus');

    // Get all action buttons to toggle visibility
    
    const modalTitle = document.getElementById('modalTitle');
    const employeeForm = document.getElementById('employeeForm');

    const adminAuthModal = document.getElementById('adminAuthModal');
    const adminNfcInput = document.getElementById('adminNfcCode');
    const adminNfcStatus = document.getElementById('adminNfcStatus');
    const confirmAuthBtn = document.getElementById('confirmAuthBtn');
    const cancelAuthBtn = document.getElementById('cancelAuthBtn');
    const realSubmitBtn = document.getElementById('realSubmitBtn');

    const adminNfcPasswordInput = document.getElementById('adminNfcPassword');
    const adminNfcPasswordError = document.getElementById('adminNfcPasswordError');

    const saveButton = modal.querySelector('.btn-save');
    const archiveButton = modal.querySelector('.btn-archive');
    const createButton = modal.querySelector('.btn-create');

    const togglePasswordButtons = document.querySelectorAll('.toggle-password');

    const SCAN_SPEED_THRESHOLD = 250; // Max milliseconds for a valid scan
    let isNfcListening = false;
    let nfcBuffer = '';
    let firstCharTimestamp = null;
    let bufferClearTimer = null;
    // This flag is crucial. It must be true for the form to submit.
    let isNfcCodeValid = false; 
    let intendedFormAction = null;

    const searchInput = document.getElementById('searchInput');
    const profileMembersContainer = document.querySelector('.profile-members'); 

    const profilePicUpload = document.getElementById('profilePicUpload');
    const modalProfileImage = document.getElementById('modalProfileImage');
    const defaultImage = 'images/default-user.png';

    
    function setupModalForAdd() {
        modalTitle.textContent = 'Add New Employee';
        saveButton.style.display = 'none';
        archiveButton.style.display = 'none';
        createButton.style.display = 'inline-block';
        modal.classList.add('is-open');
        roleSelect.value = 'User';

        isNfcCodeValid = false;
        nfcStatusEl.textContent = 'Waiting';
        nfcStatusEl.className = 'status-waiting';        
        modalProfileImage.src = defaultImage;
        profilePicUpload.value = '';
        dateEmployedInput.value = '';
        vacationLeaveInput.value = '0';
        sickLeaveInput.value = '0';
    }

    function setupModalForEdit() {
        modalTitle.textContent = 'Edit Employee';
        saveButton.style.display = 'inline-block';
        archiveButton.style.display = 'inline-block';
        createButton.style.display = 'none';
    }

    addEmployeeBtn.addEventListener('click', () => {
        employeeForm.reset(); // Reset the form ONLY on initial click
        setupModalForAdd();
    });


    profileItems.forEach(item => {
        item.addEventListener('click', () => {

            setupModalForEdit();

            // 1. Extract data from the clicked item's data-* attributes
            const accountData = item.dataset;

            // 2. Populate the form fields inside the modal
            editEmployeeIDInput.value = accountData.accountid;
            displayEmployeeIDInput.value = accountData.accountid.padStart(4, '0');
            firstNameInput.value = accountData.firstname;
            lastNameInput.value = accountData.lastname;
            genderSelect.value = accountData.gender;
            ageInput.value = accountData.age;
            usernameInput.value = accountData.username;
            positionInput.value = accountData.position;
            employmentStatusSelect.value = accountData.empstatus;
            dateEmployedInput.value = accountData.dateemployed ? accountData.dateemployed : '';
            vacationLeaveInput.value = accountData.vacationleave;
            sickLeaveInput.value = accountData.sickleave;
            roleSelect.value = accountData.role; 
            nfcCodeInput.value = accountData.iccode;
            nfcPasswordInput.value = accountData.icpassword; 

            contactNumberInput.value = accountData.contactnumber;
            emailInput.value = accountData.email;
            departmentSelect.value = accountData.departmentid;

            isNfcCodeValid = true; 
            nfcStatusEl.textContent = 'Waiting';
            nfcStatusEl.className = 'status-waiting';

            // --- IMAGE POPULATION LOGIC ---
            if (accountData.profilepic) {
                modalProfileImage.src = 'images/' + accountData.profilepic;
            } else {
                modalProfileImage.src = defaultImage;
            }
            // Clear the file input so previous selections don't carry over
            profilePicUpload.value = ''; 

            // 3. Show the modal
            modal.classList.add('is-open');
        });
    });

    profilePicUpload.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                modalProfileImage.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Close modal if user clicks outside of the content area
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            modal.classList.remove('is-open'); 
        }
        if (event.target == adminAuthModal) {
            adminAuthModal.classList.remove('is-open');
        }
    });

    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Get the input field, which is the button's sibling inside the wrapper
            const passwordInput = this.previousElementSibling;

            // Get the icons inside this specific button
            const eyeIcon = this.querySelector('.icon-eye');
            const eyeSlashIcon = this.querySelector('.icon-eye-slash');

            // Check the current type of the input
            if (passwordInput.type === 'password') {
                // If it's a password, change to text to show it
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeSlashIcon.style.display = 'block';
            } else {
                // If it's text, change back to password to hide it
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeSlashIcon.style.display = 'none';
            }
        });
    });

    if (window.location.hash === '#add-employee-error') {
        setupModalForAdd();
    }

    // --- Tab Switching Logic ---
    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const tabId = link.getAttribute('data-tab');

            // Update active state for tab links
            tabLinks.forEach(item => item.classList.remove('active'));
            link.classList.add('active');

            // Update active state for tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === tabId) {
                    content.classList.add('active');
                }
            });
        });
    });


    nfcCodeInput.addEventListener('focus', () => {
        isNfcListening = true;
        nfcStatusEl.textContent = 'Listening...';
        nfcStatusEl.className = 'status-listening';
    });

    nfcCodeInput.addEventListener('blur', () => {
        isNfcListening = false;
        // Don't reset status if the code is already valid
        if (!isNfcCodeValid) {
            nfcStatusEl.textContent = 'Waiting';
            nfcStatusEl.className = 'status-waiting';
        }
    });

    // When the user types manually, we immediately invalidate the code.
    // They must finish with a valid, fast "scan" that ends with Enter.
    nfcCodeInput.addEventListener('input', () => {
        if (isNfcListening) {
            isNfcCodeValid = false; // Invalidate on any manual change
            nfcStatusEl.textContent = 'Typing...';
            nfcStatusEl.className = 'status-listening';
        }
    });

    // The main listener that captures keystrokes
    document.addEventListener('keydown', (event) => {
        if (!isNfcListening) return;
        
        // Don't prevent default if user is tabbing away
        if (event.key === 'Tab') {
            return;
        }
        event.preventDefault(); // Prevent other key actions

        clearTimeout(bufferClearTimer);
        bufferClearTimer = setTimeout(() => { nfcBuffer = ''; firstCharTimestamp = null; }, 500);

        if (event.key.length === 1) { // It's a character
            if (nfcBuffer.length === 0) {
                firstCharTimestamp = Date.now();
            }
            nfcBuffer += event.key;
        } else if (event.key === 'Enter') { // Scan finished
            if (nfcBuffer.length > 0 && firstCharTimestamp) {
                const scanDuration = Date.now() - firstCharTimestamp;
                
                if (scanDuration < SCAN_SPEED_THRESHOLD) {
                    // VALID SCAN DETECTED
                    isNfcCodeValid = true;
                    nfcCodeInput.value = nfcBuffer;
                    nfcStatusEl.textContent = 'Success!';
                    nfcStatusEl.className = 'status-success';
                    nfcCodeInput.blur(); // Unfocus
                } else {
                    // FAILED SCAN (TOO SLOW)
                    isNfcCodeValid = false;
                    nfcCodeInput.value = '';
                    nfcStatusEl.textContent = 'Too Slow!';
                    nfcStatusEl.className = 'status-error';
                }
            }
            nfcBuffer = '';
            firstCharTimestamp = null;
        }
    });

    nfcPasswordInput.addEventListener('input', () => {
        const value = nfcPasswordInput.value;
        
        // The regex /^\d{4}$/ checks for exactly 4 digits from start to end.
        if (value && !/^\d{4}$/.test(value)) {
            nfcPasswordError.textContent = 'Password must be exactly 4 numbers.';
            nfcPasswordInput.classList.add('is-invalid');
        } else {
            // If it's valid or empty, clear the error
            nfcPasswordError.textContent = '';
            nfcPasswordInput.classList.remove('is-invalid');
        }
    });

    // Intercept form submission for final validation
    function requestAdminAuth(action) {
        intendedFormAction = action; // Store what we want to do
        adminNfcInput.value = ''; // Clear previous scans
        adminNfcStatus.textContent = 'Waiting';
        adminNfcStatus.className = 'status-waiting';

        adminNfcPasswordInput.value = '';
        adminNfcPasswordInput.disabled = true;
        adminNfcPasswordError.textContent = '';
        adminNfcPasswordInput.classList.remove('is-invalid');

        confirmAuthBtn.disabled = true; // Disable confirm until scan is successful
        adminAuthModal.classList.add('is-open');
        setTimeout(() => adminNfcInput.focus(), 50); // Focus after modal opens
    }

    // Listen for clicks on the main action buttons
    saveButton.addEventListener('click', (e) => {
        e.preventDefault();
        requestAdminAuth(e.target.value); // 'save_changes'
    });
    createButton.addEventListener('click', (e) => {
        e.preventDefault();
        requestAdminAuth(e.target.value); // 'add_employee'
    });
    archiveButton.addEventListener('click', (e) => {
        e.preventDefault();
        requestAdminAuth(e.target.value); // 'archive_employee'
    });

    // --- Admin NFC Scanner Logic ---
    let isAdminNfcListening = false;
    let adminNfcBuffer = '';
    let adminFirstCharTimestamp = null;

    adminNfcInput.addEventListener('focus', () => { 
        isAdminNfcListening = true; 
        adminNfcStatus.textContent = 'Listening...';
        adminNfcStatus.className = 'status-listening';
    });
    adminNfcInput.addEventListener('blur', () => { isAdminNfcListening = false; });

    document.addEventListener('keydown', (event) => {
        if (!isAdminNfcListening) return;
        event.preventDefault();

        if (event.key.length === 1) {
            if (adminNfcBuffer.length === 0) adminFirstCharTimestamp = Date.now();
            adminNfcBuffer += event.key;
        } else if (event.key === 'Enter') {
            const scanDuration = Date.now() - adminFirstCharTimestamp;
            if (scanDuration < SCAN_SPEED_THRESHOLD && adminNfcBuffer.length > 0) {
                adminNfcInput.value = adminNfcBuffer;
                adminNfcStatus.textContent = 'Card Scanned!';
                adminNfcStatus.className = 'status-success';

                adminNfcPasswordInput.disabled = false;
                adminNfcPasswordInput.focus();
            } else {
                adminNfcStatus.textContent = 'Scan Failed!';
                adminNfcStatus.className = 'status-error';
            }
            adminNfcBuffer = '';
        }
    });

    adminNfcPasswordInput.addEventListener('input', () => {
        const passValue = adminNfcPasswordInput.value;
        // Check if the input is exactly 4 digits long
        if (/^\d{4}$/.test(passValue)) {
            adminNfcPasswordError.textContent = '';
            adminNfcPasswordInput.classList.remove('is-invalid');
            confirmAuthBtn.disabled = false; // Enable the confirm button
        } else {
            adminNfcPasswordError.textContent = 'Password must be 4 numbers.';
            adminNfcPasswordInput.classList.add('is-invalid');
            confirmAuthBtn.disabled = true;
        }
    });

    // --- Admin Confirmation Logic ---
    confirmAuthBtn.addEventListener('click', () => {
        const adminCode = adminNfcInput.value;
        const adminPassword = adminNfcPasswordInput.value; 

        if (!adminCode || !/^\d{4}$/.test(adminPassword)) {
            alert("Invalid input. Please scan a card and enter a valid 4-digit password.");
            return;
        }


        adminNfcStatus.textContent = 'Verifying...';
        adminNfcStatus.className = 'status-listening';

        fetch('handlers/admin-auth-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `nfcCode=${encodeURIComponent(adminCode)}&nfcPassword=${encodeURIComponent(adminPassword)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                adminAuthModal.classList.remove('is-open');
                
                // Set the value of the action using the hidden input
                // Find the original hidden input and set its value
                const hiddenActionInput = document.querySelector('#employeeForm input[name="action"]');
                if(hiddenActionInput) {
                    hiddenActionInput.value = intendedFormAction;
                } else {
                    // Or if you're using buttons with name/value, we need to add a hidden input
                    let actionInput = employeeForm.querySelector('input[name="action"]');
                    if (!actionInput) {
                        actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        employeeForm.appendChild(actionInput);
                    }
                    actionInput.value = intendedFormAction;
                }
                
                // Trigger the hidden real submit button
                realSubmitBtn.click();
            } else {
                // FAILURE! Show the error message.
                adminNfcStatus.textContent = data.message || 'Authorization Failed';
                adminNfcStatus.className = 'status-error';
                confirmAuthBtn.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            adminNfcStatus.textContent = 'Network Error';
            adminNfcStatus.className = 'status-error';
        });
    });

    // --- Modal Closing Logic ---
    cancelAuthBtn.addEventListener('click', () => {
        adminAuthModal.classList.remove('is-open');
    });


    let debounceTimer;

    // --- 1. Event Listener for user typing ---
    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.trim().toLowerCase();

        // Filter the visible list in real-time
        filterEmployeeList(searchTerm);
    });

    // --- 4. Function to filter the employee list on the page ---
    function filterEmployeeList(searchTerm) {
        const allProfiles = profileMembersContainer.querySelectorAll('.profile-item');
        let foundMatch = false;

        allProfiles.forEach(profile => {
            // Use the 'data-fullname' attribute we already have
            const fullName = profile.dataset.fullname.toLowerCase();
            
            if (fullName.includes(searchTerm)) {
                profile.style.display = 'flex'; // Show matching profiles
                foundMatch = true;
            } else {
                profile.style.display = 'none'; // Hide non-matching profiles
            }
        });

        // Optional: Show a "No results" message
        let noResultsMsg = profileMembersContainer.querySelector('.no-results');
        if (!foundMatch && !noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results';
            noResultsMsg.textContent = 'No employees found matching your search.';
            profileMembersContainer.appendChild(noResultsMsg);
        } else if (foundMatch && noResultsMsg) {
            noResultsMsg.remove();
        }
    }

    // --- Toast Notification Logic ---
    const toastNotifications = document.querySelectorAll('.status-toast');
    
    toastNotifications.forEach(toast => {
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('hide-toast');
            
            // Remove from DOM after animation finishes
            toast.addEventListener('transitionend', () => {
                toast.remove();
            });
        }, 5000);

        // Allow click to dismiss immediately
        toast.addEventListener('click', () => {
            toast.classList.add('hide-toast');
            setTimeout(() => toast.remove(), 500);
        });
    });
});