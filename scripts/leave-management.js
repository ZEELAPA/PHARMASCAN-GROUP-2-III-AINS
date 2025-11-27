document.addEventListener('DOMContentLoaded', function() {
    // --- 1. ELEMENT SELECTION ---
    const modal = document.getElementById('applicationModal');
    if (!modal) return;

    const newLeaveButton = document.querySelector('.add-button-container button');
    const tableBody = document.querySelector('.leave-list-view-container tbody');
    const closeBtn = modal.querySelector('.close-btn');
    const modalTitle = modal.querySelector('#modalTitle');

    // Forms
    const applicationForm = document.getElementById('leaveApplicationForm');
    const actionForm = document.getElementById('leaveActionForm');

    // Form Fields
    const accountIDField = document.getElementById('accountID');
    const employeeNameDisplay = document.getElementById('employeeNameDisplay');
    const leaveTypeField = document.getElementById('leaveType');
    const startDateField = document.getElementById('startDate');
    const endDateField = document.getElementById('endDate');
    const statusField = document.getElementById('leaveStatus');
    const reasonField = document.getElementById('leaveReason');
    const submitButton = document.getElementById('submitButton');
    const closeModalButton = document.getElementById('closeModalButton');
    const archiveModalButton = document.getElementById('archiveModalButton');
    const modalArchiveForm = document.getElementById('modalArchiveForm');
    const modalArchiveInput = document.getElementById('modalArchiveLeaveID');

    const leaveBalanceField = document.getElementById('leaveBalance');
    const leaveBalanceGroup = document.getElementById('leaveBalanceGroup');

    const confirmModal = document.getElementById('confirmationModal');
    const btnConfirmArchive = document.getElementById('btnConfirmArchive');
    const btnCancelArchive = document.getElementById('btnCancelArchive');

    const allFormControls = [accountIDField, leaveTypeField, startDateField, endDateField, reasonField];

    // --- 2. MODAL CONTROL ---
    const openModal = () => modal.classList.add('active');
    const closeModal = () => modal.classList.remove('active');

    const updateLeaveBalanceDisplay = () => {
        const selectedOption = accountIDField.options[accountIDField.selectedIndex];
        const selectedLeaveType = leaveTypeField.value;

        if (!selectedOption || !selectedOption.value) {
            leaveBalanceField.value = ''; leaveBalanceField.placeholder = 'N/A'; return;
        }
        if (selectedLeaveType === 'Sick Leave') {
            leaveBalanceField.value = `${parseInt(selectedOption.getAttribute('data-sick-balance'))} Sick Leaves`;
        } else if (selectedLeaveType === 'Vacation Leave') {
            leaveBalanceField.value = `${parseInt(selectedOption.getAttribute('data-vacation-balance'))} Vacation Leaves`;
        } else {
            leaveBalanceField.value = ''; leaveBalanceField.placeholder = 'Select leave type';
        }
    };

    // --- 3. MODAL STATES ---
    const prepareNewApplicationModal = () => {
        modalTitle.textContent = 'New Leave Application';
        applicationForm.reset();
        actionForm.style.display = 'none';
        submitButton.style.display = 'block';
        closeModalButton.style.display = 'none';
        if(archiveModalButton) archiveModalButton.style.display = 'none'; 
        accountIDField.style.display = 'block';
        employeeNameDisplay.style.display = 'none';
        leaveBalanceGroup.style.display = 'block';
        leaveBalanceField.value = '';
        leaveBalanceField.placeholder = 'N/A';
        allFormControls.forEach(control => control.disabled = false);
        statusField.value = 'Pending';
        statusField.className = 'status-input-pending';
        const today = new Date().toISOString().split('T')[0];
        startDateField.min = today;
        endDateField.min = today;
        openModal();
    };

    const prepareViewModal = (leaveData) => {
        modalTitle.textContent = 'Leave Application Details';
        applicationForm.reset();
        submitButton.style.display = 'none';
        accountIDField.style.display = 'none';
        employeeNameDisplay.style.display = 'block';

        employeeNameDisplay.value = leaveData.employeeName;
        leaveTypeField.value = leaveData.leaveType;
        startDateField.value = leaveData.startDate;
        endDateField.value = leaveData.endDate;
        statusField.value = leaveData.status;
        reasonField.value = leaveData.remarks || 'No remarks provided.';

        statusField.className = ''; // Clear existing classes
        if (leaveData.status === 'Approved') statusField.classList.add('status-input-approved');
        else if (leaveData.status === 'Declined' || leaveData.status === 'Rejected') statusField.classList.add('status-input-rejected');
        else statusField.classList.add('status-input-pending');

        allFormControls.forEach(control => control.disabled = true);
        startDateField.removeAttribute('min');
        endDateField.removeAttribute('min');

        const isPending = leaveData.status === 'Pending';
        actionForm.style.display = isPending ? 'flex' : 'none';
        closeModalButton.style.display = isPending ? 'none' : 'block';

        if (isPending) {
            document.getElementById('actionLeaveID').value = leaveData.leaveId;
            document.getElementById('actionAccountID').value = leaveData.accountId;
        }

        if(archiveModalButton) {
            archiveModalButton.style.display = 'block';
            modalArchiveInput.value = leaveData.leaveId;
            
            archiveModalButton.onclick = (e) => {
                e.preventDefault(); 
                confirmModal.style.display = 'flex'; // Show the custom modal
            };
        }
        openModal();
    };

    // --- 4. EVENT LISTENERS ---
    if (newLeaveButton) newLeaveButton.addEventListener('click', prepareNewApplicationModal);
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-expand')) {
                const row = e.target.closest('.leave-row');
                if (row) prepareViewModal(row.dataset);
            }
        });
    }

    startDateField.addEventListener('change', () => {
        if (startDateField.value) {
            endDateField.min = startDateField.value;
            if (endDateField.value && endDateField.value < startDateField.value) endDateField.value = '';
        }
    });

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (closeModalButton) closeModalButton.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    accountIDField.addEventListener('change', updateLeaveBalanceDisplay);
    leaveTypeField.addEventListener('change', updateLeaveBalanceDisplay);

    // --- 5. URL FEEDBACK HANDLING ---
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status) {
        const message = urlParams.get('message');
        if (status === 'success') alert('Operation was successful!');
        else if (status === 'error') alert(`Error: ${message || 'An unknown error occurred.'}`);
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // --- 6. CUSTOM CALENDAR LOGIC ---
    const calendarModal = document.getElementById('calendarModal');
    const calendarTitle = document.getElementById('calendar-title');
    const calendarGrid = document.getElementById('calendar-grid');
    const prevMonthBtn = document.getElementById('prev-month-btn');
    const nextMonthBtn = document.getElementById('next-month-btn');
    
    let activeDateField = null;
    let currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth();

    const openCalendarModal = () => {
        if (!calendarModal) return;
        currentDate = new Date(); // Reset to today each time it's opened
        currentYear = currentDate.getFullYear();
        currentMonth = currentDate.getMonth();
        generateCalendar(currentYear, currentMonth);
        calendarModal.classList.add('active');
    };

    const closeCalendarModal = () => calendarModal?.classList.remove('active');

    async function fetchEvents(year, month) {
        try {
            const response = await fetch(`handlers/get-calendar-events.php'?year=${year}&month=${month + 1}`);
            if (!response.ok) throw new Error('Network response failed');
            return await response.json();
        } catch (error) {
            console.error('Failed to fetch calendar events:', error);
            return {};
        }
    }

    async function generateCalendar(year, month) {
        if (!calendarGrid || !calendarTitle) return;
        const eventsData = await fetchEvents(year, month);
        const firstDayOfMonth = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        calendarTitle.textContent = `${monthNames[month]} ${year}`;
        calendarGrid.innerHTML = '';
        
        let startingDay = firstDayOfMonth.getDay() === 0 ? 6 : firstDayOfMonth.getDay() - 1; // Mon=0, Sun=6
        
        for (let i = 0; i < 42; i++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day');

            const day = i - startingDay + 1;
            if (day > 0 && day <= daysInMonth) {
                const dayString = String(day).padStart(2, '0');
                dayElement.textContent = dayString;
                const fullDateString = `${year}-${String(month + 1).padStart(2, '0')}-${dayString}`;
                dayElement.dataset.date = fullDateString;

                if (eventsData[fullDateString]?.classes) {
                    eventsData[fullDateString].classes.forEach(cls => dayElement.classList.add(cls));
                } else {
                    dayElement.classList.add('available_leave');
                }

                const isUnavailable = dayElement.classList.contains('unavailable_leave') || dayElement.classList.contains('occupied');
                if (!isUnavailable) {
                    dayElement.addEventListener('click', () => {
                        if (activeDateField) {
                            activeDateField.value = fullDateString;
                            activeDateField.dispatchEvent(new Event('change'));
                        }
                        closeCalendarModal();
                    });
                } else {
                    dayElement.style.cursor = 'not-allowed';
                }
            } else {
                dayElement.classList.add('other-month');
            }
            calendarGrid.appendChild(dayElement);
        }
    }

    [startDateField, endDateField].forEach(field => {
        field?.addEventListener('click', (e) => {
            e.preventDefault();
            activeDateField = e.target;
            openCalendarModal();
        });
        field?.addEventListener('keydown', (e) => e.preventDefault());
    });

    prevMonthBtn?.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        generateCalendar(currentYear, currentMonth);
    });

    nextMonthBtn?.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        generateCalendar(currentYear, currentMonth);
    });

    calendarModal?.addEventListener('click', (e) => { if (e.target === calendarModal) closeCalendarModal(); });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (modal.classList.contains('active')) closeModal();
            if (calendarModal?.classList.contains('active')) closeCalendarModal();
        }
    });

    
    // Confirmation Modal

    if (confirmModal) {
        // Cancel: Hide the modal
        btnCancelArchive.addEventListener('click', (e) => {
            e.preventDefault();
            confirmModal.style.display = 'none';
        });

        // Confirm: Submit the form
        btnConfirmArchive.addEventListener('click', (e) => {
            e.preventDefault();
            if (modalArchiveForm) {
                modalArchiveForm.submit();
            }
        });

        // Close if clicking outside the white box
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                confirmModal.style.display = 'none';
            }
        });
    }
});