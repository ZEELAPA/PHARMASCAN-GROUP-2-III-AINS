<?php
// (0) SETUP
require '../vendor/autoload.php';
include('../auth.php');
require_admin();
include('../sqlconnect.php');

// (1) GET INPUT PARAMETERS
$table = $_GET['table'] ?? 'tblleaveformarchive';

// NEW: Get Filter Parameters
$startDate = $_GET['start_date'] ?? '1970-01-01'; // Default to beginning of time
$endDate = $_GET['end_date'] ?? date('Y-m-d');    // Default to today
$sortOrder = $_GET['sort_order'] ?? 'DESC';       // Default to Newest

// Validate Sort Order to prevent SQL Injection
$sortOrder = ($sortOrder === 'ASC') ? 'ASC' : 'DESC';

// Ensure End Date includes the full day (until 23:59:59)
$endDateQuery = $endDate . ' 23:59:59';
$startDateQuery = $startDate . ' 00:00:00';

// (2) SETUP TCPDF (Same as before)
class PharmaPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(24, 84, 148);
        $this->Cell(0, 10, 'PHARMASCAN ARCHIVES', 0, 1, 'C', 0, '', 0);
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 5, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'C', 0, '', 0);
        $this->Ln(5);
        $this->SetLineStyle(array('width' => 0.5, 'color' => array(0, 0, 0)));
        $this->Line($this->GetX(), $this->GetY(), $this->getPageWidth()-$this->GetX(), $this->GetY()); 
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new PharmaPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('PharmaScan');
$pdf->SetTitle('Archive Export');
$pdf->SetMargins(10, 30, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

// Add Date Range Info to PDF body
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 5, "Filter: From " . date('M d, Y', strtotime($startDate)) . " to " . date('M d, Y', strtotime($endDate)), 0, 1, 'L');
$pdf->Ln(5);

// (3) ROUTE
switch ($table) {
    case 'tblleaveformarchive':
        generateLeavePdf($conn, $pdf, $startDateQuery, $endDateQuery, $sortOrder);
        break;
    case 'tblagendaarchive':
        generateAgendaPdf($conn, $pdf, $startDateQuery, $endDateQuery, $sortOrder);
        break;
    case 'tblaccountarchive':
        generateAccountPdf($conn, $pdf, $startDateQuery, $endDateQuery, $sortOrder);
        break;
    default:
        die("Invalid table specified.");
}

// ==================================================================
// FUNCTIONS WITH FILTER LOGIC
// ==================================================================

function generateLeavePdf($conn, $pdf, $start, $end, $sort) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Leave Application Archives', 0, 1, 'L');
    
    // UPDATED SQL with WHERE and dynamic ORDER BY
    $sql = "
        SELECT la.ArchivedLeaveID, la.LeaveID,
            CONCAT(p_emp.FirstName, ' ', p_emp.LastName) AS EmployeeName,
            la.Reason, la.ScheduledLeave, la.ScheduledReturn, la.LeaveStatus, la.ArchivedAt
        FROM tblleaveformarchive la
        JOIN tblaccounts a_emp ON la.AccountID = a_emp.AccountID
        JOIN tblemployees e_emp ON a_emp.EmployeeID = e_emp.EmployeeID
        JOIN tblpersonalinfo p_emp ON e_emp.PersonalID = p_emp.PersonalID
        WHERE la.ArchivedAt BETWEEN '$start' AND '$end'
        ORDER BY la.ArchivedAt $sort;
    ";
    
    // ... (The rest of the HTML table generation code remains the same) ...
    // Note: Insert the $sql execution and HTML generation here from previous response
    // Just ensure you use the result from the NEW query above.
    
    $result = mysqli_query($conn, $sql);
    
    // -- COPY TABLE GENERATION FROM PREVIOUS RESPONSE HERE --
    $html = '<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #185494; color: #FFFFFF; font-weight: bold;">
            <th width="10%">ID</th>
            <th width="20%">Employee</th>
            <th width="20%">Type</th>
            <th width="15%">Start</th>
            <th width="15%">End</th>
            <th width="10%">Status</th>
            <th width="10%">Archived</th>
        </tr>
    </thead>
    <tbody>';

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $html .= '<tr>
                <td>' . $row['ArchivedLeaveID'] . '</td>
                <td>' . $row['EmployeeName'] . '</td>
                <td>' . $row['Reason'] . '</td>
                <td>' . date('M d, Y', strtotime($row['ScheduledLeave'])) . '</td>
                <td>' . date('M d, Y', strtotime($row['ScheduledReturn'])) . '</td>
                <td>' . $row['LeaveStatus'] . '</td>
                <td>' . date('M d, Y', strtotime($row['ArchivedAt'])) . '</td>
            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" align="center">No records found for this date range.</td></tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Leave_Archives.pdf', 'D');
}

