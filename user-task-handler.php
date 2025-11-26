<?php
// 1. Include the functions file at the top
require 'auth.php'; 
require 'sqlconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $taskName = trim($_POST['taskName']);
    $accountID = $_POST['assignEmployee'];
    $deadline = $_POST['taskDeadline'];
    $priority = $_POST['taskPriority'];
    $status = $_POST['taskStatus'];
    $remarks = trim($_POST['taskRemarks']);

    $sql = "INSERT INTO tblagenda (Task, AccountID, Date, Deadline, Priority, Status, Remarks) VALUES (?, ?, CURDATE(), ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sissss", $taskName, $accountID, $deadline, $priority, $status, $remarks);

        if ($stmt->execute()) {
            // 2. THIS IS THE CHANGE: Add a success message to the session
            add_toast('Task created successfully!', 'success');
            
            // 3. Redirect without the URL parameter
            header("Location: user-dashboard.php");
            exit();
        } else {
            // (Optional but recommended) Add an error toast on failure
            add_toast('Error: Could not create the task.', 'error');
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