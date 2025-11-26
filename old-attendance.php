<?php
    session_start();
    include('sqlconnect.php');

    date_default_timezone_set('Asia/Manila');

    // Check if a form was submitted with an action and an IC code
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ic_code'], $_POST['action'])) {
        
        $icCode = $_POST['ic_code'];
        $action = $_POST['action'];
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        // 1. Find the employee's account from the ICCode
        $stmt = $conn->prepare(
            "SELECT a.AccountID, p.FirstName, p.LastName 
            FROM tblaccounts a
            JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
            JOIN tblpersonalinfo p ON e.PersonalID = p.PersonalID
            WHERE a.ICCode = ?"
        );
        $stmt->bind_param("s", $icCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($employeeData = $result->fetch_assoc()) {
            $accountId = $employeeData['AccountID'];
            $employeeName = htmlspecialchars($employeeData['FirstName'] . ' ' . $employeeData['LastName']);

            // --- VALIDATION CHECKS (RUN THESE BEFORE THE ACTION) ---

            // NEW: Check if the user has already completed a full time-in/time-out cycle for today.
            $stmt_completed = $conn->prepare("SELECT AttendanceID FROM tblattendance WHERE AccountID = ? AND AttendanceDate = ? AND TimeOut IS NOT NULL");
            $stmt_completed->bind_param("is", $accountId, $today);
            $stmt_completed->execute();
            $completedRecord = $stmt_completed->get_result()->fetch_assoc();
            $stmt_completed->close();

            // Check for an existing open record (timed in but not out)
            $stmt_open = $conn->prepare("SELECT AttendanceID FROM tblattendance WHERE AccountID = ? AND AttendanceDate = ? AND TimeOut IS NULL");
            $stmt_open->bind_param("is", $accountId, $today);
            $stmt_open->execute();
            $openRecord = $stmt_open->get_result()->fetch_assoc();
            $stmt_open->close();


            // 2. Perform the EXPLICIT action based on the validation checks
            if ($action === 'time_in') {
                // NEW: First, check if attendance is already completed for the day.
                if ($completedRecord) {
                    $_SESSION['message'] = "ERROR: {$employeeName} has already timed in and out for today.";
                    $_SESSION['message_type'] = 'error';
                }
                // Then, check if they are currently timed in.
                elseif ($openRecord) {
                    $_SESSION['message'] = "ERROR: {$employeeName} is already timed in today.";
                    $_SESSION['message_type'] = 'error';
                } 
                // If both checks pass, they are clear to time in.
                else {
                    $stmt_insert = $conn->prepare("INSERT INTO tblattendance (AccountID, TimeIn, AttendanceDate) VALUES (?, ?, ?)");
                    $stmt_insert->bind_param("iss", $accountId, $now, $today);
                    if ($stmt_insert->execute()) {
                        $_SESSION['message'] = "SUCCESS: {$employeeName} timed IN successfully.";
                        $_SESSION['message_type'] = 'success';
                    }
                    $stmt_insert->close();
                }
            } elseif ($action === 'time_out') {
                if (!$openRecord) {
                    // This check is sufficient for time_out. A user can't time out if they haven't timed in.
                    $_SESSION['message'] = "ERROR: {$employeeName} must time in before timing out.";
                    $_SESSION['message_type'] = 'error';
                } else {
                    $stmt_update = $conn->prepare("UPDATE tblattendance SET TimeOut = ? WHERE AttendanceID = ?");
                    $stmt_update->bind_param("si", $now, $openRecord['AttendanceID']);
                    if ($stmt_update->execute()) {
                        $_SESSION['message'] = "SUCCESS: {$employeeName} timed OUT successfully.";
                        $_SESSION['message_type'] = 'success';
                    }
                    $stmt_update->close();
                }
            }
        } else {
            $_SESSION['message'] = "ERROR: NFC Card / ID not recognized.";
            $_SESSION['message_type'] = 'error';
        }
        $stmt->close();
        
        header("Location: attendance.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Employee Attendance</title>

    <link rel="stylesheet" href="styles/attendance.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="attendance-container">
        <h2>NFC Attendance Tracker</h2>
        <p class="time-display" id="liveClock"></p>

        <!-- Display result messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="session-message <?php echo $_SESSION['message_type'] ?? ''; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: User chooses an action -->
        <p><strong>Please select an action first:</strong></p>
        <div class="action-selector">
            <button id="timeInBtn" class="button time-in-btn">Time In</button>
            <button id="timeOutBtn" class="button time-out-btn">Time Out</button>
        </div>

        <hr>

        <!-- Step 2: Visual feedback for the user -->
        <div class="scan-area">
            <h3 id="scanPrompt">Waiting for action...</h3>
            <div class="nfc-icon-container">
                <!-- You can use an actual NFC icon image or SVG here -->
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-nfc" viewBox="0 0 16 16"><path d="M4.835 4.336a3.493 3.493 0 0 1 3.493 3.493c0 .265-.03.522-.087.769l-1.332 1.332a.5.5 0 0 1-.707 0l-.854-.853a.5.5 0 0 1 0-.708l.854-.854a1.494 1.494 0 0 0-2.36-.002l-.853.853a.5.5 0 0 1-.708 0L.165 6.033a.5.5 0 0 1 0-.707l.853-.853A3.493 3.493 0 0 1 4.835 4.336m9.33 3.229a3.493 3.493 0 0 1-2.681 3.23l-1.332-1.332a.5.5 0 0 0-.707 0l-.854.854a.5.5 0 0 0 0 .707l.854.854c.238.238.54.41.87.505l.002.001a1.494 1.494 0 0 0 2.36-.002l.853-.853a.5.5 0 0 0 0-.707l-2.02-2.02a.5.5 0 0 0-.707 0l-.854.854a.5.5 0 0 0 0 .708l1.332 1.332a3.493 3.493 0 0 1-3.23 2.681H4.507a3.493 3.493 0 0 1-3.23-2.681L0 7.565v.866h.001a3.493 3.493 0 0 1 2.682-3.23L4.015 6.53a.5.5 0 0 0 .707 0l.854-.854a.5.5 0 0 0 0-.707l-.854-.854a1.494 1.494 0 0 0-2.36.002L1.51 4.97a.5.5 0 0 0 0 .707l2.02 2.02a.5.5 0 0 0 .707 0l.854-.854a.5.5 0 0 0 0-.708L3.78 4.804A3.493 3.493 0 0 1 6.953 2.122h2.094a3.493 3.493 0 0 1 3.23 2.682L16 6.134v.866zM11.507.5h-7A3.5 3.5 0 0 0 1 4v8a3.5 3.5 0 0 0 3.5 3.5h7A3.5 3.5 0 0 0 15 12V4A3.5 3.5 0 0 0 11.507.5M2 4a2.5 2.5 0 0 1 2.5-2.5h7A2.5 2.5 0 0 1 14 4v8a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 12z"/></svg>
            </div>
        </div>

        <!-- This form is now completely managed by JavaScript and can be hidden -->
        <form method="POST" action="attendance.php" id="nfcForm" style="display: none;">
            <input type="hidden" name="action" id="actionInput">
            <input type="hidden" name="ic_code" id="nfcInput">
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- DOM Elements ---
            const liveClock = document.getElementById('liveClock');
            const timeInBtn = document.getElementById('timeInBtn');
            const timeOutBtn = document.getElementById('timeOutBtn');
            const nfcForm = document.getElementById('nfcForm');
            const actionInput = document.getElementById('actionInput');
            const nfcInput = document.getElementById('nfcInput');
            const scanPrompt = document.getElementById('scanPrompt');
            const scanArea = document.querySelector('.scan-area');
            const sessionMessage = document.querySelector('.session-message');

            // --- State Variables ---
            let nfcBuffer = '';
            let bufferClearTimer = null;
            let currentAction = null; // 'time_in' or 'time_out'

            // --- Clock Logic ---
            function updateClock() {
                if (liveClock) {
                    liveClock.textContent = new Date().toLocaleTimeString('en-US');
                }
            }
            updateClock();
            setInterval(updateClock, 1000);

            // --- UI Update Logic ---
            function setActiveAction(action, promptText) {
                currentAction = action;
                scanPrompt.textContent = promptText;
                scanArea.classList.add('ready');

                // Update button styles
                timeInBtn.classList.toggle('active', action === 'time_in');
                timeOutBtn.classList.toggle('active', action === 'time_out');
            }

            // --- Button Event Listeners ---
            timeInBtn.addEventListener('click', () => {
                setActiveAction('time_in', 'Ready: Scan Card to TIME IN');
            });

            timeOutBtn.addEventListener('click', () => {
                setActiveAction('time_out', 'Ready: Scan Card to TIME OUT');
            });

            // --- Global Keyboard Listener for NFC Reader ---
            document.addEventListener('keydown', (event) => {
                // Step 1: Immediately check if we are in an active scanning mode.
                // If not, do nothing and let the browser behave normally.
                if (!currentAction) {
                    return;
                }

                // Step 2: Identify if the key is part of our NFC input.
                // This includes single characters (letters, numbers, symbols) and the "Enter" key.
                const isNfcCharacter = event.key.length === 1;
                const isEnterKey = event.key === 'Enter';

                if (isNfcCharacter || isEnterKey) {
                    // Step 3: THIS IS THE FIX.
                    // We prevent the browser's default action for this key.
                    // This stops it from switching tabs, opening menus, etc.
                    event.preventDefault();
                } else {
                    // If it's another key (like F5, Tab, Shift), let the browser handle it.
                    return;
                }

                // --- The rest of the logic remains the same ---

                // Reset the buffer timeout on each keypress
                clearTimeout(bufferClearTimer);
                bufferClearTimer = setTimeout(() => { nfcBuffer = ''; }, 500);

                if (isEnterKey) {
                    if (nfcBuffer.length > 0) {
                        // We have a complete ID, submit the form
                        actionInput.value = currentAction;
                        nfcInput.value = nfcBuffer;
                        nfcForm.submit();
                    }
                    nfcBuffer = ''; // Clear buffer after processing
                } else if (isNfcCharacter) {
                    // Append the character to our buffer
                    nfcBuffer += event.key;
                }
            });

            // --- Optional: Message Auto-hide ---
            if (sessionMessage) {
                setTimeout(() => {
                    sessionMessage.style.transition = 'opacity 0.5s';
                    sessionMessage.style.opacity = '0';
                    setTimeout(() => sessionMessage.remove(), 500);
                }, 5000);
            }
        });
    </script>

</body>
</html>