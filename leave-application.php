<?php

include('auth.php');

require_user();

include('sqlconnect.php');

if (!isset($_SESSION['AccountID'])) {
    header('Location: login.php');
    exit();
}

$allLeaves = [];
$chartDataJSON = '[]';
$taskStatusDataJSON = '[]';

$currentUserID = $_SESSION['AccountID'];

$currentUserFullName = '';
$sickLeaveBalance = 0;
$vacationLeaveBalance = 0;

$detailsSql = "SELECT 
                    pi.FirstName, 
                    pi.LastName,
                    e.SickLeaveBalance,
                    e.VacationLeaveBalance
                FROM tblaccounts a
                JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
                JOIN tblpersonalinfo pi ON e.PersonalID = pi.PersonalID
                WHERE a.AccountID = ?";

if ($detailsStmt = $conn->prepare($detailsSql)) {
    $detailsStmt->bind_param("i", $currentUserID);
    $detailsStmt->execute();
    $detailsResult = $detailsStmt->get_result();
    if ($user = $detailsResult->fetch_assoc()) {
        $currentUserFullName = htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']);
        $sickLeaveBalance = $user['SickLeaveBalance'];
        $vacationLeaveBalance = $user['VacationLeaveBalance'];
    }
    $detailsStmt->close();
}

$sql = "SELECT LeaveID, AccountID, ScheduledLeave, ScheduledReturn, Reason, Remarks, LeaveStatus 
        FROM tblleaveform 
        WHERE AccountID = ?
        ORDER BY ScheduledLeave ASC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $currentUserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $allLeaves = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/leave-application.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>Leave Application | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/leave-application.js?v=<?php echo time(); ?>"></script>

    <script>
        // These variables are now safely initialized at the top of the PHP script
        const employeeDistributionData = <?php echo $chartDataJSON; ?>;
        const taskStatusData = <?php echo $taskStatusDataJSON; ?>;
    </script>
