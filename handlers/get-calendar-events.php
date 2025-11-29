<?php
include('../sqlconnect.php');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');

// Final data structure
$outputData = [];

// --- STEP 1: LOAD BASE STATUSES (Weekends/Holidays) ---
$jsonFilePath = '../calendar-events.json';
if (file_exists($jsonFilePath)) {
    $allBaseEvents = json_decode(file_get_contents($jsonFilePath), true);
    if (is_array($allBaseEvents)) {
        foreach ($allBaseEvents as $dateString => $dayData) {
            $datePrefix = sprintf('%04d-%02d', $year, $month);
            if (strpos($dateString, $datePrefix) === 0) {
                $outputData[$dateString] = $dayData;
            }
        }
    }
}

// --- STEP 2: FETCH & PROCESS LEAVE APPLICATIONS ---
$firstDayOfMonth = "$year-$month-01";
$lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));

// Fetch leaves that OVERLAP with the current month
$stmt = $conn->prepare(
    "SELECT ScheduledLeave, ScheduledReturn, LeaveStatus FROM tblleaveform 
     WHERE (LeaveStatus = 'Pending' OR LeaveStatus = 'Approved') 
     AND ScheduledLeave <= ? 
     AND (ScheduledReturn >= ? OR ScheduledReturn IS NULL)"
);
$stmt->bind_param("ss", $lastDayOfMonth, $firstDayOfMonth);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $startDate = new DateTime($row['ScheduledLeave']);
        
        // --- FIX: Add +1 day to End Date because DatePeriod is exclusive ---
        if ($row['ScheduledReturn'] === null) {
            $endDate = (clone $startDate)->modify('+1 day');
        } else {
            $endDate = new DateTime($row['ScheduledReturn']);
            $endDate->modify('+1 day'); 
        }

        $datePeriod = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

        foreach ($datePeriod as $date) {
            $dateString = $date->format('Y-m-d');
            
            // Ensure date is in the current requested month
            if (strpos($dateString, sprintf('%04d-%02d', $year, $month)) !== 0) {
                continue;
            }
            
            // Initialize day if empty
            if (!isset($outputData[$dateString])) {
                $outputData[$dateString] = ["classes" => ["available_leave"], "pendingCount" => 0];
            }

            // --- COLOR LOGIC ---
            
            // 1. Approved Leave: Definitely Occupied
            if ($row['LeaveStatus'] === 'Approved') {
                $outputData[$dateString]['classes'] = ['occupied']; 
                $outputData[$dateString]['pendingCount'] = 0;
            } 
            // 2. Pending Leave: Treat as Occupied to show color immediately
            elseif ($row['LeaveStatus'] === 'Pending') {
                
                // Check if already blocked (e.g., Weekend or Approved Leave takes precedence)
                $classes = $outputData[$dateString]['classes'];
                $isFinalState = in_array('occupied', $classes) || in_array('unavailable_leave', $classes);
                
                if (!$isFinalState) {
                    // Remove 'available_leave' so it becomes occupied
                    $outputData[$dateString]['classes'] = array_values(array_diff($classes, ['available_leave']));

                    // Add 'occupied' so it turns beige/yellow
                    if (!in_array('occupied', $outputData[$dateString]['classes'])) {
                        $outputData[$dateString]['classes'][] = 'occupied';
                    }

                    // Optional: Keep 'pending_application' if you want the underline visual too
                    if (!in_array('pending_application', $outputData[$dateString]['classes'])) {
                        $outputData[$dateString]['classes'][] = 'pending_application';
                    }
                    
                    $outputData[$dateString]['pendingCount']++;
                }
            }
        }
    }
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($outputData);
?>