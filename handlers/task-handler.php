<?php
require '../sqlconnect.php';
include '../mailer.php'; // Include the mailer function

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Collect Form Data
    $taskID = $_POST['taskID'] ?? ''; // hidden field for updates (logic below checks this)
    $taskName = trim($_POST['taskName']);
    $accountID = $_POST['assignEmployee'];
    $deadline = $_POST['taskDeadline'];
    $priority = $_POST['taskPriority'];
    $status = $_POST['taskStatus'];
    $remarks = trim($_POST['taskRemarks']);

    // 2. Prepare Date Format for Email (e.g., November 18, 2025)
    $formattedDeadline = date("F d, Y", strtotime($deadline));

    // 3. Logic: Check if this is a NEW task (INSERT) or UPDATE
    // Based on your provided code, you were only doing INSERT. 
    // I will focus on the INSERT logic to trigger the "New Task" email.
    
    if (empty($taskID)) {
        // --- CREATE NEW TASK ---

        $sql = "INSERT INTO tblagenda (Task, AccountID, Date, Deadline, Priority, Status, Remarks) VALUES (?, ?, CURDATE(), ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sissss", $taskName, $accountID, $deadline, $priority, $status, $remarks);

            if ($stmt->execute()) {
                
                // --- EMAIL NOTIFICATION LOGIC START ---
                
                // A. Fetch the assigned Employee's Email and First Name
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

                    // B. Construct the Email Body (Matching your image format)
                    $subject = "New Task Assigned: $taskName";
                    
                    $messageBody = "
                    <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                        <p>Hi <strong>$toName,</strong></p>
                        
                        <p>This is a notification to let you know that a new task has been assigned to you in PharmaScan.</p>
                        
                        <p>Below are the details of the task:</p>
                        
                        <div style='margin-left: 0px;'>
                            Task: <strong>$taskName</strong><br>
                            Priority: <strong>$priority</strong><br>
                            Due Date: <strong>$formattedDeadline</strong><br>
                            Remarks: <strong>$remarks</strong>
                        </div>
                        
                        <p>You may click this link to view the task in the system<br>
                        <a href='http://pharmascan.ct.ws' style='color: #0000EE; text-decoration: underline;'>pharmascan.ct.ws</a></p>
                        
                        <br>
                        <p>Thank you,<br>
                        <strong>PharmaPlus Team</strong></p>
                    </div>";

                    // C. Send the Email
                    send_email($toEmail, $toName, $subject, $messageBody);
                }
                $empStmt->close();
                // --- EMAIL NOTIFICATION LOGIC END ---

                header("Location: ../task-management.php?status=success");
                exit();
            } else {
                echo "Error executing statement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }

    } else {
        // --- UPDATE EXISTING TASK LOGIC (Placeholder if you implement editing later) ---
        // Typically we don't send "New Task" emails on updates, or we send a "Task Updated" email.
        
        $sql = "UPDATE tblagenda SET Task=?, AccountID=?, Deadline=?, Priority=?, Status=?, Remarks=? WHERE AgendaID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissssi", $taskName, $accountID, $deadline, $priority, $status, $remarks, $taskID);
        $stmt->execute();
        $stmt->close();
        
        header("Location: ../task-management.php?status=updated");
        exit();
    }

    $conn->close();

} else {
    header("Location: ../task-management.php");
    exit();
}
?>