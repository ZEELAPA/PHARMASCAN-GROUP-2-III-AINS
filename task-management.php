<?php

    include('auth.php');

    require_admin();

    include('sqlconnect.php');

    $tasksByStatus = [
        'Not Started' => [],
        'Pending' => [],
        'Completed' => []
    ];
    $taskCounts = [
        'Not Started' => 0,
        'Pending' => 0,
        'Completed' => 0
    ];

    $employeeList = [];
    $employeeSql = "SELECT 
            acc.AccountID, 
            CONCAT(pi.FirstName, ' ', pi.LastName) AS FullName
        FROM 
            tblaccounts AS acc
        JOIN 
            tblemployees AS emp ON acc.EmployeeID = emp.EmployeeID
        JOIN 
            tblpersonalinfo AS pi ON emp.PersonalID = pi.PersonalID
        WHERE
            emp.EmploymentStatus != 'Archived'
        ORDER BY 
            pi.FirstName, pi.LastName
    ";

    $employeeResult = $conn->query($employeeSql);
    if ($employeeResult && $employeeResult->num_rows > 0) {
        while ($empRow = $employeeResult->fetch_assoc()) {
            $employeeList[] = $empRow;
        }
    }
    $sql = "SELECT 
            a.AgendaID,
            a.Task, 
            a.Priority, 
            a.Status,
            a.Deadline,
            a.Remarks,
            acc.AccountID,
            CONCAT(pi.FirstName, ' ', pi.LastName) AS AssignedToName
        FROM 
            tblagenda AS a
        LEFT JOIN 
            tblaccounts AS acc ON a.AccountID = acc.AccountID
        LEFT JOIN 
            tblemployees AS emp ON acc.EmployeeID = emp.EmployeeID
        LEFT JOIN 
            tblpersonalinfo AS pi ON emp.PersonalID = pi.PersonalID
        ORDER BY 
            FIELD(a.Priority, 'High', 'Medium', 'Low'), a.Deadline ASC
    ";

    $allTasks = [];

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $allTasks[] = $row; 
            // --- 3. Sort each task into its respective array based on its status ---
            if (array_key_exists($row['Status'], $tasksByStatus)) {
                $tasksByStatus[$row['Status']][] = $row;
            }
        }
    }

    // Calculate the counts
    $taskCounts['Not Started'] = count($tasksByStatus['Not Started']);
    $taskCounts['Pending'] = count($tasksByStatus['Pending']);
    $taskCounts['Completed'] = count($tasksByStatus['Completed']);
    
        // 1. Prepare an array for the last 7 months with default counts of 0
    $monthlyChartData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = new DateTime("first day of -$i months");
        $monthKey = $date->format('Y-m'); // e.g., '2023-10'
        $monthName = $date->format('M');   // e.g., 'Oct'
        $monthlyChartData[$monthKey] = ['month' => $monthName, 'count' => 0];
    }

    // 2. Get the date 7 months ago to limit the SQL query
    $sevenMonthsAgo = (new DateTime("first day of -6 months"))->format('Y-m-d');

    // 3. SQL query to get counts of completed tasks
    $monthlySql = "SELECT 
            DATE_FORMAT(Deadline, '%Y-%m') AS CompletionMonth,
            COUNT(AgendaID) AS CompletedCount
        FROM 
            tblagenda
        WHERE 
            Status = 'Completed' AND Deadline >= '$sevenMonthsAgo'
        GROUP BY 
            CompletionMonth
        ORDER BY 
            CompletionMonth ASC
    ";

    $monthlyResult = $conn->query($monthlySql);

    // 4. Populate the array with data from the database
    if ($monthlyResult && $monthlyResult->num_rows > 0) {
        while ($row = $monthlyResult->fetch_assoc()) {
            if (isset($monthlyChartData[$row['CompletionMonth']])) {
                $monthlyChartData[$row['CompletionMonth']]['count'] = (int)$row['CompletedCount'];
            }
        }
    }
    
    // 5. Find the maximum count for scaling the bar heights
    $maxCount = 0;
    foreach ($monthlyChartData as $data) {
        if ($data['count'] > $maxCount) {
            $maxCount = $data['count'];
        }
    }
    // Avoid division by zero if all counts are 0
    $maxCount = $maxCount > 0 ? $maxCount : 1;


    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/task-management.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>Task Management | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/task-management.js?v=<?php echo time(); ?>"></script>

    <script>
        const employeeDistributionData = <?php echo $chartDataJSON; ?>;
    </script>
