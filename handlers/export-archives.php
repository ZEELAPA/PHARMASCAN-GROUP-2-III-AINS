<?php
// (0) SETUP
require '../vendor/autoload.php';
include('../auth.php');
require_admin();
include('../sqlconnect.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// (1) GET INPUT PARAMETERS
$table = $_GET['table'] ?? 'tblleaveformarchive';

// NEW: Capture Filter Parameters
$startDate = $_GET['start_date'] ?? '1970-01-01';
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$sortOrder = $_GET['sort_order'] ?? 'DESC';

// Sanitize inputs
$sortOrder = ($sortOrder === 'ASC') ? 'ASC' : 'DESC';
$startDateQuery = $startDate . ' 00:00:00';
$endDateQuery = $endDate . ' 23:59:59';

// (2) ROUTE TO CORRECT EXPORT FUNCTION
switch ($table) {
    case 'tblleaveformarchive':
        exportLeaveArchive($conn, $startDateQuery, $endDateQuery, $sortOrder);
        break;
    case 'tblagendaarchive':
        exportAgendaArchive($conn, $startDateQuery, $endDateQuery, $sortOrder);
        break;
    case 'tblaccountarchive':
        exportAccountArchive($conn, $startDateQuery, $endDateQuery, $sortOrder);
        break;
    default:
        die("Invalid table specified.");
}

// ==================================================================
// EXPORT FUNCTIONS
// ==================================================================

function exportLeaveArchive($conn, $start, $end, $sort) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet()->setTitle("Leave Archives");

    // 1. Set Title and Metadata
    $sheet->setCellValue('A1', 'Leave Application Archives');
    $sheet->setCellValue('A2', "Filter: " . date('M d, Y', strtotime($start)) . " to " . date('M d, Y', strtotime($end)));
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    // 2. Define Headers
    $headers = [
        'Record ID', 'Original Leave ID', 'Employee Name', 'Leave Type', 
        'Start Date', 'Return Date', 'Status', 'Remarks', 
        'Archived By', 'Archived Date'
    ];
    $sheet->fromArray($headers, null, 'A4');
    formatHeaderRow($sheet, 'A4:J4');

    // 3. Fetch Data with WHERE and ORDER BY
    $sql = "
        SELECT
            la.ArchivedLeaveID, la.LeaveID,
            CONCAT(p_emp.FirstName, ' ', p_emp.LastName) AS EmployeeName,
            la.Reason, la.ScheduledLeave, la.ScheduledReturn, 
            la.LeaveStatus, la.Remarks,
            CONCAT(p_arch.FirstName, ' ', p_arch.LastName) AS ArchiverName,
            la.ArchivedAt
        FROM tblleaveformarchive la
        JOIN tblaccounts a_emp ON la.AccountID = a_emp.AccountID
        JOIN tblemployees e_emp ON a_emp.EmployeeID = e_emp.EmployeeID
        JOIN tblpersonalinfo p_emp ON e_emp.PersonalID = p_emp.PersonalID
        LEFT JOIN tblaccounts a_arch ON la.ArchivedBy = a_arch.AccountID
        LEFT JOIN tblemployees e_arch ON a_arch.EmployeeID = e_arch.EmployeeID
        LEFT JOIN tblpersonalinfo p_arch ON e_arch.PersonalID = p_arch.PersonalID
        WHERE la.ArchivedAt BETWEEN '$start' AND '$end'
        ORDER BY la.ArchivedAt $sort;
    ";
    
    $result = mysqli_query($conn, $sql);
    $rowNum = 5;

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rowData = [
                $row['ArchivedLeaveID'],
                $row['LeaveID'],
                $row['EmployeeName'],
                $row['Reason'],
                date('M d, Y', strtotime($row['ScheduledLeave'])),
                date('M d, Y', strtotime($row['ScheduledReturn'])),
                $row['LeaveStatus'],
                $row['Remarks'],
                $row['ArchiverName'] ?? 'N/A',
                date('M d, Y h:i A', strtotime($row['ArchivedAt']))
            ];
            $sheet->fromArray($rowData, null, 'A' . $rowNum++);
        }
    } else {
        $sheet->setCellValue('A5', 'No records found for the selected date range.');
    }

    autoSizeColumns($sheet, range('A', 'J'));
    downloadSpreadsheet($spreadsheet, "Leave_Archives_" . date('Y-m-d') . ".xlsx");
}

