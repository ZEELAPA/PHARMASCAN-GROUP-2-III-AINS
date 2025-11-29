<?php

include('auth.php');

require_admin();

include('sqlconnect.php');
function fetchLeaveArchiveData($conn) {
    $sql = "
        SELECT
            la.ArchivedLeaveID AS RecordID,
            la.LeaveID,
            la.ScheduledLeave,
            la.ScheduledReturn,
            la.Reason,
            la.LeaveStatus,
            la.Remarks,
            la.CreatedAt,
            la.ArchivedAt,
            la.AccountID,
            la.ArchivedBy,
            CONCAT(p_emp.FirstName, ' ', p_emp.LastName) AS EmployeeName,
            CONCAT(p_arch.FirstName, ' ', p_arch.LastName) AS ArchiverName
        FROM
            tblleaveformarchive la
        JOIN
            tblaccounts a_emp ON la.AccountID = a_emp.AccountID
        JOIN
            tblemployees e_emp ON a_emp.EmployeeID = e_emp.EmployeeID
        JOIN
            tblpersonalinfo p_emp ON e_emp.PersonalID = p_emp.PersonalID
        LEFT JOIN
            tblaccounts a_arch ON la.ArchivedBy = a_arch.AccountID
        LEFT JOIN
            tblemployees e_arch ON a_arch.EmployeeID = e_arch.EmployeeID
        LEFT JOIN
            tblpersonalinfo p_arch ON e_arch.PersonalID = p_arch.PersonalID
        ORDER BY la.ArchivedAt DESC;
    ";
    
    $result = mysqli_query($conn, $sql);
    $records = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Prepare structured data for display and the modal details
            $record = [
                'RecordID' => $row['RecordID'],
                'TableName' => 'Leave Archive',
                'EmployeeName' => $row['EmployeeName'], // Primary name shown in the list
                'ActionType' => $row['LeaveStatus'], // Use LeaveStatus as the ActionType
                'Date' => $row['ArchivedAt'],
                'Details' => [
                    'Original Leave ID' => $row['LeaveID'],
                    'Employee Name' => $row['EmployeeName'],
                    'Leave Type' => $row['Reason'],
                    'Status' => $row['LeaveStatus'],
                    'Start Date' => date('F d, Y', strtotime($row['ScheduledLeave'])),
                    'Return Date' => date('F d, Y', strtotime($row['ScheduledReturn'])),
                    'Original Creation Date' => date('F d, Y h:i A', strtotime($row['CreatedAt'])),
                    'Remarks' => $row['Remarks'],
                    'Archived By' => $row['ArchiverName'] ?? 'N/A',
                    'Archived At' => date('F d, Y h:i A', strtotime($row['ArchivedAt'])),
                ],
            ];
            $records[] = $record;
        }
    }
    return $records;
}

function fetchAccountArchiveData($conn) {
    $sql = "
        SELECT
            aa.ArchivedAccountID AS RecordID,
            aa.OriginalAccountID,
            aa.FullName,
            aa.Role,
            aa.DepartmentName,
            aa.Position,
            aa.DateEmployed,
            aa.DateArchived,
            aa.Reason,
            CONCAT(p_arch.FirstName, ' ', p_arch.LastName) AS ArchiverName
        FROM
            tblaccountarchive aa
        LEFT JOIN
            tblaccounts a_arch ON aa.ArchivedBy = a_arch.AccountID
        LEFT JOIN
            tblemployees e_arch ON a_arch.EmployeeID = e_arch.EmployeeID
        LEFT JOIN
            tblpersonalinfo p_arch ON e_arch.PersonalID = p_arch.PersonalID
        ORDER BY aa.DateArchived DESC;
    ";

    $result = mysqli_query($conn, $sql);
    $records = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $record = [
                'RecordID' => $row['RecordID'],
                'TableName' => 'Account Archive',
                'EmployeeName' => $row['FullName'], // The archived person
                'ActionType' => 'Archived', 
                'Date' => $row['DateArchived'],
                'Details' => [
                    'Original Account ID' => $row['OriginalAccountID'],
                    'Full Name' => $row['FullName'],
                    'Last Role' => $row['Role'],
                    'Department' => $row['DepartmentName'],
                    'Last Position' => $row['Position'],
                    'Date Employed' => $row['DateEmployed'],
                    'Archived By' => $row['ArchiverName'] ?? 'System',
                    'Reason/Remarks' => $row['Reason']
                ],
            ];
            $records[] = $record;
        }
    }
    return $records;
}

