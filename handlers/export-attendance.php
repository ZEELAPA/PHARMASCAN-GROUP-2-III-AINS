<?php
// (0) SETUP - Include Composer's autoloader to use PhpSpreadsheet
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// (1) BOILERPLATE - Initialize session and database connection
include('../auth.php');
include('../sqlconnect.php');

// (2) GET INPUT - Get user from session and date from URL parameters
$accountID = $_SESSION['AccountID'];
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// (3) DATA FETCHING FUNCTIONS - Copied from user-attendance.php
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


// (4) FETCH DATA - Call the functions to get all necessary information
$userInfo = getUserInfo($conn, $accountID);
$monthlyAttendance = getUserMonthlyAttendance($conn, $accountID, $selectedYear, $selectedMonth);
$leaveDays = getLeaveStatusForMonth($conn, $accountID, $selectedYear, $selectedMonth);

// (5) GENERATE EXCEL FILE
try {
    // Load the template file
    $templatePath = '../Dtr-Sample.xlsx';
    if (!file_exists($templatePath)) {
        die("Error: Template file '{$templatePath}' not found. Please ensure it is in the correct directory.");
    }
    $spreadsheet = IOFactory::load($templatePath);
    $sheet = $spreadsheet->getActiveSheet();

    // Populate header information
    $fullName = $userInfo['FullName'] ?? 'Unknown User';
    $monthName = date('F', mktime(0, 0, 0, $selectedMonth, 1));
    $sheet->setCellValue('A2', $fullName);
    $sheet->setCellValue('A3', strtoupper($monthName) . ' ' . $selectedYear);
    $sheet->setCellValue('E4', 'Exported at: ' . date('Y-m-d H:i:s'));

    // Populate the main attendance data table
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
    $startRow = 6; // Data starts at row 6 in the Excel template

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentRow = $startRow + $day - 1;

        $currentDateStr = sprintf("%04d-%02d-%02d", $selectedYear, $selectedMonth, $day);
        $currentDateTime = new DateTime($currentDateStr);
        
        // Format day as per the template's pattern (e.g., '01 - Sun', then '10')
        $dayFormat = sprintf('%02d', $day) . ' - ' . $currentDateTime->format('D');
        
        // --- This logic is copied from your display page to ensure data consistency ---
        $timeInDisplay = '--:--'; $timeOutDisplay = '--:--';
        $totalHoursDisplay = '--:--'; $overtimeDisplay = '--:--';
        $remarks = '';

        if (isset($leaveDays[$currentDateStr])) {
            $remarks = 'On Leave';
        } elseif (isset($monthlyAttendance[$currentDateStr])) {
            $record = $monthlyAttendance[$currentDateStr];
            if (!empty($record['TimeIn'])) {
                $timeIn = new DateTime($record['TimeIn']);
                $timeInDisplay = $timeIn->format('H:i');
                if ($timeIn > new DateTime($timeIn->format('Y-m-d') . ' 08:01:00')) {
                    $remarks = 'Late';
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
                    $remarks = 'Undertime';
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
            }
        }

        // Write the calculated values to the corresponding cells in the current row
        $sheet->setCellValue('A' . $currentRow, $dayFormat);
        $sheet->setCellValue('B' . $currentRow, $timeInDisplay);
        $sheet->setCellValue('C' . $currentRow, $timeOutDisplay);
        $sheet->setCellValue('D' . $currentRow, $totalHoursDisplay);
        $sheet->setCellValue('E' . $currentRow, $overtimeDisplay);
        $sheet->setCellValue('F' . $currentRow, $remarks);
    }
    
    // Clear any extra rows in the template if the current month is shorter than 31 days
    $templateTotalDays = 31;
    if ($daysInMonth < $templateTotalDays) {
        for ($day = $daysInMonth + 1; $day <= $templateTotalDays; $day++) {
            $currentRow = $startRow + $day - 1;
            // Clear all columns for that row
            $sheet->fromArray(['', '', '', '', '', ''], null, 'A' . $currentRow);
        }
    }

    // (6) SEND FILE TO BROWSER FOR DOWNLOAD
    // Sanitize the name for a safe filename
    $safeFullName = preg_replace('/[^A-Za-z0-9_]/', '_', $fullName);
    $filename = "DTR_{$safeFullName}_{$selectedYear}-{$selectedMonth}.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    die('Error creating Excel file: ' . $e->getMessage());
}
?>