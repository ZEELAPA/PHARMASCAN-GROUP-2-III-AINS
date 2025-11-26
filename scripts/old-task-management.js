document.addEventListener('DOMContentLoaded', function() {
    const validationMessage = document.getElementById('validationMessage');

    const taskForm = document.getElementById('taskForm');
    const agendaIdInput = document.getElementById('agenda_id');
    const taskInput = document.getElementById('taskInput');
    const userSelect = document.getElementById('user-select');
    const deadlineInput = document.getElementById('deadlineInput');
    const prioritySelect = document.getElementById('priority-select');
    const statusSelect = document.getElementById('status-select');
    const remarksInput = document.getElementById('remarksInput');

    // Buttons
    const addButton = document.getElementById('addButton');
    const updateButton = document.getElementById('updateButton');
    const deleteButton = document.getElementById('deleteButton');
    const clearButton = document.getElementById('clearButton');

    const exportPdfButton = document.getElementById('exportPdfButton');

    function getCurrentDateTimeLocalString() {
        const now = new Date();
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        // Setting seconds to 00 for consistency, as datetime-local typically handles minutes.
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    const minDateTime = getCurrentDateTimeLocalString();
    deadlineInput.setAttribute('min', minDateTime);

    deadlineInput.addEventListener('change', function() {
        const selectedDateTime = new Date(this.value);
        const currentDateTime = new Date();

        if (selectedDateTime < currentDateTime) {
            validationMessage.textContent = "Please select a date and time that is not in the past.";
            this.setCustomValidity("Invalid date and time.");
        } else {
            validationMessage.textContent = "";
            this.setCustomValidity("");
        }
    });

    const taskContainers = document.querySelectorAll('.task-container');

    const clearForm = () => {
        taskForm.reset();
        agendaIdInput.value = '';

        addButton.style.display = 'inline-block';
        updateButton.style.display = 'none';
        deleteButton.style.display = 'none';
        
        taskContainers.forEach(container => container.classList.remove('selected'));
    };

    clearButton.addEventListener('click', clearForm);

    taskContainers.forEach(container => {
        container.addEventListener('click', () => {
            taskContainers.forEach(c => c.classList.remove('selected'));
            container.classList.add('selected');

            const data = container.dataset;
            agendaIdInput.value = data.id;
            taskInput.value = data.task;
            userSelect.value = data.accountid;
            deadlineInput.value = data.deadline;
            prioritySelect.value = data.priority;
            statusSelect.value = data.status;
            remarksInput.value = data.remarks;

            addButton.style.display = 'none';
            updateButton.style.display = 'inline-block';
            deleteButton.style.display = 'inline-block';
        });
    });

    clearForm();

    if (exportPdfButton) {
        exportPdfButton.addEventListener('click', () => {
            console.log('Generating structured PDF report...');

            // Initialize jsPDF
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            // --- 1. DEFINE THE TABLE COLUMNS ---
            const tableColumns = ["Task", "Assigned To", "Deadline", "Status", "Priority"];

            // --- 2. PREPARE THE TABLE ROWS FROM YOUR DATA ---
            // 'allTasksData' is the variable we created in the HTML using PHP
            const tableRows = allTasksData.map(task => {
                // Format the date for better readability in the PDF
                const formattedDeadline = new Date(task.Deadline).toLocaleDateString("en-US", {
                    year: 'numeric', month: 'short', day: 'numeric'
                });

                return [
                    task.Task,
                    task.AssignedTo,
                    formattedDeadline,
                    task.Status,
                    task.Priority
                ];
            });

            // --- 3. ADD A TITLE TO THE DOCUMENT ---
            pdf.setFontSize(18);
            pdf.text("Task List Report", 14, 22); // Title, x-position, y-position
            pdf.setFontSize(11);
            pdf.setTextColor(100);
            pdf.text(`Generated on: ${new Date().toLocaleDateString("en-US")}`, 14, 29);

            // --- 4. GENERATE THE TABLE ---
            pdf.autoTable({
                head: [tableColumns], // The header row
                body: tableRows,      // The data rows
                startY: 35,           // Y position to start the table (below the title)
                theme: 'striped',     // 'striped', 'grid', or 'plain'
                styles: {
                    font: 'helvetica',
                    fontSize: 10
                }
            });

            // --- 5. SAVE THE PDF ---
            const currentDate = new Date().toISOString().slice(0, 10);
            const fileName = `task-list-report-${currentDate}.pdf`;
            pdf.save(fileName);
        });
    }
});