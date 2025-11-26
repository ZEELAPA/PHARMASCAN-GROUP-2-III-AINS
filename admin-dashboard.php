<?php
    include('auth.php');

    require_admin();

    include('sqlconnect.php');

    if (!$conn || $conn->connect_error) {
        die("Connection failed: " . ($conn ? $conn->connect_error : "Unknown error"));
    }

    function getWorkingEmployees($conn){
        $query = "SELECT 
                    Count(*)
                FROM 
                    tblemployees
                WHERE 
                    EmploymentStatus = 'Active';";

        
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
        $employeesCount = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $employeesCount;
    }

    function getPendingTasksCount($conn){
        $query = "SELECT 
                    Count(*)
                FROM 
                    tblagenda
                WHERE 
                    Status = 'Pending'
                    OR
                    Status = 'Not Started';";

        if (!$stmt = $conn->prepare($query)) {
            error_log("Failed to prepare statement: " . $conn->error);
            return [['Count(*)' => 0]];
        }

        if (!$stmt->execute()) {
            error_log("Failed to execute statement: " . $stmt->error);
            $stmt->close();
            return [['Count(*)' => 0]];
        }
        
        $result = $stmt->get_result();
        $pendingCount = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $pendingCount;
    }

    function getTaskStatusCounts($conn) {
        $sql = "SELECT
                    Status,
                    COUNT(*) AS task_count
                FROM
                    tblagenda
                WHERE Status IN ('Not Started', 'Pending', 'Completed')
                GROUP BY
                    Status
                ORDER BY
                    FIELD(Status, 'Not Started', 'Pending', 'Completed')";

        $result = $conn->query($sql);

        $taskData = ['labels' => [], 'data' => []];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $taskData['labels'][] = $row['Status'];
                $taskData['data'][] = (int)$row['task_count'];
            }
        }
        return $taskData;
    }

    function getPendingLeaves($conn) {
        $query = "SELECT
                    pi.FirstName,
                    pi.LastName,
                    d.DepartmentName,
                    lf.Reason AS LeaveType,
                    lf.ScheduledLeave,
                    lf.ScheduledReturn
                FROM
                    tblleaveform AS lf
                JOIN tblaccounts AS a ON lf.AccountID = a.AccountID
                JOIN tblemployees AS e ON a.EmployeeID = e.EmployeeID
                JOIN tblpersonalinfo AS pi ON e.PersonalID = pi.PersonalID
                JOIN tbldepartment AS d ON e.DepartmentID = d.DepartmentID
                WHERE
                    lf.LeaveStatus = 'Pending'
                ORDER BY
                    lf.ScheduledLeave ASC
                LIMIT 10"; // Optional: Limit to the 10 most recent

        $result = $conn->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    $employeeCount = getWorkingEmployees($conn);
    
    $pendingTasksCount = getPendingTasksCount($conn);
    $taskStatusData = getTaskStatusCounts($conn);
    $taskStatusDataJSON = json_encode($taskStatusData);
    $pendingLeaves = getPendingLeaves($conn);

    $sql = "SELECT
                d.DepartmentName,
                COUNT(e.EmployeeID) AS employee_count
            FROM
                tblemployees AS e
            JOIN
                tbldepartment AS d ON e.DepartmentID = d.DepartmentID
            GROUP BY
                d.DepartmentName
            ORDER BY
                d.DepartmentName";

    $result = $conn->query($sql);

    $chartData = ['labels' => [], 'data' => []];

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $chartData['labels'][] = $row['DepartmentName'];
            $chartData['data'][] = (int)$row['employee_count'];
        }
    }

    $notes_file = 'admin_notes.json';
    $adminNotes = [];
    if (file_exists($notes_file)) {
        $json_data = file_get_contents($notes_file);
        $adminNotes = json_decode($json_data, true);
        if ($adminNotes === null) { $adminNotes = []; }
    }

    $conn->close();
    
    $chartDataJSON = json_encode($chartData);
?>


<!DOCTYPE html>
<html lang="en">
    <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/admin-dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>Admin Dashboard | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/admin-dashboard.js?v=<?php echo time(); ?>"></script>

    <script>
        const employeeDistributionData = <?php echo $chartDataJSON; ?>;
        const taskStatusData = <?php echo $taskStatusDataJSON; ?>;
    </script>
