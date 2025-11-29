<?php
include('../auth.php');
include('../sqlconnect.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

if (!isset($_SESSION['AccountID'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$accountID = $_SESSION['AccountID'];
$password = $_POST['password'] ?? '';

// Fetch the hash
$stmt = $conn->prepare("SELECT Password FROM tblaccounts WHERE AccountID = ?");
$stmt->bind_param("i", $accountID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['Password'])) {
        // SUCCESS: Set the specific flag for Profile Access
        $_SESSION['ProfileAccess'] = true; 
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Account not found']);
}
$stmt->close();
?>