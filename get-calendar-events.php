<?php
include('sqlconnect.php');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');

// Final data structure we will send to the frontend
$outputData = [];

// --- STEP 1: LOAD BASE STATUSES (CORRECTED LOGIC) ---
$jsonFilePath = 'calendar-events.json';
if (file_exists($jsonFilePath)) {
    $allBaseEvents = json_decode(file_get_contents($jsonFilePath), true);
    if (is_array($allBaseEvents)) {
        // This loop now correctly reads the object structure from the JSON file.
        foreach ($allBaseEvents as $dateString => $dayData) {
            $datePrefix = sprintf('%04d-%02d', $year, $month);
            if (strpos($dateString, $datePrefix) === 0) {
                // We directly assign the object from the file to our output data.
                $outputData[$dateString] = $dayData;
            }
        }
    }
}

// --- STEP 2: FETCH & PROCESS LEAVE APPLICATIONS ---
$firstDayOfMonth = "$year-$month-01";
$lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));
$stmt = $conn->prepare(
    "SELECT ScheduledLeave, ScheduledReturn, LeaveStatus FROM tblleaveform 
     WHERE (LeaveStatus = 'Pending' OR LeaveStatus = 'Approved') 
     AND ScheduledLeave <= ? AND (ScheduledReturn >= ? OR ScheduledReturn IS NULL)"
);
$stmt->bind_param("ss", $lastDayOfMonth, $firstDayOfMonth);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $startDate = new DateTime($row['ScheduledLeave']);
        $endDate = ($row['ScheduledReturn'] === null) ? (clone $startDate)->modify('+1 day') : new DateTime($row['ScheduledReturn']);
        $datePeriod = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

        foreach ($datePeriod as $date) {
            $dateString = $date->format('Y-m-d');
            
            // This logic correctly handles days from the DB that are NOT in the JSON file.
            // It assumes such a day must be fundamentally 'available' to have an application on it.
            if (!isset($outputData[$dateString])) {
                $outputData[$dateString] = ["classes" => ["available_leave"], "pendingCount" => 0];
            }

            // Process based on leave status
            if ($row['LeaveStatus'] === 'Approved') {
                $outputData[$dateString]['classes'] = ['occupied']; // 'Occupied' is a final state and should be the ONLY class.
                $outputData[$dateString]['pendingCount'] = 0; // An approved leave is no longer pending.
            } 
            elseif ($row['LeaveStatus'] === 'Pending') {
                // A pending application should NOT override an 'occupied' or 'unavailable' status.
                $isFinalState = in_array('occupied', $outputData[$dateString]['classes']) || in_array('unavailable_leave', $outputData[$dateString]['classes']);
                
                if (!$isFinalState) {
                    // Add pending class if it's not already there.
                    if (!in_array('pending_application', $outputData[$dateString]['classes'])) {
                        $outputData[$dateString]['classes'][] = 'pending_application';
                    }
                    // Increment the pending application count for this day.
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