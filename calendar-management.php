<?php
    include('auth.php');

    require_admin();

    include('sqlconnect.php');


?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/calendar-management.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="styles/global.css?v=<?php echo time(); ?>">
    <title>Calendar Management | PharmaScan</title>

    <link rel="icon" type="image/png" href="images/PharmaScanLogo.png">
    
    <script defer src="scripts/global.js?v=<?php echo time(); ?>"></script>
    <script defer src="scripts/calendar-management.js?v=<?php echo time(); ?>"></script>

    <script>
        // These variables are now safely initialized at the top of the PHP script
        const employeeDistributionData = <?php echo $chartDataJSON; ?>;
        const taskStatusData = <?php echo $taskStatusDataJSON; ?>;
    </script>
</head>
<body>
    <div id="toast-container"></div>
    <div id="confirmationModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Confirm Changes</h3>
            <p>Are you sure you want to save these changes?</p>
            <div class="modal-buttons">
                <button class="btn-cancel">Cancel</button>
                <button class="btn-confirm">Confirm</button>
            </div>
        </div>
    </div>

    <div class="app-container">
        <?php include('toast-message.php'); ?>
        <?php include('admin-sidebar.php'); ?>

        <main class="main-container">
            <div class="main-panel">
                <div class="main-title">
                    <h3>Calendar Management</h3>
                    <p>Plan strategic things ahead!</p>
                </div>
                <div class="panels-container">

                    <div class="calendar-panel">
                        <div class="calendar-header">
                            <button class="month-nav-btn" id="prev-month-btn"> 
                                <svg width="12" height="14" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.312472 6.03749L10.625 0.0837383C10.72 0.028883 10.8278 4.76837e-06 10.9375 3.8147e-06C11.0472 3.8147e-06 11.155 0.0288811 11.25 0.0837345C11.345 0.138588 11.4239 0.217484 11.4787 0.312493C11.5336 0.407503 11.5625 0.51528 11.5625 0.624989V12.5325C11.5625 12.6422 11.5336 12.75 11.4787 12.845C11.4239 12.94 11.345 13.0189 11.25 13.0737C11.155 13.1286 11.0472 13.1575 10.9375 13.1575C10.8278 13.1575 10.72 13.1286 10.625 13.0737L0.312472 7.11999C0.217468 7.06513 0.138576 6.98623 0.083726 6.89122C0.0288759 6.79622 0 6.68844 0 6.57874C0 6.46903 0.0288759 6.36126 0.083726 6.26625C0.138576 6.17124 0.217468 6.09234 0.312472 6.03749Z" fill="#E1EBF3"/>
                                </svg>
                            Previous</button>
                            
                            <h3 class="calendar-title" id="calendar-title">Month Year</h3>
                            
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
                            <div id="calendar-grid" class="calendar-grid"></div>
                        </div>
                    </div>

                    <div class="calendar-manager-panel">
                        <!-- NEW MANAGER PANEL HTML START -->
                        <div class="manager-content">
                            <h3 class="manager-title">Calendar Manager</h3>
                            
                            <div class="manager-section legend-section">
                                <p class="section-title">Status Legend</p>
                                <div class="legend-grid">
                                    <div class="legend-item"><span class="legend-color-box available"></span> Available</div>
                                    <div class="legend-item"><span class="legend-color-box pending"></span> Pending Application</div>
                                    <div class="legend-item"><span class="legend-color-box unavailable"></span> Unavailable</div>
                                    <div class="legend-item"><span class="legend-color-box occupied"></span> Occupied</div>
                                </div>
                            </div>

                            <div class="manager-section status-setter-section">
                                <p class="section-title">Set Date Status</p>
                                <div class="status-buttons">
                                    <button class="status-btn active" id="set-available" data-status="available_leave">
                                        <div class="status-icon">
                                            <svg width="13" height="9" viewBox="0 0 9 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3.48 5.64L0 2.16L0.56 1.6L3.48 4.52L8 0L8.56 0.560001L3.48 5.64Z" fill="#145494"/>
                                            </svg>
                                        </div>
                                        
                                        <div class="status-text">
                                            <strong>Available</strong>
                                            <span>Open for Applications</span>
                                        </div>
                                    </button>
                                    <button class="status-btn" id="set-unavailable" data-status="unavailable_leave">
                                        <div class="status-icon">
                                            <svg width="12" height="12" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1.08008 0.25C1.19428 0.252663 1.30468 0.288716 1.39746 0.352539L1.48438 0.425781L3.75391 2.86035L3.92383 3.04395L4.10645 2.87305L6.54492 0.599609C6.60082 0.547492 6.66672 0.507294 6.73828 0.480469C6.81005 0.453644 6.8873 0.440678 6.96387 0.443359C7.04021 0.446091 7.11505 0.464435 7.18457 0.496094C7.25428 0.527873 7.31787 0.572867 7.37012 0.628906C7.42221 0.684851 7.46248 0.750673 7.48926 0.822266C7.51607 0.893987 7.52903 0.970353 7.52637 1.04688C7.52369 1.12344 7.50541 1.19884 7.47363 1.26855C7.44187 1.33821 7.3968 1.40091 7.34082 1.45312L4.90234 3.72656L4.71973 3.89746L4.88965 4.08008L7.16211 6.51758V6.51855L7.16895 6.52441C7.22425 6.57979 7.26758 6.64618 7.29688 6.71875C7.32613 6.79131 7.34025 6.86904 7.33887 6.94727C7.33747 7.02538 7.3208 7.10244 7.28906 7.17383C7.2572 7.24536 7.21059 7.30989 7.15332 7.36328C7.09607 7.41662 7.02861 7.45859 6.95508 7.48535C6.88167 7.512 6.80358 7.5236 6.72559 7.51953C6.64739 7.51544 6.57038 7.49526 6.5 7.46094C6.42976 7.42663 6.36673 7.37832 6.31543 7.31934H6.31641L6.30957 7.31348L4.03711 4.875L3.86621 4.69238L3.68359 4.8623L1.24512 7.13477L1.24121 7.13867C1.1856 7.19297 1.11919 7.23618 1.04688 7.26465C0.974623 7.29306 0.896951 7.3065 0.819336 7.30469C0.741835 7.30283 0.665469 7.28562 0.594727 7.25391C0.523821 7.2221 0.460242 7.17596 0.407227 7.11914C0.354223 7.06233 0.312955 6.99577 0.286133 6.92285C0.259311 6.84991 0.247402 6.77197 0.250977 6.69434C0.254601 6.61685 0.27329 6.54075 0.306641 6.4707C0.32335 6.43562 0.343006 6.40202 0.366211 6.37109L0.445312 6.28613L0.449219 6.28223L2.88867 4.00879L3.07129 3.83887L2.90039 3.65527L0.62793 1.21777C0.5287 1.10451 0.477475 0.957188 0.484375 0.806641C0.491383 0.654443 0.557577 0.511192 0.668945 0.407227C0.780329 0.303309 0.927792 0.246515 1.08008 0.25Z" fill="#145494" stroke="#EEEEEE" stroke-width="0.5"/>
                                            </svg>
                                        </div>
                                        <div class="status-text">
                                            <strong>Unavailable</strong>
                                            <span>No Applications Allowed</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div class="manager-section summary-section">
                                <p class="section-title" id="summary-title">Month Summary</p>
                                <div class="summary-list">
                                    <div class="summary-item">
                                        <div><span class="legend-color-box available"></span> Available</div>
                                        <span id="summary-available">00 Days</span>
                                    </div>
                                    <div class="summary-item">
                                        <div><span class="legend-color-box unavailable"></span> Unavailable</div>
                                        <span id="summary-unavailable">00 Days</span>
                                    </div>
                                     <div class="summary-item">
                                        <div><span class="legend-color-box occupied"></span> Occupied</div>
                                        <span id="summary-occupied">00 Days</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="manager-actions">
                                <button class="action-btn save-btn" id="save-btn">Save</button>
                                <button class="action-btn reset-btn" id="reset-btn">Reset</button>
                            </div>
                        </div>
                </div>
            </div>
        </main>
    </div>
    <div id="calendar-tooltip">
        <svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.186525 10.4875L6.06819 0.668315C6.19204 0.464288 6.36637 0.295613 6.57437 0.178554C6.78237 0.0614944 7.01702 0 7.25569 0C7.49437 0 7.72902 0.0614944 7.93702 0.178554C8.14501 0.295613 8.31934 0.464288 8.44319 0.668315L14.3249 10.4875C14.446 10.6975 14.5101 10.9356 14.5108 11.1781C14.5114 11.4206 14.4486 11.659 14.3285 11.8697C14.2084 12.0803 14.0353 12.2559 13.8263 12.3789C13.6174 12.5019 13.3798 12.5681 13.1374 12.5708H1.37403C1.13146 12.5683 0.893781 12.5023 0.684668 12.3794C0.475555 12.2564 0.302307 12.0808 0.182181 11.8701C0.0620549 11.6593 -0.000755427 11.4208 6.85662e-06 11.1782C0.00076914 10.9356 0.0650773 10.6975 0.186525 10.4875ZM7.25569 3.82081C7.47671 3.82081 7.68867 3.90861 7.84495 4.06489C8.00123 4.22117 8.08903 4.43313 8.08903 4.65415V7.15415C8.08903 7.37516 8.00123 7.58712 7.84495 7.7434C7.68867 7.89968 7.47671 7.98748 7.25569 7.98748C7.03468 7.98748 6.82272 7.89968 6.66644 7.7434C6.51016 7.58712 6.42236 7.37516 6.42236 7.15415V4.65415C6.42236 4.43313 6.51016 4.22117 6.66644 4.06489C6.82272 3.90861 7.03468 3.82081 7.25569 3.82081ZM6.42236 9.65415C6.42236 9.43313 6.51016 9.22117 6.66644 9.06489C6.82272 8.90861 7.03468 8.82082 7.25569 8.82082H7.26236C7.48337 8.82082 7.69533 8.90861 7.85162 9.06489C8.0079 9.22117 8.09569 9.43313 8.09569 9.65415C8.09569 9.87516 8.0079 10.0871 7.85162 10.2434C7.69533 10.3997 7.48337 10.4875 7.26236 10.4875H7.25569C7.03468 10.4875 6.82272 10.3997 6.66644 10.2434C6.51016 10.0871 6.42236 9.87516 6.42236 9.65415Z" fill="#145494"/>
        </svg>
        
        <!-- This span will hold the text, making it a separate flex item -->
        <span id="tooltip-text"></span>
    </div>
</body>
</html>