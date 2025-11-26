document.addEventListener('DOMContentLoaded', function() {

    // --- MODAL ELEMENTS (Removed one redundant variable) ---
    const modalOverlay = document.getElementById('note-modal-overlay');
    const noteModal = document.getElementById('note-modal');
    // NEW: Added modalHeader selector
    const modalHeader = noteModal.querySelector('.modal-header h2');
    const closeModalBtn = document.getElementById('close-modal-btn');
    // REMOVED: const cancelNoteBtn = document.getElementById('cancel-note-btn'); // This was redundant
    const noteForm = document.getElementById('note-form');
    const noteTitleInput = document.getElementById('note-title');
    const noteBodyInput = document.getElementById('note-body');
    const noteDateInput = document.getElementById('note-date');
    const primaryBtn = document.getElementById('modal-primary-btn');
    const secondaryBtn = document.getElementById('modal-secondary-btn');

    // --- NOTE LIST ELEMENTS ---
    const addNoteBtn = document.getElementById('add-note-btn');
    const notesList = document.getElementById('admin-notes-list');

    // --- UNTOUCHED CALENDAR/CHART CODE ---
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

    function generateHtmlLegend(chart, legendContainerId) {
        const legendContainer = document.getElementById(legendContainerId);
        if (!legendContainer) return;
        
        const ul = document.createElement('ul');
        ul.classList.add('legend-list');
        const labels = chart.data.labels;
        const data = chart.data.datasets[0].data;
        const colors = chart.data.datasets[0].backgroundColor;
        
        labels.forEach((label, index) => {
            const li = document.createElement('li');
            li.innerHTML = `
            <span class="legend-color-box" style="background-color: ${colors[index % colors.length]}"></span>
            <span class="legend-value">${data[index]}</span>
            <span class="legend-label">${label}</span>`;
            ul.appendChild(li);
        });
        legendContainer.innerHTML = ''; // Clear previous legend
        legendContainer.appendChild(ul);
    }

    function renderTaskStatusChart() {
        const chartCanvas = document.getElementById('taskStatusPieChart');
        if (!chartCanvas) return;

        if (typeof taskStatusData === 'undefined' || !taskStatusData.labels.length) {
            const chartPanel = chartCanvas.closest('.panel-body');
            if (chartPanel) chartPanel.innerHTML = '<p style="text-align:center; width:100%;">No task data available.</p>';
            return;
        }

        const ctx = chartCanvas.getContext('2d');
        const chartColors = ['#A72A0C', '#AA7E01', '#3B6B50']; 
        
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: taskStatusData.labels,
                datasets: [{
                    data: taskStatusData.data,
                    backgroundColor: chartColors,
                    borderColor: '#EEEEEE',
                    borderWidth: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '60%', 
                plugins: {
                    legend: { display: false },
                    tooltip: { 
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { size: 14 },
                        bodyFont: { size: 12 },
                    }
                }
            }
        });

        generateHtmlLegend(chart, 'task-status-legend');
    }

    // --- CORRECTED MODAL LOGIC ---

    const openModalForNew = () => {
        noteModal.dataset.mode = 'add';
        noteModal.dataset.editingId = '';
        noteForm.reset();
        
        modalHeader.textContent = 'New Note';
        primaryBtn.textContent = 'Save';
        secondaryBtn.textContent = 'Cancel';

        const today = new Date();
        const options = { year: 'numeric', month: 'long', day: '2-digit' };
        noteDateInput.value = today.toLocaleDateString('en-US', options);
        noteDateInput.parentElement.style.display = 'flex';

        modalOverlay.classList.remove('hidden');
    };

    const openModalForEdit = (noteItem) => {
        const noteId = noteItem.dataset.id;
        const noteText = noteItem.querySelector('.note-text').textContent;

        const parts = noteText.split('\n\n');
        const title = parts[0] || '';
        const body = parts.slice(1).join('\n\n') || '';

        noteModal.dataset.mode = 'edit';
        noteModal.dataset.editingId = noteId;

        modalHeader.textContent = 'Edit Note';
        primaryBtn.textContent = 'Update';
        secondaryBtn.textContent = 'Delete';

        noteTitleInput.value = title;
        noteBodyInput.value = body;
        noteDateInput.parentElement.style.display = 'none';

        modalOverlay.classList.remove('hidden');
    };

    const closeModal = () => {
        modalOverlay.classList.add('hidden');
    };

    addNoteBtn.addEventListener('click', openModalForNew);
    closeModalBtn.addEventListener('click', closeModal);

    secondaryBtn.addEventListener('click', async () => {
        const mode = noteModal.dataset.mode;
        if (mode === 'edit') {
            const noteId = noteModal.dataset.editingId;
            if (confirm('Are you sure you want to delete this note?')) {
                await deleteNote(noteId);
                closeModal();
            }
        } else {
            closeModal();
        }
    });

    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });

    noteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const title = noteTitleInput.value.trim();
        const body = noteBodyInput.value.trim();
        const combinedText = title ? `${title}\n\n${body}` : body;
        
        const mode = noteModal.dataset.mode;
        
        if (mode === 'add') {
            if (!title && !body) {
                alert('Note title or body cannot be empty.');
                return;
            }
            const response = await fetch('admin-notes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', text: combinedText })
            });
            const result = await response.json();

            if (result.status === 'success') {
                const noNotesMessage = notesList.querySelector('.no-notes');
                if (noNotesMessage) noNotesMessage.remove();
                notesList.appendChild(renderNote(result.note));
                closeModal();
            } else {
                alert('Error: ' + result.message);
            }
        } else if (mode === 'edit') {
            const noteId = noteModal.dataset.editingId;
            const response = await fetch('admin-notes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update', id: noteId, text: combinedText })
            });
            const result = await response.json();
            
            if (result.status === 'success') {
                const noteItemToUpdate = notesList.querySelector(`.note-item[data-id="${noteId}"]`);
                if(noteItemToUpdate) {
                    const newTitle = noteTitleInput.value.trim();
                    noteItemToUpdate.querySelector('.note-text').textContent = newTitle;
                }
                closeModal();
            } else {
                alert('Error updating note: ' + result.message);
            }
        }
    });

    const renderNote = (note) => {
        const item = document.createElement('li');
        item.className = `note-item ${note.completed ? 'completed' : ''}`;
        item.dataset.id = note.id;

        // Get only the first line (the title) from the note text
        const title = note.text.split('\n\n')[0];

        // Use ONLY the title in the HTML. The <pre> tag is no longer needed here.
        item.innerHTML = `
            <input type="checkbox" class="note-checkbox" ${note.completed ? 'checked' : ''}>
            <span class="note-text">${title}</span>
            <button class="delete-note-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path></svg>
            </button>
        `;
        return item;
    };

    const deleteNote = async (noteId) => {
        const response = await fetch('admin-notes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id: noteId })
        });
        const result = await response.json();
        if (result.status === 'success') {
            const noteItem = notesList.querySelector(`.note-item[data-id="${noteId}"]`);
            if (noteItem) noteItem.remove();
        }
    };

    notesList.addEventListener('click', async (e) => {
        const noteItem = e.target.closest('.note-item');
        if (!noteItem) return;

        const noteId = noteItem.dataset.id;

        if (e.target.closest('.delete-note-btn')) {
            if (confirm('Are you sure you want to delete this note?')) {
                await deleteNote(noteId);
            }
        }
        else if (e.target.classList.contains('note-checkbox')) {
            const isCompleted = e.target.checked;
            const response = await fetch('admin-notes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle', id: noteId, completed: isCompleted })
            });
            const result = await response.json();
            if (result.status === 'success') {
                noteItem.classList.toggle('completed', isCompleted);
            }
        }
        else if (e.target.closest('.note-text')) {
            openModalForEdit(noteItem);
        }
    });
    
    // --- Initial function calls ---
    renderTaskStatusChart();
    generateCalendar();
});