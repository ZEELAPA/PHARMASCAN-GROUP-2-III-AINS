<?php

include('auth.php');
require_admin();
include('sqlconnect.php');

// Basic Session Check
if (!isset($_SESSION['AccountID'])) {
    header('Location: login.php');
    exit();
}

// =============================================================
//  BACKEND LOGIC: PROCESS DATA FOR CHARTS
// =============================================================

// --- 1. LEAVE BREAKDOWN (Pie Chart) ---
// Logic: Count rows based on 'Reason' keywords.
$sqlLeave = "SELECT 
    SUM(CASE WHEN Reason LIKE '%Sick%' THEN 1 ELSE 0 END) as SickCount,
    SUM(CASE WHEN Reason LIKE '%Vacation%' THEN 1 ELSE 0 END) as VacationCount
    FROM tblleaveform 
    WHERE LeaveStatus = 'Approved'"; 

$resultLeave = $conn->query($sqlLeave);
$leaveData = $resultLeave->fetch_assoc();
$sickCount = $leaveData['SickCount'] ?? 0;
$vacationCount = $leaveData['VacationCount'] ?? 0;

// --- 2. ABSENCES BY DAY (Bar Chart + Insight) ---
// Logic: (Total Active Employees) - (Average Attendance per Day)

// A. Get Total Active Employees
$sqlTotalEmp = "SELECT COUNT(*) as Total FROM tblemployees WHERE EmploymentStatus = 'Active'";
$resTotalEmp = $conn->query($sqlTotalEmp);
$totalEmployees = $resTotalEmp->fetch_assoc()['Total'];

// B. Get Attendance Stats (Last 3 Months)
$sqlAtt = "SELECT 
    DAYNAME(AttendanceDate) as DayName, 
    COUNT(*) as TotalPresent, 
    COUNT(DISTINCT AttendanceDate) as UniqueDates
    FROM tblattendance 
    JOIN tblaccounts ON tblattendance.AccountID = tblaccounts.AccountID
    JOIN tblemployees ON tblaccounts.EmployeeID = tblemployees.EmployeeID
    WHERE tblattendance.AttendanceDate >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    AND tblemployees.EmploymentStatus = 'Active'
    GROUP BY DayName";

$resultAtt = $conn->query($sqlAtt);

// Map DB results to an associative array
$attendanceMap = [];
while($row = $resultAtt->fetch_assoc()) {
    $avgPresent = $row['TotalPresent'] / $row['UniqueDates'];
    $attendanceMap[$row['DayName']] = $avgPresent;
}

// Calculate Absences for Mon-Fri
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$absenceCounts = [];
$highestDay = '';
$highestCount = -1;

foreach($daysOfWeek as $day) {
    $avgPresent = isset($attendanceMap[$day]) ? $attendanceMap[$day] : 0;
    // Absence = Total Employees - Average Present
    $absent = max(0, round($totalEmployees - $avgPresent));
    $absenceCounts[] = $absent;

    if($absent > $highestCount) {
        $highestCount = $absent;
        $highestDay = $day;
    }
}

// Generate Insight Text
$avgAbsence = array_sum($absenceCounts) / count($absenceCounts);
$pctDiff = ($avgAbsence > 0) ? round((($highestCount - $avgAbsence) / $avgAbsence) * 100) : 0;
$insightText = ($highestCount > 0) 
    ? "<strong>{$highestDay}</strong> shows <strong>{$pctDiff}% higher</strong> absences than the weekly average."
    : "No significant absence patterns detected.";

// --- 3. LEAVE FORECAST (Line Chart) ---
// Logic: Historical Data + 6-Month Moving Average Forecast

// A. Get History (Last 6 Months)
// We fetch '%Y-%m-01' to allow accurate date math for the next steps
$sqlHistory = "SELECT 
    DATE_FORMAT(ScheduledLeave, '%Y-%m-01') as FullDate, 
    COUNT(*) as Count 
    FROM tblleaveform 
    WHERE LeaveStatus = 'Approved' 
    AND ScheduledLeave >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(ScheduledLeave, '%Y-%m') 
    ORDER BY FullDate ASC";

$resultHistory = $conn->query($sqlHistory);

$historyLabels = []; // Stores "Jan", "Feb"
$historyData = [];   // Stores actual counts: 5, 2, 8
$fullDates = [];     // Stores YYYY-MM-01 for calculation

while($row = $resultHistory->fetch_assoc()) {
    // Convert YYYY-MM-01 to Short Month Name (Jan, Feb)
    $historyLabels[] = date('M', strtotime($row['FullDate'])); 
    $historyData[] = (int)$row['Count'];
    $fullDates[] = $row['FullDate'];
}

// B. Prepare Data for Forecasting
// Create a combined array to calculate the moving average continuously
$combinedData = $historyData; 
$forecastLabels = [];
$forecastDataValues = [];

