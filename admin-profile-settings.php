<?php
    include('auth.php');

    require_admin();

    include('sqlconnect.php');

    $accountID = $_SESSION['AccountID'];
    $userData = [];

    // Fetch current user data
    $stmt = $conn->prepare("
        SELECT p.FirstName, p.LastName, p.Gender, p.Age, p.ContactNumber, p.ProfilePicture, a.Email
        FROM tblaccounts a
        JOIN tblemployees e ON a.EmployeeID = e.EmployeeID
        JOIN tblpersonalinfo p ON e.PersonalID = p.PersonalID
        WHERE a.AccountID = ?
    ");
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }
    $stmt->close();
    
    // Initialize JS variables to prevent errors
    $chartDataJSON = '[]';
    $taskStatusDataJSON = '[]';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/admin-profile-settings.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>Admin Profile Settings | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/admin-profile-settings.js?v=<?php echo time(); ?>"></script>

    <script>
        // These variables are now safely initialized at the top of the PHP script
        const employeeDistributionData = <?php echo $chartDataJSON; ?>;
        const taskStatusData = <?php echo $taskStatusDataJSON; ?>;
    </script>
</head>
<body>
    <div class="app-container">
        
        <?php include('toast-message.php'); ?>
        <?php include('admin-sidebar.php'); ?>
        
        <div id="confirmationModal" class="modal-overlay">
            <div class="modal-box">
                <h3>Confirm Changes</h3>
                <p>Are you sure you want to save these changes?</p>
                <div class="modal-buttons">
                    <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button class="btn-confirm" onclick="confirmSubmit()">Confirm</button>
                </div>
            </div>
        </div>

        <main class="main-container">
            <div class="main-panel">
                <div class="main-title">
                    <h3>Account Settings</h3>
                    <p>Customize and Edit your personal and account info!</p>
                </div>
                <div class="panels-container">
                    <div class="profile-settings-panel">
                        <div class="profile-settings-header">
                            <h1>Profile Information</h1>
                        </div>
                        <hr>
                        <form id="profileForm" action="handlers/admin-settings-handler.php" method="POST" enctype="multipart/form-data">
                            <div class="form-container">
                                <div id="profileContent" class="tab-content">
                                    <div class="profile-pic-container">
                                        <div class="img-wrapper">
                                            <?php 
                                                $profilePicPath = !empty($userData['ProfilePicture']) 
                                                    ? 'images/' . htmlspecialchars($userData['ProfilePicture']) 
                                                    : 'images/default-user.png';
                                            ?>
                                            <img id="profilePreview" src="<?php echo $profilePicPath; ?>" alt="Profile Picture">
                                        </div>
                                        <label for="profilePicUpload" class="upload-btn">Change Photo</label>
                                        <input type="file" id="profilePicUpload" name="profilePic" accept="image/*" style="display: none;">
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="firstName">First Name</label>
                                            <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($userData['FirstName'] ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="lastName">Last Name</label>
                                            <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($userData['LastName'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="gender">Gender</label>
                                            <select id="gender" name="gender">
                                                <option value="Male" <?php echo (($userData['Gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo (($userData['Gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo (($userData['Gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="age">Age</label>
                                            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($userData['Age'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="contactNumber">Contact Number</label>
                                            <input type="text" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($userData['ContactNumber'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['Email'] ?? ''); ?>" required>
                                    </div>
                                    <!-- Add this inside the form -->
                                    <input type="hidden" name="action" value="save_profile">

                                    <!-- Update the button -->
                                    <div class="form-actions">
                                        <button type="button" class="btn-save" onclick="openModal('profileForm')">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="options-panel">
                        <div class="options-header">
                            <h3>My Profile</h3>
                        </div>
                        <div class="options-container">
                            <a href="admin-profile-settings.php" class="options active">Profile Information</a>
                            <a href="admin-account-settings.php" class="options">Account Settings</a>
                            <a href="admin-attendance.php" class="options">Attendance</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- NFC Authentication Modal -->
    <div id="nfcAuthModal" class="nfc-modal">
        <div class="nfc-modal-content">
            <h3>Authentication Required</h3>
            <p>Please scan your card to proceed.</p>
            
            <div class="form-group">
                <div class="nfc-input-wrapper">
                    <input type="password" id="nfcAuthCode" placeholder="Scan card..." autocomplete="off">
                    <span id="nfcAuthStatus" class="nfc-status status-waiting">Waiting</span>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <input type="password" id="nfcAuthPassword" placeholder="Enter 4-digit PIN" maxlength="4" autocomplete="off" disabled>
                <div id="nfcAuthError" style="color: red; font-size: 12px; height: 15px;"></div>
            </div>

            <div class="nfc-modal-buttons">
                <button type="button" class="btn-cancel" id="cancelNfcBtn">Cancel</button>
                <button type="button" class="btn-confirm" id="confirmNfcBtn" disabled>Verify</button>
            </div>
        </div>
    </div>
</body>
</html>