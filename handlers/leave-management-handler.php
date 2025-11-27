<?php
session_start();
require_once '../sqlconnect.php';
require_once '../auth.php'; // Contains add_toast()
require_once '../mailer.php'; 

require_admin();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ../leave-management.php');
    exit();
}

// =========================================================================
//  LOGIC FOR APPROVING or DECLINING an EXISTING Leave Application
// =========================================================================
if (isset($_POST['action'], $_POST['leaveID'], $_POST['accountID'])) {

    $action = $_POST['action'];
    $leaveID = (int)$_POST['leaveID'];
    $accountID = (int)$_POST['accountID'];
    $newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';

    if ($newStatus !== 'Approved' && $newStatus !== 'Rejected') {
        add_toast("Invalid action specified.", "error");
        header('Location: ../leave-management.php');
        exit();
    }

    $conn->begin_transaction();
    try {
        // 1. Update the leave status
        $statusSql = "UPDATE tblleaveform SET LeaveStatus = ? WHERE LeaveID = ?";
        $statusStmt = $conn->prepare($statusSql);
        $statusStmt->bind_param("si", $newStatus, $leaveID);
        if (!$statusStmt->execute()) throw new Exception("Failed to update leave status.");
        $statusStmt->close();

        // 2. If approved, deduct leave balance AND SEND EMAIL
        if ($action === 'approve') {
            // Get Leave Details
            $leaveSql = "SELECT ScheduledLeave, ScheduledReturn, Reason FROM tblleaveform WHERE LeaveID = ?";
            $leaveStmt = $conn->prepare($leaveSql);
            $leaveStmt->bind_param("i", $leaveID);
            $leaveStmt->execute();
            $leaveResult = $leaveStmt->get_result()->fetch_assoc();
            $leaveStmt->close();
            
            $duration = (new DateTime($leaveResult['ScheduledLeave']))->diff(new DateTime($leaveResult['ScheduledReturn']))->days + 1;
            $balanceColumn = ($leaveResult['Reason'] === 'Sick Leave') ? 'SickLeaveBalance' : 'VacationLeaveBalance';
            
            // Deduct Balance
            $balanceSql = "UPDATE tblemployees e JOIN tblaccounts a ON e.EmployeeID = a.EmployeeID SET e.{$balanceColumn} = e.{$balanceColumn} - ? WHERE a.AccountID = ?";
            $balanceStmt = $conn->prepare($balanceSql);
            $balanceStmt->bind_param("di", $duration, $accountID);
            if (!$balanceStmt->execute()) throw new Exception("Failed to update leave balance.");
            $balanceStmt->close();

            // --- EMAIL LOGIC START ---
            $userSql = "SELECT a.Email, pi.FirstName, e.{$balanceColumn} as NewBalance
                        FROM tblaccounts a
                        JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
                        JOIN tblpersonalinfo pi ON e.PersonalID = pi.PersonalID
                        WHERE a.AccountID = ?";
            $userStmt = $conn->prepare($userSql);
            $userStmt->bind_param("i", $accountID);
            $userStmt->execute();
            $userData = $userStmt->get_result()->fetch_assoc();
            $userStmt->close();

            if ($userData) {
                $toEmail = $userData['Email'];
                $toName = $userData['FirstName'];
                $leaveType = $leaveResult['Reason'];
                $startDate = date("F d, Y", strtotime($leaveResult['ScheduledLeave']));
                $endDate = date("F d, Y", strtotime($leaveResult['ScheduledReturn']));
                $newBalance = $userData['NewBalance'];

                $subject = "Leave Application Approved";
                $messageBody = "
                <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <p>Hi $toName,</p>
                    <p>This is a notification to let you know that your Leave Application has been approved.</p>
                    <p><strong>Leave Application Details:</strong><br>
                    Status: <strong>Approved</strong><br>
                    Leave Type: <strong>$leaveType</strong><br>
                    Start Date: <strong>$startDate</strong><br>
                    End Date: <strong>$endDate</strong></p>
                    <p>These dates have been added to the company calendar. Your remaining <strong>$leaveType</strong> balance is now <strong>$newBalance days</strong>.</p>
                    <p>We hope you enjoy your time off,<br>
                    <strong>PharmaPlus Team</strong></p>
                </div>";

                send_email($toEmail, $toName, $subject, $messageBody);
            }
            // --- EMAIL LOGIC END ---
        }

        $conn->commit();
        add_toast("Leave status updated successfully.", "success");
        header('Location: ../leave-management.php');
    } catch (Exception $e) {
        $conn->rollback();
        add_toast("Error: " . $e->getMessage(), "error");
        header('Location: ../leave-management.php');
    }
    $conn->close();
    exit();
}

