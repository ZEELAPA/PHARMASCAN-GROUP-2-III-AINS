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
                    'Target Date' => date('F d, Y', strtotime($row['Date'])),
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
                                </select>
                            </div>
                            
                            <div class="export-button-container">
                                <button>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.725 7.30125C14.5492 7.47681 14.3109 7.57543 14.0625 7.57543C13.8141 7.57543 13.5758 7.47681 13.4 7.30125L10.9375 4.83875V14.0625C10.9375 14.3111 10.8387 14.5496 10.6629 14.7254C10.4871 14.9012 10.2486 15 10 15C9.75136 15 9.5129 14.9012 9.33709 14.7254C9.16127 14.5496 9.0625 14.3111 9.0625 14.0625V4.83875L6.6 7.30125C6.42228 7.46685 6.18722 7.557 5.94435 7.55272C5.70147 7.54843 5.46974 7.45004 5.29797 7.27828C5.12621 7.10651 5.02782 6.87478 5.02353 6.6319C5.01925 6.38903 5.1094 6.15397 5.275 5.97625L9.3375 1.91375L10 1.25L10.6625 1.9125L14.725 5.975C14.8121 6.06206 14.8812 6.16544 14.9284 6.27922C14.9755 6.393 14.9998 6.51496 14.9998 6.63813C14.9998 6.76129 14.9755 6.88325 14.9284 6.99703C14.8812 7.11081 14.8121 7.21419 14.725 7.30125ZM3.125 12.1875C3.125 11.9389 3.02623 11.7004 2.85041 11.5246C2.6746 11.3488 2.43614 11.25 2.1875 11.25C1.93886 11.25 1.7004 11.3488 1.52459 11.5246C1.34877 11.7004 1.25 11.9389 1.25 12.1875V16.25C1.25 16.913 1.51339 17.5489 1.98223 18.0178C2.45107 18.4866 3.08696 18.75 3.75 18.75H16.25C16.913 18.75 17.5489 18.4866 18.0178 18.0178C18.4866 17.5489 18.75 16.913 18.75 16.25V12.1875C18.75 11.9389 18.6512 11.7004 18.4754 11.5246C18.2996 11.3488 18.0611 11.25 17.8125 11.25C17.5639 11.25 17.3254 11.3488 17.1496 11.5246C16.9738 11.7004 16.875 11.9389 16.875 12.1875V16.25C16.875 16.4158 16.8092 16.5747 16.6919 16.6919C16.5747 16.8092 16.4158 16.875 16.25 16.875H3.75C3.58424 16.875 3.42527 16.8092 3.30806 16.6919C3.19085 16.5747 3.125 16.4158 3.125 16.25V12.1875Z" fill="#EEEEEE"/>
                                    </svg>
                                    Export
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
                
                <hr style="margin: 1.5rem 0; border-top: 1px solid #ddd;">
                
                <h3>Specific Archive Details</h3>
                <!-- Dynamic details will be injected here via JavaScript -->
                <div id="dynamicDetailsContainer">
                    <p>Click "Expand" on a row to view specific details.</p>
                </div>

            </div>

        </div>
    </div>
</body>
</html>