<?php
session_start();
require 'sqlconnect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['AccountID'])) {
    $password = $_POST['password'] ?? '';
    $accountID = $_SESSION['AccountID'];

    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password is required']);
        exit;
    }

    // Fetch the hash from the database
    $stmt = $conn->prepare("SELECT Password FROM tblaccounts WHERE AccountID = ?");
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['Password'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Account not found']);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}
?>