function fetchAgendaArchiveData($conn) {
    $sql = "
        SELECT
            aa.ArchivedAgendaID AS RecordID,
            aa.AgendaID,
            aa.Task,
            aa.Date,
            aa.Deadline,
            aa.Priority,
            aa.Status,
            aa.Remarks,
            aa.ArchivedAt,
            aa.AccountID,
            aa.ArchivedBy,
            CONCAT(p_arch.FirstName, ' ', p_arch.LastName) AS ArchiverName,
            CONCAT(p_creator.FirstName, ' ', p_creator.LastName) AS CreatorName
        FROM
            tblagendaarchive aa
        LEFT JOIN
            tblaccounts a_arch ON aa.ArchivedBy = a_arch.AccountID
        LEFT JOIN
            tblemployees e_arch ON a_arch.EmployeeID = e_arch.EmployeeID
        LEFT JOIN
            tblpersonalinfo p_arch ON e_arch.PersonalID = p_arch.PersonalID
        LEFT JOIN
            tblaccounts a_creator ON aa.AccountID = a_creator.AccountID
        LEFT JOIN
            tblemployees e_creator ON a_creator.EmployeeID = e_creator.EmployeeID
        LEFT JOIN
            tblpersonalinfo p_creator ON e_creator.PersonalID = p_creator.PersonalID
        ORDER BY aa.ArchivedAt DESC;
    ";

    $result = mysqli_query($conn, $sql);
    $records = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $record = [
                'RecordID' => $row['RecordID'],
                'TableName' => 'Agenda Archive',
                'EmployeeName' => $row['ArchiverName'] ?? 'System', // Archiver is the primary list identifier
                'ActionType' => $row['Status'], // Use the final Status as the Action Type
                'Date' => $row['ArchivedAt'],
                'Details' => [
                    'Original Agenda ID' => $row['AgendaID'],
                    'Task' => $row['Task'],
                    'Assigned By' => $row['CreatorName'] ?? 'N/A',
                    'Date Created' => date('F d, Y', strtotime($row['Date'])),
                    'Deadline' => date('F d, Y h:i A', strtotime($row['Deadline'])),
                    'Priority' => $row['Priority'],
                    'Final Status' => $row['Status'],
                    'Remarks' => $row['Remarks'],
                    'Archived By' => $row['ArchiverName'] ?? 'N/A',
                    'Archived At' => date('F d, Y h:i A', strtotime($row['ArchivedAt'])),
                ],
            ];
            $records[] = $record;
        }
    }
    return $records;
}


// --- MAIN LOGIC ---

// Get the selected table from the query parameter, default to leave archive
$selectedTable = $_GET['table'] ?? 'tblleaveformarchive';

if ($selectedTable === 'tblleaveformarchive') {
    $archiveRecords = fetchLeaveArchiveData($conn);
    $currentConfig = [
        'title' => 'Leave Applications Archive',
        'headers' => ['Record ID', 'Employee Name', 'Final Status', 'Archived Date'],
    ];
} elseif ($selectedTable === 'tblagendaarchive') {
    $archiveRecords = fetchAgendaArchiveData($conn);
    $currentConfig = [
        'title' => 'Agenda/Task Archive',
        'headers' => ['Record ID', 'Archived By', 'Final Status', 'Archived Date'],
    ];
} elseif ($selectedTable === 'tblaccountarchive') {
    $archiveRecords = fetchAccountArchiveData($conn);
    $currentConfig = [
        'title' => 'Employee Account Archive',
        'headers' => ['Record ID', 'Employee Name', 'Status', 'Archived Date'],
    ];
} else {
    $archiveRecords = [];
    $currentConfig = ['title' => 'Unknown Archive', 'headers' => ['Record ID', 'Details', 'Date']];
}

