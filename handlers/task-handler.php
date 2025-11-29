<?php
    session_start(); // Ensure session is started for toast
    include('../auth.php'); // Contains add_toast function
    require '../sqlconnect.php';
    include 'mailer.php'; 

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // 1. Collect Form Data
        $taskID = $_POST['taskID'] ?? ''; 
        $taskName = trim($_POST['taskName']);
        $accountID = $_POST['assignEmployee'];
        $deadline = $_POST['taskDeadline'];
        $priority = $_POST['taskPriority'];
        $status = $_POST['taskStatus'];
        $remarks = trim($_POST['taskRemarks']);

        $formattedDeadline = date("F d, Y", strtotime($deadline));

        if (empty($taskID)) {
            // --- CREATE NEW TASK ---
            $sql = "INSERT INTO tblagenda (Task, AccountID, Date, Deadline, Priority, Status, Remarks) VALUES (?, ?, CURDATE(), ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sissss", $taskName, $accountID, $deadline, $priority, $status, $remarks);

                if ($stmt->execute()) {
                    
                    // --- EMAIL NOTIFICATION LOGIC ---
                    $empSql = "SELECT a.Email, p.FirstName 
                               FROM tblaccounts a
                               JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
                               JOIN tblpersonalinfo p ON e.PersonalID = p.PersonalID
                               WHERE a.AccountID = ?";
                    
                    $empStmt = $conn->prepare($empSql);
                    $empStmt->bind_param("i", $accountID);
                    $empStmt->execute();
                    $result = $empStmt->get_result();

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $toEmail = $row['Email'];
                        $toName = $row['FirstName'];

                        $subject = "New Task Assigned: $taskName";
                        $messageBody = "
                        <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                            <p>Hi <strong>$toName,</strong></p>
                            <p>This is a notification to let you know that a new task has been assigned to you in PharmaScan.</p>
                            <div style='margin-left: 0px;'>
                                Task: <strong>$taskName</strong><br>
                                Priority: <strong>$priority</strong><br>
                                Due Date: <strong>$formattedDeadline</strong><br>
                                Remarks: <strong>$remarks</strong>
                            </div>
                            <p>You may click this link to view the task in the system<br>
                            <a href='http://pharmascan.ct.ws' style='color: #0000EE; text-decoration: underline;'>pharmascan.ct.ws</a></p>
                            <br>
                            <p>Thank you,<br><strong>PharmaPlus Team</strong></p>
                        </div>";

                        send_email($toEmail, $toName, $subject, $messageBody);
                    }
                    $empStmt->close();
                    
                    // SUCCESS TOAST
                    add_toast("Task assigned successfully and notification sent!", "success");
                    header("Location: ../task-management.php");
                    exit();
                } else {
                    add_toast("Database Error: " . $stmt->error, "error");
                    header("Location: ../task-management.php");
                    exit();
                }
                $stmt->close();
            } else {
                add_toast("Preparation Error: " . $conn->error, "error");
                header("Location: ../task-management.php");
                exit();
            }

        } else {
            // Fallback if ID exists (though edit-task-handler handles updates usually)
            add_toast("Invalid operation.", "error");
            header("Location: ../task-management.php");
            exit();
        }
        $conn->close();
    } else {
        header("Location: ../task-management.php");
        exit();
    }
?>