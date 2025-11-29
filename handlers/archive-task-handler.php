<?php
    session_start(); // Important for session based toast
    include('../auth.php');
    require_admin(); 
    include('../sqlconnect.php');

    $response = ['success' => false, 'message' => ''];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['taskID']) && isset($_POST['archivedBy'])) {
        
        $taskID = (int)$_POST['taskID'];
        $archivedBy = (int)$_POST['archivedBy'];

        $conn->begin_transaction();

        try {
            // 1. INSERT
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
            $stmtInsert->bind_param("ii", $archivedBy, $taskID);
            if (!$stmtInsert->execute()) throw new Exception($stmtInsert->error);
            $stmtInsert->close();

            // 2. DELETE
            $deleteSql = "DELETE FROM tblagenda WHERE AgendaID = ?";
            $stmtDelete = $conn->prepare($deleteSql);
            $stmtDelete->bind_param("i", $taskID);
            if (!$stmtDelete->execute()) throw new Exception($stmtDelete->error);
            $stmtDelete->close();
            
            $conn->commit();
            
            // SET TOAST IN SESSION
            add_toast("Task archived successfully.", "success");
            $response['success'] = true;

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = $e->getMessage();
            add_toast("Archiving failed: " . $e->getMessage(), "error");
        }

        $conn->close();

    } else {
        $response['message'] = 'Invalid request.';
        add_toast("Invalid request parameters.", "error");
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
?>