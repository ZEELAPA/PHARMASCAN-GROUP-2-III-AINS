<?php
// 1. Include the functions file at the top

require 'auth.php'; 
require 'sqlconnect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $taskID = $_POST['taskID'];
    $taskName = trim($_POST['taskName']);
    $accountID = $_POST['assignEmployee'];
    $deadline = $_POST['taskDeadline'];
    $priority = $_POST['taskPriority'];
    $status = $_POST['taskStatus'];
    $remarks = trim($_POST['taskRemarks']);

    if (empty($taskID)) {
        add_toast('Error: Task ID is missing.', 'error');
        header("Location: user-dashboard.php");
        exit();
    }

    $sql = "UPDATE tblagenda SET Task = ?, AccountID = ?, Deadline = ?, Priority = ?, Status = ?, Remarks = ? WHERE AgendaID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sissssi", $taskName, $accountID, $deadline, $priority, $status, $remarks, $taskID);

        if ($stmt->execute()) {
            // 2. THIS IS THE CHANGE: Add a success message to the session
            add_toast('Task updated successfully!', 'success');
            
            // 3. Redirect without the URL parameter
            header("Location: user-dashboard.php");
            exit();
        } else {
            add_toast('Error: Could not update the task.', 'error');
            header("Location: user-dashboard.php");
            exit();
        }
        
        $stmt->close();
    } else {
        add_toast('Error preparing the database request.', 'error');
        header("Location: user-dashboard.php");
        exit();
    }

    $conn->close();

} else {
    header("Location: user-dashboard.php");
    exit();
}
?>