</head>
<body>
    <div class="app-container">
        
        <?php include('admin-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                <div class="main-title">
                    <h3>Admin Dashboard</h3>
                    <p>Welcome Admin! What's cooking today?</p>
                </div>
                
                <div class="panels-container">
                    <div class="top-panels">
                        <div class="admin-notes-panel">
                            <div class="admin-notes-header">
                                <h4>Admin Notes</h4>
                                <button id="add-note-btn">+</button>
                            </div>
                            <hr>
                            <ul id="admin-notes-list" class="admin-notes-list">
                                <?php if (empty($adminNotes)): ?>
                                    <li class="no-notes">No notes yet. Click '+' to add one.</li>
                                <?php else: ?>
                                    <?php foreach ($adminNotes as $note): ?>
                                        <li class="note-item <?= $note['completed'] ? 'completed' : '' ?>" data-id="<?= htmlspecialchars($note['id']) ?>">
                                            <input type="checkbox" class="note-checkbox" <?= $note['completed'] ? 'checked' : '' ?>>
                                            <span class="note-text"><?= htmlspecialchars($note['text']) ?></span>
                                            <button class="delete-note-btn">
                                                <!-- Simple SVG for trash can -->
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path></svg>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="working-pending-panels">
                            <!-- Current Working Employees Panel -->
                            <div class="info-panel working-employees-panel">
                                <div class="panel-content">
                                    <div class="number-container">
                                        <svg width="40" height="40" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="15" cy="15" r="15" />
                                            <path d="M13.75 21.5C13.75 21.5 12.5 21.5 12.5 20.25C12.5 19 13.75 15.25 18.75 15.25C23.75 15.25 25 19 25 20.25C25 21.5 23.75 21.5 23.75 21.5H13.75ZM18.75 14C19.7446 14 20.6984 13.6049 21.4017 12.9017C22.1049 12.1984 22.5 11.2446 22.5 10.25C22.5 9.25544 22.1049 8.30161 21.4017 7.59835C20.6984 6.89509 19.7446 6.5 18.75 6.5C17.7554 6.5 16.8016 6.89509 16.0983 7.59835C15.3951 8.30161 15 9.25544 15 10.25C15 11.2446 15.3951 12.1984 16.0983 12.9017C16.8016 13.6049 17.7554 14 18.75 14ZM11.52 21.5C11.3348 21.1097 11.2424 20.6819 11.25 20.25C11.25 18.5562 12.1 16.8125 13.67 15.6C12.8865 15.358 12.07 15.24 11.25 15.25C6.25 15.25 5 19 5 20.25C5 21.5 6.25 21.5 6.25 21.5H11.52ZM10.625 14C11.4538 14 12.2487 13.6708 12.8347 13.0847C13.4208 12.4987 13.75 11.7038 13.75 10.875C13.75 10.0462 13.4208 9.25134 12.8347 8.66529C12.2487 8.07924 11.4538 7.75 10.625 7.75C9.7962 7.75 9.00134 8.07924 8.41529 8.66529C7.82924 9.25134 7.5 10.0462 7.5 10.875C7.5 11.7038 7.82924 12.4987 8.41529 13.0847C9.00134 13.6708 9.7962 14 10.625 14Z"/>
                                        </svg>
                                        <h4><?php echo str_pad($employeeCount[0]['Count(*)'], 2, '0', STR_PAD_LEFT); ?></h4>
                                    </div>
                                    <p>Current Working <br>Employees</p>
                                </div>
                                <img src="images/Female-Doctor2.png" alt="Illustration of a female doctor" class="panel-image">
                            </div>
                            
                            <!-- Pending Tasks Panel -->
                            <div class="info-panel pending-tasks-panel">
                                <div class="panel-content">
                                    <div class="number-container">
                                        <svg width="40" height="40" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="15" cy="15" r="15"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M11.1799 6.49996C10.9509 7.02574 10.8329 7.59313 10.8333 8.16663C10.8333 8.60865 11.0088 9.03258 11.3214 9.34514C11.634 9.6577 12.0579 9.83329 12.4999 9.83329H17.4999C17.9419 9.83329 18.3659 9.6577 18.6784 9.34514C18.991 9.03258 19.1666 8.60865 19.1666 8.16663C19.1666 7.57413 19.0433 7.00996 18.8199 6.49996H19.9999C20.4419 6.49996 20.8659 6.67555 21.1784 6.98811C21.491 7.30068 21.6666 7.7246 21.6666 8.16663V20.6666C21.6666 21.1087 21.491 21.5326 21.1784 21.8451C20.8659 22.1577 20.4419 22.3333 19.9999 22.3333H9.99992C9.55789 22.3333 9.13397 22.1577 8.82141 21.8451C8.50885 21.5326 8.33325 21.1087 8.33325 20.6666V8.16663C8.33325 7.7246 8.50885 7.30068 8.82141 6.98811C9.13397 6.67555 9.55789 6.49996 9.99992 6.49996H11.1799ZM14.9999 15.6666H12.4999C12.2789 15.6666 12.0669 15.7544 11.9107 15.9107C11.7544 16.067 11.6666 16.2789 11.6666 16.5C11.6666 16.721 11.7544 16.9329 11.9107 17.0892C12.0669 17.2455 12.2789 17.3333 12.4999 17.3333H14.9999C15.2209 17.3333 15.4329 17.2455 15.5892 17.0892C15.7455 16.9329 15.8333 16.721 15.8333 16.5C15.8333 16.2789 15.7455 16.067 15.5892 15.9107C15.4329 15.7544 15.2209 15.6666 14.9999 15.6666ZM17.4999 12.3333H12.4999C12.2875 12.3335 12.0832 12.4149 11.9288 12.5607C11.7743 12.7065 11.6814 12.9058 11.6689 13.1178C11.6565 13.3298 11.7255 13.5386 11.8618 13.7015C11.9981 13.8644 12.1915 13.969 12.4024 13.9941L12.4999 14H17.4999C17.7209 14 17.9329 13.9122 18.0892 13.7559C18.2455 13.5996 18.3333 13.3876 18.3333 13.1666C18.3333 12.9456 18.2455 12.7337 18.0892 12.5774C17.9329 12.4211 17.7209 12.3333 17.4999 12.3333ZM14.9999 5.66663C15.3517 5.66663 15.6994 5.74086 16.0205 5.88446C16.3416 6.02806 16.6288 6.23779 16.8633 6.49996C17.2199 6.89829 17.4499 7.41079 17.4924 7.97663L17.4999 8.16663H12.4999C12.4999 7.56246 12.7141 7.00829 13.0708 6.57663L13.1366 6.49996C13.5949 5.98829 14.2599 5.66663 14.9999 5.66663Z"/>
                                        </svg>
                                        <h4><?php echo str_pad($pendingTasksCount[0]['Count(*)'], 2, '0', STR_PAD_LEFT); ?></h4>
                                    </div>
                                    <p>Pending Tasks</p>
                                </div>
                                <img src="images/Male-Doctor.png" alt="Illustration of a male doctor" class="panel-image">
                            </div>
                        </div>
        
                        <div class="calendar-panel">
                            <div class="calendar-header">
                                <h3 class="calendar-title" id="calendar-title">Month</h3>
                            </div>
                            <div class="calendar-body">
                                <div class="calendar-days">
                                    <p>Mon</p>
                                    <p>Tue</p>
                                    <p>Wed</p>
                                    <p>Thu</p>
                                    <p>Fri</p>
                                    <p>Sat</p>
                                    <p>Sun</p>
                                </div>
                                <div id="calendar-grid" class="calendar-grid"></div>
                            </div>
                        </div>
                    </div>
    
                    <div class="bottom-panels">
                        
                        <div class="employee-distribution-panel">
                            <h4>Task Status Distribution</h4>
                            <div class="panel-body chart-panel-body"> <!-- Added class for flex -->
                                <div class="chart-container">
                                    <canvas id="taskStatusPieChart"></canvas>
                                </div>
                                <!-- The legend will be generated here by JavaScript -->
                                <div id="task-status-legend" class="chart-legend-container"></div>
                            </div>
                        </div>
        
                        <div class="pending-applications-panel">
                            <div class="pending-header">
                                <h4>Pending Applications</h4>
                                <!-- Format count to have a leading zero if less than 10 -->
                                <h4 class="leave-count"><?php echo str_pad(count($pendingLeaves), 2, '0', STR_PAD_LEFT); ?></h4>
                            </div>
                            <hr>
                            <div class="pendings-list-container">
                                <?php if (empty($pendingLeaves)): ?>
                                    <p class="no-applications">No pending applications found.</p>
                                <?php else: ?>
                                    <?php foreach ($pendingLeaves as $leave): ?>
                                        <a href="leave-management.php" class="pending-item">
                                            <div class="pending-item-details">
                                                <h5><?= htmlspecialchars($leave['FirstName'] . ' ' . $leave['LastName']) ?></h5>
                                                <p>
                                                    <?= date('M. d, Y', strtotime($leave['ScheduledLeave'])) ?> to 
                                                    <?= date('M. d, Y', strtotime($leave['ScheduledReturn'])) ?>
                                                </p>
                                            </div>
                                            <div class="pending-item-info">
                                                <h5><?= htmlspecialchars($leave['LeaveType']) ?></h5>
                                                <p><?= htmlspecialchars($leave['DepartmentName']) ?></p>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <div id="note-modal-overlay" class="modal-overlay hidden">
        <div id="note-modal" class="modal-container">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2>New Note</h2>
                <button id="close-modal-btn" class="close-btn">&times;</button>
            </div>

            <!-- Modal Form -->
            <form id="note-form" class="note-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="note-title">Note Title:</label>
                        <input type="text" id="note-title" name="note-title" placeholder="Enter note title">
                    </div>
                    <div class="form-group">
                        <label for="note-date">Date Created:</label>
                        <input type="text" id="note-date" name="note-date" readonly>
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="note-body">Note Body:</label>
                    <textarea id="note-body" name="note-body" rows="6" placeholder="Lorem ipsum dolor sit amet..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="modal-primary-btn">Save</button>
                    <button type="button" class="btn btn-secondary" id="modal-secondary-btn">Delete</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>