// Close connection
mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/archives-management.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>Archives Management | PharmaScan</title> 

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/archives-management.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div class="app-container">
        
        <?php include('admin-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                <div class="panels-container">
                    <div class="main-title">
                        <h3>Archives</h3>
                        <p>Looking for something? A complete history of every action.</p>
                    </div>    
                    <div class="archive-list-panel">
                        <div class="archive-list-header">
                            <div class="archive-selection">
                                <select id="archiveTableSelect" onchange="window.location.href='archives-management.php?table=' + this.value">
                                    <option value="tblleaveformarchive" <?= $selectedTable === 'tblleaveformarchive' ? 'selected' : '' ?>>Leave Archives</option>
                                    <option value="tblagendaarchive" <?= $selectedTable === 'tblagendaarchive' ? 'selected' : '' ?>>Agenda Archives</option>
                                    <option value="tblaccountarchive" <?= $selectedTable === 'tblaccountarchive' ? 'selected' : '' ?>>Account Archives</option>
                                </select>
                            </div>
                            
                            <div class="export-button-container">
                                <!-- Change button to open modal -->
                                <button id="openExportModalBtn">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 2C2.89543 2 2 2.89543 2 4V16C2 17.1046 2.89543 18 4 18H16C17.1046 18 18 17.1046 18 16V8L12 2H4Z" fill="#EEEEEE"/>
                                    </svg>
                                    Export Options
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div class="archive-list-body"> 
                            <div class="archive-list-view-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <?php foreach ($currentConfig['headers'] as $header): ?>
                                                <th><?= htmlspecialchars($header) ?></th>
                                            <?php endforeach; ?>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($archiveRecords)): ?>
                                            <tr>
                                                <td colspan="<?= count($currentConfig['headers']) + 1 ?>" style="text-align: center;">No archive records found for this table.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($archiveRecords as $record): ?>
                                                <tr class="archive-row"
                                                    data-record-id="<?= htmlspecialchars($record['RecordID']) ?>"
                                                    data-table-name="<?= htmlspecialchars($record['TableName']) ?>"
                                                    data-employee-name="<?= htmlspecialchars($record['EmployeeName']) ?>"
                                                    data-action-type="<?= htmlspecialchars($record['ActionType']) ?>"
                                                    data-date="<?= htmlspecialchars($record['Date']) ?>"
                                                    data-details='<?= htmlspecialchars(json_encode($record['Details']), ENT_QUOTES, 'UTF-8') ?>'> 
                                                    
                                                    <td>#<?= htmlspecialchars($record['RecordID']) ?></td>
                                                    <td><?= htmlspecialchars($record['EmployeeName']) ?></td>
                                                    <td>
                                                        <?php 
                                                            $statusClass = strtolower(str_replace([' ', '/', '-'], '-', $record['ActionType']));
                                                        ?>
                                                        <span class="status-tag-list <?= htmlspecialchars($statusClass) ?>">
                                                            <?= htmlspecialchars($record['ActionType']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars(date('F d, Y', strtotime($record['Date']))) ?></td>
                                                    <td><button class="btn-expand">Expand</button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <!-- ARCHIVE DETAIL MODAL -->
    <div id="archiveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Archive Details</h2>
                <span class="close-btn">&times;</span>
            </div>
            
            <div id="archiveDetailDisplay">
                
                <div class="form-group">
                    <label>Archive Table</label>
                    <input type="text" id="detailTable" readonly>
                </div>

                <div class="form-group">
                    <label>Record ID</label>
                    <input type="text" id="detailRecordId" readonly>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>User/Employee</label>
                        <input type="text" id="detailEmployeeName" readonly>
                    </div>
                    <div class="form-group">
                        <label>Date of Archival</label>
                        <input type="text" id="detailDate" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label>Action/Final Status</label>
                    <input type="text" id="detailActionType" readonly>
                </div>
                
                <hr style="margin: 1rem 0 1.25rem; border-top: 1px solid #ddd;">
                
                <h3>Specific Archive Details</h3>
                <div id="dynamicDetailsContainer">
                    <p>Click "Expand" on a row to view specific details.</p>
                </div>

            </div>

        </div>
    </div>
    <!-- EXPORT CONFIGURATION MODAL -->
    <div id="exportConfigModal" class="modal">
        <div class="modal-content" style="height: auto; max-height: 100vh;">
            <div class="modal-header">
                <h2>Export Options</h2>
                <span class="close-export-btn close-btn">&times;</span>
            </div>
            
            <!-- Pointing to the PDF handler -->
            <form action="handlers/export-archives-pdf.php" method="GET" target="_blank">
                <!-- Hidden input to store the current table -->
                <input type="hidden" name="table" value="<?= htmlspecialchars($selectedTable) ?>">

                <div class="form-group">
                    <label>Date Range</label>
                    <div class="form-row">
                        <div class="form-group">
                            <small>From:</small>
                            <input type="date" name="start_date" required 
                                value="<?= date('Y-m-01') ?>"> <!-- Default to 1st of current month -->
                        </div>
                        <div class="form-group">
                            <small>To:</small>
                            <input type="date" name="end_date" required 
                                value="<?= date('Y-m-d') ?>"> <!-- Default to today -->
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Sort By Date</label>
                    <select name="sort_order">
                        <option value="DESC">Newest First (Descending)</option>
                        <option value="ASC">Oldest First (Ascending)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Format</label>
                    <!-- Optional: Toggle between PDF and Excel handlers via JS if you want both -->
                    <select id="exportFormatSelector">
                        <option value="pdf">PDF Document</option>
                        <option value="excel">Excel Spreadsheet</option>
                    </select>
                </div>

                <div class="form-row" style="margin-top: 2rem;">
                    <button type="button" class="close-export-btn" style="background: #ccc; border:none; padding: 10px 20px; cursor:pointer;">Cancel</button>
                    <button type="submit" style="background: var(--clr-blue); color: white; border:none; padding: 10px 20px; cursor:pointer; flex: 1;">Generate Export</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>