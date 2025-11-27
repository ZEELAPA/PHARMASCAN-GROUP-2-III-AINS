<?php
// archive-task-handler.php

include('../auth.php');
require_admin(); // Ensure only authorized users can archive
include('../sqlconnect.php');

// Initialize response array
$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['taskID']) && isset($_POST['archivedBy'])) {
    
    $taskID = (int)$_POST['taskID'];
    $archivedBy = (int)$_POST['archivedBy'];

    // Use a transaction to ensure either both copy/delete succeed or neither happens
    $conn->begin_transaction();

    try {
        // 1. INSERT task data into the archive table
        $insertSql = "
            INSERT INTO tblagendaarchive (
                AgendaID, AccountID, Task, Date, Deadline, Priority, Status, Remarks, ArchivedBy
            )
            SELECT 
                AgendaID, AccountID, Task, Date, Deadline, Priority, Status, Remarks, ?
            FROM 
                tblagenda 
            WHERE 
                AgendaID = ?
        ";
        
        $stmtInsert = $conn->prepare($insertSql);
        if (!$stmtInsert) {
            throw new Exception("Error preparing insert statement: " . $conn->error);
        }
        $stmtInsert->bind_param("ii", $archivedBy, $taskID);
        
        if (!$stmtInsert->execute()) {
            throw new Exception("Error executing insert: " . $stmtInsert->error);
        }
        $stmtInsert->close();

        // 2. DELETE the task from the original table
        $deleteSql = "DELETE FROM tblagenda WHERE AgendaID = ?";
        
        $stmtDelete = $conn->prepare($deleteSql);
        if (!$stmtDelete) {
            throw new Exception("Error preparing delete statement: " . $conn->error);
        }
        $stmtDelete->bind_param("i", $taskID);

        if (!$stmtDelete->execute()) {
            throw new Exception("Error executing delete: " . $stmtDelete->error);
        }
        $stmtDelete->close();
        
        // If both steps successful, commit the transaction
        $conn->commit();
        $response['success'] = true;

    } catch (Exception $e) {
        // Rollback on any failure
        $conn->rollback();
        $response['message'] = "Database Transaction Failed. " . $e->getMessage();
    }

    $conn->close();

} else {
    $response['message'] = 'Invalid request or missing data.';
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>