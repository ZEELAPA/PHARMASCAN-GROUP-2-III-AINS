<?php
    
    include('auth.php');
    
    require_admin();
    
    include('sqlconnect.php');

    if (!$conn || $conn->connect_error) {
        die("Connection failed: " . ($conn ? $conn->connect_error : "Unknown error"));
    }

    function getAccounts($conn) {
        $query = "SELECT
                    p.FirstName,
                    p.LastName,
                    p.Age,
                    p.Gender,
                    p.ContactNumber,
                    p.ProfilePicture,
                    a.AccountID,
                    a.Username,
                    a.Email,
                    a.ICCode,
                    a.ICPassword,
                    a.Role,
                    e.DepartmentID,
                    e.Position,
                    e.EmploymentStatus,
                    e.VacationLeaveBalance,
                    e.SickLeaveBalance,
                    e.DateEmployed,
                    d.DepartmentName
                FROM
                    tblaccounts AS a
                JOIN
                    tblemployees AS e ON a.EmployeeID = e.EmployeeID
                JOIN
                    tblpersonalinfo AS p ON e.PersonalID = p.PersonalID
                JOIN
                    tbldepartment AS d ON e.DepartmentID = d.DepartmentID;";

        if (!$stmt = $conn->prepare($query)) {
            error_log("Failed to prepare statement: " . $conn->error);
            return [];
        }

        if (!$stmt->execute()) {
            error_log("Failed to execute statement: " . $stmt->error);
            $stmt->close();
            return [];
        }
        
        $result = $stmt->get_result();
        $allAccountsData = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $allAccountsData;
    }

    $profileAccounts = getAccounts($conn);

    function getDepartments($conn) {
        $query = "SELECT DepartmentID, DepartmentName FROM tbldepartment ORDER BY DepartmentName ASC;";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    $allDepartments = getDepartments($conn);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/user-management.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>User Management | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/user-management.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div class="app-container">
        
        <!-- Sidebar -->
        <?php include('toast-message.php'); ?>
        <?php include('admin-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                <div class="main-title">
                    <h3>User Accounts Management</h3>
                </div>
                <div class="acc-mgmt">
                    <div class="profile-list-container">
                        <div class="search-panel">
                            <div class="search-wrapper">
                                <svg class="search-icon" width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.0387 18.2712L25.3925 23.6237L23.6237 25.3925L18.2712 20.0387C16.2797 21.6353 13.8025 22.5036 11.25 22.5C5.04 22.5 0 17.46 0 11.25C0 5.04 5.04 0 11.25 0C17.46 0 22.5 5.04 22.5 11.25C22.5036 13.8025 21.6353 16.2797 20.0387 18.2712ZM17.5312 17.3437C19.1173 15.7121 20.0032 13.5255 20 11.25C20 6.41625 16.0837 2.5 11.25 2.5C6.41625 2.5 2.5 6.41625 2.5 11.25C2.5 16.0837 6.41625 20 11.25 20C13.5255 20.0032 15.7121 19.1173 17.3437 17.5312L17.5312 17.3437Z" fill="#145494"/>
                                </svg>

                                <input type="text" id="searchInput" placeholder="Search by name...">
                            </div>
                        </div>
                        <div class="profile-list">
                            <div class="profile-header">
                                <h4>Employee Accounts</h4>
                                <button id="addEmployeeBtn" class="add-new-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    <span>Add New Employee</span>
                                </button>
                            </div>
                            <hr>
                            <div class="profile-members">
                                <?php if (!empty($profileAccounts)): ?>
                                    <?php foreach ($profileAccounts as $account): ?>
                                        <?php
                                            // The full name calculation is already here, which is great.
                                            $nameParts = [$account['FirstName'], $account['LastName']];
                                            $fullName = implode(' ', array_filter($nameParts));

                                            $profilePicPath = !empty($account['ProfilePicture']) 
                                                ? 'images/' . htmlspecialchars($account['ProfilePicture']) 
                                                : 'images/default-user.png';
                                        ?>
                                        
                                        <div class="profile-item"
                                            data-accountid="<?php echo htmlspecialchars($account['AccountID']); ?>"
                                            data-firstname="<?php echo htmlspecialchars($account['FirstName']); ?>"
                                            data-lastname="<?php echo htmlspecialchars($account['LastName']); ?>"
                                            data-fullname="<?php echo htmlspecialchars($fullName); ?>"
                                            data-age="<?php echo htmlspecialchars($account['Age']); ?>"
                                            data-gender="<?php echo htmlspecialchars($account['Gender']); ?>"
                                            data-contactnumber="<?php echo htmlspecialchars($account['ContactNumber']); ?>"
                                            data-username="<?php echo htmlspecialchars($account['Username']); ?>"
                                            data-email="<?php echo htmlspecialchars($account['Email']); ?>"       
                                            data-position="<?php echo htmlspecialchars($account['Position']); ?>"
                                            data-empstatus="<?php echo htmlspecialchars($account['EmploymentStatus']); ?>"
                                            data-vacationleave="<?php echo htmlspecialchars($account['VacationLeaveBalance']); ?>"
                                            data-sickleave="<?php echo htmlspecialchars($account['SickLeaveBalance']); ?>"
                                            data-dateemployed="<?php echo htmlspecialchars($account['DateEmployed']); ?>"
                                            data-departmentid="<?php echo htmlspecialchars($account['DepartmentID']); ?>"
                                            data-iccode="<?php echo htmlspecialchars($account['ICCode']); ?>"
                                            data-icpassword="<?php echo htmlspecialchars($account['ICPassword']); ?>"
                                            data-role="<?php echo htmlspecialchars($account['Role']); ?>"
                                            data-profilepic="<?php echo htmlspecialchars($account['ProfilePicture'] ?? ''); ?>" 
                                            >

                                            <div class="profile-icon">
                                                <img src="<?php echo $profilePicPath; ?>" alt="Profile" class="list-profile-img">
                                            </div>

                                            <div class="profile-item-details">
                                                <h5 class="profile-item-name"><?php echo htmlspecialchars($fullName); ?></h5>
                                                <p class="profile-item-gender"><?php echo htmlspecialchars($account['Gender'] . ', ' . $account['Age'] . ' years old'); ?></p>
                                            </div>

                                            <div class="profile-item-ranks">
                                                <h5 class="profile-item-position"><?php echo htmlspecialchars($account['Position']); ?></h5>
                                                <p class="profile-item-role"><?php echo htmlspecialchars($account['DepartmentName']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-accounts">
                                        <p>No user data found</p> 
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div id="editEmployeeModal" class="modal">
        <div class="modal-content">
            
            <h2 id="modalTitle" class="modal-title">Edit Employee</h2>
            
            <!-- Tab Navigation -->
            <div class="modal-tabs">
                <button class="tab-link active" data-tab="profileContent">Profile</button>
                <button class="tab-link" data-tab="accountContent">Account</button>
            </div>

            <?php if (isset($_SESSION['form_errors'])): ?>
                <div class="form-error-container">
                    <ul>
                        <?php foreach ($_SESSION['form_errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Form that wraps both tab contents -->
            <form id="employeeForm" action="employee-handler.php" method="POST" enctype="multipart/form-data">
                <!-- Hidden input to know which employee is being edited -->
                <input type="hidden" id="editEmployeeID" name="employeeID" value="">

                <!-- ======================= -->
                <!--   PROFILE TAB CONTENT   -->
                <!-- ======================= -->
                <div id="profileContent" class="tab-content active">
                    <div class="profile-pic-container">
                        <div class="img-wrapper">
                            <img id="modalProfileImage" src="images/default-user.png" alt="Profile Picture">
                        </div>
                        <!-- Styled label acting as button -->
                        <label for="profilePicUpload" class="upload-btn">Change Photo</label>
                        <!-- Hidden file input -->
                        <input type="file" id="profilePicUpload" name="profilePic" accept="image/*" style="display: none;">
                    </div>
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" value="">
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" value="">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="Male" selected>Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="contactNumber">Contact Number</label>
                        <input type="text" id="contactNumber" name="contactNumber" value="">
                    </div>
                </div>

                <!-- ======================= -->
                <!--   ACCOUNT TAB CONTENT   -->
                <!-- ======================= -->
                <div id="accountContent" class="tab-content">
                    <div class="form-group">
                        <label for="displayEmployeeID">Employee ID</label>
                        <input type="text" id="displayEmployeeID" name="" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <!-- It's better practice to have a "Reset Password" button than to show the password -->
                         <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Enter new password to change">
                            <button type="button" class="toggle-password">
                                <!-- Eye Icon SVG -->
                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <!-- Eye-slash Icon SVG (initially hidden) -->
                                <svg class="icon-eye-slash" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="">
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="departmentID">
                            <?php foreach ($allDepartments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['DepartmentID']); ?>">
                                    <?php echo htmlspecialchars($dept['DepartmentName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" value="">
                    </div>
                    <div class="form-group">
                        <label for="employmentStatus">Employment Status</label>
                        <select id="employmentStatus" name="employmentStatus">
                            <option value="Active">Active</option>
                            <option value="Inactive" selected>Inactive</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Terminated">Terminated</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dateEmployed">Date Employed</label>
                        <input type="date" id="dateEmployed" name="dateEmployed">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="vacationLeave">Vacation Leave Balance</label>
                            <input type="number" id="vacationLeave" name="vacationLeave" step="1" min="0" placeholder="0.0">
                        </div>
                        <div class="form-group">
                            <label for="sickLeave">Sick Leave Balance</label>
                            <input type="number" id="sickLeave" name="sickLeave" step="1" min="0" placeholder="0.0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="role">Account Role</label>
                        <select id="role" name="role">
                            <option value="User">User</option>
                            <option value="Administrator">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nfcCode">NFC Code (Scan NFC to Register)</label>
                        <!-- Wrapper to position the status indicator -->
                        <div class="nfc-input-wrapper">
                            <input type="password" id="nfcCode" name="nfcCode" value="" placeholder="Click here, then scan card...">
                            <!-- The new status indicator element -->
                            <span id="nfcScanStatus" class="status-waiting">Waiting</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nfcPassword">NFC 4-Pin Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="nfcPassword" name="nfcPassword" value="">
                            <button type="button" class="toggle-password">
                                <!-- Eye Icon SVG -->
                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <!-- Eye-slash Icon SVG (initially hidden) -->
                                <svg class="icon-eye-slash" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            </button>
                        </div>                        
                        <div id="nfcPasswordError" class="input-error-message"></div>
                    </div>
                </div>
                <div class="form-actions">
                    <!-- NOTE: The 'save_profile' and 'save_account' buttons are now combined into one -->
                    <button type="button" name="action" value="save_changes" class="btn-save">Save Changes</button>
                    <button type="button" name="action" value="archive_employee" class="btn-archive">Archive</button>
                    <button type="button" name="action" value="add_employee" class="btn-create" style="display: none;">Create Employee</button>

                    <!-- ADD THIS: A hidden, real submit button that our JS will trigger -->
                    <button type="submit" id="realSubmitBtn" style="display:none;"></button>
                </div>
            </form>
            <?php
                unset($_SESSION['form_errors']);
                unset($_SESSION['form_data']);
            ?>
        </div>
    </div>
    <div id="adminAuthModal" class="modal">
        <div class="modal-content auth-modal-content">
            <h2 class="modal-title">Admin Authorization Required</h2>
            <p class="auth-instruction">To proceed, an administrator must scan their card.</p>
            
            <div class="form-group">
                <label for="adminNfcCode">Administrator NFC Code</label>
                <div class="nfc-input-wrapper">
                    <input type="password" id="adminNfcCode" placeholder="Scan admin card now..." readonly>
                    <span id="adminNfcStatus" class="status-waiting">Waiting</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="adminNfcPassword">Administrator NFC Password</label>
                <input type="password" id="adminNfcPassword" placeholder="Enter 4-digit password" maxlength="4" autocomplete="off" disabled>
                <div id="adminNfcPasswordError" class="input-error-message"></div>
            </div>

            <div class="auth-actions">
                <button type="button" id="cancelAuthBtn" class="btn-cancel">Cancel</button>
                <button type="button" id="confirmAuthBtn" class="btn-confirm" disabled>Confirm Action</button>
            </div>
        </div>
    </div>
</body>
</html>