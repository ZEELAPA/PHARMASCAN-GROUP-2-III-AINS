<?php
    include('auth.php');
    require_user();
    include('sqlconnect.php');

    $accountID = $_SESSION['AccountID'];
    $userData = [];

    $stmt = $conn->prepare("SELECT Username, ICPassword FROM tblaccounts WHERE AccountID = ?");
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }
    $stmt->close();
    
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
    <link rel="stylesheet" href="styles/user-account-settings.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>User Account Settings | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/user-account-settings.js?v=<?php echo time(); ?>"></script>

    <script>
        const employeeDistributionData = <?php echo $chartDataJSON; ?>;
        const taskStatusData = <?php echo $taskStatusDataJSON; ?>;
    </script>
</head>
<body>
    <div class="app-container">

        <?php include('toast-message.php'); ?>
        <?php include('user-sidebar.php'); ?>
        
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
                            <h1>Account Security</h1>
                        </div>
                        <hr>
                        <form class="accountForm" id="accountForm" action="handlers/user-settings-handler.php" method="POST">
                            <div class="form-container">
                                <div id="profileContent" class="tab-content">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['Username'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="oldPassword">Old Password</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="oldPassword" name="oldPassword" placeholder="Enter current password to change">
                                            <button type="button" class="toggle-password">
                                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                <svg class="icon-eye-slash" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="newPassword">New Password</label>
                                        <div class="password-wrapper">
                                            <!-- Added regex pattern matching the PHP handler requirements -->
                                            <input type="password" id="newPassword" name="newPassword" 
                                                   placeholder="Enter new password"
                                                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" 
                                                   title="Must be at least 8 characters and contain: 1 uppercase, 1 lowercase, 1 number, and 1 special character.">
                                            <button type="button" class="toggle-password">
                                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                <svg class="icon-eye-slash" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="nfcPassword">NFC Password</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="nfcPassword" name="nfcPassword" value="<?php echo htmlspecialchars($userData['ICPassword'] ?? ''); ?>" maxlength="4">
                                            <button type="button" class="toggle-password">
                                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                <svg class="icon-eye-slash" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <input type="hidden" name="action" value="save_account">

                                    <div class="form-actions">
                                        <button type="button" class="btn-save" onclick="openModal('accountForm')">Save Changes</button>
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
                            <a href="user-profile-settings.php" class="options">Profile Information</a>
                            <a href="user-account-settings.php" class="options active">Account Settings</a>
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
                    <input type="password" id="nfcAuthCode" placeholder="Scan card..." readonly>
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