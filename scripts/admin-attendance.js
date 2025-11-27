document.addEventListener('DOMContentLoaded', () => {
    // --- Get DOM Elements ---
    const searchInput = document.getElementById('search-employee-input');
    const searchIdInput = document.getElementById('search-employee-id');
    const resultsContainer = document.getElementById('autocomplete-results');
    const datePicker = document.getElementById('date-picker');
    const dailyBtn = document.getElementById('btn-daily');
    const weeklyBtn = document.getElementById('btn-weekly');
    const table = document.querySelector('.attendance-table');
    const tableHead = table.querySelector('thead');
    const tableBody = document.getElementById('attendance-table-body');

    const exportBtn = document.getElementById('exportBtn');
    const exportModal = document.getElementById('exportAuthModal');
    const exportPasswordInput = document.getElementById('exportPasswordInput');
    const confirmExportBtn = document.getElementById('confirmExportBtn');
    const exportErrorMsg = document.getElementById('exportAuthError');
    let targetExportUrl = '';

    let currentView = 'daily'; // Track the current view state

    // --- Main Function to Fetch and Render Table Data ---
    const fetchAndUpdateTable = async () => {
        const selectedDate = datePicker.value;
        const selectedEmployeeId = searchIdInput.value;
        
        const url = `handlers/get-attendance-data.php?date=${selectedDate}&employee_id=${selectedEmployeeId}&view_type=${currentView}`;

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();

            if (currentView === 'daily') {
                renderDailyTable(data);
            } else {
                renderWeeklyTable(data);
            }
        } catch (error) {
            console.error("Could not fetch attendance data:", error);
            tableBody.innerHTML = `<tr><td colspan="9">Error loading data. Please try again.</td></tr>`;
        }
    };

    // --- RENDER DAILY VIEW ---
    const renderDailyTable = (records) => {
        table.classList.remove('weekly-view');
        // Set Daily Header
        tableHead.innerHTML = `
            <tr>
                <th>Employee Name</th>
                <th>Status</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Overtime Hours</th>
                <th>Remarks</th>
            </tr>`;
        tableBody.innerHTML = ''; // Clear existing data

        if (!records || records.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6">No records found.</td></tr>`;
            return;
        }

        records.forEach(record => {
            // (Your existing daily rendering logic here - slightly modified for clarity)
            let { status, statusClass, timeIn, timeOut, overtimeDisplay, remarks, remarksClass } = processDailyRecord(record);
            const row = `
                <tr>
                    <td>${record.FullName}</td>
                    <td><span class="status-tag ${statusClass}">${status}</span></td>
                    <td>${timeIn}</td>
                    <td>${timeOut}</td>
                    <td>${overtimeDisplay}</td>
                    <td class="${remarksClass}">${remarks}</td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', row);
        });
        
        padTableWithEmptyRows();
    };

    const renderWeeklyTable = (data) => {
        const { records, week_start_date } = data;
        table.classList.add('weekly-view');

        // --- Generate Weekly Header ---
        const weekStartDate = new Date(week_start_date + 'T00:00:00'); // Avoid timezone issues
        let headerHtml = '<tr><th>Employee Name</th>';
        const dayFormatter = new Intl.DateTimeFormat('en-US', { weekday: 'short' });
        for (let i = 0; i < 7; i++) {
            const currentDate = new Date(weekStartDate);
            currentDate.setDate(weekStartDate.getDate() + i);
            const dayName = dayFormatter.format(currentDate);
            const dayNum = String(currentDate.getDate()).padStart(2, '0');
            headerHtml += `<th>${dayName}<br><span class="header-date">${dayNum}</span></th>`;
        }
        headerHtml += '<th>Total Hours</th></tr>';
        tableHead.innerHTML = headerHtml;

        // --- Process and Group Data by Employee ---
        const employeeData = {};
        records.forEach(rec => {
            if (!employeeData[rec.FullName]) {
                employeeData[rec.FullName] = { dailyMinutes: {}, totalMinutes: 0 };
            }
            if (rec.TimeIn && rec.TimeOut) {
                const timeIn = new Date(rec.TimeIn);
                const timeOut = new Date(rec.TimeOut);
                const diffMs = timeOut - timeIn;
                const minutesWorked = Math.round(diffMs / 60000);
                employeeData[rec.FullName].dailyMinutes[rec.AttendanceDate] = minutesWorked;
                employeeData[rec.FullName].totalMinutes += minutesWorked;
            }
        });

        // --- Generate Weekly Body ---
        tableBody.innerHTML = '';
        if (Object.keys(employeeData).length === 0) {
            tableBody.innerHTML = `<tr><td colspan="9">No attendance records found for this week.</td></tr>`;
            return;
        }

        for (const name in employeeData) {
            let rowHtml = `<tr><td>${name}</td>`;
            for (let i = 0; i < 7; i++) {
                const currentDate = new Date(weekStartDate);
                currentDate.setDate(weekStartDate.getDate() + i);
                const dateString = currentDate.toISOString().split('T')[0];
                
                const minutes = employeeData[name].dailyMinutes[dateString];
                if (minutes) {
                    const hours = Math.floor(minutes / 60);
                    const mins = minutes % 60;
                    rowHtml += `<td>${hours}:${String(mins).padStart(2, '0')}</td>`;
                } else {
                    rowHtml += '<td>--:--</td>';
                }
            }
            const totalH = Math.floor(employeeData[name].totalMinutes / 60);
            const totalM = employeeData[name].totalMinutes % 60;
            rowHtml += `<td>${totalH} h ${totalM} m</td></tr>`;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
        }
        
        padTableWithEmptyRows();
    };
    
    // --- Helper function for daily record processing ---
    const processDailyRecord = (record) => {
        let status = 'Absent', statusClass = 'status-absent', timeIn = '--:--', timeOut = '--:--',
            overtimeDisplay = '--:--', // Default to blank
            remarks = record.Remarks || '--:--',
            remarksClass = record.Remarks ? `remarks-${record.Remarks.toLowerCase()}` : '';

        if (record.TimeIn) {
            const timeInObj = new Date(record.TimeIn);
            timeIn = timeInObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

            if (record.TimeOut) {
                status = 'Finished'; 
                statusClass = 'status-finished';
                
                const timeOutObj = new Date(record.TimeOut);
                timeOut = timeOutObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                
                // --- REPLACED LOGIC FOR OVERTIME CALCULATION ---
                // Define the end of shift (5:00 PM on that specific day)
                const shiftEnd = new Date(timeOutObj.getFullYear(), timeOutObj.getMonth(), timeOutObj.getDate(), 17, 0, 0);

                // Check if clock-out time is after the shift ended
                if (timeOutObj > shiftEnd) {
                    const diffMs = timeOutObj - shiftEnd;
                    const hours = Math.floor(diffMs / 3600000); // Get only the integer for hours

                    // Only display if there's at least one full hour
                    if (hours > 0) {
                        // Handle pluralization ("1 Hour" vs "2 Hours")
                        const label = (hours === 1) ? ' Hour' : ' Hours';
                        overtimeDisplay = `${hours}${label}`;
                    }
                    // If less than an hour, overtimeDisplay remains blank
                }
            } else {
                status = 'Working'; 
                statusClass = 'status-working';
            }
        }
        return { status, statusClass, timeIn, timeOut, overtimeDisplay, remarks, remarksClass };
    };
    // --- Event Listeners ---
    
    searchInput.addEventListener('input', function() {
        const value = this.value;
        resultsContainer.innerHTML = ''; // Clear previous results

        // If the input is cleared, show all employees again
        if (!value) {
            searchIdInput.value = 'all'; 
            fetchAndUpdateTable();
            return;
        }

        // Filter the global 'allEmployees' array (from the PHP script tag)
        const filteredEmployees = allEmployees.filter(emp => 
            emp.FullName.toLowerCase().includes(value.toLowerCase())
        );

        filteredEmployees.forEach(emp => {
            const item = document.createElement('DIV');
            item.innerHTML = emp.FullName;
            item.dataset.id = emp.EmployeeID; // Store the ID on the element

            // Add click event to each result item
            item.addEventListener('click', function() {
                searchInput.value = this.innerHTML; // Set input text to the selected name
                searchIdInput.value = this.dataset.id; // Set hidden input to the selected ID
                resultsContainer.innerHTML = ''; // Close the dropdown
                fetchAndUpdateTable(); // Refresh the table with the new filter
            });
            resultsContainer.appendChild(item);
        });
    });

    // Add a listener to close the dropdown if the user clicks anywhere else
    document.addEventListener('click', function (e) {
        if (e.target !== searchInput) {
            resultsContainer.innerHTML = '';
        }
    });

    datePicker.addEventListener('change', fetchAndUpdateTable);

    dailyBtn.addEventListener('click', () => {
        if (currentView === 'daily') return; // Do nothing if already active
        currentView = 'daily';
        dailyBtn.classList.add('active');
        weeklyBtn.classList.remove('active');
        fetchAndUpdateTable();
    });

    weeklyBtn.addEventListener('click', () => {
        if (currentView === 'weekly') return; // Do nothing if already active
        currentView = 'weekly';
        weeklyBtn.classList.add('active');
        dailyBtn.classList.remove('active');
        fetchAndUpdateTable();
    });

    function padTableWithEmptyRows() {
        const tableBody = document.getElementById('attendance-table-body');
        if (!tableBody) return;

        const MIN_VISIBLE_ROWS = 12; // Set how many rows you want visible at minimum
        const existingRows = tableBody.querySelectorAll('tr').length;
        
        // Calculate how many empty rows we need to add
        const rowsToAdd = MIN_VISIBLE_ROWS - existingRows;

        if (rowsToAdd > 0) {
            // Get the number of columns from the table header
            const columnCount = document.querySelectorAll('.attendance-table thead th').length;
            
            // Create a document fragment for performance
            const fragment = document.createDocumentFragment();

            for (let i = 0; i < rowsToAdd; i++) {
                const tr = document.createElement('tr');
                tr.className = 'empty-row'; // Add a class for styling
                
                // Create an empty cell for each column
                for (let j = 0; j < columnCount; j++) {
                    const td = document.createElement('td');
                    td.innerHTML = '&nbsp;'; // Non-breaking space to ensure cell renders
                    tr.appendChild(td);
                }
                fragment.appendChild(tr);
            }
            // Append all new rows at once
            tableBody.appendChild(fragment);
            tableBody.style.visibility = 'visible';
        }
    }

    // --- NEW: EXPORT MODAL LOGIC ---

    // 1. Intercept Export Click
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            e.preventDefault(); 
            targetExportUrl = this.href; 
            openExportModal();
        });
    }

    // 2. Modal Open/Close Functions
    const openExportModal = () => {
        if(!exportModal) return;
        exportModal.style.display = 'flex';
        exportModal.classList.add('is-open');
        exportPasswordInput.value = '';
        exportErrorMsg.textContent = '';
        if (confirmExportBtn) {
            confirmExportBtn.textContent = "Confirm Export"; // Reset text
            confirmExportBtn.disabled = false;               // Re-enable button
        }
        setTimeout(() => exportPasswordInput.focus(), 100);
    }

    window.closeExportModal = () => {
        if(!exportModal) return;
        exportModal.style.display = 'none';
        exportModal.classList.remove('is-open');
    }

    // 3. Confirm Logic with Password
    if (confirmExportBtn) {
        confirmExportBtn.addEventListener('click', function() {
            const password = exportPasswordInput.value;
            
            if (!password) {
                exportErrorMsg.textContent = "Password is required";
                return;
            }

            // Visual feedback
            const originalText = confirmExportBtn.textContent;
            confirmExportBtn.textContent = "Verifying...";
            confirmExportBtn.disabled = true;

            // AJAX Request to handlers/verify-password.php
            fetch('handlers/verify-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'password=' + encodeURIComponent(password)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success: Close modal and trigger download
                    closeExportModal();
                    window.location.href = targetExportUrl;
                } else {
                    // Error: Show message
                    exportErrorMsg.textContent = data.message || "Incorrect password";
                    confirmExportBtn.textContent = originalText;
                    confirmExportBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                exportErrorMsg.textContent = "Server error occurred";
                confirmExportBtn.textContent = originalText;
                confirmExportBtn.disabled = false;
            });
        });
    }

    // 4. Submit on Enter Key
    if (exportPasswordInput) {
        exportPasswordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmExportBtn.click();
            }
        });
    }
    

    // Call the function when the page loads
    padTableWithEmptyRows();
    
    fetchAndUpdateTable(); 
});