<?php
require 'sqlconnect.php'; // Ensure this path is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve all form data, including the hidden taskID
    $taskID = $_POST['taskID'];
    $taskName = trim($_POST['taskName']);
    $accountID = $_POST['assignEmployee'];
    $deadline = $_POST['taskDeadline'];
    $priority = $_POST['taskPriority'];
    $status = $_POST['taskStatus'];
    $remarks = trim($_POST['taskRemarks']);

    // Validate that we have a task ID
    if (empty($taskID)) {
        die("Error: Task ID is missing.");
    }

    // Prepare an UPDATE statement
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
        // Bind parameters. Note the types and the order. The integer ID is last.
        // s - string, i - integer
        $stmt->bind_param("sissssi", $taskName, $accountID, $deadline, $priority, $status, $remarks, $taskID);

        if ($stmt->execute()) {
            // Success: redirect back to the task page
            header("Location: task-management.php?status=updated");
            exit();
        } else {
            echo "Error executing update: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();

} else {
    // Redirect if not a POST request
    header("Location: task-management.php");
    exit();
}
?>