// Determine where to start forecasting
// If we have history, start next month after the last history entry.
// If no history, start next month from Today.
$lastDateStr = empty($fullDates) ? date('Y-m-01') : end($fullDates);
$startDate = new DateTime($lastDateStr);

// C. Generate 6 Months of Forecast
$monthsToForecast = 3;

for ($i = 1; $i <= $monthsToForecast; $i++) {
    // 1. Move to next month
    $startDate->modify('+1 month');
    $forecastLabels[] = $startDate->format('M');

    // 2. Calculate Simple Moving Average (SMA) of last 3 months
    $count = count($combinedData);
    $prediction = 0;

    if ($count >= 3) {
        // Average of last 3 data points (which may include previous forecasts)
        $sum = $combinedData[$count-1] + $combinedData[$count-2] + $combinedData[$count-3];
        $prediction = round($sum / 3);
    } elseif ($count > 0) {
        // Fallback if less than 3 months of data exist
        $prediction = round(array_sum($combinedData) / $count);
    }
    
    // 3. Add prediction to arrays
    $forecastDataValues[] = $prediction;
    $combinedData[] = $prediction; // Add to combined so next loop uses this value
}

// D. Prepare Chart Arrays (Visual formatting)

// Final Labels: History + Forecast
$finalLabels = array_merge($historyLabels, $forecastLabels);

// Solid Line (History): Pad the end with 'null' so the line stops where history ends
$solidData = $historyData;
// Add nulls for every forecast month
for($i = 0; $i < count($forecastDataValues); $i++) {
    $solidData[] = null;
}

// Dashed Line (Forecast): 
// Needs to start with nulls, THEN connect to the last history point, THEN show forecast.
$dashedData = array_fill(0, count($historyData) - 1, null);

// Connection Point: The last actual data point (prevents a gap in the line)
if (!empty($historyData)) {
    $dashedData[] = end($historyData);
} else {
    // If no history exists, pad with one null just to match structure
    $dashedData[] = null; 
}

// Append the calculated forecast values
foreach($forecastDataValues as $val) {
    $dashedData[] = $val;
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/admin-analytics.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>Admin Analytics | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- INJECT PHP DATA INTO JS -->
    <script>
        const dashboardData = {
            pie: {
                sick: <?php echo $sickCount; ?>,
                vacation: <?php echo $vacationCount; ?>
            },
            bar: {
                labels: <?php echo json_encode($daysOfWeek); ?>,
                data: <?php echo json_encode($absenceCounts); ?>
            },
            line: {
                // Note: Use $finalLabels, $solidData, and $dashedData calculated above
                labels: <?php echo json_encode($finalLabels); ?>,
                history: <?php echo json_encode($solidData); ?>,
                forecast: <?php echo json_encode($dashedData); ?>
            }
        };
    </script>

    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/admin-analytics.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div class="app-container">
        
        <?php include('toast-message.php'); ?>
        <?php include('admin-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                
                <div class="header-section">
                    <div class="main-title">
                        <h3>Analytics</h3>
                        <p>Data-driven Decisions at your Screen</p>
                    </div>
                </div>

                <div class="panels-container">
                    <!-- TOP SECTION: 2 Panels -->
                    <div class="top-panels">
                        
                        <!-- Top Panel 1: Breakdown -->
                        <div class="content-panel top-left-panel">
                            <div class="panel-header">
                                <h4>Breakdown of Leave Types</h4>
                            </div>
                            <div class="panel-body">
                                <p style="font-size: 0.8rem; color: #888; margin-bottom:10px;">Distribution of leave categories</p>
                                <div class="chart-container">
                                    <canvas id="leavePieChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Top Panel 2: Absences -->
                        <div class="content-panel top-right-panel">
                            <div class="panel-header">
                                <h4>Absences by Day of Week</h4>
                            </div>
                            <div class="panel-body">
                                <p style="font-size: 0.8rem; color: #888; margin-bottom:10px;">Pattern analysis for potential morale indicators (Weekly Absences Average)</p>
                                <div class="chart-container" style="max-height: 150px;">
                                    <canvas id="absenceBarChart"></canvas>
                                </div>
                                <!-- Dynamic Insight -->
                                <div class="insight-box">
                                    <i class="fa fa-info-circle"></i>
                                    <span><?php echo $insightText; ?></span>
                                </div>
                            </div>
                        </div>

                    </div>
    
                    <!-- BOTTOM SECTION: Forecast -->
                    <div class="bottom-panels">
                        <div class="content-panel bottom-full-panel">
                            <div class="panel-header">
                                <h4>Leave Forecasting - Time Series Analysis</h4>
                            </div>
                            <div class="panel-body"> 
                                <p style="font-size: 0.8rem; color: #888; margin-bottom:10px;">Historical data with predictive forecast using Simple Moving Average</p>
                                <div class="chart-container" style="height: 225px;">
                                    <canvas id="forecastLineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</body>
</html>