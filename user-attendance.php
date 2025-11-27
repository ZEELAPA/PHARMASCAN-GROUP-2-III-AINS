<?php
    include('auth.php');
    require_user();
    include('sqlconnect.php');

    // --- Get Logged-in User and Selected Date ---
    $accountID = $_SESSION['AccountID'];
    $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
    $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // --- Data Fetching Functions ---

    function getUserInfo($conn, $accountID) {
        $stmt = $conn->prepare("SELECT 
                                    CONCAT_WS(' ', info.FirstName, info.LastName) AS FullName,
                                    dept.DepartmentName
                                FROM tblaccounts AS acc
                                JOIN tblemployees AS emp ON acc.EmployeeID = emp.EmployeeID
                                JOIN tblpersonalinfo AS info ON emp.PersonalID = info.PersonalID
                                JOIN tbldepartment AS dept ON emp.DepartmentID = dept.DepartmentID
                                WHERE acc.AccountID = ?");
        $stmt->bind_param("i", $accountID);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function getUserMonthlyAttendance($conn, $accountID, $year, $month) {
        $stmt = $conn->prepare("SELECT AttendanceDate, TimeIn, TimeOut FROM tblattendance
                                WHERE AccountID = ? AND YEAR(AttendanceDate) = ? AND MONTH(AttendanceDate) = ?
                                ORDER BY AttendanceDate ASC");
        $stmt->bind_param("iii", $accountID, $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendanceData = [];
        while ($row = $result->fetch_assoc()) {
            $attendanceData[$row['AttendanceDate']] = $row;
        }
        return $attendanceData;
    }

    function getLeaveStatusForMonth($conn, $accountID, $year, $month) {
        $stmt = $conn->prepare("SELECT ScheduledLeave, ScheduledReturn FROM tblleaveform 
                                WHERE AccountID = ? AND LeaveStatus = 'Approved' AND 
                                ( (YEAR(ScheduledLeave) = ? AND MONTH(ScheduledLeave) = ?) OR
                                  (YEAR(ScheduledReturn) = ? AND MONTH(ScheduledReturn) = ?) )");
        $stmt->bind_param("iiiii", $accountID, $year, $month, $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $leaveDates = [];
        while ($row = $result->fetch_assoc()) {
            $period = new DatePeriod(new DateTime($row['ScheduledLeave']), new DateInterval('P1D'), (new DateTime($row['ScheduledReturn']))->modify('+1 day'));
            foreach ($period as $date) {
                $leaveDates[$date->format('Y-m-d')] = true;
            }
        }
        return $leaveDates;
    }

    // --- Fetch and Process Data ---
    $userInfo = getUserInfo($conn, $accountID);
    $monthlyAttendance = getUserMonthlyAttendance($conn, $accountID, $selectedYear, $selectedMonth);
    $leaveDays = getLeaveStatusForMonth($conn, $accountID, $selectedYear, $selectedMonth);

    // --- Initialize Summary Variables ---
    $totalRegularHours = 0;
    $totalOvertimeHours = 0;
    $totalDaysWorked = 0;
    $totalTardiness = 0;
    $totalAbsences = 0;
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

    // --- Summary Calculations ---
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDateStr = sprintf("%04d-%02d-%02d", $selectedYear, $selectedMonth, $day);
        $currentDateTime = new DateTime($currentDateStr);
        $dayOfWeek = $currentDateTime->format('N'); // 1 (Mon) to 7 (Sun)
        $isWorkingDay = ($dayOfWeek < 6); // Monday to Friday

        if (isset($monthlyAttendance[$currentDateStr])) {
            $record = $monthlyAttendance[$currentDateStr];
            $totalDaysWorked++;

            if (!empty($record['TimeIn']) && !empty($record['TimeOut'])) {
                $timeIn = new DateTime($record['TimeIn']);
                $timeOut = new DateTime($record['TimeOut']);
                $shiftStart = new DateTime($timeIn->format('Y-m-d') . ' 08:01:00');
                $shiftEnd = new DateTime($timeOut->format('Y-m-d') . ' 17:00:00');

                // Calculate worked hours (minus 1hr lunch)
                $diff = $timeOut->diff($timeIn);
                $minutesWorked = ($diff->h * 60) + $diff->i;
                if ($minutesWorked > 60) $minutesWorked -= 60;
                $totalRegularHours += $minutesWorked / 60;

                // Calculate overtime
                if ($timeOut > $shiftEnd) {
                    $overtimeDiff = $timeOut->diff($shiftEnd);
                    $overtimeMinutes = ($overtimeDiff->h * 60) + $overtimeDiff->i;
                    $totalOvertimeHours += $overtimeMinutes / 60;
                }

                // Check for tardiness (late if after 8:00 AM)
                if ($timeIn > $shiftStart) {
                    $totalTardiness++;
                }
            }
        } elseif ($isWorkingDay && !isset($leaveDays[$currentDateStr])) {
            $totalAbsences++;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="styles/user-attendance.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">

    <title>Attendance Overview | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/user-attendance.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div class="app-container">
        <?php include('user-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                <div class="background-glow top"></div>
                <div class="background-glow bottom"></div>
                <div class="main-title">
                    <h3>Attendance Overview</h3>
                </div>
                <div class="panel-containers">
                    <div class="overview-container">
                        <div class="attendance-list">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Total Hours</th>
                                    <th>Overtime</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-table-body">
                                <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                                    <?php
                                        $currentDateStr = sprintf("%04d-%02d-%02d", $selectedYear, $selectedMonth, $day);
                                        $currentDateTime = new DateTime($currentDateStr);
                                        
                                        $timeInDisplay = '--:--'; $timeOutDisplay = '--:--';
                                        $totalHoursDisplay = '--:--'; $overtimeDisplay = '--:--';
                                        $remarks = ''; $remarksClass = '';

                                        if (isset($leaveDays[$currentDateStr])) {
                                            $remarks = 'On Leave';
                                            $remarksClass = 'remarks-standard'; // Green text
                                        } elseif (isset($monthlyAttendance[$currentDateStr])) {
                                            $record = $monthlyAttendance[$currentDateStr];
                                            if (!empty($record['TimeIn'])) {
                                                $timeIn = new DateTime($record['TimeIn']);
                                                $timeInDisplay = $timeIn->format('H:i');
                                                if ($timeIn > new DateTime($timeIn->format('Y-m-d') . ' 08:01:00')) {
                                                    $remarks = 'Late'; $remarksClass = 'remarks-undertime'; // Red text
                                                }
                                            }
                                            if (!empty($record['TimeOut'])) {
                                                $timeOut = new DateTime($record['TimeOut']);
                                                $timeOutDisplay = $timeOut->format('H:i');
                                                $shiftEnd = new DateTime($timeOut->format('Y-m-d') . ' 17:00:00');

                                                if (isset($timeIn)) {
                                                    $diff = $timeOut->diff($timeIn);
                                                    $minutes = ($diff->h * 60) + $diff->i;
                                                    if ($minutes > 60) $minutes -= 60; // Lunch break
                                                    $totalHoursDisplay = sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
                                                }

                                                if ($timeOut < $shiftEnd && empty($remarks)) {
                                                    $remarks = 'Undertime'; $remarksClass = 'remarks-undertime'; // Red text
                                                }
                                                
                                                if ($timeOut > $shiftEnd) {
                                                    $ovDiff = $timeOut->diff($shiftEnd);
                                                    $ovMinutes = ($ovDiff->h * 60) + $ovDiff->i;
                                                    $overtimeDisplay = sprintf('%02d:%02d', floor($ovMinutes / 60), $ovMinutes % 60);
                                                }
                                            }
                                        } else {
                                            $dayOfWeek = $currentDateTime->format('N');
                                            if ($dayOfWeek < 6) { // It's a weekday
                                                $remarks = 'Absent';
                                                $remarksClass = 'remarks-undertime'; // Red text
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $currentDateTime->format('d - D'); ?></td>
                                        <td><?php echo $timeInDisplay; ?></td>
                                        <td><?php echo $timeOutDisplay; ?></td>
                                        <td><?php echo $totalHoursDisplay; ?></td>
                                        <td><?php echo $overtimeDisplay; ?></td>
                                        <td class="<?php echo $remarksClass; ?>"><?php echo $remarks; ?></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                        </div>
                        <div class="attendance-information">
                            <h3><?php echo htmlspecialchars($userInfo['FullName'] ?? 'User Name'); ?></h3>
                            <p class="departmentName"><?php echo htmlspecialchars($userInfo['DepartmentName'] ?? 'Department'); ?> Department</p>
                            <hr>
                            <div class="select-container">
                                <p>Select Month</p>
                                <select name="selectMonth" id="selectMonth" onchange="location = this.value;">
                                    <?php
                                    $start = new DateTime(date('Y-m-01'));
                                    for ($i = 0; $i < 12; $i++) {
                                        $year = $start->format('Y');
                                        $month = $start->format('m');
                                        $displayText = $start->format('F Y');
                                        $url = "user-attendance.php?year=$year&month=$month";
                                        $selected = ($year == $selectedYear && $month == $selectedMonth) ? 'selected' : '';
                                        echo "<option value='$url' $selected>$displayText</option>";
                                        $start->modify('-1 month');
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="info-row">
                                <p>Total Regular Hours Worked</p>
                                <h4><?php echo floor($totalRegularHours); ?> Hours</h4>
                            </div>
                            <div class="info-row">
                                <p>Total Days Worked</p>
                                <h4><?php echo $totalDaysWorked; ?> Days</h4>
                            </div>
                            <div class="info-row">
                                <p>Total Overtime Hours</p>
                                <h4><?php echo floor($totalOvertimeHours); ?> Hours</h4>
                            </div>
                            <div class="info-row">
                                <p>Total Tardiness</p>
                                <h4><?php echo $totalTardiness; ?> Days</h4>
                            </div>
                            <div class="info-row">
                                <p>Total Absences</p>
                                <h4><?php echo $totalAbsences; ?> Days</h4>
                            </div>
                            <a href="handlers/export-attendance.php?year=<?php echo $selectedYear; ?>&month=<?php echo $selectedMonth; ?>" id="exportBtn" class="export-btn" style="text-decoration: none;">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M13.725 6.30125C13.5492 6.47681 13.3109 6.57543 13.0625 6.57543C12.8141 6.57543 12.5758 6.47681 12.4 6.30125L9.9375 3.83875V13.0625C9.9375 13.3111 9.83873 13.5496 9.66291 13.7254C9.4871 13.9012 9.24864 14 9 14C8.75136 14 8.5129 13.9012 8.33709 13.7254C8.16127 13.5496 8.0625 13.3111 8.0625 13.0625V3.83875L5.6 6.30125C5.42228 6.46685 5.18722 6.557 4.94435 6.55272C4.70147 6.54843 4.46974 6.45004 4.29797 6.27828C4.12621 6.10651 4.02782 5.87478 4.02353 5.6319C4.01925 5.38903 4.1094 5.15397 4.275 4.97625L8.3375 0.91375L9 0.25L9.6625 0.9125L13.725 4.975C13.8121 5.06206 13.8812 5.16544 13.9284 5.27922C13.9755 5.393 13.9998 5.51496 13.9998 5.63813C13.9998 5.76129 13.9755 5.88325 13.9284 5.99703C13.8812 6.11081 13.8121 6.21419 13.725 6.30125ZM2.125 11.1875C2.125 10.9389 2.02623 10.7004 1.85041 10.5246C1.6746 10.3488 1.43614 10.25 1.1875 10.25C0.93886 10.25 0.700403 10.3488 0.524587 10.5246C0.348772 10.7004 0.25 10.9389 0.25 11.1875V15.25C0.25 15.913 0.513392 16.5489 0.982233 17.0178C1.45107 17.4866 2.08696 17.75 2.75 17.75H15.25C15.913 17.75 16.5489 17.4866 17.0178 17.0178C17.4866 16.5489 17.75 15.913 17.75 15.25V11.1875C17.75 10.9389 17.6512 10.7004 17.4754 10.5246C17.2996 10.3488 17.0611 10.25 16.8125 10.25C16.5639 10.25 16.3254 10.3488 16.1496 10.5246C15.9738 10.7004 15.875 10.9389 15.875 11.1875V15.25C15.875 15.4158 15.8092 15.5747 15.6919 15.6919C15.5747 15.8092 15.4158 15.875 15.25 15.875H2.75C2.58424 15.875 2.42527 15.8092 2.30806 15.6919C2.19085 15.5747 2.125 15.4158 2.125 15.25V11.1875Z" fill="#EEEEEE"/>
                                </svg>
                                <span>Export</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Password Confirmation Modal Component -->
    <div id="exportAuthModal" class="modal">
        <div class="modal-content export-modal-content">
            <div class="modal-header">
                <h3>Confirm Export</h3>
                <span class="close-btn" onclick="closeExportModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Please enter your password to authorize this export.</p>
                
                <div class="form-group export-input-group">
                    <input type="password" id="exportPasswordInput" placeholder="Enter your password" autocomplete="current-password">
                    <div id="exportAuthError" class="input-error-message"></div>
                </div>

                <div class="form-actions modal-footer-actions">
                    <button type="button" class="btn-cancel" onclick="closeExportModal()">Cancel</button>
                    <button type="button" id="confirmExportBtn" class="btn-confirm">Confirm Export</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>



