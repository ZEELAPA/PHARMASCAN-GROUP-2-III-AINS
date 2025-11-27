<?php

    include('auth.php');

    require_admin();

    include('sqlconnect.php');

    $sql = "SELECT 
                lf.LeaveID, 
                lf.AccountID, 
                lf.ScheduledLeave, 
                lf.ScheduledReturn, 
                lf.Reason, 
                lf.Remarks, 
                lf.LeaveStatus,
                pi.FirstName,
                pi.LastName
            FROM tblleaveform lf
            JOIN tblaccounts a ON lf.AccountID = a.AccountID
            JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
            JOIN tblpersonalinfo pi ON e.PersonalID = pi.PersonalID
            ORDER BY lf.LeaveStatus = 'Pending' DESC, lf.ScheduledLeave ASC";

    $allLeaves = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();
        $allLeaves = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

    $allEmployees = [];
    $employeeSql = "SELECT 
                        a.AccountID, 
                        pi.FirstName, 
                        pi.LastName,
                        e.SickLeaveBalance,
                        e.VacationLeaveBalance
                    FROM tblaccounts a
                    JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
                    JOIN tblpersonalinfo pi ON e.PersonalID = pi.PersonalID
                    WHERE a.Role = 'User'
                    ORDER BY pi.LastName ASC, pi.FirstName ASC";

    if ($employeeResult = $conn->query($employeeSql)) {
        $allEmployees = $employeeResult->fetch_all(MYSQLI_ASSOC);
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
    <link rel="stylesheet" href="styles/leave-management.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>Leave Management | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/leave-management.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div class="app-container">
        <?php include 'toast-message.php'; ?>
        <?php include('admin-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                
                <div class="panels-container">
                    <div class="main-title">
                        <h3>Vacations and Leaves</h3>
                        <p>Your Personal Leave Planner</p>
                    </div>    
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
                                            <th>Employee Name</th> <!-- New Column -->
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
                                                <td colspan="6" style="text-align: center;">No leave applications found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($allLeaves as $leave): ?>
                                                <tr class="leave-row"
                                                    data-leave-id="<?= htmlspecialchars($leave['LeaveID']) ?>"
                                                    data-account-id="<?= htmlspecialchars($leave['AccountID']) ?>"
                                                    data-employee-name="<?= htmlspecialchars($leave['FirstName'] . ' ' . $leave['LastName']) ?>"
                                                    data-leave-type="<?= htmlspecialchars($leave['Reason']) ?>"
                                                    data-start-date="<?= htmlspecialchars($leave['ScheduledLeave']) ?>"
                                                    data-end-date="<?= htmlspecialchars($leave['ScheduledReturn']) ?>"
                                                    data-status="<?= htmlspecialchars($leave['LeaveStatus']) ?>"
                                                    data-remarks="<?= htmlspecialchars($leave['Remarks']) ?>">
                                                    
                                                    <!-- Display Employee Name -->
                                                    <td><?= htmlspecialchars($leave['FirstName'] . ' ' . $leave['LastName']) ?></td>
                                                    
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
        </main>
    </div>
    <div id="applicationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Leave Details</h2>
                <span class="close-btn">&times;</span>
            </div>
            
            <!-- A single, flexible form for both Creating and Viewing -->
            <form id="leaveApplicationForm" action="handlers/leave-management-handler.php" method="POST">
                
                <div class="form-group">
                    <label for="accountID">Employee Name</label>
                    <!-- This will be a dropdown for new applications -->
                    <select id="accountID" name="accountID" required>
                        <?php foreach ($allEmployees as $employee): ?>
                            <option 
                                value="<?= htmlspecialchars($employee['AccountID']) ?>"
                                data-sick-balance="<?= htmlspecialchars($employee['SickLeaveBalance']) ?>"
                                data-vacation-balance="<?= htmlspecialchars($employee['VacationLeaveBalance']) ?>">
                                <?= htmlspecialchars($employee['LastName'] . ', ' . $employee['FirstName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- This input is for displaying the name when viewing details -->
                    <input type="text" id="employeeNameDisplay" readonly class="read-only-display">
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

                <div class="form-row">
                    <div class="form-group">
                        <label for="leaveStatus">Status</label>
                        <input type="text" id="leaveStatus" name="leaveStatus" value="Pending" readonly>
                    </div>
                    
                    <div class="form-group" id="leaveBalanceGroup">
                        <label for="leaveBalance">Remaining Balance</label>
                        <input type="text" id="leaveBalance" name="leaveBalance" placeholder="N/A" readonly>
                    </div>
                </div>  
                
                <div class="form-group">
                    <label for="leaveReason">Reason/Remarks</label>
                    <textarea id="leaveReason" name="leaveReason" rows="4" placeholder="Admin note for leave..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" id="submitButton" class="btn-submit">Submit Application</button>
                </div>

                <!-- Hidden Form to handle Archiving -->
            </form>
            
            <!-- Action form for APPROVE/DECLINE -->
            <form id="leaveActionForm" action="handlers/leave-management-handler.php" method="POST">
                <input type="hidden" id="actionLeaveID" name="leaveID">
                <input type="hidden" id="actionAccountID" name="accountID">
                <div class="form-actions modal-actions">
                    <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                    <button type="submit" name="action" value="reject" class="btn-decline">Decline</button>
                </div>
            </form>

            <div class="archive-container">
                <button type="button" id="closeModalButton" class="btn-submit">Close</button>
                <button type="button" id="archiveModalButton" class="btn-archive-modal" style="display:none;">Archive Record</button>
            </div>

            <form id="modalArchiveForm" action="handlers/leave-management-handler.php" method="POST">
                <input type="hidden" name="archive_leave_id" id="modalArchiveLeaveID">
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
                    
                    <h3 class="calendar-title" id="calendar-title">Month Year</h3>
                    
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
                    <div id="calendar-grid" class="calendar-grid"></div>
                </div>
            </div>
        </div>
    </div>
    <div id="confirmationModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Confirm Archive</h3>
        <p>Are you sure you want to archive this record?</p>
        <div class="modal-buttons">
            <button class="btn-cancel" id="btnCancelArchive">Cancel</button>
            <button class="btn-confirm" id="btnConfirmArchive">Confirm</button>
        </div>
    </div>
</div>
</body>
</html>