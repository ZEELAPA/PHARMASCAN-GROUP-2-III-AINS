document.addEventListener('DOMContentLoaded', function() {
    // --- GLOBAL STATE VARIABLES --- //
    let currentDate = new Date();
    let currentEventsData = {};   
    let originalEventsData = {};  
    let selectedDateString = null;
    let selectedDayElement = null; 

    // --- ELEMENT SELECTORS --- //
    const calendarTitle = document.getElementById('calendar-title');
    const calendarGrid = document.getElementById('calendar-grid');
    const prevMonthBtn = document.getElementById('prev-month-btn');
    const nextMonthBtn = document.getElementById('next-month-btn');
    const summaryTitle = document.getElementById('summary-title');
    const saveBtn = document.getElementById('save-btn');
    const resetBtn = document.getElementById('reset-btn');
    const statusButtons = document.querySelectorAll('.status-btn');

    // --- MODAL SELECTORS ---
    const confirmationModal = document.getElementById('confirmationModal');
    const modalConfirmBtn = confirmationModal.querySelector('.btn-confirm');
    const modalCancelBtn = confirmationModal.querySelector('.btn-cancel');
    const modalTitle = confirmationModal.querySelector('h3');
    const modalText = confirmationModal.querySelector('p');
    let pendingModalAction = null;

    // --- TOAST SVGs --- //
    const toastIcons = {
        success: `<svg class="toast-icon" width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg"><g filter="url(#filter0_d_1379_615)"><rect x="4" width="50" height="50" rx="5" fill="#23C35E"/></g><path d="M25.9272 29.0938L38.2866 16.7344C38.5782 16.4427 38.9185 16.2969 39.3074 16.2969C39.6963 16.2969 40.0366 16.4427 40.3282 16.7344C40.6199 17.026 40.7657 17.3726 40.7657 17.7742C40.7657 18.1757 40.6199 18.5218 40.3282 18.8125L26.948 32.2292C26.6564 32.5208 26.3161 32.6667 25.9272 32.6667C25.5383 32.6667 25.198 32.5208 24.9064 32.2292L18.6355 25.9583C18.3439 25.6667 18.2039 25.3206 18.2155 24.92C18.2272 24.5194 18.3793 24.1728 18.672 23.8802C18.9646 23.5876 19.3112 23.4417 19.7118 23.4427C20.1123 23.4437 20.4584 23.5895 20.7501 23.8802L25.9272 29.0938Z" fill="#F5F5F5"/><defs><filter id="filter0_d_1379_615" x="0" y="0" width="58" height="58" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4"/><feGaussianBlur stdDeviation="2"/><feComposite in2="hardAlpha" operator="out"/><feColorMatrix type="matrix" values="0 0 0 0 0.137255 0 0 0 0 0.764706 0 0 0 0 0.368627 0 0 0 0.5 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1379_615"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1379_615" result="shape"/></filter></defs></svg>`,
        error: `<svg class="toast-icon" width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg"><g filter="url(#filter0_d_115_16)"><rect x="4" width="50" height="50" rx="5" fill="#A72A0C"/></g><path fill-rule="evenodd" clip-rule="evenodd" d="M17.1292 31.7917L27.4221 14.6081C27.6388 14.2511 27.9439 13.9559 28.3079 13.751C28.6719 13.5462 29.0825 13.4386 29.5002 13.4386C29.9179 13.4386 30.3285 13.5462 30.6925 13.751C31.0565 13.9559 31.3616 14.2511 31.5783 14.6081L41.8712 31.7917C42.0833 32.1592 42.1955 32.5759 42.1966 33.0002C42.1977 33.4246 42.0877 33.8418 41.8776 34.2105C41.6675 34.5791 41.3645 34.8864 40.9988 35.1016C40.6331 35.3169 40.2174 35.4327 39.7931 35.4375H19.2073C18.7828 35.4331 18.3669 35.3176 18.0009 35.1024C17.635 34.8873 17.3318 34.58 17.1216 34.2112C16.9113 33.8424 16.8014 33.4249 16.8027 33.0004C16.8041 32.5759 16.9166 32.1591 17.1292 31.7917ZM29.5002 20.125C29.887 20.125 30.2579 20.2786 30.5314 20.5521C30.8049 20.8256 30.9585 21.1966 30.9585 21.5833V25.9583C30.9585 26.3451 30.8049 26.716 30.5314 26.9895C30.2579 27.263 29.887 27.4167 29.5002 27.4167C29.1134 27.4167 28.7425 27.263 28.469 26.9895C28.1955 26.716 28.0419 26.3451 28.0419 25.9583V21.5833C28.0419 21.1966 28.1955 20.8256 28.469 20.5521C28.7425 20.2786 29.1134 20.125 29.5002 20.125ZM28.0419 30.3333C28.0419 29.9466 28.1955 29.5756 28.469 29.3021C28.7425 29.0286 29.1134 28.875 29.5002 28.875H29.5119C29.8986 28.875 30.2696 29.0286 30.5431 29.3021C30.8166 29.5756 30.9702 29.9466 30.9702 30.3333C30.9702 30.7201 30.8166 31.091 30.5431 31.3645C30.2696 31.638 29.8986 31.7917 29.5119 31.7917H29.5002C29.1134 31.7917 28.7425 31.638 28.469 31.3645C28.1955 31.091 28.0419 30.7201 28.0419 30.3333Z" fill="#F5F5F5"/><defs><filter id="filter0_d_115_16" x="0" y="0" width="58" height="58" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4"/><feGaussianBlur stdDeviation="2"/><feComposite in2="hardAlpha" operator="out"/><feColorMatrix type="matrix" values="0 0 0 0 0.654902 0 0 0 0 0.164706 0 0 0 0 0.0470588 0 0 0 0.5 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_115_16"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_115_16" result="shape"/></filter></defs></svg>`,
        info: `<svg class="toast-icon" width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg"><g filter="url(#filter0_d_1379_616)"><rect x="4" width="50" height="50" rx="5" fill="#145494"/></g><path d="M42.0831 30.3317C41.9696 30.195 41.8582 30.0583 41.7488 29.9264C40.2449 28.1073 39.3351 27.0095 39.3351 21.86C39.3351 19.1939 38.6973 17.0064 37.4401 15.3658C36.5132 14.1538 35.2602 13.2344 33.6086 12.5549C33.5873 12.5431 33.5684 12.5276 33.5525 12.5091C32.9585 10.5198 31.3329 9.1875 29.4995 9.1875C27.6661 9.1875 26.0412 10.5198 25.4472 12.507C25.4314 12.5249 25.4126 12.5399 25.3918 12.5515C21.5377 14.1381 19.6647 17.1821 19.6647 21.8579C19.6647 27.0095 18.7562 28.1073 17.2509 29.9243C17.1415 30.0562 17.0301 30.1902 16.9166 30.3297C16.6235 30.6832 16.4378 31.1133 16.3814 31.569C16.3251 32.0248 16.4005 32.4871 16.5987 32.9014C17.0205 33.79 17.9194 34.3417 18.9455 34.3417H40.061C41.0823 34.3417 41.9751 33.7907 42.3983 32.9062C42.5973 32.4918 42.6735 32.0291 42.6176 31.5728C42.5618 31.1165 42.3763 30.6858 42.0831 30.3317ZM29.4995 39.8125C30.4873 39.8117 31.4565 39.5436 32.3043 39.0365C33.152 38.5295 33.8468 37.8024 34.3148 36.9325C34.3368 36.8908 34.3477 36.8441 34.3464 36.797C34.345 36.7499 34.3315 36.7039 34.3072 36.6635C34.2828 36.6231 34.2484 36.5897 34.2074 36.5665C34.1663 36.5434 34.1199 36.5312 34.0728 36.5312H24.9276C24.8804 36.5311 24.834 36.5431 24.7928 36.5663C24.7516 36.5894 24.7171 36.6228 24.6927 36.6632C24.6683 36.7036 24.6547 36.7496 24.6534 36.7968C24.652 36.844 24.6629 36.8908 24.685 36.9325C25.1529 37.8023 25.8475 38.5293 26.6952 39.0363C27.5428 39.5434 28.5118 39.8116 29.4995 39.8125Z" fill="#F5F5F5"/><defs><filter id="filter0_d_1379_616" x="0" y="0" width="58" height="58" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/><feOffset dy="4"/><feGaussianBlur stdDeviation="2"/><feComposite in2="hardAlpha" operator="out"/><feColorMatrix type="matrix" values="0 0 0 0 0.0784314 0 0 0 0 0.329412 0 0 0 0 0.580392 0 0 0 0.5 0"/><feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1379_616"/><feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1379_616" result="shape"/></filter></defs></svg>`
    };

    function showToast(type, message) {
        // Find or create container
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        // Create Toast Element
        const toast = document.createElement('div');
        toast.className = `status-toast status-${type}`;
        
        // Use the defined SVG or a fallback
        const svg = toastIcons[type] || toastIcons['info'];

        toast.innerHTML = `
            ${svg}
            <span class="toast-message">${message}</span>
        `;

        // Add to DOM
        container.appendChild(toast);

        // Trigger transition by adding 'show' class after a slight delay
        // This allows the browser to render the initial state (translate -120%) first.
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Remove after 3 seconds
        setTimeout(() => {
            // Remove 'show' to trigger the fade-out/slide-out transition
            toast.classList.remove('show');
            
            // Wait for the transition to finish before removing from DOM
            toast.addEventListener('transitionend', () => {
                toast.remove();
            });
        }, 3000);
    }

    function openModal(title, message, actionCallback) {
        modalTitle.textContent = title;
        modalText.textContent = message;
        pendingModalAction = actionCallback;
        confirmationModal.style.display = 'flex';
    }

    function closeModal() {
        confirmationModal.style.display = 'none';
        pendingModalAction = null;
    }


    if (confirmationModal) {
        modalCancelBtn.addEventListener('click', closeModal);
        
        modalConfirmBtn.addEventListener('click', () => {
            if (pendingModalAction) {
                pendingModalAction();
            }
            closeModal();
        });

        // Close modal if clicking outside the box
        confirmationModal.addEventListener('click', (e) => {
            if (e.target === confirmationModal) {
                closeModal();
            }
        })
    }
    // --- CORE FUNCTIONS --- //

    // ... (generateCalendar function remains unchanged) ...
    async function generateCalendar(year, month) {
        // ... previous code ...
        const eventsData = await fetchEvents(year, month);
        currentEventsData = { ...eventsData };
        originalEventsData = { ...eventsData };

        const now = new Date();
        const firstDayOfMonth = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        calendarTitle.textContent = `${monthNames[month]} ${year}`;
        summaryTitle.textContent = `${monthNames[month]} Summary`;
        calendarGrid.innerHTML = '';
        selectedDateString = null;
        selectedDayElement = null;

        let startingDay = firstDayOfMonth.getDay();
        if (startingDay === 0) { startingDay = 7; }

        const totalCells = 42;
        let day = 1;
        let nextMonthDay = 1;

        for (let i = 0; i < totalCells; i++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day');

            // Days from the PREVIOUS month
            if (i < startingDay - 1) {
                const prevDate = daysInPrevMonth - (startingDay - 2 - i);
                dayElement.textContent = String(prevDate).padStart(2, '0');
                dayElement.classList.add('other-month');
            }
            // Days from the CURRENT month
            else if (day <= daysInMonth) {
                const dayString = String(day).padStart(2, '0');
                dayElement.textContent = dayString;

                const fullDateString = `${year}-${String(month + 1).padStart(2, '0')}-${dayString}`;
                dayElement.dataset.date = fullDateString;

                // --- THIS IS THE CRITICAL SECTION (WITH THE FIX) ---
                if (currentEventsData[fullDateString]) {
                    // This day has a special status from the server (unavailable, pending, occupied).
                    const dayData = currentEventsData[fullDateString];
                    
                    if (dayData.classes && Array.isArray(dayData.classes)) {
                        dayData.classes.forEach(statusClass => {
                            dayElement.classList.add(statusClass);
                        });
                    }

                    if (dayData.pendingCount > 0) {
                        dayElement.dataset.pendingCount = dayData.pendingCount;
                    }
                } else {
                    // *** THE FIX IS HERE ***
                    // This day has no special status, so it is 'available' by default.
                    dayElement.classList.add('available_leave');
                }

                if (day === now.getDate() && month === now.getMonth() && year === now.getFullYear()) {
                    dayElement.classList.add('today');
                }
                
                dayElement.addEventListener('click', handleDayClick);
                day++;
            }
            // Days from the NEXT month
            else {
                dayElement.textContent = String(nextMonthDay).padStart(2, '0');
                dayElement.classList.add('other-month');
                nextMonthDay++;
            }
            
            calendarGrid.appendChild(dayElement);
        }
        updateSummary();
    }

    // ... (fetchEvents function remains unchanged) ...
    async function fetchEvents(year, month) {
        try {
            const response = await fetch(`get-calendar-events.php?year=${year}&month=${month + 1}`);
            if (!response.ok) throw new Error('Network response was not ok.');
            return await response.json();
        } catch (error) {
            console.error("Failed to fetch calendar events:", error);
            return {};
        }
    }
    
    async function saveEvents() {
        try {
            const response = await fetch('save-calendar-events.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(currentEventsData)
            });

            if (!response.ok) throw new Error('Failed to save data.');
            
            const result = await response.json();
            if (result.status === 'success') {
                // *** CHANGED: Use showToast instead of alert ***
                showToast('success', 'Calendar saved successfully!');
                originalEventsData = { ...currentEventsData };
            } else {
                throw new Error(result.message || 'Unknown error occurred.');
            }
        } catch (error) {
            console.error('Save Error:', error);
            // *** CHANGED: Use showToast instead of alert ***
            showToast('error', 'Error saving calendar: ' + error.message);
        }
    }

    // ... (updateSummary function remains unchanged) ...
    function updateSummary() {
        const counts = { available_leave: 0, unavailable_leave: 0, occupied: 0, pending_application: 0 };
        
        for (const date in currentEventsData) {
            const dayData = currentEventsData[date];
            
            // Check if dayData and its 'classes' property are valid
            if (dayData && dayData.classes && Array.isArray(dayData.classes)) {
                // Check for 'occupied' or 'unavailable' first, as they are final states.
                if (dayData.classes.includes('occupied')) {
                    counts.occupied++;
                } else if (dayData.classes.includes('unavailable_leave')) {
                    counts.unavailable_leave++;
                } else if (dayData.classes.includes('available_leave')) {
                    counts.available_leave++;
                }
            }
        }

        document.getElementById('summary-available').textContent = `${String(counts.available_leave).padStart(2, '0')} Days`;
        document.getElementById('summary-unavailable').textContent = `${String(counts.unavailable_leave).padStart(2, '0')} Days`;
        document.getElementById('summary-occupied').textContent = `${String(counts.occupied).padStart(2, '0')} Days`;
    }

    // --- EVENT HANDLERS --- //

    // ... (handleDayClick remains unchanged) ...
    function handleDayClick(event) {
        const clickedDay = event.currentTarget;

        // Deselect the previously selected day, if any
        if (selectedDayElement) {
            selectedDayElement.classList.remove('selected');
        }

        // Update the global state to the newly clicked day
        selectedDayElement = clickedDay;
        selectedDateString = clickedDay.dataset.date;

        // Add the visual selection indicator
        selectedDayElement.classList.add('selected');
    }

    function handleStatusSelectorClick(event) {
        if (!selectedDayElement) {
            // *** CHANGED: Use showToast for validation error ***
            showToast('info', 'Please select a date from the calendar first!');
            return; 
        }

        const statusToSet = event.currentTarget.dataset.status;
        applyStatusChange(statusToSet);
    }

    // ... (applyStatusChange and resetChanges remain unchanged) ...
    function applyStatusChange(newStatus) {
        if (!selectedDateString || !selectedDayElement) return;

        // --- 1. Update the live data object with the CORRECT STRUCTURE ---
        currentEventsData[selectedDateString] = {
            classes: [newStatus],
            pendingCount: 0 // When an admin sets a status, it overrides and clears pending apps for that day.
        };

        // --- 2. Update the calendar day's appearance ---
        selectedDayElement.className = 'calendar-day selected'; 
        
        const today = new Date();
        const todayString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        if (selectedDateString === todayString) {
            selectedDayElement.classList.add('today');
        }

        selectedDayElement.classList.add(newStatus);
        
        // Clear any pending count data attribute from the element
        delete selectedDayElement.dataset.pendingCount;

        // --- 3. Update the summary counts ---
        updateSummary();
    }
    
    function resetChanges() {
        if (confirm('Are you sure you want to reset all changes for this month?')) {
            generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        }
    }

    // ... (Tooltip logic remains unchanged) ...
    // --- TOOLTIP LOGIC ---
    const tooltip = document.getElementById('calendar-tooltip');
    const tooltipText = document.getElementById('tooltip-text');
    
    if (calendarGrid && tooltip && tooltipText) {
        calendarGrid.addEventListener('mouseover', function(event) {
            const dayElement = event.target.closest('.calendar-day.pending_application');

            if (dayElement) {
                const count = dayElement.dataset.pendingCount || 0;
                if (count > 0) {
                    const plural = count > 1 ? 's' : '';
                    tooltipText.textContent = `(${count}) Pending Application${plural}`;
                    tooltip.style.left = `${event.pageX + 10}px`;
                    tooltip.style.top = `${event.pageY + 10}px`;
                    tooltip.style.display = 'block';
                }
            }
        });

        calendarGrid.addEventListener('mouseout', function(event) {
            if (event.target.closest('.calendar-day')) {
                tooltip.style.display = 'none';
            }
        });

        calendarGrid.addEventListener('mousemove', function(event) {
            if (tooltip.style.display === 'block') {
                tooltip.style.left = `${event.pageX + 10}px`;
                tooltip.style.top = `${event.pageY + 10}px`;
            }
        });
    }

    // ... (Initialization remains unchanged) ...
    if (calendarGrid) {
        prevMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });

        nextMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });

        statusButtons.forEach(button => button.addEventListener('click', handleStatusSelectorClick));
        saveBtn.addEventListener('click', saveEvents);
        resetBtn.addEventListener('click', () => {
            openModal(
                'Confirm Reset',
                'Are you sure you want to reset all changes for this month? This cannot be undone.',
                function() {
                    // This code runs only when user clicks "Confirm" in the modal
                    generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
                    showToast('info', 'Calendar changes have been reset.');
                }
            );
        });

        generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
    }
});