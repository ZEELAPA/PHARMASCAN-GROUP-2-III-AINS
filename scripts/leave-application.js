document.addEventListener('DOMContentLoaded', function() {
    // --- 1. ELEMENT SELECTION ---
    const leaveModal = document.getElementById('newLeaveApplicationModal');
    if (!leaveModal) return;

    const newLeaveButton = document.querySelector('.add-button-container button');
    const tableBody = document.querySelector('.leave-list-view-container tbody');
    
    // Application Modal elements
    const closeBtn = leaveModal.querySelector('.close-btn');
    const modalTitle = leaveModal.querySelector('#modalTitle');
    const leaveForm = document.getElementById('leaveApplicationForm');
    const submitButton = leaveModal.querySelector('#submitButton');
    const leaveIDField = document.getElementById('leaveID');
    const cancelButton = document.getElementById('cancelButton'); 
    const leaveTypeField = document.getElementById('leaveType');
    const startDateField = document.getElementById('startDate');
    const endDateField = document.getElementById('endDate');
    const statusField = document.getElementById('leaveStatus');
    const reasonField = document.getElementById('leaveReason');
    const allFormControls = [leaveTypeField, startDateField, endDateField, reasonField];

    // --- NEW: CONFIRMATION MODAL SELECTORS ---
    const confirmationModal = document.getElementById('confirmationModal');
    const modalConfirmBtn = confirmationModal ? confirmationModal.querySelector('.btn-confirm') : null;
    const modalCancelBtn = confirmationModal ? confirmationModal.querySelector('.btn-cancel') : null;
    const modalTitleText = confirmationModal ? confirmationModal.querySelector('h3') : null;
    const modalMessage = confirmationModal ? confirmationModal.querySelector('p') : null;
    
    let pendingModalAction = null;

    // --- NEW: MODAL FUNCTIONS ---
    function openConfirmModal(title, message, actionCallback) {
        if (!confirmationModal) {
            // Fallback if modal HTML is missing
            if(confirm(message)) actionCallback();
            return;
        }
        modalTitleText.textContent = title;
        modalMessage.textContent = message;
        pendingModalAction = actionCallback;
        confirmationModal.style.display = 'flex';
    }

    function closeConfirmModal() {
        if (confirmationModal) {
            confirmationModal.style.display = 'none';
            pendingModalAction = null;
        }
    }

    // --- NEW: CONFIRMATION MODAL EVENT LISTENERS ---
    if (confirmationModal) {
        modalCancelBtn.addEventListener('click', closeConfirmModal);
        
        modalConfirmBtn.addEventListener('click', () => {
            if (pendingModalAction) {
                pendingModalAction();
            }
            closeConfirmModal();
        });

        confirmationModal.addEventListener('click', (e) => {
            if (e.target === confirmationModal) closeConfirmModal();
        });
    }


    // --- 2. MODAL CONTROL (Application Modal) ---
    const openAppModal = () => leaveModal.classList.add('active');
    const closeAppModal = () => leaveModal.classList.remove('active');
    
    // --- 3. MODAL STATE FUNCTIONS (Application Modal) ---
    const prepareViewModal = (leaveData) => {
        modalTitle.textContent = 'Leave Details';
        leaveIDField.value = leaveData.leaveId;
        leaveTypeField.value = leaveData.leaveType;
        startDateField.value = leaveData.startDate;
        endDateField.value = leaveData.endDate;
        statusField.value = leaveData.status;
        reasonField.value = leaveData.remarks;
        statusField.className = '';
        const statusLower = leaveData.status.toLowerCase();
        if (statusLower === 'approved') statusField.classList.add('status-input-approved');
        else if (['declined', 'rejected', 'cancelled'].includes(statusLower)) statusField.classList.add('status-input-declined'); 
        else statusField.classList.add('status-input-pending');
        submitButton.style.display = 'none';
        cancelButton.style.display = (statusLower === 'pending') ? 'inline-block' : 'none';
        startDateField.removeAttribute('min');
        endDateField.removeAttribute('min');
        allFormControls.forEach(control => control.disabled = true);
        openAppModal();
    };

    const prepareNewApplicationModal = () => {
        modalTitle.textContent = 'New Application';
        leaveForm.reset();
        leaveIDField.value = '';
        cancelButton.style.display = 'none'; 
        statusField.value = 'Pending';
        statusField.className = 'status-input-pending';
        const today = new Date().toISOString().split('T')[0];
        startDateField.min = today;
        endDateField.min = today;
        allFormControls.forEach(control => control.disabled = false);
        submitButton.style.display = 'block';
        openAppModal();
    };

    // --- 4. EVENT LISTENERS (Application Modal & Page) ---
    if (newLeaveButton) newLeaveButton.addEventListener('click', prepareNewApplicationModal);
    
    if (tableBody) {
        tableBody.addEventListener('click', (event) => {
            if (event.target.classList.contains('btn-expand')) {
                const row = event.target.closest('.leave-row');
                if (row) prepareViewModal(row.dataset);
            }
        });
    }

    // *** UPDATED CANCEL BUTTON LOGIC TO USE MODAL ***
    if (cancelButton) {
        cancelButton.addEventListener('click', () => {
            const leaveId = leaveIDField.value;
            const accountId = document.getElementById('accountID').value;
            
            openConfirmModal(
                'Cancel Application',
                'Are you sure you want to cancel this leave application?',
                function() {
                    window.location.href = `handlers/cancel-leave.php?leaveID=${leaveId}&accountID=${accountId}`;
                }
            );
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', closeAppModal);
    leaveModal.addEventListener('click', (event) => { if (event.target === leaveModal) closeAppModal(); });
    
    startDateField.addEventListener('change', () => {
        if (startDateField.value) {
            endDateField.min = startDateField.value;
            if (endDateField.value && endDateField.value < startDateField.value) endDateField.value = '';
        }
    });

    
    // --- 6. DASHBOARD STATIC CALENDAR ---
    function generateDashboardCalendar() {
        const calendarTitle = document.getElementById('calendar-title');
        const calendarGrid = document.getElementById('calendar-grid');
        if (!calendarTitle || !calendarGrid) return;

        const now = new Date();
        const currentMonth = now.getMonth();
        const currentYear = now.getFullYear();
        const currentDay = now.getDate();

        const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();
        const startingDay = firstDayOfMonth.getDay() === 0 ? 7 : firstDayOfMonth.getDay();

        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        calendarTitle.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        
        calendarGrid.innerHTML = '';
        const totalDaysToShow = (startingDay - 1) + daysInMonth;
        const weeks = Math.ceil(totalDaysToShow / 7);
        let day = 1;
        let nextMonthDay = 1;

        for (let i = 0; i < weeks * 7; i++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day');

            if (i < startingDay - 1) {
                const prevDate = daysInPrevMonth - (startingDay - 2 - i);
                dayElement.textContent = prevDate;
                dayElement.classList.add('other-month');
            } else if (day <= daysInMonth) {
                dayElement.textContent = day;
                if (day === currentDay) {
                    dayElement.classList.add('today');
                }
                day++;
            } else {
                dayElement.textContent = nextMonthDay;
                dayElement.classList.add('other-month');
                nextMonthDay++;
            }
            calendarGrid.appendChild(dayElement);
        }
    }
    generateDashboardCalendar(); // Run on page load

    // --- 7. INTERACTIVE CALENDAR MODAL LOGIC ---
    const calendarModal = document.getElementById('calendarModal');
    const modalCalendarTitle = document.getElementById('modal-calendar-title');
    const modalCalendarGrid = document.getElementById('modal-calendar-grid');
    const prevMonthBtn = document.getElementById('prev-month-btn');
    const nextMonthBtn = document.getElementById('next-month-btn');
    
    let activeDateField = null;
    let currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth();

    const openCalendarModal = () => {
        if (!calendarModal) return;
        currentDate = new Date();
        currentYear = currentDate.getFullYear();
        currentMonth = currentDate.getMonth();
        generateModalCalendar(currentYear, currentMonth);
        calendarModal.classList.add('active');
    };
    const closeCalendarModal = () => calendarModal?.classList.remove('active');

    async function fetchEvents(year, month) {
        try {
            const response = await fetch(`handlers/get-calendar-events.php?year=${year}&month=${month + 1}`);
            if (!response.ok) throw new Error('Network response failed');
            return await response.json();
        } catch (error) {
            console.error('Failed to fetch calendar events:', error);
            return {};
        }
    }

    async function generateModalCalendar(year, month) {
        if (!modalCalendarGrid || !modalCalendarTitle) return;
        const eventsData = await fetchEvents(year, month);
        const firstDayOfMonth = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        modalCalendarTitle.textContent = `${monthNames[month]} ${year}`;
        modalCalendarGrid.innerHTML = '';
        let startingDay = firstDayOfMonth.getDay() === 0 ? 6 : firstDayOfMonth.getDay() - 1;
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
                const isUnavailable = dayElement.classList.contains('unavailable_leave');
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
            modalCalendarGrid.appendChild(dayElement);
        }
    }

    [startDateField, endDateField].forEach(field => {
        field?.addEventListener('click', (e) => { e.preventDefault(); activeDateField = e.target; openCalendarModal(); });
        field?.addEventListener('keydown', (e) => e.preventDefault());
    });
    
    prevMonthBtn?.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        generateModalCalendar(currentYear, currentMonth);
    });

    nextMonthBtn?.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        generateModalCalendar(currentYear, currentMonth);
    });

    calendarModal?.addEventListener('click', (e) => { if (e.target === calendarModal) closeCalendarModal(); });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (leaveModal.classList.contains('active')) closeAppModal();
            if (calendarModal?.classList.contains('active')) closeCalendarModal();
            if (confirmationModal?.style.display === 'flex') closeConfirmModal();
        }
    });
});