function generateAgendaPdf($conn, $pdf, $start, $end, $sort) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Agenda Archives', 0, 1, 'L');

    // UPDATED SQL
    $sql = "
        SELECT aa.ArchivedAgendaID, aa.Task, aa.Date, aa.Deadline, aa.Priority, aa.Status, aa.ArchivedAt,
        CONCAT(p_arch.FirstName, ' ', p_arch.LastName) AS ArchiverName
        FROM tblagendaarchive aa
        LEFT JOIN tblaccounts a_arch ON aa.ArchivedBy = a_arch.AccountID
        LEFT JOIN tblemployees e_arch ON a_arch.EmployeeID = e_arch.EmployeeID
        LEFT JOIN tblpersonalinfo p_arch ON e_arch.PersonalID = p_arch.PersonalID
        WHERE aa.ArchivedAt BETWEEN '$start' AND '$end'
        ORDER BY aa.ArchivedAt $sort;
    ";

    $result = mysqli_query($conn, $sql);
    
    // -- COPY TABLE GENERATION FROM PREVIOUS RESPONSE HERE --
    // (Reuse the Table HTML logic, just ensure you handle "No records" case like above)
    $html = '<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #185494; color: #FFFFFF; font-weight: bold;">
            <th width="10%">ID</th>
            <th width="25%">Task</th>
            <th width="15%">Target</th>
            <th width="15%">Deadline</th>
            <th width="10%">Priority</th>
            <th width="10%">Status</th>
            <th width="15%">Archiver</th>
        </tr>
    </thead>
    <tbody>';

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $html .= '<tr>
                <td>' . $row['ArchivedAgendaID'] . '</td>
                <td>' . $row['Task'] . '</td>
                <td>' . date('M d, Y', strtotime($row['Date'])) . '</td>
                <td>' . date('M d, Y', strtotime($row['Deadline'])) . '</td>
                <td>' . $row['Priority'] . '</td>
                <td>' . $row['Status'] . '</td>
                <td>' . ($row['ArchiverName'] ?? 'N/A') . '</td>
            </tr>';
        }
    } else {
         $html .= '<tr><td colspan="7" align="center">No records found.</td></tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Agenda_Archives.pdf', 'D');
}

function generateAccountPdf($conn, $pdf, $start, $end, $sort) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Account Archives', 0, 1, 'L');

    // UPDATED SQL
    $sql = "
        SELECT aa.ArchivedAccountID, aa.FullName, aa.Role, aa.DepartmentName, aa.Position, aa.Reason, aa.DateArchived
        FROM tblaccountarchive aa
        WHERE aa.DateArchived BETWEEN '$start' AND '$end'
        ORDER BY aa.DateArchived $sort;
    ";

    $result = mysqli_query($conn, $sql);

    // -- COPY TABLE GENERATION FROM PREVIOUS RESPONSE HERE --
    $html = '<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color: #185494; color: #FFFFFF; font-weight: bold;">
            <th width="10%">ID</th>
            <th width="20%">Name</th>
            <th width="10%">Role</th>
            <th width="15%">Department</th>
            <th width="15%">Position</th>
            <th width="20%">Reason</th>
            <th width="10%">Date</th>
        </tr>
    </thead>
    <tbody>';

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $html .= '<tr>
                <td>' . $row['ArchivedAccountID'] . '</td>
                <td>' . $row['FullName'] . '</td>
                <td>' . $row['Role'] . '</td>
                <td>' . $row['DepartmentName'] . '</td>
                <td>' . $row['Position'] . '</td>
                <td>' . $row['Reason'] . '</td>
                <td>' . date('M d, Y', strtotime($row['DateArchived'])) . '</td>
            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" align="center">No records found.</td></tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Account_Archives.pdf', 'D');
}
?>