<?php
    session_start();
    include('../auth.php'); // For add_toast
    require '../sqlconnect.php'; 

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $taskID = $_POST['taskID'];
        $taskName = trim($_POST['taskName']);
        $accountID = $_POST['assignEmployee'];
        $deadline = $_POST['taskDeadline'];
        $priority = $_POST['taskPriority'];
        $status = $_POST['taskStatus'];
        $remarks = trim($_POST['taskRemarks']);

        if (empty($taskID)) {
            add_toast("Error: Task ID is missing.", "error");
            header("Location: ../task-management.php");
            exit();
        }

        $sql = "UPDATE tblagenda SET 
                    Task = ?, 
                    AccountID = ?, 
                    Deadline = ?, 
                    Priority = ?, 
                    Status = ?, 
                    Remarks = ? 
                WHERE 
                    AgendaID = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sissssi", $taskName, $accountID, $deadline, $priority, $status, $remarks, $taskID);

            if ($stmt->execute()) {
                // SUCCESS TOAST
                add_toast("Task details updated successfully!", "success");
            } else {
                add_toast("Error updating task: " . $stmt->error, "error");
            }

            $stmt->close();
        } else {
            add_toast("Database preparation error.", "error");
        }

        $conn->close();
        header("Location: ../task-management.php");
        exit();

    } else {
        header("Location: ../task-management.php");
        exit();
    }
?>