</head>
<body>
    <div class="app-container">
        <?php include('toast-message.php'); ?>
        <?php include('admin-sidebar.php'); ?>    

        <main class="main-container">
            <div class="main-panel">
                <div class="top-row-panels">
                    <div class="top-left-panels">
                        <div class="main-title">
                            <h3>Task Management</h3>
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
                    <div class="reports-panel">
                        <div class="task-overview-chart">
                            <div class="chart-header">
                                <h4>Task Overview</h4>
                                <p>Total Task Completed Each Month</p>
                            </div>
                            <div class="chart-bars">
                                <?php foreach ($monthlyChartData as $data): ?>
                                    <?php 
                                        // Calculate bar height as a percentage of the max count
                                        $barHeightPercentage = ($data['count'] / $maxCount) * 100;
                                    ?>
                                    <div class="bar-column">
                                        <div class="month-label"><?= htmlspecialchars($data['month']) ?></div>
                                        <div class="bar-container">
                                            <div class="bar" style="height: <?= $barHeightPercentage ?>%;"></div>
                                        </div>
                                        <div class="bar-value"><?= htmlspecialchars($data['count']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
    
                    </div>
                </div>
                        
                <div class="task-management-panel">
                    <div class="taskmgr-header">
                        <h4>All Tasks</h4>
                        <!-- Grid view and List view -->
                        <div class="view-and-new-container">
                            <div class="view-toggle-container">
                                <button id="grid-view-btn" class="view-btn active">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="17" viewBox="0 0 15 17" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.3333 7.58427C13.7538 7.58413 14.1588 7.75008 14.4671 8.04883C14.7754 8.34759 14.9643 8.75709 14.9958 9.19523L15 9.32585V14.5506C15.0001 14.9899 14.8413 15.4131 14.5554 15.7353C14.2695 16.0575 13.8776 16.2548 13.4583 16.2878L13.3333 16.2921H10C9.57952 16.2923 9.17453 16.1263 8.86621 15.8276C8.55789 15.5288 8.36904 15.1193 8.3375 14.6812L8.33333 14.5506V9.32585C8.3332 8.88647 8.49201 8.46327 8.77791 8.1411C9.06382 7.81892 9.4557 7.62158 9.875 7.58863L10 7.58427H13.3333ZM5 11.0674C5.44203 11.0674 5.86595 11.2509 6.17851 11.5775C6.49107 11.9041 6.66667 12.3471 6.66667 12.809V14.5506C6.66667 15.0125 6.49107 15.4554 6.17851 15.782C5.86595 16.1087 5.44203 16.2921 5 16.2921H1.66667C1.22464 16.2921 0.800716 16.1087 0.488155 15.782C0.175595 15.4554 0 15.0125 0 14.5506V12.809C0 12.3471 0.175595 11.9041 0.488155 11.5775C0.800716 11.2509 1.22464 11.0674 1.66667 11.0674H5ZM13.3333 9.32585H10V14.5506H13.3333V9.32585ZM5 12.809H1.66667V14.5506H5V12.809ZM5 0.617981C5.44203 0.617981 5.86595 0.801468 6.17851 1.12808C6.49107 1.45468 6.66667 1.89766 6.66667 2.35955V7.58427C6.66667 8.04617 6.49107 8.48914 6.17851 8.81575C5.86595 9.14236 5.44203 9.32585 5 9.32585H1.66667C1.22464 9.32585 0.800716 9.14236 0.488155 8.81575C0.175595 8.48914 0 8.04617 0 7.58427V2.35955C0 1.89766 0.175595 1.45468 0.488155 1.12808C0.800716 0.801468 1.22464 0.617981 1.66667 0.617981H5ZM5 2.35955H1.66667V7.58427H5V2.35955ZM13.3333 0.617981C13.7754 0.617981 14.1993 0.801468 14.5118 1.12808C14.8244 1.45468 15 1.89766 15 2.35955V4.10113C15 4.56302 14.8244 5.006 14.5118 5.3326C14.1993 5.65921 13.7754 5.8427 13.3333 5.8427H10C9.55797 5.8427 9.13405 5.65921 8.82149 5.3326C8.50893 5.006 8.33333 4.56302 8.33333 4.10113V2.35955C8.33333 1.89766 8.50893 1.45468 8.82149 1.12808C9.13405 0.801468 9.55797 0.617981 10 0.617981H13.3333ZM13.3333 2.35955H10V4.10113H13.3333V2.35955Z" fill="currentColor"/>
                                    </svg>
                                    <span class="btn-text">Grid View</span>
                                </button>
                                <button id="list-view-btn" class="view-btn">
                                    <i class="fas fa-bars"></i>
                                    <span class="btn-text">List View</span>
                                </button>
                            </div>
                            <div class="add-button-container">
                                <button>New +</button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="task-status-container">
                        <div class="task-status-panel task-notstarted-panel">
                            <div class="panel-header">
                                <div class="left-header">
                                    <span class="status-circle not-started"></span>
                                    <h5>Not Started</h5>
                                </div>
                                <div class="right-header">
                                    <h5><?php echo $taskCounts['Not Started']; ?></h5>
                                </div>
                            </div>
                            <?php if (empty($tasksByStatus['Not Started'])): ?>
                                <p>No tasks here!</p>
                            <?php else: ?>
                                <?php foreach ($tasksByStatus['Not Started'] as $index => $task): ?>
                                    <div class="task-card <?php echo ($index == 0) ? 'is-expanded' : ''; ?>"
                                        data-task-id="<?= htmlspecialchars($task['AgendaID']) ?>"
                                        data-task-name="<?= htmlspecialchars($task['Task']) ?>"
                                        data-account-id="<?= htmlspecialchars($task['AccountID']) ?>"
                                        data-deadline="<?= htmlspecialchars(date('Y-m-d', strtotime($task['Deadline']))) ?>"
                                        data-priority="<?= htmlspecialchars($task['Priority']) ?>"
                                        data-status="<?= htmlspecialchars($task['Status']) ?>"
                                        data-remarks="<?= htmlspecialchars($task['Remarks']) ?>">
                                        
                                        <p class="task-name"><?= htmlspecialchars($task['Task']) ?></p>
                                        <div class="task-details">
                                            <p class="task-assignment">
                                                <span><?= htmlspecialchars($task['AssignedToName'] ?? 'Unassigned') ?></span>
                                            </p>
                                            <p class="task-priority">
                                                <span class="priority-tag <?= strtolower(htmlspecialchars($task['Priority'])) ?>">
                                                    <?= htmlspecialchars($task['Priority']) ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Task List View -->

                        <div class="task-status-panel task-pending-panel">
                            <div class="panel-header">
                                <div class="left-header">
                                    <span class="status-circle pending"></span>
                                    <h5>Pending</h5>
                                </div>
                                <div class="right-header">
                                    <h5><?php echo $taskCounts['Pending']; ?></h5>
                                </div>
                            </div>
                            <!-- Content for 'Pending' tasks goes here -->
                            <?php if (empty($tasksByStatus['Pending'])): ?>
                                <p>No tasks here!</p>
                            <?php else: ?>
                                <?php foreach ($tasksByStatus['Pending'] as $index => $task): ?>
                                    <div class="task-card <?php echo ($index == 0) ? 'is-expanded' : ''; ?>"
                                        data-task-id="<?= htmlspecialchars($task['AgendaID']) ?>"
                                        data-task-name="<?= htmlspecialchars($task['Task']) ?>"
                                        data-account-id="<?= htmlspecialchars($task['AccountID']) ?>"
                                        data-deadline="<?= htmlspecialchars(date('Y-m-d', strtotime($task['Deadline']))) ?>"
                                        data-priority="<?= htmlspecialchars($task['Priority']) ?>"
                                        data-status="<?= htmlspecialchars($task['Status']) ?>"
                                        data-remarks="<?= htmlspecialchars($task['Remarks']) ?>">

                                        <p class="task-name"><?= htmlspecialchars($task['Task']) ?></p>
                                        <div class="task-details">
                                            <p class="task-assignment">
                                                <span><?= htmlspecialchars($task['AssignedToName'] ?? 'Unassigned') ?></span>
                                            </p>
                                            <p class="task-priority">
                                                <span class="priority-tag <?= strtolower(htmlspecialchars($task['Priority'])) ?>">
                                                    <?= htmlspecialchars($task['Priority']) ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="task-status-panel task-completed-panel">
                            <div class="panel-header">
                                <div class="left-header">
                                    <span class="status-circle completed"></span>
                                    <h5>Completed</h5>
                                </div>
                                <div class="right-header">
                                    <h5><?php echo $taskCounts['Completed']; ?></h5>
                                </div>
                            </div>
                            <!-- Content for 'Completed' tasks goes here -->
                            <?php if (empty($tasksByStatus['Completed'])): ?>
                                <p>No tasks here!</p>
                            <?php else: ?>
                                <?php foreach ($tasksByStatus['Completed'] as $index => $task): ?>
                                    <div class="task-card <?php echo ($index == 0) ? 'is-expanded' : ''; ?>"
                                        data-task-id="<?= htmlspecialchars($task['AgendaID']) ?>"
                                        data-task-name="<?= htmlspecialchars($task['Task']) ?>"
                                        data-account-id="<?= htmlspecialchars($task['AccountID']) ?>"
                                        data-deadline="<?= htmlspecialchars(date('Y-m-d', strtotime($task['Deadline']))) ?>"
                                        data-priority="<?= htmlspecialchars($task['Priority']) ?>"
                                        data-status="<?= htmlspecialchars($task['Status']) ?>"
                                        data-remarks="<?= htmlspecialchars($task['Remarks']) ?>">

                                        <p class="task-name"><?= htmlspecialchars($task['Task']) ?></p>
                                        <div class="task-details">
                                            <p class="task-assignment">
                                                <span><?= htmlspecialchars($task['AssignedToName'] ?? 'Unassigned') ?></span>
                                            </p>
                                            <p class="task-priority">
                                                <span class="priority-tag <?= strtolower(htmlspecialchars($task['Priority'])) ?>">
                                                    <?= htmlspecialchars($task['Priority']) ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="task-list-view-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Aa Task Title</th>
                                    <th><i class="fa-regular fa-circle"></i> Assigned To</th>
                                    <th><i class="fa-regular fa-circle"></i> Status</th>
                                    <th><i class="fa-solid fa-signal"></i> Priority Level</th>
                                    <th><i class="fa-regular fa-calendar"></i> Deadline</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allTasks)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No tasks to display.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allTasks as $task): ?>
                                        <tr class="task-row"
                                            data-task-id="<?= htmlspecialchars($task['AgendaID']) ?>"
                                            data-task-name="<?= htmlspecialchars($task['Task']) ?>"
                                            data-account-id="<?= htmlspecialchars($task['AccountID']) ?>"
                                            data-deadline="<?= htmlspecialchars(date('Y-m-d', strtotime($task['Deadline']))) ?>"
                                            data-priority="<?= htmlspecialchars($task['Priority']) ?>"
                                            data-status="<?= htmlspecialchars($task['Status']) ?>"
                                            data-remarks="<?= htmlspecialchars($task['Remarks']) ?>">
                                            
                                            <td class="row-task-name"><?= htmlspecialchars($task['Task']) ?></td>
                                            <td class="row-assigned-name"><?= htmlspecialchars($task['AssignedToName']) ?></td>
                                            <td><?= htmlspecialchars($task['Status']) ?></td>
                                            <td>
                                                <span class="priority-tag-list <?= strtolower(htmlspecialchars($task['Priority'])) ?>">
                                                    <?= htmlspecialchars($task['Priority']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(date('F d, Y', strtotime($task['Deadline']))) ?></td>
                                            <td><button class="btn-update">Update</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <div id="addTaskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Task</h2>
                <span class="close-btn">&times;</span>
            </div>
            <form id="taskForm" action="handlers/task-handler.php" method="POST">
                <input type="hidden" id="editTaskID" name="taskID" value="">
                <div class="form-group">
                    <label for="taskName">Task Name</label>
                    <input type="text" id="taskName" name="taskName" placeholder="Task Management Translation" required>
                </div>

                <div class="form-group">
                    <label for="assignEmployee">Assign to Employee</label>
                    <select id="assignEmployee" name="assignEmployee" required>
                        <option value="" disabled selected>Select an employee...</option>
                        <?php foreach ($employeeList as $employee): ?>
                            <option value="<?= $employee['AccountID'] ?>">
                                <?= htmlspecialchars($employee['FullName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="taskDeadline">Deadline</label>
                        <input type="date" id="taskDeadline" name="taskDeadline" class="taskDeadline" required>
                    </div>
                    <div class="form-group">
                        <label for="taskPriority">Priority</label>
                        <select id="taskPriority" name="taskPriority" required>
                            <!-- ADD disabled/selected option as placeholder -->
                            <option value="" disabled selected>Choose priority...</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="taskStatus">Status</label>
                    <select id="taskStatus" name="taskStatus" required>
                        <option value="Not Started" selected>Not Started</option>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="taskRemarks">Remarks</label>
                    <textarea id="taskRemarks" name="taskRemarks" rows="3" placeholder="Add any additional notes..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" id="submitButton" class="btn-submit">Assign Task</button>
                    <button type="button" id="archiveButton" class="btn-archive" style="display:none;">Archive Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add this to task-management.php before </body> -->
    <div id="calendarModal" class="modal-left">
        <div class="modal-content-left">
            <div class="calendar-panel">
                <div class="calendar-header">
                    <button class="month-nav-btn" id="prev-month-btn"> 
                        <svg width="12" height="14" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.312472 6.03749L10.625 0.0837383C10.72 0.028883 10.8278 4.76837e-06 10.9375 3.8147e-06C11.0472 3.8147e-06 11.155 0.0288811 11.25 0.0837345C11.345 0.138588 11.4239 0.217484 11.4787 0.312493C11.5336 0.407503 11.5625 0.51528 11.5625 0.624989V12.5325C11.5625 12.6422 11.5336 12.75 11.4787 12.845C11.4239 12.94 11.345 13.0189 11.25 13.0737C11.155 13.1286 11.0472 13.1575 10.9375 13.1575C10.8278 13.1575 10.72 13.1286 10.625 13.0737L0.312472 7.11999C0.217468 7.06513 0.138576 6.98623 0.083726 6.89122C0.0288759 6.79622 0 6.68844 0 6.57874C0 6.46903 0.0288759 6.36126 0.083726 6.26625C0.138576 6.17124 0.217468 6.09234 0.312472 6.03749Z" fill="#E1EBF3"/>
                        </svg>
                    Previous</button>
                    
                    <h3 class="calendar-title" id="modal-calendar-title">Month Year</h3>
                    
                    <button class="month-nav-btn" id="next-month-btn">Next
                        <svg width="12" height="14" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.25 7.11998L0.937528 13.0737C0.842517 13.1286 0.73474 13.1575 0.625031 13.1575C0.515322 13.1575 0.407546 13.1286 0.312534 13.0737C0.217523 13.0189 0.138623 12.94 0.0837669 12.845C0.0289106 12.75 3.05176e-05 12.6422 2.76566e-05 12.5325V0.624984C3.05176e-05 0.515275 0.0289106 0.407499 0.0837669 0.31249C0.138623 0.21748 0.217523 0.138583 0.312534 0.0837301C0.407546 0.0288768 0.515322 -6.96769e-07 0.625031 0C0.73474 6.96794e-07 0.842517 0.0288796 0.937528 0.0837342L11.25 6.03748C11.345 6.09234 11.4239 6.17124 11.4788 6.26625C11.5336 6.36126 11.5625 6.46903 11.5625 6.57873C11.5625 6.68844 11.5336 6.79621 11.4788 6.89122C11.4239 6.98623 11.345 7.06513 11.25 7.11998Z" fill="#E1EBF3"/>
                        </svg>
                    </button>
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
                    <div id="modal-calendar-grid" class="calendar-grid"></div>
                </div>
            </div>
        </div>
    </div>
    <div id="confirmationModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Confirm Archival</h3>
            <p>Are you sure you want to archive this task?</p>
            <div class="modal-buttons">
                <button id="btnCancelConfirm" class="btn-cancel">Cancel</button>
                <button id="btnYesConfirm" class="btn-confirm">Confirm</button>
            </div>
        </div>
    </div>
    <script>
        const CURRENT_USER_ACCOUNT_ID = <?php echo json_encode($_SESSION['AccountID'] ?? null); ?>;
    </script>
</body>
</html>