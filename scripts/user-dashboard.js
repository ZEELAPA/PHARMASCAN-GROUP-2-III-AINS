document.addEventListener('DOMContentLoaded', function() {
    const gridViewBtn = document.getElementById('grid-view-btn');
    const listViewBtn = document.getElementById('list-view-btn');
    const taskManagementPanel = document.querySelector('.task-management-panel');
    const listViewContainer = document.querySelector('.task-list-view-container');

    const addTaskBtn = document.querySelector('.add-button-container button');
    const modal = document.getElementById('addTaskModal');

    if (!modal) return;

    const closeBtn = modal.querySelector('.close-btn');
    const taskForm = document.getElementById('taskForm');
    const modalTitle = document.getElementById('modalTitle');
    const submitButton = document.getElementById('submitButton');
    const editTaskIDInput = document.getElementById('editTaskID');

    const taskNameInput = document.getElementById('taskName');
    const assignEmployeeInput = document.getElementById('assignEmployee');
    const taskDeadlineInput = document.getElementById('taskDeadline');
    const taskPriorityInput = document.getElementById('taskPriority');
    const taskStatusInput = document.getElementById('taskStatus');
    const taskRemarksInput = document.getElementById('taskRemarks');


    const allTaskCards = document.querySelectorAll('.task-card');
    const defaultExpandedCard = document.querySelector('.task-card.is-expanded');
    function collapseAllCards() {
        allTaskCards.forEach(card => {
            card.classList.remove('is-expanded');
        });
    }

    function generateCalendar() {
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

    if (gridViewBtn && listViewBtn && taskManagementPanel) {
        // This toggles the main class that controls which view is visible via CSS
        gridViewBtn.addEventListener('click', () => {
            if (!gridViewBtn.classList.contains('active')) {
                gridViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
                taskManagementPanel.classList.remove('list-view-active');
            }
        });

        listViewBtn.addEventListener('click', () => {
            if (!listViewBtn.classList.contains('active')) {
                listViewBtn.classList.add('active');
                gridViewBtn.classList.remove('active');
                taskManagementPanel.classList.add('list-view-active');
            }
        });
    }

    // --- Functions to control the modal's state ---

    const openModalAsEdit = (card) => {
        // Change modal appearance for "Edit" mode
        modalTitle.textContent = 'Edit Task';
        submitButton.textContent = 'Update Task';
        taskForm.action = 'user-edit-task-handler.php'; // Point form to the update script

        // Populate form fields with data from the card's data-* attributes
        editTaskIDInput.value = card.dataset.taskId;
        taskNameInput.value = card.dataset.taskName;
        assignEmployeeInput.value = card.dataset.accountId;
        taskDeadlineInput.value = card.dataset.deadline;
        taskPriorityInput.value = card.dataset.priority;
        taskStatusInput.value = card.dataset.status;
        taskRemarksInput.value = card.dataset.remarks;

        // Show the modal
        modal.classList.add('active');
    };

    const openModalAsAdd = () => {
        // Set modal appearance for "Add" mode
        modalTitle.textContent = 'Add New Task';
        submitButton.textContent = 'Create Task';
        taskForm.action = 'user-task-handler.php'; // Point form to the add script
        
        // Reset the form and clear the hidden ID
        taskForm.reset();
        editTaskIDInput.value = '';

        // Show the modal
        modal.classList.add('active');
    };

    const closeModal = () => {
        modal.classList.remove('active');
    };

    // --- Event Listeners Setup ---

    // Listener for the "New +" button to open in "Add" mode
    if (addTaskBtn) {
        addTaskBtn.addEventListener('click', openModalAsAdd);
    }

    // Replaces the old hover logic with a new click listener for each card
    allTaskCards.forEach(card => {
        card.addEventListener('click', () => {
            openModalAsEdit(card);
        });
    });

    // Centralized listeners for closing the modal
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    if (listViewContainer) {
        listViewContainer.addEventListener('click', (event) => {
            // Check if an update button was clicked
            if (event.target.classList.contains('btn-update')) {
                // Find the parent table row (which holds all the task data)
                const taskRow = event.target.closest('.task-row');
                if (taskRow) {
                    // The taskRow has the same data-* attributes as the grid view cards,
                    // so we can reuse the same function to open the edit modal.
                    openModalAsEdit(taskRow);
                }
            }
        });
    }

    generateCalendar();

    // --- CALENDAR MODAL LOGIC FOR TASKS ---
    const calendarModal = document.getElementById('calendarModal');
    const taskDeadlineField = document.getElementById('taskDeadline');
    const modalCalendarTitle = document.getElementById('modal-calendar-title');
    const modalCalendarGrid = document.getElementById('modal-calendar-grid');
    const prevMonthBtn = document.getElementById('prev-month-btn');
    const nextMonthBtn = document.getElementById('next-month-btn');
    
    if (!calendarModal || !taskDeadlineField) return;

    let activeDateField = null;
    let currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth();

    const openCalendarModal = () => {
        currentDate = new Date();
        // If field has a value, open calendar to that month
        if (activeDateField && activeDateField.value) {
            const valDate = new Date(activeDateField.value);
            if (!isNaN(valDate)) {
                currentYear = valDate.getFullYear();
                currentMonth = valDate.getMonth();
            }
        } else {
            currentYear = currentDate.getFullYear();
            currentMonth = currentDate.getMonth();
        }
        
        generateModalCalendar(currentYear, currentMonth);
        calendarModal.classList.add('active');
    };

    const closeCalendarModal = () => calendarModal.classList.remove('active');

    // Optional: Fetch events if you want to see leaves/tasks on the picker
    async function fetchEvents(year, month) {
        try {
            // Reusing the endpoint from leave-application if available
            const response = await fetch(`get-calendar-events.php?year=${year}&month=${month + 1}`);
            if (!response.ok) throw new Error('Network response failed');
            return await response.json();
        } catch (error) {
            // Silently fail or return empty object if endpoint doesn't exist for tasks
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
        
        // Adjust for Monday start (0=Sun, 1=Mon)
        let startingDay = firstDayOfMonth.getDay() === 0 ? 6 : firstDayOfMonth.getDay() - 1;
        
        // Create 6 rows (42 boxes)
        for (let i = 0; i < 42; i++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day');
            const day = i - startingDay + 1;

            if (day > 0 && day <= daysInMonth) {
                const dayString = String(day).padStart(2, '0');
                dayElement.textContent = dayString;
                const fullDateString = `${year}-${String(month + 1).padStart(2, '0')}-${dayString}`;
                dayElement.dataset.date = fullDateString;

                // Add classes from fetched data (e.g. leaves)
                if (eventsData[fullDateString]?.classes) {
                    eventsData[fullDateString].classes.forEach(cls => dayElement.classList.add(cls));
                } else {
                    // Default style for task picker
                    dayElement.classList.add('available_leave'); 
                }

                // Check constraints (e.g. don't allow picking unavailable days)
                const isUnavailable = dayElement.classList.contains('unavailable_leave') || dayElement.classList.contains('occupied');
                
                if (!isUnavailable) {
                    dayElement.addEventListener('click', () => {
                        if (activeDateField) {
                            activeDateField.value = fullDateString;
                            // Trigger change event for any validation listeners
                            activeDateField.dispatchEvent(new Event('change'));
                        }
                        closeCalendarModal();
                    });
                }
            } else {
                dayElement.classList.add('other-month');
            }
            modalCalendarGrid.appendChild(dayElement);
        }
    }

    // Attach event listener to the Deadline field
    taskDeadlineField.addEventListener('click', (e) => { 
        e.preventDefault(); // Prevent default browser picker
        activeDateField = e.target; 
        openCalendarModal(); 
    });
    
    // Prevent typing manually if desired
    taskDeadlineField.addEventListener('keydown', (e) => e.preventDefault());
    
    // Navigation Buttons
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

    // Close when clicking outside
    calendarModal.addEventListener('click', (e) => { 
        if (e.target === calendarModal) closeCalendarModal(); 
    });
    
    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && calendarModal.classList.contains('active')) {
            closeCalendarModal();
        }
    });
});