function exportAgendaArchive($conn, $start, $end, $sort) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet()->setTitle("Agenda Archives");

    $sheet->setCellValue('A1', 'Agenda/Task Archives');
    $sheet->setCellValue('A2', "Filter: " . date('M d, Y', strtotime($start)) . " to " . date('M d, Y', strtotime($end)));
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    $headers = [
        'Record ID', 'Agenda ID', 'Task', 'Assigned By', 
        'Date Created', 'Deadline', 'Priority', 'Final Status', 
        'Remarks', 'Archived By', 'Archived Date'
    ];
    $sheet->fromArray($headers, null, 'A4');
    formatHeaderRow($sheet, 'A4:K4');

    $sql = "
        SELECT
            aa.ArchivedAgendaID, aa.AgendaID, aa.Task,
            CONCAT(p_creator.FirstName, ' ', p_creator.LastName) AS CreatorName,
            aa.Date, aa.Deadline, aa.Priority, aa.Status, aa.Remarks,
            CONCAT(p_arch.FirstName, ' ', p_arch.LastName) AS ArchiverName,
            aa.ArchivedAt
        FROM tblagendaarchive aa
        LEFT JOIN tblaccounts a_arch ON aa.ArchivedBy = a_arch.AccountID
        LEFT JOIN tblemployees e_arch ON a_arch.EmployeeID = e_arch.EmployeeID
        LEFT JOIN tblpersonalinfo p_arch ON e_arch.PersonalID = p_arch.PersonalID
        LEFT JOIN tblaccounts a_creator ON aa.AccountID = a_creator.AccountID
        LEFT JOIN tblemployees e_creator ON a_creator.EmployeeID = e_creator.EmployeeID
        LEFT JOIN tblpersonalinfo p_creator ON e_creator.PersonalID = p_creator.PersonalID
        WHERE aa.ArchivedAt BETWEEN '$start' AND '$end'
        ORDER BY aa.ArchivedAt $sort;
    ";

    $result = mysqli_query($conn, $sql);
    $rowNum = 5;

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rowData = [
                $row['ArchivedAgendaID'],
                $row['AgendaID'],
                $row['Task'],
                $row['CreatorName'] ?? 'N/A',
                date('M d, Y', strtotime($row['Date'])),
                date('M d, Y h:i A', strtotime($row['Deadline'])),
                $row['Priority'],
                $row['Status'],
                $row['Remarks'],
                $row['ArchiverName'] ?? 'N/A',
                date('M d, Y h:i A', strtotime($row['ArchivedAt']))
            ];
            $sheet->fromArray($rowData, null, 'A' . $rowNum++);
        }
    } else {
        $sheet->setCellValue('A5', 'No records found for the selected date range.');
    }

    autoSizeColumns($sheet, range('A', 'K'));
    downloadSpreadsheet($spreadsheet, "Agenda_Archives_" . date('Y-m-d') . ".xlsx");
}

function exportAccountArchive($conn, $start, $end, $sort) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet()->setTitle("Account Archives");

    $sheet->setCellValue('A1', 'Employee Account Archives');
    $sheet->setCellValue('A2', "Filter: " . date('M d, Y', strtotime($start)) . " to " . date('M d, Y', strtotime($end)));
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    $headers = [
        'Record ID', 'Original Acct ID', 'Employee Name', 'Last Role', 
        'Department', 'Last Position', 'Date Employed', 
        'Reason/Remarks', 'Archived By', 'Archived Date'
    ];
    $sheet->fromArray($headers, null, 'A4');
    formatHeaderRow($sheet, 'A4:J4');

    $sql = "
        SELECT
            aa.ArchivedAccountID, aa.OriginalAccountID, aa.FullName, 
            aa.Role, aa.DepartmentName, aa.Position, aa.DateEmployed, 
            aa.Reason, aa.DateArchived,
            CONCAT(p_arch.FirstName, ' ', p_arch.LastName) AS ArchiverName
        FROM tblaccountarchive aa
        LEFT JOIN tblaccounts a_arch ON aa.ArchivedBy = a_arch.AccountID
        LEFT JOIN tblemployees e_arch ON a_arch.EmployeeID = e_arch.EmployeeID
        LEFT JOIN tblpersonalinfo p_arch ON e_arch.PersonalID = p_arch.PersonalID
        WHERE aa.DateArchived BETWEEN '$start' AND '$end'
        ORDER BY aa.DateArchived $sort;
    ";

    $result = mysqli_query($conn, $sql);
    $rowNum = 5;

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rowData = [
                $row['ArchivedAccountID'],
                $row['OriginalAccountID'],
                $row['FullName'],
                $row['Role'],
                $row['DepartmentName'],
                $row['Position'],
                $row['DateEmployed'],
                $row['Reason'],
                $row['ArchiverName'] ?? 'System',
                date('M d, Y', strtotime($row['DateArchived']))
            ];
            $sheet->fromArray($rowData, null, 'A' . $rowNum++);
        }
    } else {
        $sheet->setCellValue('A5', 'No records found for the selected date range.');
    }

    autoSizeColumns($sheet, range('A', 'J'));
    downloadSpreadsheet($spreadsheet, "Account_Archives_" . date('Y-m-d') . ".xlsx");
}

// ==================================================================
// HELPER FUNCTIONS (Same as before)
// ==================================================================

function formatHeaderRow($sheet, $range) {
    $styleArray = [
        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF4F81BD'] 
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
    ];
    $sheet->getStyle($range)->applyFromArray($styleArray);
}

function autoSizeColumns($sheet, $columns) {
    foreach ($columns as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

function downloadSpreadsheet($spreadsheet, $filename) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>