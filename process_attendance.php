<?php
session_start();
include('sqlconnect.php'); // Make sure this path is correct

// Set the content type to JSON
header('Content-Type: application/json');

// Initialize the response array
$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred.'
];

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ic_code'], $_POST['action'])) {
    $icCode = $_POST['ic_code'];
    $action = $_POST['action'];
    $now = date('Y-m-d H:i:s');

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare(
            "SELECT a.AccountID, p.FirstName, p.LastName 
            FROM tblaccounts a
            JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
            JOIN tblpersonalinfo p ON e.PersonalID = p.PersonalID
            WHERE a.ICCode = ?"
        );
        $stmt->bind_param("s", $icCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($employeeData = $result->fetch_assoc()) {
            $accountId = $employeeData['AccountID'];
            $employeeName = htmlspecialchars($employeeData['FirstName'] . ' ' . $employeeData['LastName']);

            $stmt_open = $conn->prepare("SELECT AttendanceID FROM tblattendance WHERE AccountID = ? AND AttendanceDate = ? AND TimeOut IS NULL");
            $stmt_open->bind_param("is", $accountId, $today);
            $stmt_open->execute();
            $openRecord = $stmt_open->get_result()->fetch_assoc();
            $stmt_open->close();
            
            if ($action === 'time_in') {
                $stmt_completed = $conn->prepare("SELECT AttendanceID FROM tblattendance WHERE AccountID = ? AND AttendanceDate = ? AND TimeOut IS NOT NULL");
                $stmt_completed->bind_param("is", $accountId, $today);
                $stmt_completed->execute();
                $completedRecord = $stmt_completed->get_result()->fetch_assoc();
                $stmt_completed->close();
                
                if ($completedRecord) {
                    $response['message'] = "ERROR: {$employeeName} has already completed attendance for today.";
                } elseif ($openRecord) {
                    $response['message'] = "ERROR: {$employeeName} is already timed in.";
                } else {
                    $stmt_insert = $conn->prepare("INSERT INTO tblattendance (AccountID, TimeIn, AttendanceDate) VALUES (?, ?, ?)");
                    $stmt_insert->bind_param("iss", $accountId, $now, $today);
                    if ($stmt_insert->execute()) {
                        $response['status'] = 'success';
                        $response['message'] = "SUCCESS: {$employeeName} timed IN successfully.";
                        $response['newRecord'] = [
                            'fullName' => strtoupper($employeeName),
                            'eventType' => 'Time In',
                            'eventTime' => date('h:i A', strtotime($now))
                        ];
                    }
                    $stmt_insert->close();
                }
            } elseif ($action === 'time_out') {
                if (!$openRecord) {
                    $response['message'] = "ERROR: {$employeeName} must time in before timing out.";
                } else {
                    $stmt_update = $conn->prepare("UPDATE tblattendance SET TimeOut = ? WHERE AttendanceID = ?");
                    $stmt_update->bind_param("si", $now, $openRecord['AttendanceID']);
                    if ($stmt_update->execute()) {
                        $response['status'] = 'success';
                        $response['message'] = "SUCCESS: {$employeeName} timed OUT successfully.";
                        $response['newRecord'] = [
                            'fullName' => strtoupper($employeeName),
                            'eventType' => 'Time Out',
                            'eventTime' => date('h:i A', strtotime($now))
                        ];
                    }
                    $stmt_update->close();
                }
            }
        } else {
            $response['message'] = "ERROR: NFC Card / ID not recognized.";
        }
        $stmt->close();
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Database Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request.';
}

// Echo the response as a JSON string
echo json_encode($response);
exit();
?>