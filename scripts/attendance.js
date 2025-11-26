document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const timeInBtn = document.getElementById('timeInBtn');
    const timeOutBtn = document.getElementById('timeOutBtn');
    const nfcForm = document.getElementById('nfcForm');
    const actionInput = document.getElementById('actionInput');
    const nfcInput = document.getElementById('nfcInput');
    const scanPrompt = document.getElementById('scanPrompt');
    const messageContainer = document.getElementById('messageContainer');
    const dynamicList = document.getElementById('dynamic-list');

    // --- State Variables ---
    let nfcBuffer = '';
    let bufferClearTimer = null;
    let currentAction = null;
    let isSubmitting = false; // Prevents double submission

    // --- UI Update Functions ---
    const setStatus = (status, text) => {
        scanPrompt.dataset.status = status;
        scanPrompt.textContent = text;
    };

    const resetToWaitingState = () => {
        setStatus('waiting', 'Waiting for input...');
        currentAction = null;
        isSubmitting = false;
        timeInBtn.classList.remove('active');
        timeOutBtn.classList.remove('active');
    };
    
    const displayMessage = (message, type) => {
        messageContainer.innerHTML = ''; // Clear previous messages
        const messageDiv = document.createElement('div');
        messageDiv.className = `session-message ${type}`;
        messageDiv.textContent = message;
        messageContainer.appendChild(messageDiv);

        setTimeout(() => {
            messageDiv.style.transition = 'opacity 0.5s';
            messageDiv.style.opacity = '0';
            setTimeout(() => messageDiv.remove(), 500);
        }, 5000);
    };

    const addRecordToList = (record) => {
        // Remove the "No records" placeholder if it exists
        const noRecordsCard = document.getElementById('no-records-card');
        if (noRecordsCard) {
            noRecordsCard.remove();
        }

        const card = document.createElement('div');
        card.className = 'attendance-card';
        card.style.animation = 'fadeIn 0.5s ease';

        const name = document.createElement('p');
        name.className = 'attendanceName';
        name.textContent = record.fullName;

        const type = document.createElement('span');
        type.className = 'attendanceType';
        type.classList.add(record.eventType === 'Time In' ? 'type-time-in' : 'type-time-out');
        type.textContent = record.eventType;

        const time = document.createElement('p');
        time.className = 'attendanceTime';
        time.textContent = record.eventTime;

        card.appendChild(name);
        card.appendChild(type);
        card.appendChild(time);
        
        dynamicList.prepend(card); // Adds the new record to the top of the list
    };


    const submitAttendance = async () => {
        if (isSubmitting) return;
        isSubmitting = true;

        setStatus('processing', 'Processing...');
        const formData = new FormData(nfcForm);

        try {
            const response = await fetch('process_attendance.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }

            const data = await response.json();
            displayMessage(data.message, data.status);

            if (data.status === 'success' && data.newRecord) {
                addRecordToList(data.newRecord);
                setStatus('scanned', 'Scanned!');
                setTimeout(resetToWaitingState, 10000); // Reset after 10 seconds
            } else {
                // If there's an error, reset immediately to allow another scan
                resetToWaitingState();
            }

        } catch (error) {
            console.error('Fetch Error:', error);
            displayMessage('A client-side error occurred. Check console.', 'error');
            resetToWaitingState();
        }
    };


    // --- Event Listeners ---
    timeInBtn.addEventListener('click', () => {
        currentAction = 'time_in';
        actionInput.value = 'time_in';
        setStatus('ready', 'Scanner Ready...');
        timeInBtn.classList.add('active');
        timeOutBtn.classList.remove('active');
    });

    timeOutBtn.addEventListener('click', () => {
        currentAction = 'time_out';
        actionInput.value = 'time_out';
        setStatus('ready', 'Scanner Ready...');
        timeOutBtn.classList.add('active');
        timeInBtn.classList.remove('active');
    });
    
    document.addEventListener('keydown', (event) => {
        if (!currentAction || isSubmitting) return;

        if (event.key.length === 1) {
            event.preventDefault();
            nfcBuffer += event.key;
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (nfcBuffer.length > 0) {
                nfcInput.value = nfcBuffer;
                submitAttendance();
            }
            nfcBuffer = '';
        }

        clearTimeout(bufferClearTimer);
        bufferClearTimer = setTimeout(() => { nfcBuffer = ''; }, 500);
    });
});