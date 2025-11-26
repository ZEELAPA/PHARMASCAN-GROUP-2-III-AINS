<?php

include('auth.php');
include('sqlconnect.php');

// --- Pre-flight Checks ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin-dashboard.php");
    exit();
}

if (!isset($_SESSION['AccountID'])) {
    add_toast("You must be logged in to perform this action.", "error");
    header("Location: login.php");
    exit();
}

// --- Main Logic Router ---
$action = $_POST['action'] ?? '';
$accountID = $_SESSION['AccountID'];

switch ($action) {
    case 'save_profile':
        handle_save_profile($conn, $accountID);
        break;
    case 'save_account':
        handle_save_account($conn, $accountID);
        break;
    default:
        add_toast("Invalid action performed.", "error");
        header("Location: admin-profile-settings.php");
        exit();
}

// --- Function to Handle Profile Updates ---
function handle_save_profile($conn, $accountID) {
    // 1. Get data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $gender = $_POST['gender'];
    $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
    $contactNumber = trim($_POST['contactNumber']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // 2. Handle Image Upload
    $newProfilePic = null;
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
        $newProfilePic = upload_profile_picture($_FILES['profilePic']);
    }

    // 3. Validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        add_toast("First Name, Last Name, and Email are required.", "warning");
        header("Location: admin-profile-settings.php");
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        add_toast("Please enter a valid email address.", "warning");
        header("Location: admin-profile-settings.php");
        exit();
    }

    // Check if email is already used
    $stmt_check_email = $conn->prepare("SELECT AccountID FROM tblaccounts WHERE Email = ? AND AccountID != ?");
    $stmt_check_email->bind_param("si", $email, $accountID);
    $stmt_check_email->execute();
    if ($stmt_check_email->get_result()->num_rows > 0) {
        add_toast("This email address is already in use by another account.", "error");
        header("Location: admin-profile-settings.php");
        exit();
    }
    $stmt_check_email->close();

    $conn->begin_transaction();
    try {
        // Step A: Get PersonalID
        $stmt_get_ids = $conn->prepare("SELECT e.PersonalID FROM tblemployees e JOIN tblaccounts a ON e.EmployeeID = a.EmployeeID WHERE a.AccountID = ?");
        $stmt_get_ids->bind_param("i", $accountID);
        $stmt_get_ids->execute();
        $result = $stmt_get_ids->get_result();
        if ($result->num_rows === 0) throw new Exception("Could not find employee record.");
        $personalID = $result->fetch_assoc()['PersonalID'];
        $stmt_get_ids->close();

        // Step B: Update tblpersonalinfo
        if ($newProfilePic) {
            $stmt_personal = $conn->prepare("UPDATE tblpersonalinfo SET FirstName = ?, LastName = ?, Gender = ?, Age = ?, ContactNumber = ?, ProfilePicture = ? WHERE PersonalID = ?");
            $stmt_personal->bind_param("sssissi", $firstName, $lastName, $gender, $age, $contactNumber, $newProfilePic, $personalID);
        } else {
            $stmt_personal = $conn->prepare("UPDATE tblpersonalinfo SET FirstName = ?, LastName = ?, Gender = ?, Age = ?, ContactNumber = ? WHERE PersonalID = ?");
            $stmt_personal->bind_param("sssisi", $firstName, $lastName, $gender, $age, $contactNumber, $personalID);
        }

        if (!$stmt_personal->execute()) throw new Exception($stmt_personal->error);
        $stmt_personal->close();

        // Step C: Update tblaccounts with new email
        $stmt_acc = $conn->prepare("UPDATE tblaccounts SET Email = ? WHERE AccountID = ?");
        $stmt_acc->bind_param("si", $email, $accountID);
        if (!$stmt_acc->execute()) throw new Exception($stmt_acc->error);
        $stmt_acc->close();

        $conn->commit();
        add_toast("Profile details updated successfully!", "success");
    } catch (Exception $e) {
        $conn->rollback();
        add_toast("Error updating profile: " . $e->getMessage(), "error");
    }
    
    header("Location: admin-profile-settings.php");
    exit();
}

