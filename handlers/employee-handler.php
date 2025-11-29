<?php
    session_start();
    include('../auth.php');

    include('../sqlconnect.php');
    
    if (!$conn || $conn->connect_error) {
        // A more user-friendly error page should be used in production
        die("Connection failed: " . ($conn ? $conn->connect_error : "Unknown error"));
    }

    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        // If not a POST request, redirect back to the main page
        header("Location: ../user-management.php");
        exit();
    }

    // --- Main Logic Router ---
    // Use the 'action' value from the submitted button to determine the course of action.
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'save_changes':
            handle_save_changes($conn);
            break;
        case 'add_employee':
            handle_add_employee($conn);
            break;
        case 'archive_employee':
            // 1. Get IDs
            $targetEmployeeID = filter_input(INPUT_POST, 'employeeID', FILTER_SANITIZE_NUMBER_INT);
            // Assuming the logged-in admin's ID is stored in session
            $adminID = $_SESSION['AccountID'] ?? 0; 

            if (!$targetEmployeeID) {
                add_toast("Invalid Employee ID.", "error");
                header("Location: ../user-management.php");
                exit();
            }

            // 2. Prevent Self-Archiving
            // We need to check if the target employee ID belongs to the currently logged-in admin
            $stmt_check_self = $conn->prepare("SELECT AccountID FROM tblaccounts WHERE EmployeeID = ?");
            $stmt_check_self->bind_param("i", $targetEmployeeID);
            $stmt_check_self->execute();
            $res_self = $stmt_check_self->get_result();
            $target_acc_data = $res_self->fetch_assoc();
            
            if ($target_acc_data && $target_acc_data['AccountID'] == $adminID) {
                add_toast("Action Denied: You cannot archive your own account.", "error");
                header("Location: ../user-management.php");
                exit();
            }
            $stmt_check_self->close();

            $conn->begin_transaction();

            try {
                // 3. Fetch Full Details for the Snapshot
                $query = "SELECT 
                            a.AccountID, a.Role, 
                            e.Position, e.DateEmployed,
                            d.DepartmentName,
                            p.FirstName, p.LastName
                          FROM tblaccounts a
                          JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
                          JOIN tblpersonalinfo p ON e.PersonalID = p.PersonalID
                          JOIN tbldepartment d ON e.DepartmentID = d.DepartmentID
                          WHERE a.EmployeeID = ?";
                
                $stmt_fetch = $conn->prepare($query);
                $stmt_fetch->bind_param("i", $targetEmployeeID);
                $stmt_fetch->execute();
                $data = $stmt_fetch->get_result()->fetch_assoc();
                $stmt_fetch->close();

                if (!$data) throw new Exception("Employee data not found.");

                $fullName = $data['FirstName'] . ' ' . $data['LastName'];
                $originalAccountID = $data['AccountID'];
                $role = $data['Role'];
                $deptName = $data['DepartmentName'];
                $position = $data['Position'];
                $dateEmployed = $data['DateEmployed'];
                $reason = "Employee Archived via User Management"; 

                // 4. Create Snapshot in tblaccountarchive
                $stmt_archive = $conn->prepare("INSERT INTO tblaccountarchive (OriginalAccountID, FullName, Role, DepartmentName, Position, DateEmployed, ArchivedBy, Reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_archive->bind_param("isssssis", $originalAccountID, $fullName, $role, $deptName, $position, $dateEmployed, $adminID, $reason);
                if (!$stmt_archive->execute()) throw new Exception("Failed to create archive snapshot.");
                $stmt_archive->close();

                // 5. Soft Delete: Update Employment Status
                $stmt_soft_del = $conn->prepare("UPDATE tblemployees SET EmploymentStatus = 'Archived' WHERE EmployeeID = ?");
                $stmt_soft_del->bind_param("i", $targetEmployeeID);
                if (!$stmt_soft_del->execute()) throw new Exception("Failed to update employment status.");
                $stmt_soft_del->close();

                // 6. Security: Scramble Password & Deactivate Role
                // We change role to 'User' (to strip admin rights) and set a dummy password
                $dummyPass = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);
                $stmt_block = $conn->prepare("UPDATE tblaccounts SET Password = ?, Role = 'User' WHERE EmployeeID = ?");
                $stmt_block->bind_param("si", $dummyPass, $targetEmployeeID);
                if (!$stmt_block->execute()) throw new Exception("Failed to block account access.");
                $stmt_block->close();

                $conn->commit();
                add_toast("Employee archived successfully.", "success");

            } catch (Exception $e) {
                $conn->rollback();
                add_toast("Error archiving employee: " . $e->getMessage(), "error");
            }

            header("Location: ../user-management.php");
            exit();

        default:
            // Handle unexpected action
            add_toast("Invalid action performed", "error");
            header("Location: ../user-management.php");
            exit();
    }

    // --- Function to Handle Profile Updates ---
    function handle_save_changes($conn) {
        // 1. Get Data
        $employeeID = filter_input(INPUT_POST, 'employeeID', FILTER_SANITIZE_NUMBER_INT);
        
        // Profile Data
        $firstName = trim($_POST['firstName']);
        $lastName = trim($_POST['lastName']);
        $gender = trim($_POST['gender']);
        $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
        $rawContact = trim($_POST['contactNumber']);
        $rawContact = preg_replace('/[^0-9]/', '', $rawContact);
        $role = $_POST['role'];

        if ($role === 'Administrator') {
            $employmentStatus = 'Active';
        }
        
        // Account Data
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $departmentID = filter_input(INPUT_POST, 'departmentID', FILTER_SANITIZE_NUMBER_INT);
        $position = trim($_POST['position']);
        $employmentStatus = trim($_POST['employmentStatus']);
        $dateEmployed = !empty($_POST['dateEmployed']) ? $_POST['dateEmployed'] : null;
        $vacationLeave = filter_input(INPUT_POST, 'vacationLeave', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $sickLeave = filter_input(INPUT_POST, 'sickLeave', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $nfcCode = trim($_POST['nfcCode']);
        $nfcPassword = trim($_POST['nfcPassword']);

        // 2. Handle Image Upload
        $newProfilePic = null;
        if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
            $newProfilePic = upload_profile_picture($_FILES['profilePic']);
        }

        // 3. Validation
        if (!empty($nfcPassword) && !preg_match('/^\d{4}$/', $nfcPassword)) {
            add_toast("NFC Password must be exactly 4 numbers.", "error");
            header("Location: ../user-management.php");
            exit();
        }
        
        if (empty($employeeID) || empty($firstName) || empty($lastName) || empty($username) || empty($email)) {
            add_toast("Please fill all required fields.", "error");
            header("Location: ../user-management.php");
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            add_toast("Please enter a valid email address.", "error");
            header("Location: ../user-management.php");
            exit();
        }

        if (!preg_match('/^9\d{9}$/', $rawContact)) {
            add_toast("Contact number must follow the format 9xxxxxxxxx.", "error");
            header("Location: ../user-management.php");
            exit();
        }
        // Prepend 63
        $contactNumber = '63' . $rawContact;

        if ($role !== 'Administrator') {
            // 1. Check if the user being edited is currently an Administrator
            $stmt_current = $conn->prepare("SELECT Role FROM tblaccounts WHERE EmployeeID = ?");
            $stmt_current->bind_param("i", $employeeID);
            $stmt_current->execute();
            $curr_data = $stmt_current->get_result()->fetch_assoc();
            $current_role_in_db = $curr_data['Role'] ?? '';
            $stmt_current->close();

            // 2. If they ARE currently an Admin, count how many Admins exist in total
            if ($current_role_in_db === 'Administrator') {
                $count_query = "SELECT COUNT(*) as count FROM tblaccounts WHERE Role = 'Administrator'";
                $count_result = $conn->query($count_query);
                $total_admins = $count_result->fetch_assoc()['count'];

                // 3. If there is only 1 (or fewer), block the change
                if ($total_admins <= 1) {
                    add_toast("Action Denied: You cannot demote the last Administrator.", "error");
                    header("Location: ../user-management.php");
                    exit();
                }
            }
        }

        $conn->begin_transaction();

        try {
            // Step A: Get PersonalID
            $stmt_get_pid = $conn->prepare("SELECT PersonalID FROM tblemployees WHERE EmployeeID = ?");
            $stmt_get_pid->bind_param("i", $employeeID);
            $stmt_get_pid->execute();
            $result = $stmt_get_pid->get_result();
            if ($result->num_rows === 0) throw new Exception("Employee not found.");
            $personalID = $result->fetch_assoc()['PersonalID'];
            $stmt_get_pid->close();

            // Step B: Update tblpersonalinfo
            // Logic: If a new picture exists, update that column. If not, leave it alone.
            if ($newProfilePic) {
                $stmt_personal = $conn->prepare("UPDATE tblpersonalinfo SET FirstName = ?, LastName = ?, Gender = ?, Age = ?, ContactNumber = ?, ProfilePicture = ? WHERE PersonalID = ?");
                $stmt_personal->bind_param("sssissi", $firstName, $lastName, $gender, $age, $contactNumber, $newProfilePic, $personalID);
            } else {
                $stmt_personal = $conn->prepare("UPDATE tblpersonalinfo SET FirstName = ?, LastName = ?, Gender = ?, Age = ?, ContactNumber = ? WHERE PersonalID = ?");
                $stmt_personal->bind_param("sssisi", $firstName, $lastName, $gender, $age, $contactNumber, $personalID);
            }

            if (!$stmt_personal->execute()) throw new Exception($stmt_personal->error);
            $stmt_personal->close();

            // Step C: Update tblemployees
            $stmt_emp = $conn->prepare("UPDATE tblemployees SET Position = ?, EmploymentStatus = ?, DepartmentID = ?, DateEmployed = ?, VacationLeaveBalance = ?, SickLeaveBalance = ? WHERE EmployeeID = ?");
            $stmt_emp->bind_param("ssisddi", $position, $employmentStatus, $departmentID, $dateEmployed, $vacationLeave, $sickLeave, $employeeID);
            
            if (!$stmt_emp->execute()) throw new Exception($stmt_emp->error);
            $stmt_emp->close();
            
            // Step D: Update tblaccounts
            // Logic: Only hash and update password if the field is not empty
            if (!empty($password)) {
                if (strlen($password) < 8 || 
                    !preg_match('/[A-Z]/', $password) || 
                    !preg_match('/[a-z]/', $password) || 
                    !preg_match('/[0-9]/', $password) || 
                    !preg_match('/[^a-zA-Z0-9]/', $password)) {
                    
                    add_toast("Password must be 8+ chars, include uppercase, lowercase, number, and special char.", "error");
                    header("Location: ../user-management.php");
                    exit();
                }
                // ---------------------------------

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt_acc = $conn->prepare("UPDATE tblaccounts SET Username = ?, Password = ?, Email = ?, ICCode = ?, ICPassword = ?, Role = ? WHERE EmployeeID = ?");
                $stmt_acc->bind_param("ssssssi", $username, $hashedPassword, $email, $nfcCode, $nfcPassword, $role, $employeeID);
            } else {
                $stmt_acc = $conn->prepare("UPDATE tblaccounts SET Username = ?, Email = ?, ICCode = ?, ICPassword = ?, Role = ? WHERE EmployeeID = ?");
                $stmt_acc->bind_param("sssssi", $username, $email, $nfcCode, $nfcPassword, $role, $employeeID);
            }
            if (!$stmt_acc->execute()) throw new Exception($stmt_acc->error);
            $stmt_acc->close();

            $conn->commit();
            add_toast("Employee details updated successfully!", "success");
        } catch (Exception $e) {
            $conn->rollback();
            add_toast("Error updating details: " . $e->getMessage(), "error");
        }

        header("Location: ../user-management.php");
        exit();
    }
    function handle_add_employee($conn) {
        // 1. Collect Data
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
        $rawContact = trim($_POST['contactNumber'] ?? '');
        $rawContact = preg_replace('/[^0-9]/', '', $rawContact);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $departmentID = filter_input(INPUT_POST, 'departmentID', FILTER_SANITIZE_NUMBER_INT);
        $position = trim($_POST['position'] ?? '');
        $employmentStatus = $_POST['employmentStatus'] ?? '';
        $dateEmployed = !empty($_POST['dateEmployed']) ? $_POST['dateEmployed'] : null;
        $vacationLeave = !empty($_POST['vacationLeave']) ? $_POST['vacationLeave'] : 0.0;
        $sickLeave = !empty($_POST['sickLeave']) ? $_POST['sickLeave'] : 0.0;
        $nfcCode = trim($_POST['nfcCode'] ?? '');
        $nfcPassword = $_POST['nfcPassword'] ?? '';
        $role = $_POST['role'] ?? 'User';

        
        if ($role === 'Administrator') {
            $employmentStatus = 'Active';
        }

        // 2. Handle Image Upload
        $uploadedProfilePic = null;
        if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
            $uploadedProfilePic = upload_profile_picture($_FILES['profilePic']);
        }

        // 3. Comprehensive Validation
        $errors = [];

        if (empty($firstName)) { $errors[] = "First Name is required."; }
        if (empty($lastName)) { $errors[] = "Last Name is required."; }
        if (empty($username)) { $errors[] = "Username is required."; }
        if (empty($password)) { $errors[] = "Password is required."; }
        if (empty($email)) { $errors[] = "Email is required."; }
        if (empty($departmentID)) { $errors[] = "Department is required."; }
        if (empty($position)) { $errors[] = "Position is required."; }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        if (!empty($age) && ($age < 18 || $age > 100)) {
            $errors[] = "Age must be a valid number between 18 and 100.";
        }

        if (!preg_match('/^9\d{9}$/', $rawContact)) {
            $errors[] = "Contact number must be 10 digits starting with 9 (e.g., 9123456789).";
        }
        $contactNumber = '63' . $rawContact;

        // Password Strength
        if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters long.";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must contain at least one uppercase letter.";
        if (!preg_match('/[a-z]/', $password)) $errors[] = "Password must contain at least one lowercase letter.";
        if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain at least one number.";
        // Added Special Character Check
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) $errors[] = "Password must contain at least one special character.";

        if (!empty($nfcPassword) && !preg_match('/^\d{4}$/', $nfcPassword)) {
            $errors[] = "NFC Password must be exactly 4 numbers.";
        }

        // Uniqueness Checks
        if (empty($errors)) {
            $stmt_check = $conn->prepare("SELECT AccountID FROM tblaccounts WHERE Username = ? OR Email = ?");
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $errors[] = "Username or Email is already in use.";
            }
            $stmt_check->close();
        }

        // 4. Handle Errors
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header("Location: ../user-management.php#add-employee-error");
            exit();
        }

        // 5. Database Insertion
        $conn->begin_transaction();
        try {
            // Step A: Insert into tblpersonalinfo (Includes ProfilePicture)
            $stmt_personal = $conn->prepare("INSERT INTO tblpersonalinfo (FirstName, LastName, Age, Gender, ContactNumber, ProfilePicture) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_personal->bind_param("ssisss", $firstName, $lastName, $age, $gender, $contactNumber, $uploadedProfilePic);
            
            if (!$stmt_personal->execute()) throw new Exception($stmt_personal->error);
            $personalID = $conn->insert_id;
            $stmt_personal->close();

            // Step B: Insert into tblemployees
            $stmt_employee = $conn->prepare("INSERT INTO tblemployees (PersonalID, DepartmentID, Position, EmploymentStatus, DateEmployed, VacationLeaveBalance, SickLeaveBalance) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_employee->bind_param("iisssdd", $personalID, $departmentID, $position, $employmentStatus, $dateEmployed, $vacationLeave, $sickLeave);
            if (!$stmt_employee->execute()) throw new Exception($stmt_employee->error);
            $employeeID = $conn->insert_id;
            $stmt_employee->close();

            // Step C: Insert into tblaccounts
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt_account = $conn->prepare("INSERT INTO tblaccounts (EmployeeID, Username, Password, Email, ICCode, ICPassword, Role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_account->bind_param("issssss", $employeeID, $username, $hashedPassword, $email, $nfcCode, $nfcPassword, $role);
            if (!$stmt_account->execute()) throw new Exception($stmt_account->error);
            $stmt_account->close();
            
            $conn->commit();
            add_toast("New employee added successfully!", "success");

        } catch (Exception $e) {
            $conn->rollback();
            add_toast("Error adding employee: " . $e->getMessage(), "error");
        }

        header("Location: ../user-management.php");
        exit();
    }

    function upload_profile_picture($file) {
    // Define target directory
    $targetDir = "../images/";
    
    // Check if file was uploaded without errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        
        // Extract extension
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Allowed extensions
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Create unique filename to prevent overwriting
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $targetDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                return $newFileName;
            }
        }
    }
    return null; // Return null if upload failed or no file
}
?>