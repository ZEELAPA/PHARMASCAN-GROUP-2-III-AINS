<?php
// (0) SETUP
require '../vendor/autoload.php';
include('../auth.php');
require_admin();
include('../sqlconnect.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

// (1) GET INPUT PARAMETERS
$exportType = $_GET['type'] ?? 'daily';
$employeeId = $_GET['employee_id'] ?? 'all';
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedYear = $_GET['year'] ?? date('Y');
$selectedMonth = $_GET['month'] ?? date('m');

// (2) ROUTE TO THE CORRECT EXPORT FUNCTION
switch ($exportType) {
    case 'monthly':
        exportMonthlyDTR($conn, $employeeId, $selectedYear, $selectedMonth);
        break;
    case 'daily':
        exportDailyReport($conn, $selectedDate, $employeeId);
        break;
    case 'weekly':
        exportWeeklyReport($conn, $selectedDate, $employeeId);
        break;
    default:
        die("Invalid export type specified.");
}

// ==================================================================
// EXPORT FUNCTIONS
// ==================================================================

function exportDailyReport($conn, $date, $employeeId) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet()->setTitle("Daily Attendance " . $date);

    // --- NEW HEADER BLOCK ---
    // Format the date for display
    $formattedDate = (new DateTime($date))->format('F j, Y');

    // Set header content
    $sheet->setCellValue('A1', 'Daily Attendance Report');
    $sheet->setCellValue('A2', 'Date: ' . $formattedDate);
    $sheet->setCellValue('A3', 'Exported at: ' . date('Y-m-d H:i:s'));

    // Make the main title bold
    $sheet->getStyle('A1')->getFont()->setBold(true);
    
    // --- TABLE HEADERS (Shifted Down) ---
    $headers = ['Employee Name', 'Status', 'Time In', 'Time Out', 'Overtime Hours', 'Remarks'];
    $sheet->fromArray($headers, null, 'A5'); // Start headers at row 5

    // --- DATA ROWS (Shifted Down) ---
    $records = getDailyRecords($conn, $date, $employeeId);
    $rowNum = 6; // Start data at row 6
    foreach ($records as $record) {
        // ... (the rest of your data processing logic remains exactly the same)
        $status = 'Absent'; $timeIn = '--:--'; $timeOut = '--:--'; $overtimeDisplay = '--:--'; $remarks = $record['Remarks'] ?? '--:--';

        if ($record['TimeIn']) {
            $timeInObj = new DateTime($record['TimeIn']);
            $timeIn = $timeInObj->format('g:i A');

            if ($record['TimeOut']) {
                $status = 'Finished';
                $timeOutObj = new DateTime($record['TimeOut']);
                $timeOut = $timeOutObj->format('g:i A');
                
                $shiftEnd = new DateTime($date . ' 17:00:00');
                if ($timeOutObj > $shiftEnd) {
                    $diff = $timeOutObj->diff($shiftEnd);
                    $hours = $diff->h + ($diff->days * 24);
                    if ($hours > 0) {
                        $label = ($hours === 1) ? ' Hour' : ' Hours';
                        $overtimeDisplay = "{$hours}{$label}";
                    }
                }
            } else {
                $status = 'Working';
            }
        }

        $rowData = [$record['FullName'], $status, $timeIn, $timeOut, $overtimeDisplay, $remarks];
        $sheet->fromArray($rowData, null, 'A' . $rowNum++);
    }

    foreach (range('A', 'F') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
    downloadSpreadsheet($spreadsheet, "Daily_Report_{$date}.xlsx");
}

function exportWeeklyReport($conn, $date, $employeeId) {
    $spreadsheet = new Spreadsheet();
    $dt = new DateTime($date);
    $weekDay = $dt->format('w');
    $weekStart = (clone $dt)->modify('-' . $weekDay . ' days');
    $weekEnd = (clone $dt)->modify('+' . (6 - $weekDay) . ' days');
    $sheet = $spreadsheet->getActiveSheet()->setTitle("Weekly " . $weekStart->format('Y-m-d'));

    // --- NEW HEADER BLOCK ---
    // Format the date range for display
    $formattedRange = $weekStart->format('M j, Y') . ' to ' . $weekEnd->format('M j, Y');

    // Set header content
    $sheet->setCellValue('A1', 'Weekly Attendance Report');
    $sheet->setCellValue('A2', 'Week of: ' . $formattedRange);
    $sheet->setCellValue('A3', 'Exported at: ' . date('Y-m-d H:i:s'));
    
    // Make the main title bold
    $sheet->getStyle('A1')->getFont()->setBold(true);

    // --- TABLE HEADERS (Shifted Down) ---
    $headers = ['Employee Name'];
    $period = new DatePeriod($weekStart, new DateInterval('P1D'), (clone $weekEnd)->modify('+1 day'));
    foreach ($period as $day) $headers[] = $day->format('D, M j');
    $headers[] = 'Total Hours';
    $sheet->fromArray($headers, null, 'A5'); // Start headers at row 5

    // --- DATA ROWS (Shifted Down) ---
    $records = getWeeklyRecords($conn, $weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d'), $employeeId);
    
    // ... (Your data processing logic for employeeData remains exactly the same)
    $employeeData = [];
    foreach($records as $rec) {
        if (!isset($employeeData[$rec['FullName']])) {
            $employeeData[$rec['FullName']] = ['totalMinutes' => 0, 'days' => []];
        }
        if ($rec['TimeIn'] && $rec['TimeOut']) {
            $minutes = ((new DateTime($rec['TimeOut']))->getTimestamp() - (new DateTime($rec['TimeIn']))->getTimestamp()) / 60;
            $employeeData[$rec['FullName']]['days'][$rec['AttendanceDate']] = $minutes;
            $employeeData[$rec['FullName']]['totalMinutes'] += $minutes;
        }
    }
    
    $rowNum = 6; // Start data at row 6
    foreach ($employeeData as $name => $data) {
        $rowData = [$name];
        $currentDay = clone $weekStart;
        for ($i = 0; $i < 7; $i++) {
            $dateStr = $currentDay->format('Y-m-d');
            $minutes = $data['days'][$dateStr] ?? 0;
            $rowData[] = $minutes > 0 ? sprintf('%d:%02d', floor($minutes/60), $minutes%60) : '--:--';
            $currentDay->modify('+1 day');
        }
        $totalMinutes = $data['totalMinutes'];
        $rowData[] = sprintf('%d h %d m', floor($totalMinutes/60), $totalMinutes%60);
        $sheet->fromArray($rowData, null, 'A' . $rowNum++);
    }

    foreach (range('A', 'I') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
    downloadSpreadsheet($spreadsheet, "Weekly_Report_{$weekStart->format('Y-m-d')}.xlsx");
}


// ==================================================================
// DATA FETCHING FUNCTIONS - SYNCHRONIZED WITH handlers/get-attendance-data.php
// ==================================================================

function getDailyRecords($conn, $date, $employeeId) {
    // THIS QUERY IS NOW IDENTICAL TO THE ONE IN handlers/get-attendance-data.php
    $query = "SELECT 
                CONCAT_WS(' ', info.FirstName, info.LastName) AS FullName,
                att.TimeIn,
                att.TimeOut,
                att.Remarks
            FROM 
                tblemployees AS emp
            JOIN 
                tblpersonalinfo AS info ON emp.PersonalID = info.PersonalID
            JOIN 
                tblaccounts AS acc ON emp.EmployeeID = acc.EmployeeID
            LEFT JOIN 
                tblattendance AS att ON acc.AccountID = att.AccountID AND att.AttendanceDate = ?
            WHERE emp.EmploymentStatus IN ('Active', 'On Leave', 'On Vacation')";

    if ($employeeId !== 'all') {
        $query .= " AND emp.EmployeeID = " . (int)$employeeId;
    }
    $query .= " ORDER BY info.LastName ASC, info.FirstName ASC;";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getWeeklyRecords($conn, $start, $end, $employeeId) {
    // THIS QUERY IS NOW IDENTICAL TO THE (IMPROVED) ONE IN handlers/get-attendance-data.php
    $query = "SELECT 
                CONCAT_WS(' ', info.FirstName, info.LastName) AS FullName,
                att.AttendanceDate,
                att.TimeIn,
                att.TimeOut
            FROM 
                tblemployees AS emp
            JOIN 
                tblpersonalinfo AS info ON emp.PersonalID = info.PersonalID
            JOIN 
                tblaccounts AS acc ON emp.EmployeeID = acc.EmployeeID
            LEFT JOIN 
                tblattendance AS att ON acc.AccountID = att.AccountID 
                AND att.AttendanceDate BETWEEN ? AND ?
            WHERE emp.EmploymentStatus IN ('Active', 'On Leave', 'On Vacation')";

    if ($employeeId !== 'all') {
        $query .= " AND emp.EmployeeID = " . (int)$employeeId;
    }
    $query .= " ORDER BY info.LastName ASC, info.FirstName ASC, att.AttendanceDate ASC;";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// ==================================================================
// HELPER FUNCTIONS (Includes Monthly DTR logic)
// ==================================================================
function downloadSpreadsheet($spreadsheet, $filename) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
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

function exportMonthlyDTR($conn, $employeeId, $year, $month) {
    // This is nearly identical to your previous handlers/export-attendance.php script
    // We just need a function to get AccountID from EmployeeID
    $stmt = $conn->prepare("SELECT AccountID FROM tblaccounts WHERE EmployeeID = ?");
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) die("Employee not found or has no account.");
    $accountID = $result->fetch_assoc()['AccountID'];
    
    // Reuse data fetching logic from the user-attendance page
    // (You should ideally move these functions to a shared 'utils.php' file)
    $userInfo = getUserInfo($conn, $accountID);
    $monthlyAttendance = getUserMonthlyAttendance($conn, $accountID, $year, $month);

    $spreadsheet = IOFactory::load('../Dtr-Sample.xlsx');
    $sheet = $spreadsheet->getActiveSheet();

    $fullName = $userInfo['FullName'] ?? 'Unknown User';
    $monthName = date('F', mktime(0, 0, 0, $month, 1));
    $sheet->setCellValue('A2', $fullName);
    $sheet->setCellValue('A3', strtoupper($monthName) . ' ' . $year);
    $sheet->setCellValue('A4', 'Exported at: ' . date('Y-m-d H:i:s'));

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $startRow = 6;
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentRow = $startRow + $day - 1;
        $currentDateStr = sprintf("%04d-%02d-%02d", $year, $month, $day);
        $currentDateTime = new DateTime($currentDateStr);
        $dayFormat = sprintf('%02d', $day) . ($day <= 9 ? ' - ' . $currentDateTime->format('D') : '');

        $timeInDisplay = '--:--'; $timeOutDisplay = '--:--'; $totalHoursDisplay = '--:--'; $overtimeDisplay = '--:--'; $remarks = '';
        if (isset($monthlyAttendance[$currentDateStr])) {
            $record = $monthlyAttendance[$currentDateStr];
            if (!empty($record['TimeIn'])) {
                $timeIn = new DateTime($record['TimeIn']); $timeInDisplay = $timeIn->format('H:i');
                if ($timeIn > new DateTime($timeIn->format('Y-m-d') . ' 08:01:00')) $remarks = 'Late';
            }
            if (!empty($record['TimeOut'])) {
                $timeOut = new DateTime($record['TimeOut']); $timeOutDisplay = $timeOut->format('H:i');
                $shiftEnd = new DateTime($timeOut->format('Y-m-d') . ' 17:00:00');
                if (isset($timeIn)) {
                    $diff = $timeOut->diff($timeIn); $minutes = ($diff->h * 60) + $diff->i;
                    if ($minutes > 60) $minutes -= 60;
                    $totalHoursDisplay = sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
                }
                if ($timeOut < $shiftEnd && empty($remarks)) $remarks = 'Undertime';
                if ($timeOut > $shiftEnd) {
                    $ovDiff = $timeOut->diff($shiftEnd); $ovMinutes = ($ovDiff->h * 60) + $ovDiff->i;
                    $overtimeDisplay = sprintf('%02d:%02d', floor($ovMinutes / 60), $ovMinutes % 60);
                }
            }
        }
        $sheet->setCellValue('A' . $currentRow, $dayFormat)->setCellValue('B' . $currentRow, $timeInDisplay)->setCellValue('C' . $currentRow, $timeOutDisplay)->setCellValue('D' . $currentRow, $totalHoursDisplay)->setCellValue('E' . $currentRow, $overtimeDisplay)->setCellValue('F' . $currentRow, $remarks);
    }
    
    if ($daysInMonth < 31) {
        for ($day = $daysInMonth + 1; $day <= 31; $day++) {
            $sheet->fromArray(['', '', '', '', '', ''], null, 'A' . ($startRow + $day - 1));
        }
    }

    $safeName = preg_replace('/[^A-Za-z0-9_]/', '_', $fullName);
    downloadSpreadsheet($spreadsheet, "DTR_{$safeName}_{$year}-{$month}.xlsx");
}