<?php
    // 1. Include the necessary files
    require_once '../auth.php';
    require_once '../sqlconnect.php';

    // ... authentication checks are already good ...

    if (isset($_GET['leaveID']) && isset($_GET['accountID'])) {
        $leaveID = (int)$_GET['leaveID'];
        $requestingAccountID = (int)$_GET['accountID'];
        
        if ($requestingAccountID !== $_SESSION['AccountID']) {
            add_toast('Unauthorized Action.', 'error');
            header('Location: ../leave-application.php');
            exit();
        }
        
        // ... DB connection is handled by sqlconnect.php ...

        $conn->begin_transaction();
        try {
            // ... your transaction logic is great and remains the same ...
            $checkSql = "SELECT LeaveStatus FROM tblleaveform WHERE LeaveID = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("i", $leaveID);
            $checkStmt->execute();
            $leaveData = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if (!$leaveData || $leaveData['LeaveStatus'] !== 'Pending') {
                throw new Exception("Only Pending applications can be cancelled.");
            }
            
            // Your archive and delete logic is perfect
            $archiveSql = "INSERT INTO tblleaveformarchive (LeaveID, AccountID, ScheduledLeave, ScheduledReturn, Reason, LeaveStatus, Remarks, CreatedAt, ArchivedBy, ArchivedAt) SELECT LeaveID, AccountID, ScheduledLeave, ScheduledReturn, Reason, 'Cancelled', Remarks, CreatedAt, ?, NOW() FROM tblleaveform WHERE LeaveID = ?";
            $archiveStmt = $conn->prepare($archiveSql);
            $archiveStmt->bind_param("ii", $_SESSION['AccountID'], $leaveID);
            $archiveStmt->execute();
            $archiveStmt->close();

            $deleteSql = "DELETE FROM tblleaveform WHERE LeaveID = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $leaveID);
            $deleteStmt->execute();
            $deleteStmt->close();
            
            $conn->commit();
            
            // SUCCESS: Use a success toast
            add_toast('Leave application has been cancelled.', 'success');
            
        } catch (Exception $e) {
            $conn->rollback();
            // ERROR: Use an error toast
            add_toast('Error: ' . $e->getMessage(), 'error');
        }
        
        $conn->close();
        // Redirect cleanly
        header('Location: ../leave-application.php');
        exit();

    } else {
        add_toast('Missing required parameters.', 'error');
        header('Location: ../leave-application.php');
        exit();
    }
?>