// =========================================================================
//  LOGIC FOR CREATING a NEW Leave Application (as Admin)
// =========================================================================
elseif (isset($_POST['accountID'], $_POST['leaveType'], $_POST['startDate'], $_POST['endDate'])) {

    $accountID = (int)$_POST['accountID'];
    $leaveType = $_POST['leaveType'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $remarks = $_POST['leaveReason'] ?? '';
    $status = 'Approved'; 

    // --- Server-side Validation ---
    if (empty($accountID) || empty($leaveType) || empty($startDate) || empty($endDate)) {
        add_toast("Required fields are missing.", "error");
        header('Location: ../leave-management.php');
        exit();
    }
    
    $duration = (new DateTime($startDate))->diff(new DateTime($endDate))->days + 1;

    // --- Balance Check Validation ---
    $balanceSql = "SELECT e.SickLeaveBalance, e.VacationLeaveBalance FROM tblemployees e JOIN tblaccounts a ON e.EmployeeID = a.EmployeeID WHERE a.AccountID = ?";
    $balanceStmt = $conn->prepare($balanceSql);
    $balanceStmt->bind_param("i", $accountID);
    $balanceStmt->execute();
    $balances = $balanceStmt->get_result()->fetch_assoc();
    $balanceStmt->close();

    if ($leaveType === 'Sick Leave' && $duration > $balances['SickLeaveBalance']) {
        add_toast("Insufficient Sick Leave balance.", "error");
        header('Location: ../leave-management.php');
        exit();
    }
    if ($leaveType === 'Vacation Leave' && $duration > $balances['VacationLeaveBalance']) {
        add_toast("Insufficient Vacation Leave balance.", "error");
        header('Location: ../leave-management.php');
        exit();
    }
    
    // --- Transaction for Insert + Balance Update ---
    $conn->begin_transaction();
    try {
        // 1. Insert the new leave record
        $insertSql = "INSERT INTO tblleaveform (AccountID, ScheduledLeave, ScheduledReturn, Reason, Remarks, LeaveStatus) VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("isssss", $accountID, $startDate, $endDate, $leaveType, $remarks, $status);
        if (!$insertStmt->execute()) throw new Exception("Failed to create leave record.");
        $insertStmt->close();

        // 2. Deduct the leave balance since it's auto-approved
        $balanceColumn = ($leaveType === 'Sick Leave') ? 'SickLeaveBalance' : 'VacationLeaveBalance';
        $updateSql = "UPDATE tblemployees e JOIN tblaccounts a ON e.EmployeeID = a.EmployeeID SET e.{$balanceColumn} = e.{$balanceColumn} - ? WHERE a.AccountID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("di", $duration, $accountID);
        if (!$updateStmt->execute()) throw new Exception("Failed to update leave balance after creation.");
        $updateStmt->close();

        // --- EMAIL LOGIC START ---
        $userSql = "SELECT a.Email, pi.FirstName FROM tblaccounts a 
                    JOIN tblemployees e ON a.EmployeeID = e.EmployeeID 
                    JOIN tblpersonalinfo pi ON e.PersonalID = pi.PersonalID 
                    WHERE a.AccountID = ?";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bind_param("i", $accountID);
        $userStmt->execute();
        $userData = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();

        if ($userData) {
            $toEmail = $userData['Email'];
            $toName = $userData['FirstName'];
            
            $oldBalance = ($leaveType === 'Sick Leave') ? $balances['SickLeaveBalance'] : $balances['VacationLeaveBalance'];
            $newBalance = $oldBalance - $duration;
            $fmtStartDate = date("F d, Y", strtotime($startDate));
            $fmtEndDate = date("F d, Y", strtotime($endDate));

            $subject = "Leave Application Approved";
            $messageBody = "
            <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                <p>Hi $toName,</p>
                <p>This is a notification to let you know that your Leave Application has been approved.</p>
                <p><strong>Leave Application Details:</strong><br>
                Status: <strong>Approved</strong><br>
                Leave Type: <strong>$leaveType</strong><br>
                Start Date: <strong>$fmtStartDate</strong><br>
                End Date: <strong>$fmtEndDate</strong></p>
                <p>These dates have been added to the company calendar. Your remaining <strong>$leaveType</strong> balance is now <strong>$newBalance days</strong>.</p>
                <p>We hope you enjoy your time off,<br>
                <strong>PharmaPlus Team</strong></p>
            </div>";

            send_email($toEmail, $toName, $subject, $messageBody);
        }
        // --- EMAIL LOGIC END ---

        $conn->commit();
        add_toast("Leave created successfully.", "success");
        header('Location: ../leave-management.php');
    } catch (Exception $e) {
        $conn->rollback();
        add_toast("Error: " . $e->getMessage(), "error");
        header('Location: ../leave-management.php');
    }
    $conn->close();
    exit();
}

elseif (isset($_POST['archive_leave_id'])) {
    
    $leaveID = (int)$_POST['archive_leave_id'];
    $archivedBy = $_SESSION['AccountID'] ?? 0; 

    $conn->begin_transaction();
    try {
        // 1. Copy to archive
        $archiveSql = "INSERT INTO tblleaveformarchive 
                       (LeaveID, AccountID, ScheduledLeave, ScheduledReturn, Reason, LeaveStatus, Remarks, CreatedAt, ArchivedBy, ArchivedAt)
                       SELECT LeaveID, AccountID, ScheduledLeave, ScheduledReturn, Reason, LeaveStatus, Remarks, CreatedAt, ?, NOW()
                       FROM tblleaveform 
                       WHERE LeaveID = ?";
        $archiveStmt = $conn->prepare($archiveSql);
        $archiveStmt->bind_param("ii", $archivedBy, $leaveID);
        if (!$archiveStmt->execute()) throw new Exception("Failed to copy record to archive.");
        $archiveStmt->close();

        // 2. Delete original
        $deleteSql = "DELETE FROM tblleaveform WHERE LeaveID = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $leaveID);
        if (!$deleteStmt->execute()) throw new Exception("Failed to delete original record.");
        $deleteStmt->close();

        $conn->commit();
        add_toast("Leave record archived successfully.", "success");
        header('Location: ../leave-management.php');

    } catch (Exception $e) {
        $conn->rollback();
        add_toast("Archive Error: " . $e->getMessage(), "error");
        header('Location: ../leave-management.php');
    }
    $conn->close();
    exit();
}

else {
    add_toast("Invalid request.", "error");
    header('Location: ../leave-management.php');
    exit();
}
?>