<?php
include('auth.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$jsonInput = file_get_contents('php://input');
$newMonthData = json_decode($jsonInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON received.']);
    exit;
}

$filePath = 'calendar-events.json';
$allEvents = [];

// Read the existing full data from the JSON file
if (file_exists($filePath)) {
    $jsonContent = file_get_contents($filePath);
    $allEvents = json_decode($jsonContent, true);
    if (!is_array($allEvents)) {
        $allEvents = [];
    }
}

// --- CORE LOGIC: Intelligently update the master events list ---
// We only care about statuses that an admin can set.
// The default is 'available', so we only need to store the exceptions ('unavailable').

foreach ($newMonthData as $dateString => $dayData) {
    if (isset($dayData['classes']) && is_array($dayData['classes'])) {
        
        // Case 1: Admin set the day to 'unavailable'. We MUST record this.
        if (in_array('unavailable_leave', $dayData['classes'])) {
            // We only save the base status, not any temporary derived statuses.
            $allEvents[$dateString] = [
                "classes" => ["unavailable_leave"],
                "pendingCount" => 0 // pendingCount is a derived value, it should always be 0 in the base file.
            ];
        } 
        // Case 2: Admin set the day to 'available'. This means we should REMOVE any 'unavailable' override from our file.
        elseif (in_array('available_leave', $dayData['classes'])) {
            if (isset($allEvents[$dateString])) {
                unset($allEvents[$dateString]);
            }
        }
        // Case 3: The day's status is 'occupied' or just 'pending'. These are derived from the database
        // and should NEVER be saved to the JSON file. We do nothing.
    }
}


// Write the cleaned, updated data back to the file.
if (file_put_contents($filePath, json_encode($allEvents, JSON_PRETTY_PRINT))) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Calendar updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to write to file.']);
}