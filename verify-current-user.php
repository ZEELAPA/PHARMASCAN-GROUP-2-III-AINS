<?php
/**
 * verify-current-user.php
 * Handles AJAX requests to verify the currently logged-in user's NFC credentials.
 */

// Ensure the response is treated as JSON
header('Content-Type: application/json');

// Start session to access $_SESSION['AccountID']
session_start();

// Include database connection
include('sqlconnect.php');

// 1. Check if user is logged in
if (!isset($_SESSION['AccountID'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Session expired. Please log in again.'
    ]);
    exit();
}

// 2. Check Database Connection
if (!$conn || $conn->connect_error) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed.'
    ]);
    exit();
}

// 3. Get POST data
$inputNfcCode = isset($_POST['nfcCode']) ? trim($_POST['nfcCode']) : '';
$inputNfcPassword = isset($_POST['nfcPassword']) ? trim($_POST['nfcPassword']) : '';

// Basic validation
if (empty($inputNfcCode) || empty($inputNfcPassword)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please provide both NFC Code and Password.'
    ]);
    exit();
}

// 4. Fetch the actual credentials for the current user
$currentAccountID = $_SESSION['AccountID'];

$stmt = $conn->prepare("SELECT ICCode, ICPassword FROM tblaccounts WHERE AccountID = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
    exit();
}

$stmt->bind_param("i", $currentAccountID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Account not found.']);
    exit();
}

$row = $result->fetch_assoc();
$storedCode = $row['ICCode'];
$storedPassword = $row['ICPassword'];

$stmt->close();
$conn->close();

// 5. Compare Credentials
// NOTE: Based on your previous code, these are stored as plain text. 
// If the Code or Password matches exactly, verification passes.

if ($inputNfcCode === $storedCode && $inputNfcPassword === $storedPassword) {
    echo json_encode([
        'success' => true,
        'message' => 'Verification successful.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid NFC Card or 4-Digit Password.'
    ]);
}
?>