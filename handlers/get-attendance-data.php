<?php
    include('../sqlconnect.php');

    // Get parameters from the AJAX request
    $date = $_GET['date'] ?? date('Y-m-d');
    $employeeId = $_GET['employee_id'] ?? 'all';
    $viewType = $_GET['view_type'] ?? 'daily'; // New parameter: 'daily' or 'weekly'

    // Sanitize inputs
    $safeDate = $conn->real_escape_string($date);
    $safeEmployeeId = ($employeeId !== 'all') ? (int)$employeeId : 'all';

    // --- LOGIC FOR WEEKLY VIEW ---
    if ($viewType === 'weekly') {
        // Calculate the start and end dates of the week (Sunday to Saturday)
        $selectedDateTime = new DateTime($safeDate);
        $dayOfWeek = $selectedDateTime->format('w'); // 0 (Sun) to 6 (Sat)
        
        // Find Sunday (start of the week)
        $startDate = clone $selectedDateTime;
        if ($dayOfWeek > 0) {
            $startDate->modify("-$dayOfWeek days");
        }

        // Find Saturday (end of the week)
        $endDate = clone $startDate;
        $endDate->modify('+6 days');

        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');

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
                AND att.AttendanceDate BETWEEN '{$startDateStr}' AND '{$endDateStr}'
            WHERE emp.EmploymentStatus IN ('Active', 'On Leave', 'On Vacation')";

        if ($safeEmployeeId !== 'all') {
            $query .= " AND emp.EmployeeID = {$safeEmployeeId}";
        }
        
        $query .= " ORDER BY info.LastName ASC, info.FirstName ASC, att.AttendanceDate ASC;";

    } else { // --- LOGIC FOR DAILY VIEW (Existing Logic) ---
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
                    tblattendance AS att ON acc.AccountID = att.AccountID AND att.AttendanceDate = '{$safeDate}'
                WHERE emp.EmploymentStatus IN ('Active', 'On Leave', 'On Vacation')";

        if ($safeEmployeeId !== 'all') {
            $query .= " AND emp.EmployeeID = {$safeEmployeeId}";
        }
        
        $query .= " ORDER BY info.LastName ASC, info.FirstName ASC;";
    }


    $result = $conn->query($query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
        exit();
    }

    $records = $result->fetch_all(MYSQLI_ASSOC);

    header('Content-Type: application/json');
    // For weekly view, also send back the start date of the week
    if ($viewType === 'weekly') {
        echo json_encode(['records' => $records, 'week_start_date' => $startDateStr]);
    } else {
        echo json_encode($records);
    }
?>