</head>
<body>
    <div class="app-container">
        
        <?php include('toast-message.php'); ?>
        <?php include('user-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                
                <div class="panels-container">
                    <div class="top-panels">
                        <div class="top-left-panels">
                            <div class="main-title">
                                <h3>Vacations and Leaves</h3>
                                <p>Your Personal Leave Planner</p>
                            </div>
                            <div class="leave-count-panel">
                                <div class="left-container">
                                    <h4>Few Clicks <br>To your Next Trip!</h4>
                                    <p>Submit a Leave Application <br>Now!</p>
                                </div>
                                <div class="sick-vacation-count">
                                    <div class="text-container">
                                        <h1><?php echo str_pad((float)$sickLeaveBalance, 2, '0', STR_PAD_LEFT); ?></h1>
                                        <p>Remaining Sick Leaves</p>
                                    </div>

                                    <div class="text-container">
                                        <h1><?php echo str_pad((float)$vacationLeaveBalance, 2, '0', STR_PAD_LEFT); ?></h1>
                                        <p>Remaining Vacation Leaves</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="calendar-panel">
                            <div class="calendar-header">
                                <h3 class="calendar-title" id="calendar-title">Month</h3>
                            </div>
                            <div class="calendar-body">
                                <div class="calendar-days">
                                    <p>Mon</p>
                                    <p>Tue</p>
                                    <p>Wed</p>
                                    <p>Thu</p>
                                    <p>Fri</p>
                                    <p>Sat</p>
                                    <p>Sun</p>
                                </div>
                                <div id="calendar-grid" class="calendar-grid"></div>
                            </div>
                        </div>
                    </div>
    
                    <div class="bottom-panels">
                        <div class="leave-list-panel">
                            <div class="leave-list-header">
                                <h4>Vacations and Leaves</h4>
                                <div class="add-button-container">
                                    <button>New +</button>
                                </div>
                            </div>
                            <hr>
                            <div class="leave-list-body"> 
                                <div class="leave-list-view-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Start</th>
                                                <th>Until</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($allLeaves)): ?>
                                                <tr>
                                                    <td colspan="5" style="text-align: center;">You have not requested any leaves.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($allLeaves as $leave): ?>
                                                    <tr class="leave-row"
                                                        data-leave-id="<?= htmlspecialchars($leave['LeaveID']) ?>"
                                                        data-leave-type="<?= htmlspecialchars($leave['Reason']) ?>"
                                                        data-start-date="<?= htmlspecialchars($leave['ScheduledLeave']) ?>"
                                                        data-end-date="<?= htmlspecialchars($leave['ScheduledReturn']) ?>"
                                                        data-status="<?= htmlspecialchars($leave['LeaveStatus']) ?>"
                                                        data-remarks="<?= htmlspecialchars($leave['Remarks']) ?>">
                                                        
                                                        <td class="leave-reason-cell"><?= htmlspecialchars($leave['Reason']) ?></td>
                                                        <td><?= htmlspecialchars(date('F d, Y', strtotime($leave['ScheduledLeave']))) ?></td>
                                                        <td><?= htmlspecialchars(date('F d, Y', strtotime($leave['ScheduledReturn']))) ?></td>
                                                        <td>
                                                            <span class="status-tag-list <?= strtolower(htmlspecialchars(str_replace(' ', '-', $leave['LeaveStatus']))) ?>">
                                                                <?= htmlspecialchars($leave['LeaveStatus']) ?>
                                                            </span>
                                                        </td>
                                                        <td><button class="btn-expand">Expand</button></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <div id="newLeaveApplicationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">New Application</h2>
                <span class="close-btn">&times;</span>
            </div>
            <form id="leaveApplicationForm" action="handlers/leave-handler.php" method="POST">
                
                <!-- This hidden input would store the ID of the logged-in employee -->
                <input type="hidden" id="accountID" name="accountID" value="<?php echo $currentUserID; ?>">
                
                <input type="hidden" id="leaveID" name="leaveID" value="">

                <div class="form-group">
                    <label for="employeeName">Employee Name</label>
                    <!-- This field is auto-filled and uneditable as per the mockup -->
                <input type="text" id="employeeName" name="employeeName" value="<?php echo $currentUserFullName; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="leaveType">Leave Type</label>
                    <select id="leaveType" name="leaveType" required>
                        <option value="" disabled selected>Select a leave type...</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Vacation Leave">Vacation Leave</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="startDate">Start</label>
                        <input type="date" id="startDate" name="startDate" required>
                    </div>
                    <div class="form-group">
                        <label for="endDate">End</label>
                        <input type="date" id="endDate" name="endDate" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="leaveStatus">Status</label>
                    <input type="text" id="leaveStatus" name="leaveStatus" value="Pending" readonly>
                </div>
                
                <div class="form-group">
                    <label for="leaveReason">Reason/Remarks</label>
                    <textarea id="leaveReason" name="leaveReason" rows="4" placeholder="Please provide a brief reason for your leave..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancelButton" class="btn-cancel" style="display: none;">Cancel Application</button>
                    <button type="submit" id="submitButton" class="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <div id="calendarModal" class="modal-left">
        <div class="modal-content-left">
            <div class="calendar-panel">
                <div class="calendar-header">
                    <button class="month-nav-btn" id="prev-month-btn"> 
                        <svg width="12" height="14" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.312472 6.03749L10.625 0.0837383C10.72 0.028883 10.8278 4.76837e-06 10.9375 3.8147e-06C11.0472 3.8147e-06 11.155 0.0288811 11.25 0.0837345C11.345 0.138588 11.4239 0.217484 11.4787 0.312493C11.5336 0.407503 11.5625 0.51528 11.5625 0.624989V12.5325C11.5625 12.6422 11.5336 12.75 11.4787 12.845C11.4239 12.94 11.345 13.0189 11.25 13.0737C11.155 13.1286 11.0472 13.1575 10.9375 13.1575C10.8278 13.1575 10.72 13.1286 10.625 13.0737L0.312472 7.11999C0.217468 7.06513 0.138576 6.98623 0.083726 6.89122C0.0288759 6.79622 0 6.68844 0 6.57874C0 6.46903 0.0288759 6.36126 0.083726 6.26625C0.138576 6.17124 0.217468 6.09234 0.312472 6.03749Z" fill="#E1EBF3"/>
                        </svg>
                    Previous</button>
                    
                    <h3 class="calendar-title" id="modal-calendar-title">Month Year</h3>
                    
                    <button class="month-nav-btn" id="next-month-btn">Next
                        <svg width="12" height="14" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.25 7.11998L0.937528 13.0737C0.842517 13.1286 0.73474 13.1575 0.625031 13.1575C0.515322 13.1575 0.407546 13.1286 0.312534 13.0737C0.217523 13.0189 0.138623 12.94 0.0837669 12.845C0.0289106 12.75 3.05176e-05 12.6422 2.76566e-05 12.5325V0.624984C3.05176e-05 0.515275 0.0289106 0.407499 0.0837669 0.31249C0.138623 0.21748 0.217523 0.138583 0.312534 0.0837301C0.407546 0.0288768 0.515322 -6.96769e-07 0.625031 0C0.73474 6.96794e-07 0.842517 0.0288796 0.937528 0.0837342L11.25 6.03748C11.345 6.09234 11.4239 6.17124 11.4788 6.26625C11.5336 6.36126 11.5625 6.46903 11.5625 6.57873C11.5625 6.68844 11.5336 6.79621 11.4788 6.89122C11.4239 6.98623 11.345 7.06513 11.25 7.11998Z" fill="#E1EBF3"/>
                        </svg>
                    </button>
                </div>
                <div class="calendar-body">
                    <div class="calendar-days">
                        <p>Mon</p>
                        <p>Tue</p>
                        <p>Wed</p>
                        <p>Thu</p>
                        <p>Fri</p>
                        <p>Sat</p>
                        <p>Sun</p>
                    </div>
                    <div id="modal-calendar-grid" class="calendar-grid"></div>
                </div>
            </div>
        </div>
    </div>
    <div id="confirmationModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Confirm Action</h3>
            <p>Are you sure you want to proceed?</p>
            <div class="modal-buttons">
                <button class="btn-cancel">No, Keep it</button>
                <button class="btn-confirm">Yes, Confirm</button>
            </div>
        </div>
    </div>
</body>
</html>