// --- Function to Handle Account Security Updates ---
function handle_save_account($conn, $accountID) {
    // 1. Get data
    $username = trim($_POST['username']);
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $nfcPassword = trim($_POST['nfcPassword']);

    // 2. Validation
    if (empty($username)) {
        add_toast("Username cannot be empty.", "warning");
        header("Location: admin-account-settings.php");
        exit();
    }
    if (!empty($nfcPassword) && !preg_match('/^\d{4}$/', $nfcPassword)) {
        add_toast("NFC Password must be exactly 4 digits.", "warning");
        header("Location: admin-account-settings.php");
        exit();
    }
    // Check if username is already used
    $stmt_check_user = $conn->prepare("SELECT AccountID FROM tblaccounts WHERE Username = ? AND AccountID != ?");
    $stmt_check_user->bind_param("si", $username, $accountID);
    $stmt_check_user->execute();
    if ($stmt_check_user->get_result()->num_rows > 0) {
        add_toast("This username is already taken.", "error");
        header("Location: admin-account-settings.php");
        exit();
    }
    $stmt_check_user->close();

    // Password change logic
    $passwordUpdateSQL = "";
    $params = [];
    $types = "";

    if (!empty($newPassword)) {
        // --- Password Strength Validation ---
        if (strlen($newPassword) < 8) {
            add_toast("Password must be at least 8 characters long.", "warning");
            header("Location: admin-account-settings.php");
            exit();
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            add_toast("Password must contain at least one uppercase letter.", "warning");
            header("Location: admin-account-settings.php");
            exit();
        }
        if (!preg_match('/[a-z]/', $newPassword)) {
            add_toast("Password must contain at least one lowercase letter.", "warning");
            header("Location: admin-account-settings.php");
            exit();
        }
        if (!preg_match('/[0-9]/', $newPassword)) {
            add_toast("Password must contain at least one number.", "warning");
            header("Location: admin-account-settings.php");
            exit();
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
            add_toast("Password must contain at least one special character.", "warning");
            header("Location: admin-account-settings.php");
            exit();
        }
        // ------------------------------------

        if (empty($oldPassword)) {
            add_toast("Please enter your old password to set a new one.", "warning");
            header("Location: admin-account-settings.php");
            exit();
        }

        // Verify old password
        $stmt_pass = $conn->prepare("SELECT Password FROM tblaccounts WHERE AccountID = ?");
        $stmt_pass->bind_param("i", $accountID);
        $stmt_pass->execute();
        $hashedPasswordInDB = $stmt_pass->get_result()->fetch_assoc()['Password'];
        $stmt_pass->close();

        if (password_verify($oldPassword, $hashedPasswordInDB)) {
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $passwordUpdateSQL = ", Password = ?";
            $params[] = $hashedNewPassword;
            $types .= "s";
        } else {
            add_toast("The old password you entered is incorrect.", "error");
            header("Location: admin-account-settings.php");
            exit();
        }
    }

    // Prepare and execute
    $sql = "UPDATE tblaccounts SET Username = ?, ICPassword = ? $passwordUpdateSQL WHERE AccountID = ?";
    $final_params = array_merge([$username, $nfcPassword], $params, [$accountID]);
    $final_types = "ss" . $types . "i";
    
    $stmt_update = $conn->prepare($sql);
    $stmt_update->bind_param($final_types, ...$final_params);
    
    if ($stmt_update->execute()) {
        add_toast("Account settings updated successfully!", "success");
    } else {
        add_toast("Error updating account settings: " . $stmt_update->error, "error");
    }
    $stmt_update->close();

    header("Location: admin-account-settings.php");
    exit();
}

function upload_profile_picture($file) {
    $targetDir = "images/";
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = $file['name'];
        $fileTmpPath = $file['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = time() . '_' . md5($fileName) . '.' . $fileExtension;
            $destPath = $targetDir . $newFileName;
            if(move_uploaded_file($fileTmpPath, $destPath)) {
                return $newFileName;
            }
        }
    }
    return null;
}
?>