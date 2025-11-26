<?php
    include('auth.php');

    require_admin();

    include('sqlconnect.php');

    function getAttendanceRecords($conn, $date) {
        $safeDate = $conn->real_escape_string($date);
        
        $query = "SELECT 
                    CONCAT_WS(' ', info.FirstName, info.LastName) AS FullName,
                    att.TimeIn,
                    att.TimeOut,
                    att.AttendanceDate, 
                    att.Remarks
                FROM 
                    tblemployees AS emp
                JOIN 
                    tblpersonalinfo AS info ON emp.PersonalID = info.PersonalID
                JOIN 
                    tblaccounts AS acc ON emp.EmployeeID = acc.EmployeeID
                LEFT JOIN 
                    tblattendance AS att ON acc.AccountID = att.AccountID AND att.AttendanceDate = '{$safeDate}'
                WHERE emp.EmploymentStatus IN ('Active', 'On Leave', 'On Vacation')
                ORDER BY 
                    info.LastName ASC, info.FirstName ASC;";

        $result = $conn->query($query);

        if (!$result) {
            error_log("Database query failed: " . $conn->error);
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    function getAllEmployees($conn) {
        $query = "SELECT 
                    emp.EmployeeID,
                    CONCAT_WS(' ', info.FirstName, info.LastName) AS FullName
                FROM 
                    tblemployees AS emp
                JOIN 
                    tblpersonalinfo AS info ON emp.PersonalID = info.PersonalID
                WHERE emp.EmploymentStatus = 'Active'
                ORDER BY 
                    info.LastName ASC, info.FirstName ASC;";
        
        $result = $conn->query($query);
        if (!$result) {
            error_log("Database query for employees failed: " . $conn->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    $allEmployees = getAllEmployees($conn);

    $selectedDate = date('Y-m-d');
    $allAttendanceRecords = getAttendanceRecords($conn, $selectedDate);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="styles/attendance-management.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">

    <title>Attendance Management | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/attendance-management.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div class="app-container">
        <?php include('admin-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                <div class="background-glow top"></div>
                <div class="background-glow bottom"></div>
                <div class="main-title">
                    <h3>Attendance Overview</h3>
                    <button class="export-btn" id="show-export-modal-btn">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M13.725 6.30125C13.5492 6.47681 13.3109 6.57543 13.0625 6.57543C12.8141 6.57543 12.5758 6.47681 12.4 6.30125L9.9375 3.83875V13.0625C9.9375 13.3111 9.83873 13.5496 9.66291 13.7254C9.4871 13.9012 9.24864 14 9 14C8.75136 14 8.5129 13.9012 8.33709 13.7254C8.16127 13.5496 8.0625 13.3111 8.0625 13.0625V3.83875L5.6 6.30125C5.42228 6.46685 5.18722 6.557 4.94435 6.55272C4.70147 6.54843 4.46974 6.45004 4.29797 6.27828C4.12621 6.10651 4.02782 5.87478 4.02353 5.6319C4.01925 5.38903 4.1094 5.15397 4.275 4.97625L8.3375 0.91375L9 0.25L9.6625 0.9125L13.725 4.975C13.8121 5.06206 13.8812 5.16544 13.9284 5.27922C13.9755 5.393 13.9998 5.51496 13.9998 5.63813C13.9998 5.76129 13.9755 5.88325 13.9284 5.99703C13.8812 6.11081 13.8121 6.21419 13.725 6.30125ZM2.125 11.1875C2.125 10.9389 2.02623 10.7004 1.85041 10.5246C1.6746 10.3488 1.43614 10.25 1.1875 10.25C0.93886 10.25 0.700403 10.3488 0.524587 10.5246C0.348772 10.7004 0.25 10.9389 0.25 11.1875V15.25C0.25 15.913 0.513392 16.5489 0.982233 17.0178C1.45107 17.4866 2.08696 17.75 2.75 17.75H15.25C15.913 17.75 16.5489 17.4866 17.0178 17.0178C17.4866 16.5489 17.75 15.913 17.75 15.25V11.1875C17.75 10.9389 17.6512 10.7004 17.4754 10.5246C17.2996 10.3488 17.0611 10.25 16.8125 10.25C16.5639 10.25 16.3254 10.3488 16.1496 10.5246C15.9738 10.7004 15.875 10.9389 15.875 11.1875V15.25C15.875 15.4158 15.8092 15.5747 15.6919 15.6919C15.5747 15.8092 15.4158 15.875 15.25 15.875H2.75C2.58424 15.875 2.42527 15.8092 2.30806 15.6919C2.19085 15.5747 2.125 15.4158 2.125 15.25V11.1875Z" fill="#EEEEEE"/>
                        </svg>

                        <span>Export</span>
                    </button>
                </div>
                <div class="panel-containers">
                    <div class="overview-container">
                        <div class="header-container">
                            <label for="search-employee">Search Employee:</label>
                            <div class="autocomplete-container">
                                <input type="text" id="search-employee-input" placeholder="Search by name..." autocomplete="off">
                                <!-- Hidden input to store the selected employee ID -->
                                <input type="hidden" id="search-employee-id" value="all">
                                <div id="autocomplete-results" class="autocomplete-items"></div>
                            </div>
                            
                            <div class="controls-right">
                                <div class="time-toggle">
                                    <button id="btn-daily" class="active">Daily</button>
                                    <button id="btn-weekly">Weekly</button>
                                </div>
                                <div class="date-picker-container">
                                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_667_928)">
                                        <path d="M4.82205 0C4.97985 0 5.13119 0.0626873 5.24278 0.174271C5.35436 0.285856 5.41705 0.437196 5.41705 0.595V1.70765H11.8065V0.60265C11.8065 0.444846 11.8692 0.293506 11.9808 0.181921C12.0924 0.0703373 12.2437 0.00765 12.4015 0.00765C12.5593 0.00765 12.7106 0.0703373 12.8222 0.181921C12.9338 0.293506 12.9965 0.444846 12.9965 0.60265V1.70765H15.3C15.7507 1.70765 16.183 1.88664 16.5018 2.20527C16.8206 2.5239 16.9998 2.95608 17 3.4068V15.3008C16.9998 15.7516 16.8206 16.1838 16.5018 16.5024C16.183 16.821 15.7507 17 15.3 17H1.7C1.24928 17 0.817006 16.821 0.498219 16.5024C0.179431 16.1838 0.00022536 15.7516 0 15.3008L0 3.4068C0.00022536 2.95608 0.179431 2.5239 0.498219 2.20527C0.817006 1.88664 1.24928 1.70765 1.7 1.70765H4.22705V0.59415C4.22728 0.436494 4.29006 0.285371 4.40162 0.173971C4.51318 0.0625705 4.66439 -1.60874e-07 4.82205 0ZM1.19 6.5807V15.3008C1.19 15.3678 1.20319 15.4341 1.22882 15.496C1.25445 15.5579 1.29202 15.6141 1.33938 15.6615C1.38673 15.7088 1.44296 15.7464 1.50483 15.772C1.56671 15.7977 1.63303 15.8109 1.7 15.8109H15.3C15.367 15.8109 15.4333 15.7977 15.4952 15.772C15.557 15.7464 15.6133 15.7088 15.6606 15.6615C15.708 15.6141 15.7455 15.5579 15.7712 15.496C15.7968 15.4341 15.81 15.3678 15.81 15.3008V6.5926L1.19 6.5807ZM5.66695 12.4262V13.8422H4.25V12.4262H5.66695ZM9.20805 12.4262V13.8422H7.79195V12.4262H9.20805ZM12.75 12.4262V13.8422H11.3331V12.4262H12.75ZM5.66695 9.0457V10.4618H4.25V9.0457H5.66695ZM9.20805 9.0457V10.4618H7.79195V9.0457H9.20805ZM12.75 9.0457V10.4618H11.3331V9.0457H12.75ZM4.22705 2.8968H1.7C1.63303 2.8968 1.56671 2.90999 1.50483 2.93562C1.44296 2.96125 1.38673 2.99882 1.33938 3.04618C1.29202 3.09353 1.25445 3.14976 1.22882 3.21163C1.20319 3.27351 1.19 3.33983 1.19 3.4068V5.39155L15.81 5.40345V3.4068C15.81 3.33983 15.7968 3.27351 15.7712 3.21163C15.7455 3.14976 15.708 3.09353 15.6606 3.04618C15.6133 2.99882 15.557 2.96125 15.4952 2.93562C15.4333 2.90999 15.367 2.8968 15.3 2.8968H12.9965V3.68645C12.9965 3.84425 12.9338 3.99559 12.8222 4.10718C12.7106 4.21876 12.5593 4.28145 12.4015 4.28145C12.2437 4.28145 12.0924 4.21876 11.9808 4.10718C11.8692 3.99559 11.8065 3.84425 11.8065 3.68645V2.8968H5.41705V3.6788C5.41705 3.8366 5.35436 3.98794 5.24278 4.09953C5.13119 4.21111 4.97985 4.2738 4.82205 4.2738C4.66425 4.2738 4.51291 4.21111 4.40132 4.09953C4.28974 3.98794 4.22705 3.8366 4.22705 3.6788V2.8968Z" fill="#145494"/>
                                        </g>
                                        <defs>
                                        <clipPath id="clip0_667_928">
                                        <rect width="17" height="17" fill="white"/>
                                        </clipPath>
                                        </defs>
                                    </svg>

                                    <input type="date" class="date-picker-container" id="date-picker" value="<?php echo $selectedDate; ?>">
                                </div>

                            </div>
                        </div>

                        <div class="attendance-list">
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Status</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Overtime Hours</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="attendance-table-body">
                                    <?php if (!empty($allAttendanceRecords)): ?>
                                        <?php foreach ($allAttendanceRecords as $record): ?>
                                            <?php
                                                $status = '';
                                                $statusClass = '';
                                                $timeIn = '--:--';
                                                $timeOut = '--:--';
                                                $overtimeDisplay = '--:--';
                                                $remarks = '--:--';
                                                $remarksClass = '';

                                                if (empty($record['TimeIn'])) {
                                                    $status = 'Absent';
                                                    $statusClass = 'status-absent';
                                                } else {
                                                    $timeInObj = new DateTime($record['TimeIn']);
                                                    $timeIn = $timeInObj->format('g:i A');

                                                    if (empty($record['TimeOut'])) {
                                                        $status = 'Working';
                                                        $statusClass = 'status-working';
                                                    } else {
                                                        $status = 'Finished'; 
                                                        $statusClass = 'status-finished';

                                                        $timeOutObj = new DateTime($record['TimeOut']);
                                                        $timeOut = $timeOutObj->format('g:i A');
                                                        
                                                        $shiftEnd = new DateTime($record['AttendanceDate'] . ' 17:00:00');

                                                        if ($timeOutObj > $shiftEnd) {
                                                            $overtimeInterval = $shiftEnd->diff($timeOutObj);
                                                            $overtimeHours = $overtimeInterval->h;

                                                            if ($overtimeHours > 0) {
                                                                $label = ($overtimeHours == 1) ? " Hour" : " Hours";
                                                                $overtimeDisplay = $overtimeHours . $label;
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                                // Handle Remarks
                                                if (!empty($record['Remarks'])) {
                                                    $remarks = htmlspecialchars($record['Remarks']);
                                                    $remarksClass = 'remarks-' . strtolower($remarks);
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['FullName']); ?></td>
                                                <td><span class="status-tag <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                                <td><?php echo $timeIn; ?></td>
                                                <td><?php echo $timeOut; ?></td>
                                                <td><?php echo $overtimeDisplay; ?></td>
                                                <td class="<?php echo $remarksClass; ?>"><?php echo $remarks; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="no-records">No employees found.</td>
                                        </tr>
                                    <?php endif; ?>

                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div id="export-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <span class="modal-close-btn">&times;</span>
            <h2>Export Attendance</h2>
            <div class="modal-form-group">
                <label for="export-employee-select">Select Employee</label>
                <select id="export-employee-select">
                    <!-- Options will be populated by JavaScript -->
                </select>
            </div>
            <div class="modal-form-group">
                <label for="export-type-select">Select Report Type</label>
                <select id="export-type-select">
                    <!-- Options will change based on employee selection -->
                </select>
            </div>
            <div id="export-date-container" class="modal-form-group" style="display: none;">
                <label for="export-date-picker">Select Date</label>
                <input type="date" id="export-date-picker" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <button id="confirm-export-btn" class="modal-confirm-btn">Confirm Export</button>
        </div>
    </div>
    <script>
        const allEmployees = <?php echo json_encode($allEmployees); ?>;
    </script>
</body>
</html>



