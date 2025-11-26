<?php
// 1. Include the files that start the session and define add_toast()
// NOTE: Your session_start() is in auth.php, so we don't need it here again.
require_once 'auth.php';
require_once 'sqlconnect.php';

// 2. Check if the user is logged in is already handled by auth.php/require_user
// but an explicit check here is good practice.
if (!isset($_SESSION['AccountID'])) {
    header('Location: login.php');
    exit();
}

// 3. Ensure the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Sanitize and retrieve POST data
    $accountID = $_POST['accountID'];
    $leaveType = $_POST['leaveType'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $remarks = $_POST['leaveReason'];
    $status = 'Pending';

    if ($accountID != $_SESSION['AccountID']) {
        add_toast('Authentication error occurred.', 'error');
        header('Location: leave-application.php');
        exit();
    }
    
    if (empty($accountID) || empty($leaveType) || empty($startDate) || empty($endDate)) {
        add_toast('Please fill out all required fields.', 'error');
        header('Location: leave-application.php');
        exit();
    }
    
    // Check leave balance
    if ($leaveType === 'Sick Leave' || $leaveType === 'Vacation Leave') {
        $startDateObj = new DateTime($startDate);
        $endDateObj = new DateTime($endDate);
        $leaveDuration = $startDateObj->diff($endDateObj)->days + 1;

        $balanceSql = "SELECT e.SickLeaveBalance, e.VacationLeaveBalance FROM tblemployees e JOIN tblaccounts a ON e.EmployeeID = a.EmployeeID WHERE a.AccountID = ?";
        $balanceStmt = $conn->prepare($balanceSql);
        $balanceStmt->bind_param("i", $accountID);
        $balanceStmt->execute();
        $balances = $balanceStmt->get_result()->fetch_assoc();
        $balanceStmt->close();

        if ($leaveType === 'Sick Leave' && $leaveDuration > $balances['SickLeaveBalance']) {
            add_toast('Insufficient sick leave balance.', 'error');
            header('Location: leave-application.php');
            exit();
        }
        if ($leaveType === 'Vacation Leave' && $leaveDuration > $balances['VacationLeaveBalance']) {
            add_toast('Insufficient vacation leave balance.', 'error');
            header('Location: leave-application.php');
            exit();
        }
    }

    $sql = "INSERT INTO tblleaveform (AccountID, ScheduledLeave, ScheduledReturn, Reason, Remarks, LeaveStatus) VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isssss", $accountID, $startDate, $endDate, $leaveType, $remarks, $status);

        if ($stmt->execute()) {
            // THIS IS THE KEY CHANGE for the new toast type
            add_toast('Application submitted successfully!', 'info');
        } else {
            add_toast('Database error: Could not submit application.', 'error');
        }
        $stmt->close();
    } else {
        add_toast('Database error: Could not prepare request.', 'error');
    }

    $conn->close();
    // Redirect cleanly after adding the toast
    header('Location: leave-application.php');
    exit();

} else {
    header('Location: leave-application.php');
    exit();
}
?>