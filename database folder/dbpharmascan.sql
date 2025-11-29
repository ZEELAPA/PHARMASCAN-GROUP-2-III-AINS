-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql108.infinityfree.com
-- Generation Time: Nov 29, 2025 at 06:41 AM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40279152_dbpharmascan`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblaccountarchive`
--

CREATE TABLE `tblaccountarchive` (
  `ArchivedAccountID` int(11) NOT NULL,
  `OriginalAccountID` int(11) DEFAULT NULL,
  `FullName` varchar(255) DEFAULT NULL,
  `Role` varchar(50) DEFAULT NULL,
  `DepartmentName` varchar(100) DEFAULT NULL,
  `Position` varchar(100) DEFAULT NULL,
  `DateEmployed` date DEFAULT NULL,
  `DateArchived` timestamp NULL DEFAULT current_timestamp(),
  `ArchivedBy` int(11) DEFAULT NULL,
  `Reason` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblaccounts`
--

CREATE TABLE `tblaccounts` (
  `AccountID` int(11) NOT NULL,
  `EmployeeID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `ICCode` varchar(50) DEFAULT NULL,
  `ICPassword` varchar(10) NOT NULL DEFAULT '0000',
  `Role` enum('User','Administrator','','') NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblaccounts`
--

INSERT INTO `tblaccounts` (`AccountID`, `EmployeeID`, `Username`, `Password`, `Email`, `ICCode`, `ICPassword`, `Role`, `CreatedAt`) VALUES
(1, 1, 'zarcangel', '$2y$10$yrc4XlCXpMhiEgBQl3qHFuD3Niw1Oxlyj/lSAnVfoH1/MOUojmrCK', 'zarsenal.k12152421@umak.edu.ph', '1190156469', '1108', 'Administrator', '2025-09-25 17:59:48'),
(2, 2, 'rgumatay', 'rgumatay', 'r.gumatay@pharma.org', '1190137861', '0000', 'User', '2025-09-25 17:59:48'),
(3, 3, 'carsenal', '$2y$10$8h/pJGSDOTS6v7RVKl4PYOQRHsIeujNRhpf.RBuzAXnOq89Y6qTS2', 'c.arsenal@pharma.org', NULL, '0000', 'User', '2025-09-25 17:59:48'),
(4, 4, 'larsenal', '$2y$10$2ItFprkoReiIuMzL8huEvOOVazCC9PsRDWaLn.L7cdtlPl4X18vO2', 'l.arsenal@pharma.org', NULL, '0000', 'User', '2025-09-25 17:59:48'),
(5, 5, 'rpanes', '$2y$10$hDoJFnYDu9QBFCfFximHWuJe98ZGw8gXOeW1WNjrU3R0sORG64ZWG', 'r.panes@pharma.org', NULL, '0000', 'User', '2025-09-25 17:59:48'),
(6, 6, 'mlabradores', 'mlabradores', 'm.labradores@pharma.org', '1226357045', '0000', 'User', '2025-09-25 17:59:48'),
(7, 7, 'Zhann123', '$2y$10$UlizyGOUxN0zveqYTWQ2h.0ac9ygknbeTDkzDCf0Kkolywhmifxnu', 'zhannthony1108@gmail.com', '', '0000', 'User', '2025-09-26 11:02:51'),
(8, 8, 'csoberano', '$2y$10$KN3vHhHFdx1dof/mUHhC9.EAVFZFGoNE9osRC5tJ6sTBJhY//5jgG', 'csoberano@pharma.org', '1188328581', '3233', 'User', '2025-10-22 02:41:10'),
(9, 9, 'clabradores', '$2y$10$h4qzcjvWbAFSvlXu6sf5eudxjZqGSkzkqpvr.erZUx2SsQdv.9j6i', 'clabradores@pharma.org', '321', '4321', 'User', '2025-10-22 08:04:49'),
(10, 10, 'Mikhail', '$2y$10$ZWBPXg0jsM6U2wZzBt07DO2dR0c.5uSsNoVFXKQ0JWR7WpepWTVF.', 'rsoberano.k12152953@umak.edu.ph', '1189949317', '1234', 'User', '2025-11-25 07:03:22');

-- --------------------------------------------------------

--
-- Table structure for table `tblagenda`
--

CREATE TABLE `tblagenda` (
  `AgendaID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `Task` text NOT NULL,
  `Date` date NOT NULL,
  `Deadline` datetime DEFAULT NULL,
  `Priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `Status` enum('Not Started','Pending','Completed','Overdue') DEFAULT NULL,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblagenda`
--

INSERT INTO `tblagenda` (`AgendaID`, `AccountID`, `Task`, `Date`, `Deadline`, `Priority`, `Status`, `Remarks`) VALUES
(37, 3, 'Schedule the weekly team meeting', '2025-10-18', '2025-10-20 00:00:00', 'Medium', 'Completed', 'Aim for Friday afternoon.'),
(42, 1, 'Organize the dispensary storage shelves', '2025-10-18', '2025-10-25 00:00:00', 'Low', 'Completed', ''),
(43, 2, 'Calibrate the digital weighing scales', '2025-10-18', '2025-11-18 00:00:00', 'Medium', 'Completed', 'Annual requirement.'),
(46, 5, 'Complete the mandatory online compliance training', '2025-10-18', '2025-10-30 00:00:00', 'High', 'Completed', 'Module 3 is now available.'),
(47, 6, 'Check stock levels for over-the-counter allergy medication', '2025-10-18', '2025-10-19 00:00:00', 'Medium', 'Completed', 'Pollen season is starting.'),
(49, 1, 'Generate weekly sales performance summary', '2025-10-18', '2025-10-19 00:00:00', 'High', 'Completed', 'tite'),
(51, 4, 'asd', '2025-10-18', '2025-10-24 00:00:00', 'Medium', 'Completed', ''),
(52, 1, 'Admin Dashboard Elements', '2025-10-25', '2025-10-26 00:00:00', 'High', 'Completed', 'Follow the margins and paddings of the elements in the figma, the title of the panels are too close to the edges omsim'),
(53, 1, 'Admin Dashboard Task Distribution Wheel Color', '2025-10-25', '2025-10-26 00:00:00', 'High', 'Completed', 'Change the color ganern'),
(54, 1, 'Task Management', '2025-10-25', '2025-10-26 00:00:00', 'High', 'Completed', 'Task Overview'),
(55, 1, 'Task Management Modal', '2025-10-25', '2025-10-26 00:00:00', 'Medium', 'Completed', 'Adding New Task modal - After selecting deadline the font is set to raleway, change to metropolis'),
(57, 1, 'Blurry Blue Background', '2025-10-25', '2025-10-26 00:00:00', 'High', 'Completed', 'yung blueish thinggy sa corners ng pages for kaartehan'),
(58, 1, 'Admin Dashboard Pending Applications', '2025-10-25', '2025-10-26 00:00:00', 'High', 'Completed', 'Yung text tol dagdagan ng space sa taas pati sa left'),
(59, 8, 'Zhann', '2025-10-26', '2025-10-10 00:00:00', 'Medium', 'Completed', ''),
(61, 7, 'asd', '2025-10-27', '2025-09-29 00:00:00', 'Medium', 'Completed', ''),
(64, 1, 'LEAVE MANAGEMENT LEAVE COUNT', '2025-10-27', '2025-10-30 00:00:00', 'Medium', 'Completed', '- ADD LEAVE COUNT DISPLAY'),
(65, 1, 'MAIN ATTENDANCE PAGE', '2025-10-27', '2025-10-27 00:00:00', 'Low', 'Pending', '- EXPORT BUTTON OPTIONS'),
(66, 9, 'asda', '2025-10-27', '2025-09-28 00:00:00', 'Low', 'Completed', ''),
(68, 3, 'USER MANAGEMENT LEAVE COUNT', '2025-10-27', '2025-10-21 00:00:00', 'Medium', 'Completed', '- EDIT LEAVE COUNT'),
(69, 3, 'NOTIF (UPDATE SUCCESS ETC)', '2025-10-27', '2025-10-21 00:00:00', 'Low', 'Pending', ''),
(70, 9, 'USER LEAVE APPLICATION CANCEL BUTTON', '2025-10-27', '2025-10-20 00:00:00', 'Medium', 'Completed', ''),
(71, 1, 'LEAVE MANAGEMENT NOTIF', '2025-10-27', '2025-10-23 00:00:00', 'Low', 'Completed', 'NOTIFICATION FOR INSUFICIENT LEAVE COUNT'),
(72, 1, 'USER PANEL TAASAN MARGIN', '2025-10-28', '2025-10-29 00:00:00', 'High', 'Completed', ''),
(73, 1, 'REVISIONS / SUGGESTIONS', '2025-10-28', '2025-11-04 00:00:00', 'Low', 'Completed', 'Leave Application Dates Limitation - Calendar\r\nSample Computation per Overtime Hour\r\nAbsence/Tardiness Count / Working Days need, Holidays - Double Pay\r\n\r\nFuture Payroll System (Recommendation)\r\n\r\nCreatedAt / HiredAt - \r\n\r\nCalendar Revisionsn'),
(74, 8, '5:52', '2025-11-15', '2025-11-15 00:00:00', 'High', 'Completed', ''),
(75, 1, 'Admin Dashboard Changes', '2025-11-15', '2025-11-16 00:00:00', 'Low', 'Completed', 'Please make the vacation leave application entries in the dashboard clickable and it will direct to the vacations and leaves management page'),
(76, 1, 'User Side Calendar Translation', '2025-11-15', '2025-11-16 00:00:00', 'Low', 'Not Started', 'Please accomplish the translation of the user side calendar, message me upon completion'),
(77, 1, 'Accounts omsim Pictures', '2025-11-15', '2025-11-16 00:00:00', 'Medium', 'Completed', 'lagyan ng pics yung mga account sa users tab also allow the account settings to upload new pics'),
(79, 1, 'Admin Attendance Overview', '2025-11-15', '2025-11-16 00:00:00', 'High', 'Completed', 'Attendance Overview for the admin account incorporate in account settings add another option in the right panel'),
(80, 1, 'Admin Attendance Overview', '2025-11-15', '2025-11-16 00:00:00', 'High', 'Completed', 'Attendance Overview for the admin account incorporate in account settings add another option in the right panel'),
(81, 1, 'Calendars For Date Time Pickers', '2025-11-15', '2025-11-16 00:00:00', 'Medium', 'Completed', 'Slide to right'),
(82, 1, 'Calendars For Date Time Pickers', '2025-11-15', '2025-11-16 00:00:00', 'Medium', 'Completed', 'Slide to right'),
(84, 8, 'Duplicate Testing', '2025-11-15', '2025-11-16 00:00:00', 'Medium', 'Completed', ''),
(85, 1, 'Calendar Modal', '2025-11-15', '2025-11-16 00:00:00', 'High', 'Completed', ''),
(86, 1, 'Calendar Page', '2025-11-15', '2025-11-16 00:00:00', 'High', 'Completed', ''),
(87, 1, 'TOASTS TRANSLATION', '2025-11-16', '2025-11-17 00:00:00', 'High', 'Pending', 'Change muna lahat ng error messages and etc'),
(88, 8, 'Login Background', '2025-11-16', '2025-11-17 00:00:00', 'Medium', 'Completed', 'Hello Zhann this task is now accomplished kindly refer to the for translation page there have been changes in the log in page'),
(89, 8, 'Calendar Icon for Sidebar', '2025-11-16', '2025-11-18 00:00:00', 'Low', 'Completed', 'Please refer to the branding page in the figma file there is an admin sidebar for reference there'),
(90, 8, 'Email Template', '2025-11-16', '2025-11-18 00:00:00', 'Medium', 'Completed', ''),
(92, 1, 'Session Expiration', '2025-11-17', '2025-11-20 00:00:00', 'High', 'Completed', 'add a session expiration feature for security purposes, the account will be logged out after 5 mins kapag walang activity na ginagawa, on the day of the presentation pls make it to 1 min so that we can demonstrate the feature.'),
(94, 8, 'Export Modal', '2025-11-17', '2025-11-18 00:00:00', 'High', 'Completed', ''),
(95, 1, 'User Side Tasks Page', '2025-11-17', '2025-11-19 00:00:00', 'High', 'Completed', 'Tol di scrollable yung tasks na panels sa user side'),
(96, 1, 'Email Validation', '2025-11-18', '2025-11-19 00:00:00', 'Medium', 'Completed', ''),
(97, 1, 'Email Sending', '2025-11-18', '2025-11-19 00:00:00', 'Medium', 'Completed', '- Tuwing may Task Assigned\r\n- Tuwing Approved yung Leave Appl'),
(98, 1, 'Employment Status Deactivation of Account', '2025-11-18', '2025-11-19 00:00:00', 'High', 'Completed', ''),
(99, 1, 'Transfer Handlers and Action Files to another folder', '2025-11-18', '2025-11-19 00:00:00', 'High', 'Not Started', ''),
(100, 1, 'DTR Exporting', '2025-11-18', '2025-11-19 00:00:00', 'High', 'Completed', ''),
(101, 1, 'Attendance Page Toast', '2025-11-19', '2025-11-20 00:00:00', 'Low', 'Not Started', ''),
(102, 1, 'Attendance Page Toast', '2025-11-19', '2025-11-20 00:00:00', 'Low', 'Not Started', ''),
(103, 1, 'Tasks Management Module', '2025-11-19', '2025-11-20 00:00:00', 'High', 'Not Started', 'Archives Confirmation and Toasts'),
(104, 1, 'Task Management Module', '2025-11-19', '2025-11-22 00:00:00', 'Medium', 'Completed', 'Date Picker use our calendar'),
(105, 8, 'test 1', '2025-11-19', '2025-11-22 00:00:00', 'Medium', 'Completed', ''),
(106, 4, 'test1', '2025-11-19', '2025-11-21 00:00:00', 'Low', 'Pending', ''),
(107, 8, 'Maligo', '2025-11-20', '2025-11-21 00:00:00', 'Medium', 'Completed', 'asdasfsdg'),
(108, 1, 'Password complexity', '2025-11-22', '2025-11-23 00:00:00', 'Medium', 'Completed', ''),
(109, 1, 'Account Settings', '2025-11-22', '2025-11-24 00:00:00', 'Medium', 'Completed', '- NFC Login for Accessing\r\n- NFC Authentication'),
(110, 1, 'User Management', '2025-11-24', '2025-11-26 00:00:00', 'High', 'Pending', 'in the user management modal Account tab please add an option account type and allow the admin to select whether the account is an employee or admin account'),
(111, 1, 'User Management', '2025-11-24', '2025-11-26 00:00:00', 'Medium', 'Pending', 'Please add in the user management account tab. date of recruitment'),
(112, 1, 'Export NFC Confirmation', '2025-11-24', '2025-11-26 00:00:00', 'Medium', 'Completed', 'Please add a security prompt to scan nfc and input nfc password before allowing all the users to export their data\r\n\r\nAll PCs would require an NFC Scanner if this was the case'),
(114, 10, 'Task Test', '2025-11-25', '2025-11-29 00:00:00', 'Low', 'Completed', 'For Functional Testing Procedure'),
(115, 8, 'Confirmations', '2025-11-25', '2025-11-27 00:00:00', 'Low', 'Not Started', 'User Leave Application Confirmation and Cancellation Confirmation'),
(116, 1, 'Notification | Admin Leave Application', '2025-11-25', '2025-11-27 00:00:00', 'Low', 'Not Started', 'After the admin creates a leave application there is a browser notification that the operation was successful, please apply the toast/notif design to that +++ when the admin approves a leave application there must be a confirmation'),
(117, 1, 'Admin Calendar', '2025-11-25', '2025-11-27 00:00:00', 'Low', 'Not Started', 'Saving confirmation prompt and also the successful notifcation'),
(120, 10, 'TESTING EMAIL', '2025-11-26', '2025-11-13 00:00:00', 'Low', 'Not Started', ''),
(121, 10, 'Task Testing', '2025-11-27', '2025-11-28 00:00:00', 'Medium', 'Not Started', 'This task is used for functional testing'),
(122, 10, 'delegate task', '2025-11-27', '2025-11-06 00:00:00', 'Medium', 'Completed', 'natapos na'),
(123, 1, 'PBL Test Email', '2025-11-27', '2025-11-30 00:00:00', 'Medium', 'Not Started', ''),
(124, 10, 'PB: Test', '2025-11-27', '2025-11-23 00:00:00', 'High', 'Not Started', '');

-- --------------------------------------------------------

--
-- Table structure for table `tblagendaarchive`
--

CREATE TABLE `tblagendaarchive` (
  `ArchivedAgendaID` int(11) NOT NULL,
  `AgendaID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `Task` text NOT NULL,
  `Date` date NOT NULL,
  `Deadline` datetime DEFAULT NULL,
  `Priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `Status` enum('Not Started','Pending','Completed','Overdue') DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `ArchivedBy` int(11) DEFAULT NULL,
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblagendaarchive`
--

INSERT INTO `tblagendaarchive` (`ArchivedAgendaID`, `AgendaID`, `AccountID`, `Task`, `Date`, `Deadline`, `Priority`, `Status`, `Remarks`, `ArchivedBy`, `ArchivedAt`) VALUES
(1, 67, 7, 'SDASD', '2025-10-27', '2025-09-28 00:00:00', 'High', 'Completed', '', 1, '2025-10-27 16:54:35'),
(2, 63, 3, 'ADMIN VACATIONS AND LEAVES', '2025-10-27', '2025-10-28 00:00:00', 'Medium', 'Completed', 'FIGMA - FOR TRANSLATIONS GITNA BANDA', 1, '2025-10-28 11:10:49'),
(3, 60, 8, 'TASK MANAGEMENT ARCHIVE BUTTON', '2025-10-26', '2025-10-10 00:00:00', 'High', 'Not Started', 'ARCHIVE BUTTON', 1, '2025-11-15 09:57:01'),
(4, 44, 3, 'ULOLLLLL', '2025-10-18', '2025-10-21 00:00:00', 'High', 'Completed', '', 1, '2025-11-15 10:03:36'),
(5, 78, 1, 'test', '2025-11-15', '2025-11-17 00:00:00', 'Low', 'Not Started', 'sadasdasd', 1, '2025-11-15 10:07:08'),
(6, 93, 1, 'Session Expiration', '2025-11-17', '2025-11-20 00:00:00', 'High', 'Not Started', 'add a session expiration feature for security purposes, the account will be logged out after 5 mins kapag walang activity na ginagawa, on the day of the presentation pls make it to 1 min so that we can demonstrate the feature.', 1, '2025-11-17 16:02:39'),
(7, 50, 3, 'asd', '2025-10-18', '2025-10-04 00:00:00', 'High', 'Completed', '', 1, '2025-11-20 04:28:19'),
(8, 62, 7, 'asd', '2025-10-27', '2025-10-21 00:00:00', 'High', 'Completed', '', 1, '2025-11-20 04:28:29'),
(9, 113, 10, 'Task Testing', '2025-11-24', '2025-11-28 00:00:00', 'Medium', 'Not Started', 'This Task is used for functional testing', 1, '2025-11-25 08:26:20'),
(10, 83, 8, 'Chapter 2', '2025-11-15', '2025-11-15 00:00:00', 'High', 'Not Started', 'gawa na atleast 10 rrls', 1, '2025-11-26 06:21:14'),
(11, 118, 9, 'Chapter 2', '2025-11-26', '2025-11-29 00:00:00', 'Low', 'Pending', '', 1, '2025-11-26 08:37:14'),
(12, 56, 1, 'Calendar', '2025-10-25', '2025-10-26 00:00:00', 'High', 'Completed', 'Fix the overall look of the calendar', 1, '2025-11-27 23:39:36'),
(13, 91, 8, 'Confirmation Modal Saan may mga ganyan pre', '2025-11-17', '2025-11-18 00:00:00', 'Medium', 'Pending', '', 1, '2025-11-28 02:13:29'),
(14, 119, 1, 'email test', '2025-11-26', '2025-11-15 00:00:00', 'Medium', 'Not Started', '', 1, '2025-11-28 02:14:07');

-- --------------------------------------------------------

--
-- Table structure for table `tblattendance`
--

CREATE TABLE `tblattendance` (
  `AttendanceID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `TimeIn` datetime DEFAULT NULL,
  `TimeOut` datetime DEFAULT NULL,
  `AttendanceDate` date NOT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `OvertimeHours` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblattendance`
--

INSERT INTO `tblattendance` (`AttendanceID`, `AccountID`, `TimeIn`, `TimeOut`, `AttendanceDate`, `Remarks`, `OvertimeHours`) VALUES
(47, 1, '2025-10-20 14:09:19', NULL, '2025-10-20', NULL, 0),
(48, 3, '2025-10-21 14:09:23', NULL, '2025-10-21', NULL, 0),
(49, 4, '2025-10-21 14:09:25', NULL, '2025-10-21', NULL, 0),
(50, 2, '2025-10-21 14:09:26', '2025-10-21 18:14:48', '2025-10-21', NULL, 0),
(51, 6, '2025-10-21 14:09:29', '2025-10-21 18:14:50', '2025-10-21', NULL, 0),
(52, 1, '2025-10-21 18:14:27', '2025-10-21 18:14:53', '2025-10-21', NULL, 0),
(53, 3, '2025-11-14 20:19:42', NULL, '2025-11-14', NULL, 0),
(54, 2, '2025-11-14 20:20:09', NULL, '2025-11-14', NULL, 0),
(55, 6, '2025-11-14 20:20:11', NULL, '2025-11-14', NULL, 0),
(56, 1, '2025-11-14 20:20:21', NULL, '2025-11-14', NULL, 0),
(57, 8, '2025-11-14 20:21:43', '2025-11-14 20:21:46', '2025-11-14', NULL, 0),
(58, 1, '2025-11-15 15:54:30', '2025-11-15 18:10:05', '2025-11-15', NULL, 0),
(59, 8, '2025-11-15 15:54:39', NULL, '2025-11-15', NULL, 0),
(60, 6, '2025-11-15 15:55:25', '2025-11-15 15:55:48', '2025-11-15', NULL, 0),
(61, 2, '2025-11-15 15:55:30', NULL, '2025-11-15', NULL, 0),
(62, 1, '2025-11-20 13:32:09', '2025-11-20 13:32:55', '2025-11-20', NULL, 0),
(63, 8, '2025-11-28 12:28:48', '2025-11-28 12:29:21', '2025-11-28', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbldepartment`
--

CREATE TABLE `tbldepartment` (
  `DepartmentID` int(11) NOT NULL,
  `DepartmentName` varchar(100) NOT NULL,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbldepartment`
--

INSERT INTO `tbldepartment` (`DepartmentID`, `DepartmentName`, `Remarks`) VALUES
(1, 'Marketing', NULL),
(2, 'Pharmacy', NULL),
(3, 'Management', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblemployees`
--

CREATE TABLE `tblemployees` (
  `EmployeeID` int(11) NOT NULL,
  `PersonalID` int(11) NOT NULL,
  `DepartmentID` int(11) DEFAULT NULL,
  `Position` varchar(100) DEFAULT NULL,
  `EmploymentStatus` enum('Active','Inactive','On Leave','Archived') NOT NULL DEFAULT 'Inactive',
  `VacationLeaveBalance` decimal(4,1) NOT NULL DEFAULT 7.0,
  `SickLeaveBalance` decimal(4,1) NOT NULL DEFAULT 7.0,
  `DateEmployed` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblemployees`
--

INSERT INTO `tblemployees` (`EmployeeID`, `PersonalID`, `DepartmentID`, `Position`, `EmploymentStatus`, `VacationLeaveBalance`, `SickLeaveBalance`, `DateEmployed`) VALUES
(1, 1, 3, 'IT Administrator', 'Active', '7.0', '7.0', NULL),
(2, 2, 2, 'Head Pharmacist', 'Active', '7.0', '7.0', NULL),
(3, 3, 2, 'Pharmacist', 'Active', '1.0', '1.0', NULL),
(4, 4, 2, 'Pharmacy Technician', 'Active', '6.0', '7.0', NULL),
(5, 5, 3, 'HR Manager', 'Active', '7.0', '7.0', NULL),
(6, 6, 2, 'Pharmacy Assistant', 'Active', '0.0', '7.0', NULL),
(7, 7, 1, 'IT Support Staff', 'Active', '6.0', '7.0', NULL),
(8, 8, 1, 'IT Support Staff', 'Active', '-3.0', '-4.0', NULL),
(9, 9, 1, 'Pharmacy Cashier', 'Inactive', '7.0', '7.0', NULL),
(10, 10, 1, 'Head Marketing', 'Active', '1.0', '0.0', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblleaveform`
--

CREATE TABLE `tblleaveform` (
  `LeaveID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `ScheduledLeave` date NOT NULL,
  `ScheduledReturn` date DEFAULT NULL,
  `Reason` text NOT NULL,
  `LeaveStatus` varchar(50) NOT NULL,
  `Remarks` text DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblleaveform`
--

INSERT INTO `tblleaveform` (`LeaveID`, `AccountID`, `ScheduledLeave`, `ScheduledReturn`, `Reason`, `LeaveStatus`, `Remarks`, `CreatedAt`) VALUES
(7, 8, '2025-11-01', '2025-11-19', 'Vacation Leave', 'Rejected', '', '2025-10-23 21:12:19'),
(15, 8, '2025-11-21', '2025-11-21', 'Sick Leave', 'Approved', '', '2025-11-19 20:58:46'),
(18, 10, '2025-12-02', '2025-12-04', 'Vacation Leave', 'Approved', 'Holiday Leave', '2025-11-24 23:22:52'),
(19, 10, '2025-12-04', '2025-12-06', 'Sick Leave', 'Rejected', 'asdasda', '2025-11-24 23:25:27'),
(21, 10, '2025-12-05', '2025-12-06', 'Sick Leave', 'Approved', '', '2025-11-25 22:19:26'),
(23, 10, '2025-11-28', '2025-11-29', 'Sick Leave', 'Approved', '', '2025-11-27 03:04:43'),
(25, 10, '2025-12-18', '2025-12-20', 'Vacation Leave', 'Approved', '', '2025-11-27 06:38:47'),
(26, 10, '2025-12-26', '2025-12-27', 'Sick Leave', 'Approved', '', '2025-11-27 15:30:55'),
(27, 10, '2025-11-29', '2025-11-29', 'Sick Leave', 'Approved', '', '2025-11-27 20:35:31'),
(28, 10, '2025-12-11', '2025-12-11', 'Sick Leave', 'Pending', '', '2025-11-27 20:36:08');

-- --------------------------------------------------------

--
-- Table structure for table `tblleaveformarchive`
--

CREATE TABLE `tblleaveformarchive` (
  `ArchivedLeaveID` int(11) NOT NULL,
  `LeaveID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `ScheduledLeave` date NOT NULL,
  `ScheduledReturn` date DEFAULT NULL,
  `Reason` text NOT NULL,
  `LeaveStatus` varchar(50) NOT NULL,
  `Remarks` text DEFAULT NULL,
  `CreatedAt` datetime NOT NULL,
  `ArchivedBy` int(11) DEFAULT NULL,
  `ArchivedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblleaveformarchive`
--

INSERT INTO `tblleaveformarchive` (`ArchivedLeaveID`, `LeaveID`, `AccountID`, `ScheduledLeave`, `ScheduledReturn`, `Reason`, `LeaveStatus`, `Remarks`, `CreatedAt`, `ArchivedBy`, `ArchivedAt`) VALUES
(1, 4, 8, '2025-10-23', '2025-10-22', 'Maternity Leave', 'Cancelled', '', '2025-10-23 21:10:17', 8, '2025-10-28 00:30:19'),
(2, 3, 8, '2025-10-08', '2025-10-24', 'Vacation Leave', 'Cancelled', '', '2025-10-23 21:10:17', 8, '2025-11-15 01:53:46'),
(3, 17, 10, '2025-12-02', '2025-12-04', 'Vacation Leave', 'Cancelled', 'Holiday Leave', '2025-11-24 23:21:46', 10, '2025-11-24 23:22:00'),
(6, 13, 3, '2025-10-27', '2025-10-27', 'Vacation Leave', 'Approved', 'Zhann', '2025-10-27 18:51:54', 1, '2025-11-27 15:43:32'),
(7, 8, 8, '2025-10-27', '2025-10-29', 'Sick Leave', 'Approved', '', '2025-10-23 21:12:33', 1, '2025-11-27 15:43:38'),
(8, 11, 7, '2025-10-27', '2025-10-27', 'Vacation Leave', 'Approved', '', '2025-10-27 18:42:01', 1, '2025-11-27 15:43:48'),
(9, 12, 4, '2025-10-27', '2025-10-27', 'Vacation Leave', 'Approved', '', '2025-10-27 18:51:35', 1, '2025-11-27 15:43:53'),
(10, 24, 10, '2025-12-06', '2025-12-07', 'Sick Leave', 'Rejected', '', '2025-11-27 04:18:10', 1, '2025-11-27 15:44:01'),
(11, 9, 8, '2025-10-25', '2025-10-25', 'Vacation Leave', 'Approved', '', '2025-10-24 03:23:13', 1, '2025-11-27 15:44:06'),
(12, 22, 3, '2025-12-11', '2025-12-15', 'Vacation Leave', 'Approved', 'blablabla', '2025-11-27 02:37:46', 1, '2025-11-27 15:44:11'),
(13, 20, 3, '2025-12-09', '2025-12-11', 'Sick Leave', 'Approved', '', '2025-11-25 22:16:20', 1, '2025-11-27 15:44:19'),
(14, 14, 8, '2025-10-29', '2025-11-04', 'Sick Leave', 'Approved', 'papatira ako', '2025-10-28 19:34:56', 1, '2025-11-27 15:44:38'),
(15, 6, 8, '2025-11-01', '2025-11-01', 'Vacation Leave', 'Approved', '', '2025-10-23 21:12:08', 1, '2025-11-27 15:44:44'),
(16, 16, 3, '2025-11-21', '2025-11-23', 'Sick Leave', 'Approved', '', '2025-11-19 21:46:27', 1, '2025-11-27 15:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `tblpersonalinfo`
--

CREATE TABLE `tblpersonalinfo` (
  `PersonalID` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Age` int(10) DEFAULT NULL,
  `Gender` varchar(50) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `ProfilePicture` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpersonalinfo`
--

INSERT INTO `tblpersonalinfo` (`PersonalID`, `FirstName`, `LastName`, `Age`, `Gender`, `ContactNumber`, `ProfilePicture`) VALUES
(1, 'Zhann Llyn Anthony', 'Arsenal', 28, 'Male', '639760602712', ''),
(2, 'Ralph', 'Gumatay', 34, 'Male', '09228765432', ''),
(3, 'Christian', 'Arsenal', 25, 'Female', '', ''),
(4, 'Luke', 'Arsenal', 22, 'Male', '09457890123', ''),
(5, 'Red', 'Panes', 29, 'Female', '09334455667', ''),
(6, 'Mark', 'Labradores', 31, 'Male', '09182345678', ''),
(7, 'hellop', 'dasd', 30, 'Male', '3213123123', ''),
(8, 'Christopher', 'Soberano', 30, 'Male', '123', '1763581668_28081a07c00fb0f37ae96b1ad19522e2.png'),
(9, 'Christian Dave', 'Labradores', 30, 'Male', '', ''),
(10, 'Mikhail', 'Doe', 20, 'Male', '639613599430', '924f5375b635b6e388567adc4a04a754.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblaccountarchive`
--
ALTER TABLE `tblaccountarchive`
  ADD PRIMARY KEY (`ArchivedAccountID`);

--
-- Indexes for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  ADD PRIMARY KEY (`AccountID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `ICCode` (`ICCode`);

--
-- Indexes for table `tblagenda`
--
ALTER TABLE `tblagenda`
  ADD PRIMARY KEY (`AgendaID`);

--
-- Indexes for table `tblagendaarchive`
--
ALTER TABLE `tblagendaarchive`
  ADD PRIMARY KEY (`ArchivedAgendaID`),
  ADD KEY `idx_original_agendaID` (`AgendaID`);

--
-- Indexes for table `tblattendance`
--
ALTER TABLE `tblattendance`
  ADD PRIMARY KEY (`AttendanceID`);

--
-- Indexes for table `tbldepartment`
--
ALTER TABLE `tbldepartment`
  ADD PRIMARY KEY (`DepartmentID`);

--
-- Indexes for table `tblemployees`
--
ALTER TABLE `tblemployees`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD KEY `fk_employees_personalinfo` (`PersonalID`);

--
-- Indexes for table `tblleaveform`
--
ALTER TABLE `tblleaveform`
  ADD PRIMARY KEY (`LeaveID`),
  ADD KEY `fk_leave_account` (`AccountID`);

--
-- Indexes for table `tblleaveformarchive`
--
ALTER TABLE `tblleaveformarchive`
  ADD PRIMARY KEY (`ArchivedLeaveID`);

--
-- Indexes for table `tblpersonalinfo`
--
ALTER TABLE `tblpersonalinfo`
  ADD PRIMARY KEY (`PersonalID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblaccountarchive`
--
ALTER TABLE `tblaccountarchive`
  MODIFY `ArchivedAccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  MODIFY `AccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tblagenda`
--
ALTER TABLE `tblagenda`
  MODIFY `AgendaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `tblagendaarchive`
--
ALTER TABLE `tblagendaarchive`
  MODIFY `ArchivedAgendaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tblattendance`
--
ALTER TABLE `tblattendance`
  MODIFY `AttendanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `tbldepartment`
--
ALTER TABLE `tbldepartment`
  MODIFY `DepartmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblemployees`
--
ALTER TABLE `tblemployees`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tblleaveform`
--
ALTER TABLE `tblleaveform`
  MODIFY `LeaveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `tblleaveformarchive`
--
ALTER TABLE `tblleaveformarchive`
  MODIFY `ArchivedLeaveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tblpersonalinfo`
--
ALTER TABLE `tblpersonalinfo`
  MODIFY `PersonalID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
