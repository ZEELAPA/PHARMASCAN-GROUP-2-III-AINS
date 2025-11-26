-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2025 at 04:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbgroup3`
--
CREATE DATABASE IF NOT EXISTS `dbgroup3` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `dbgroup3`;

-- --------------------------------------------------------

--
-- Table structure for table `pp3`
--

CREATE TABLE `pp3` (
  `AgendaID INT` int(3) DEFAULT NULL,
  `Account ID` varchar(9) DEFAULT NULL,
  `Task Text` varchar(23) DEFAULT NULL,
  `Date` varchar(10) DEFAULT NULL,
  `Deadline` varchar(10) DEFAULT NULL,
  `Urgency ENUM` varchar(6) DEFAULT NULL,
  `Remarks Text` varchar(28) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `pp3`
--

INSERT INTO `pp3` (`AgendaID INT`, `Account ID`, `Task Text`, `Date`, `Deadline`, `Urgency ENUM`, `Remarks Text`) VALUES
(101, 'A12345229', 'Finalize budget', '08-30-2025', '09/03/2025', 'High', 'Need for Wed meeting'),
(102, 'A12345473', 'Send meeting invites', '08-30-2025', '09/01/2025', 'Medium', 'Use the updated email list'),
(103, 'K12152953', 'Review project timeline', '08-31-2025', '09/02/2025', 'Low', 'Review with PM only'),
(104, 'A12345460', 'Finalize budget', '08-29-2025', '09/03/2025', 'High', 'Needs director approval'),
(105, 'A12345434', 'Collect team feedback', '08-30-2025', '09/05/2025', 'Medium', 'Anonymous feedback encourage');

-- --------------------------------------------------------

--
-- Table structure for table `pp3_1`
--

CREATE TABLE `pp3_1` (
  `AgendaID INT` int(3) DEFAULT NULL,
  `Account ID` varchar(9) DEFAULT NULL,
  `Task Text` varchar(23) DEFAULT NULL,
  `Date` varchar(10) DEFAULT NULL,
  `Deadline` varchar(10) DEFAULT NULL,
  `Urgency ENUM` varchar(6) DEFAULT NULL,
  `Remarks Text` varchar(28) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `pp3_1`
--

INSERT INTO `pp3_1` (`AgendaID INT`, `Account ID`, `Task Text`, `Date`, `Deadline`, `Urgency ENUM`, `Remarks Text`) VALUES
(101, 'A12345229', 'Finalize budget', '08-30-2025', '09/03/2025', 'High', 'Need for Wed meeting'),
(102, 'A12345473', 'Send meeting invites', '08-30-2025', '09/01/2025', 'Medium', 'Use the updated email list'),
(103, 'K12152953', 'Review project timeline', '08-31-2025', '09/02/2025', 'Low', 'Review with PM only'),
(104, 'A12345460', 'Finalize budget', '08-29-2025', '09/03/2025', 'High', 'Needs director approval'),
(105, 'A12345434', 'Collect team feedback', '08-30-2025', '09/05/2025', 'Medium', 'Anonymous feedback encourage');

-- --------------------------------------------------------

--
-- Table structure for table `tblaccounts`
--

CREATE TABLE `tblaccounts` (
  `AccountID` int(11) NOT NULL,
  `PersonalID` int(11) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `DepartmentID` int(11) NOT NULL,
  `Position` varchar(50) NOT NULL,
  `QRCode` varchar(255) NOT NULL,
  `EmploymentStatus` enum('Active','Blocked','Pending','') NOT NULL,
  `CreatedAt` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `STATUS` enum('Present','Absent','Leave') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblleaveform`
--

CREATE TABLE `tblleaveform` (
  `LeaveId` int(11) NOT NULL,
  `AccountId` int(11) DEFAULT NULL,
  `ScheduledLeave` date NOT NULL,
  `ScheduledReturn` date DEFAULT NULL,
  `Reason` text DEFAULT NULL,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblpersonalinfo`
--

CREATE TABLE `tblpersonalinfo` (
  `PersonalID` int(11) NOT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `FName` varchar(100) DEFAULT NULL,
  `LName` varchar(100) DEFAULT NULL,
  `MName` varchar(100) DEFAULT NULL,
  `Age` int(3) DEFAULT NULL,
  `Contact` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  ADD PRIMARY KEY (`AccountID`),
  ADD UNIQUE KEY `QRCode` (`QRCode`);

--
-- Indexes for table `tblattendance`
--
ALTER TABLE `tblattendance`
  ADD PRIMARY KEY (`AttendanceID`);

--
-- Indexes for table `tblleaveform`
--
ALTER TABLE `tblleaveform`
  ADD PRIMARY KEY (`LeaveId`);

--
-- Indexes for table `tblpersonalinfo`
--
ALTER TABLE `tblpersonalinfo`
  ADD PRIMARY KEY (`PersonalID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblattendance`
--
ALTER TABLE `tblattendance`
  MODIFY `AttendanceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblpersonalinfo`
--
ALTER TABLE `tblpersonalinfo`
  MODIFY `PersonalID` int(11) NOT NULL AUTO_INCREMENT;
--
-- Database: `dbguerra`
--
CREATE DATABASE IF NOT EXISTS `dbguerra` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `dbguerra`;

-- --------------------------------------------------------

--
-- Table structure for table `tblcompsci`
--

CREATE TABLE `tblcompsci` (
  `StudentID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `MiddleName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `EnrollmentDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcompsci`
--

INSERT INTO `tblcompsci` (`StudentID`, `FirstName`, `MiddleName`, `LastName`, `Email`, `EnrollmentDate`) VALUES
(201, 'Ali', 'A.', 'Naim', 'ali.naim@university.com', '2024-08-15'),
(202, 'Rommel', 'L.', 'Dorin', 'rommel.dorin@university.com', '2024-08-16'),
(203, 'Abelardo', 'T.', 'Bucad', 'abelardo.bucad@university.com', '2024-08-17'),
(204, 'Mary Ellaine', 'R.', 'Cervantes', 'mary.cervantes@university.com', '2024-08-19'),
(205, 'Christian Michael', 'M.', 'Mansueto', 'christian.mansueto@university.com', '2024-08-21'),
(206, 'Rommel', 'T.', 'Garma', 'rommel.garma@university.com', '2024-08-22'),
(207, 'Nino', 'A', 'Narido', 'nino.narido@university.com', '2024-09-03');

-- --------------------------------------------------------

--
-- Table structure for table `tblcourse`
--

CREATE TABLE `tblcourse` (
  `CourseID` int(11) NOT NULL,
  `CourseName` varchar(100) NOT NULL,
  `CourseCode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcourse`
--

INSERT INTO `tblcourse` (`CourseID`, `CourseName`, `CourseCode`) VALUES
(1, 'Introduction to Computing', '001'),
(2, 'Networking 1', '002'),
(3, 'Web Applications', '003'),
(4, 'Computer Programming', '004'),
(5, 'Discrete Mathematics', '005');

-- --------------------------------------------------------

--
-- Table structure for table `tbldiploma`
--

CREATE TABLE `tbldiploma` (
  `StudentID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `MiddleName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `EnrollmentDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbldiploma`
--

INSERT INTO `tbldiploma` (`StudentID`, `FirstName`, `MiddleName`, `LastName`, `Email`, `EnrollmentDate`) VALUES
(301, 'Corazon', 'E.', 'Benosa', 'corazon.benosa@university.com', '2025-01-10'),
(302, 'Jarby', 'DC.', 'Gabriel', 'jarby.gabriel@university.com', '2025-01-10'),
(303, 'Genesis', 'S.', 'Lobarbio', 'genesis.lobarbio@university.com', '2025-01-11'),
(304, 'Emily', 'F.', 'Sicat', 'emily.sicat@university.com', '2025-01-12'),
(305, 'Jose', 'H.', 'Varona', 'jose.varona@university.com', '2025-01-15'),
(306, 'Kim', 'G.', 'Salvador', 'kim.salvador@university.com', '2025-01-15'),
(307, 'Brando', 'T.', 'Talaguit', 'brando.talaguit@university.com', '2025-01-16');

-- --------------------------------------------------------

--
-- Table structure for table `tblenrollment`
--

CREATE TABLE `tblenrollment` (
  `EnrollmentID` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL,
  `CourseID` int(11) NOT NULL,
  `Grade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblenrollment`
--

INSERT INTO `tblenrollment` (`EnrollmentID`, `StudentID`, `CourseID`, `Grade`) VALUES
(1, 101, 1, 88.50),
(2, 101, 4, 91.00),
(3, 102, 3, 94.00),
(4, 102, 2, 89.50),
(5, 103, 1, 78.00),
(6, 104, 3, 85.50),
(7, 105, 2, 92.00),
(8, 105, 1, 95.00),
(9, 106, 4, 81.50),
(10, 107, 3, 76.00),
(11, 108, 1, 98.00),
(12, 108, 2, 96.50),
(13, 201, 4, 95.50),
(14, 201, 5, 92.00),
(15, 202, 1, 88.00),
(16, 203, 4, 76.50),
(17, 203, 5, 80.00),
(18, 204, 3, 99.00),
(19, 204, 1, 97.50),
(20, 205, 4, NULL),
(21, 206, 5, 89.00),
(22, 207, 2, 91.00),
(23, 301, 1, 85.00),
(24, 302, 1, 92.50),
(25, 302, 3, 88.00),
(26, 303, 2, 79.00),
(27, 304, 1, NULL),
(28, 305, 3, 96.00),
(29, 306, 4, 93.00),
(30, 307, 1, 84.50),
(31, 307, 2, 87.00);

-- --------------------------------------------------------

--
-- Table structure for table `tblinfomationtechnology`
--

CREATE TABLE `tblinfomationtechnology` (
  `StudentID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `MiddleName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `EnrollmentDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblinfomationtechnology`
--

INSERT INTO `tblinfomationtechnology` (`StudentID`, `FirstName`, `MiddleName`, `LastName`, `Email`, `EnrollmentDate`) VALUES
(101, 'Percival', 'D.', 'Adao', 'percival.adao@university.com', '2024-08-15'),
(102, 'Lilibeth', 'H.', 'Arcalas', 'lilibeth.arcalas@university.com', '2024-08-16'),
(103, 'Raul', 'M', 'De Vera', 'raul.devera@university.com', '2024-08-18'),
(104, 'Bhai Nhuraisha', 'I.', 'Deplomo', 'bhai.deplomo@university.com', '2024-08-20'),
(105, 'Michael', 'C.', 'Olivo', 'michael.olivo@university.com', '2024-09-01'),
(106, 'Roel', 'C.', 'Traballo', 'roel.traballo@university.com', '2024-09-02'),
(107, 'Erwin', 'E.', 'Guerra', 'erwin.guerra@university.com', '2024-09-05'),
(108, 'Rita', 'J.', 'Famini', 'rita.famini@university.com', '2024-09-05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblcompsci`
--
ALTER TABLE `tblcompsci`
  ADD PRIMARY KEY (`StudentID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `tblcourse`
--
ALTER TABLE `tblcourse`
  ADD PRIMARY KEY (`CourseID`),
  ADD UNIQUE KEY `CourseCode` (`CourseCode`);

--
-- Indexes for table `tbldiploma`
--
ALTER TABLE `tbldiploma`
  ADD PRIMARY KEY (`StudentID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `tblenrollment`
--
ALTER TABLE `tblenrollment`
  ADD PRIMARY KEY (`EnrollmentID`),
  ADD KEY `CourseID` (`CourseID`);

--
-- Indexes for table `tblinfomationtechnology`
--
ALTER TABLE `tblinfomationtechnology`
  ADD PRIMARY KEY (`StudentID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblenrollment`
--
ALTER TABLE `tblenrollment`
  MODIFY `EnrollmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblenrollment`
--
ALTER TABLE `tblenrollment`
  ADD CONSTRAINT `tblenrollment_ibfk_1` FOREIGN KEY (`CourseID`) REFERENCES `tblcourse` (`CourseID`);
--
-- Database: `dbhavencrest`
--
CREATE DATABASE IF NOT EXISTS `dbhavencrest` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `dbhavencrest`;

-- --------------------------------------------------------

--
-- Table structure for table `tblaccounts`
--

CREATE TABLE `tblaccounts` (
  `AccountID` int(11) NOT NULL COMMENT 'Primary key for accounts',
  `Email` varchar(100) NOT NULL COMMENT 'Unique email for login',
  `Username` varchar(50) NOT NULL COMMENT 'Unique username for login',
  `Password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `Role` varchar(20) NOT NULL COMMENT 'User role (e.g., Patient, Doctor, Admin)',
  `PatientID` int(11) DEFAULT NULL COMMENT 'Foreign key linking to tblPatients if Role is Patient',
  `DoctorID` int(11) DEFAULT NULL COMMENT 'Foreign key linking to tblDoctors if Role is Doctor',
  `ProfilePicture` mediumblob NOT NULL,
  `PictureName` varchar(255) NOT NULL,
  `MimeType` varchar(100) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Timestamp when the account was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores user account details and credentials';

--
-- Dumping data for table `tblaccounts`
--

INSERT INTO `tblaccounts` (`AccountID`, `Email`, `Username`, `Password`, `Role`, `PatientID`, `DoctorID`, `ProfilePicture`, `PictureName`, `MimeType`, `CreatedAt`) VALUES
(58, 'acerilla.k12151773@umak.edu.ph', 'Alex123', 'Alex@123', 'Doctor', NULL, 14, '', '', '', '2025-05-13 16:17:35'),
(59, 'rsoberano.k12152953@umak.edu.ph', 'Chris123', 'Chris@123', 'Doctor', NULL, 15, '', '', '', '2025-05-30 03:41:25'),
(60, 'privera.k12149475@umak.edu.ph', 'Paul123', 'Paul@123', 'Doctor', NULL, 16, '', '', '', '2025-05-30 04:09:30'),
(61, 'jabunda.k12151048@umak.edu.ph', 'John123', 'John@123', 'Doctor', NULL, 17, '', '', '', '2025-05-13 16:17:35'),
(62, 'mbalanlay.k12149786@umak.edu.ph', 'Mathew123', 'Mathew@123', 'Doctor', NULL, 18, '', '', '', '2025-05-30 03:44:20'),
(63, 'aagana.a12344938@umak.edu.ph', 'Andrei123', 'Andrei_123', 'Patient', 6, NULL, '', '', '', '2025-05-30 04:09:08'),
(64, 'zarsenal.k12152421@umak.edu.ph', 'Zhann123', 'Admin@123', 'Admin', 0, 0, '', '', '', '2025-05-30 04:22:36'),
(70, 'michael@gmail.com', 'Kel12345', 'Kel@12345', 'Patient', 8, NULL, '', '', '', '2025-05-30 03:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `tblappointmenthistory`
--

CREATE TABLE `tblappointmenthistory` (
  `HistoryID` int(11) NOT NULL,
  `AppointmentID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DoctorID` int(11) NOT NULL,
  `ServiceType` varchar(100) DEFAULT NULL,
  `Service` varchar(150) DEFAULT NULL,
  `AppointmentDate` date NOT NULL,
  `AppointmentTime` time NOT NULL,
  `AppointmentStatus` varchar(20) NOT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `RescheduleDate` date DEFAULT NULL,
  `RescheduleTime` time DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Timestamp when the record was moved to history',
  `ArchiveReason` varchar(255) DEFAULT NULL COMMENT 'Reason for archiving (e.g., System completion, Manual cancellation)',
  `PaymentStatus` varchar(50) DEFAULT NULL COMMENT 'Payment status (e.g., Paid, Unpaid, Partial, Waived)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores historical and completed appointment records';

--
-- Dumping data for table `tblappointmenthistory`
--

INSERT INTO `tblappointmenthistory` (`HistoryID`, `AppointmentID`, `PatientID`, `DoctorID`, `ServiceType`, `Service`, `AppointmentDate`, `AppointmentTime`, `AppointmentStatus`, `Remarks`, `RescheduleDate`, `RescheduleTime`, `CreatedAt`, `ArchivedAt`, `ArchiveReason`, `PaymentStatus`) VALUES
(1, 118, 6, 16, 'Dental', 'Fluoride Treatment (Kids)', '2025-06-04', '13:00:00', 'Cancelled', NULL, NULL, NULL, '2025-05-30 04:09:24', '2025-05-30 04:09:36', 'Cancelled by Doctor', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblappointments`
--

CREATE TABLE `tblappointments` (
  `AppointmentID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL COMMENT 'Foreign key linking to the patient',
  `DoctorID` int(11) NOT NULL COMMENT 'Foreign key linking to the doctor',
  `ServiceType` varchar(100) DEFAULT NULL COMMENT 'Type of service (e.g., Consultation, Follow-up) - Consider linking to tblService instead',
  `Service` varchar(150) DEFAULT NULL COMMENT 'Specific service for the appointment - Consider linking to tblService instead',
  `AppointmentDate` date NOT NULL COMMENT 'Date of the appointment',
  `AppointmentTime` time NOT NULL COMMENT 'Time of the appointment',
  `AppointmentStatus` varchar(20) NOT NULL DEFAULT 'Scheduled' COMMENT 'e.g., Scheduled, Completed, Cancelled, NoShow',
  `Remarks` varchar(255) DEFAULT NULL,
  `RescheduleDate` date DEFAULT NULL,
  `RescheduleTime` time DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Timestamp when the appointment was booked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblappointments`
--

INSERT INTO `tblappointments` (`AppointmentID`, `PatientID`, `DoctorID`, `ServiceType`, `Service`, `AppointmentDate`, `AppointmentTime`, `AppointmentStatus`, `Remarks`, `RescheduleDate`, `RescheduleTime`, `CreatedAt`) VALUES
(117, 6, 16, 'Dental', 'Bunot (Tooth Extraction)', '2025-06-04', '09:00:00', 'Approved', NULL, NULL, NULL, '2025-05-30 04:01:24');

-- --------------------------------------------------------

--
-- Table structure for table `tblaudittrails`
--

CREATE TABLE `tblaudittrails` (
  `AuditLogID` int(11) NOT NULL,
  `AuditTimeStamp` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Timestamp of the action',
  `AccountID` int(11) DEFAULT NULL COMMENT 'Account that performed the action (NULL for system actions)',
  `ActionType` varchar(100) NOT NULL COMMENT 'Type of action (e.g., INSERT, UPDATE, DELETE, LOGIN, LOGOUT)',
  `TableName` varchar(100) DEFAULT NULL COMMENT 'Table affected by the action (if applicable)',
  `RecordID` int(11) DEFAULT NULL COMMENT 'ID of the record affected (if applicable)',
  `OldData` text DEFAULT NULL COMMENT 'Data before an UPDATE or DELETE (e.g., JSON string)',
  `NewData` text DEFAULT NULL COMMENT 'Data after an INSERT or UPDATE (e.g., JSON string)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Logs database and application actions';

--
-- Dumping data for table `tblaudittrails`
--

INSERT INTO `tblaudittrails` (`AuditLogID`, `AuditTimeStamp`, `AccountID`, `ActionType`, `TableName`, `RecordID`, `OldData`, `NewData`) VALUES
(1, '2025-05-19 22:33:40', 64, 'LOGIN_SUCCESS', NULL, 64, NULL, NULL),
(2, '2025-05-19 22:34:52', 64, 'LOGOUT', NULL, 64, NULL, NULL),
(3, '2025-05-19 22:34:57', 64, 'LOGIN_SUCCESS', NULL, 64, NULL, NULL),
(4, '2025-05-19 22:36:10', 64, 'LOGIN_SUCCESS', NULL, 64, NULL, NULL),
(5, '2025-05-19 22:37:03', 64, 'LOGIN_SUCCESS', NULL, 64, NULL, NULL),
(6, '2025-05-19 22:39:08', 64, 'LOGIN_SUCCESS', NULL, 64, NULL, NULL),
(7, '2025-05-19 22:39:38', 64, 'ADMIN_UPDATE_PROFILE', 'tblaccounts, tbldoctors', 58, '{\"AccountID\": 58, \"Age\": 59, \"ContactNumber\": \"09587575342\", \"DoctorID\": 14, \"Email\": \"acerilla.k12151773@umak.edu.ph\", \"FirstName\": \"Alexander\", \"Gender\": \"Male\", \"LastName\": \"Cerilla\", \"Role\": \"Doctor\", \"Specialty\": \"Medical\", \"SpecialtyID\": 1, \"Username\": \"Alexander\"}', '{\"AccountID\": 58, \"Age\": 60, \"ContactNumber\": \"09587575342\", \"Email\": \"acerilla.k12151773@umak.edu.ph\", \"FirstName\": \"Alexander\", \"Gender\": \"Male\", \"LastName\": \"Cerilla\", \"Role\": \"Doctor\", \"SpecialtyID\": 1, \"Username\": \"Alexander\"}'),
(8, '2025-05-19 22:39:50', 64, 'LOGOUT', NULL, 64, NULL, NULL),
(9, '2025-05-19 23:54:43', 60, 'LOGIN_SUCCESS', NULL, 60, NULL, NULL),
(10, '2025-05-20 00:04:33', 63, 'LOGIN_SUCCESS', NULL, 63, NULL, NULL),
(11, '2025-05-20 00:04:40', 63, 'LOGOUT', NULL, 63, NULL, NULL),
(12, '2025-05-20 00:04:47', 60, 'LOGIN_SUCCESS', NULL, 60, NULL, NULL),
(13, '2025-05-30 03:41:16', 63, 'LOGIN_SUCCESS', '0', 63, NULL, '0'),
(14, '2025-05-30 03:41:25', 59, 'LOGIN_SUCCESS', '0', 59, NULL, '0'),
(15, '2025-05-30 03:41:25', 15, 'VIEW_DOCTOR_DASHBOARD', NULL, NULL, NULL, '0'),
(16, '2025-05-30 03:41:32', 64, 'LOGIN_SUCCESS', '0', 64, NULL, '0'),
(17, '2025-05-30 03:44:20', 62, 'LOGIN_SUCCESS', '0', 62, NULL, '0'),
(18, '2025-05-30 03:44:20', 18, 'VIEW_DOCTOR_DASHBOARD', NULL, NULL, NULL, '0'),
(19, '2025-05-30 03:49:12', 18, 'VIEW_DOCTOR_DASHBOARD', NULL, NULL, NULL, '0'),
(20, '2025-05-30 03:49:42', 63, 'LOGIN_SUCCESS', '0', 63, NULL, '0'),
(21, '2025-05-30 03:49:51', 60, 'LOGIN_SUCCESS', '0', 60, NULL, '0'),
(22, '2025-05-30 03:49:51', 16, 'VIEW_DOCTOR_DASHBOARD', NULL, NULL, NULL, '0'),
(23, '2025-05-30 03:49:55', 16, 'VIEW_DOCTOR_DASHBOARD', NULL, NULL, NULL, '0'),
(24, '2025-05-30 03:59:47', 63, 'LOGIN_SUCCESS', '0', 63, NULL, '0'),
(25, '2025-05-30 04:06:27', 63, 'LOGIN_SUCCESS', '0', 63, NULL, '0'),
(26, '2025-05-30 04:06:52', 60, 'LOGIN_SUCCESS', '0', 60, NULL, '0'),
(27, '2025-05-30 04:06:52', 16, 'VIEW_DOCTOR_DASHBOARD', NULL, NULL, NULL, '0'),
(28, '2025-05-30 04:07:45', 16, 'APPROVE_APPOINTMENT', '0', 117, '{\"AppointmentStatus\":\"Scheduled\"}', '0'),
(29, '2025-05-30 04:07:50', 16, 'VIEW_DOCTOR_DASHBOARD', NULL, NULL, NULL, '0'),
(30, '2025-05-30 04:08:06', 16, 'VIEW_DOCTOR_APPOINTMENTS', '0', NULL, NULL, '0'),
(31, '2025-05-30 04:09:04', 63, 'FAILED_LOGIN', '0', 63, '{\"attempted_username\":\"Andrei123\",\"ip_address\":\"::1\"}', '0'),
(32, '2025-05-30 04:09:08', 63, 'LOGIN_SUCCESS', '0', 63, NULL, '0'),
(33, '2025-05-30 04:09:30', 60, 'LOGIN_SUCCESS', '0', 60, NULL, '0'),
(34, '2025-05-30 04:09:30', 16, 'VIEW_DOCTOR_DASHBOARD', NULL, NULL, NULL, '0'),
(35, '2025-05-30 04:09:36', 16, 'CANCEL_SCHEDULED_APPOINTMENT', '0', 118, '{\"AppointmentID\":118,\"PatientID\":6,\"DoctorID\":16,\"ServiceType\":\"Dental\",\"Service\":\"Fluoride Treatment (Kids)\",\"AppointmentDate\":\"2025-06-04\",\"AppointmentTime\":\"13:00:00\",\"Remarks\":null,\"AppointmentStatus\":\"Scheduled\"}', '0'),
(36, '2025-05-30 04:09:43', 16, 'VIEW_DOCTOR_APPOINTMENTS', '0', NULL, NULL, '0'),
(37, '2025-05-30 04:10:59', 64, 'LOGIN_SUCCESS', '0', 64, NULL, '0'),
(38, '2025-05-30 04:11:31', NULL, 'UPDATE_ACCOUNT', '0', 60, '{\"Email\":\"privera.k12149475@umak.edu.ph\",\"Role\":\"Doctor\",\"ProfilePicture\":\"\",\"PictureName\":\"\",\"MimeType\":\"\",\"FirstName\":\"Paul Angelo\",\"LastName\":\"Rivera\",\"Age\":35,\"Gender\":\"Male\",\"ContactNumber\":\"09608098700\",\"SpecialtyID\":3}', '0'),
(39, '2025-05-30 04:13:04', NULL, 'CREATE_ACCOUNT_FAILED', '0', NULL, '{\"AccountID\":\"60\",\"FirstName\":\"Ron Vincent\",\"LastName\":\"Suarez\",\"Gender\":\"Male\",\"Age\":\"21\",\"Contact\":\"09770049119\",\"Email\":\"michael@gmail.com\",\"Role\":\"Patient\",\"action\":\"Create\"}', '0'),
(40, '2025-05-30 04:13:39', NULL, 'CREATE_ACCOUNT', '0', 71, NULL, '0'),
(41, '2025-05-30 04:14:13', NULL, 'UPDATE_ACCOUNT', '0', 71, '{\"Email\":\"ronsuarez@gmail.com\",\"Role\":\"Patient\",\"ProfilePicture\":\"\",\"PictureName\":\"\",\"MimeType\":\"\",\"FirstName\":\"Ron Vincent\",\"LastName\":\"Suarez\",\"Age\":21,\"Gender\":\"Male\",\"ContactNumber\":\"09770049119\",\"SpecialtyID\":null}', '0'),
(42, '2025-05-30 04:14:27', NULL, 'DELETE_ACCOUNT', '0', 71, '{\"Email\":\"ronsuarez@gmail.com\",\"Username\":\"ronsuarez\",\"Role\":\"Patient\",\"FirstName\":\"Ron Vincent\",\"LastName\":\"Suarez\",\"Age\":21,\"Gender\":\"Male\",\"ContactNumber\":\"09770049119\",\"SpecialtyID\":null}', NULL),
(43, '2025-05-30 04:22:36', 64, 'LOGIN_SUCCESS', '0', 64, NULL, '0');

-- --------------------------------------------------------

--
-- Table structure for table `tbldoctors`
--

CREATE TABLE `tbldoctors` (
  `DoctorID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL COMMENT 'Foreign key linking to the user account',
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Age` int(11) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `ContactNumber` varchar(15) DEFAULT NULL,
  `SpecialtyID` int(11) DEFAULT NULL COMMENT 'Optional: Link to a primary service offered by the doctor',
  `AvailableDay` varchar(100) DEFAULT NULL COMMENT 'Doctor availability (e.g., "Mon,Wed,Fri" or JSON)',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Timestamp when the doctor record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbldoctors`
--

INSERT INTO `tbldoctors` (`DoctorID`, `AccountID`, `FirstName`, `LastName`, `Age`, `Gender`, `ContactNumber`, `SpecialtyID`, `AvailableDay`, `CreatedAt`) VALUES
(14, 58, 'Alexander', 'Cerilla', 60, 'Male', '09587575342', 1, 'Monday', '2025-05-13 16:17:35'),
(15, 59, 'Ralph Christopher', 'Soberano', 45, 'Male', '09074164939', 2, 'Tuesday', '2025-05-13 16:17:35'),
(16, 60, 'Paul', 'Rivera', 20, 'Male', '09608098700', 3, 'Wednesday', '2025-05-13 16:17:35'),
(17, 61, 'John Kennidy', 'Bergonia', 55, 'Female', '09817998714', 4, 'Thursday', '2025-05-13 16:17:35'),
(18, 62, 'Mathew Angelo', 'Balanlay', 26, 'Male', '09265697502', 5, 'Friday', '2025-05-13 16:17:35');

-- --------------------------------------------------------

--
-- Table structure for table `tblpatients`
--

CREATE TABLE `tblpatients` (
  `PatientID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL COMMENT 'Foreign key linking to the user account',
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Age` int(11) DEFAULT NULL COMMENT 'Patient age, consider storing DateOfBirth instead for accuracy',
  `Gender` varchar(10) DEFAULT NULL COMMENT 'e.g., Male, Female, Other',
  `ContactNumber` varchar(15) DEFAULT NULL COMMENT 'Patient contact number (e.g., 09760628712)',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Timestamp when the patient record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpatients`
--

INSERT INTO `tblpatients` (`PatientID`, `AccountID`, `FirstName`, `LastName`, `Age`, `Gender`, `ContactNumber`, `CreatedAt`) VALUES
(6, 63, 'Andrei', 'Agana', 20, 'Male', '09111111111', '2025-05-13 16:24:02'),
(7, 69, 'Alexander', 'Cerilla', 25, 'Male', '09999999999', '2025-05-14 05:52:31'),
(8, 70, 'Mic', 'Valdez', 18, 'Male', NULL, '2025-05-30 03:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `tblservice`
--

CREATE TABLE `tblservice` (
  `ServiceID` int(11) NOT NULL,
  `SpecialtyID` int(11) NOT NULL,
  `ServiceName` varchar(100) NOT NULL COMMENT 'ex. Check-up, X-Ray, Injection',
  `ServicePrice` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblservice`
--

INSERT INTO `tblservice` (`ServiceID`, `SpecialtyID`, `ServiceName`, `ServicePrice`) VALUES
(1, 1, 'Check-Up', '600'),
(2, 1, 'Circumcision', '1500'),
(3, 1, 'X-Ray', '600'),
(4, 1, 'Vaccination Service', '200'),
(5, 1, 'Wound Dressing', '250'),
(6, 1, 'Minor Surgery', '2000'),
(7, 1, 'Ear Cleaning', '200'),
(8, 1, 'Asthma Treatment (Nebulization)', '300'),
(9, 2, 'Hematology Test', '500'),
(10, 2, 'Urinalysis', '250'),
(11, 2, 'Blood Sugar Test', '300'),
(12, 2, 'Fecalysis', '100'),
(13, 2, 'ECG (Electrocardiogram)', '500'),
(14, 3, 'Dental Check-Up', '500'),
(15, 3, 'Bunot (Tooth Extraction)', '500'),
(16, 3, 'Pasta (Tooth Filling)', '800'),
(17, 3, 'Linis (Dental Cleaning)', '700'),
(18, 3, 'Fluoride Treatment (Kids)', '500'),
(19, 3, 'Full Dentures (Set)', '20000'),
(20, 3, 'Prescription Braces', '30000'),
(21, 4, 'Assessment', '700'),
(22, 4, 'Therapeutic Ultrasound', '500'),
(23, 4, 'Electrical Stimulation Therapy', '600'),
(24, 4, 'Hot/Cold Compress Therapy', '250'),
(25, 4, 'Strength and Mobility Training', '800'),
(26, 5, 'Eye Check-Up', '400'),
(27, 5, 'Visual Test', '300'),
(28, 5, 'Prescription Glasses (Basic)', '1200'),
(29, 5, 'Contact Lens Fitting', '1000'),
(30, 5, 'Eye Pressure Test (Tonometry)', '600');

-- --------------------------------------------------------

--
-- Table structure for table `tblspecialty`
--

CREATE TABLE `tblspecialty` (
  `SpecialtyID` int(11) NOT NULL,
  `Specialty` varchar(100) NOT NULL COMMENT 'ex. Surgeon, Dentist, Medical'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblspecialty`
--

INSERT INTO `tblspecialty` (`SpecialtyID`, `Specialty`) VALUES
(3, 'Dental'),
(2, 'Laboratory'),
(1, 'Medical'),
(5, 'Optometry'),
(4, 'Physical Therapy');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  ADD PRIMARY KEY (`AccountID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_patientid` (`PatientID`),
  ADD KEY `idx_doctorid` (`DoctorID`);

--
-- Indexes for table `tblappointmenthistory`
--
ALTER TABLE `tblappointmenthistory`
  ADD PRIMARY KEY (`HistoryID`);

--
-- Indexes for table `tblappointments`
--
ALTER TABLE `tblappointments`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD KEY `idx_app_patientid` (`PatientID`),
  ADD KEY `idx_app_doctorid` (`DoctorID`),
  ADD KEY `idx_app_datetime` (`AppointmentDate`,`AppointmentTime`);

--
-- Indexes for table `tblaudittrails`
--
ALTER TABLE `tblaudittrails`
  ADD PRIMARY KEY (`AuditLogID`);

--
-- Indexes for table `tbldoctors`
--
ALTER TABLE `tbldoctors`
  ADD PRIMARY KEY (`DoctorID`),
  ADD UNIQUE KEY `AccountID` (`AccountID`),
  ADD KEY `SpecialtyID` (`SpecialtyID`);

--
-- Indexes for table `tblpatients`
--
ALTER TABLE `tblpatients`
  ADD PRIMARY KEY (`PatientID`),
  ADD UNIQUE KEY `AccountID` (`AccountID`);

--
-- Indexes for table `tblservice`
--
ALTER TABLE `tblservice`
  ADD PRIMARY KEY (`ServiceID`),
  ADD KEY `SpecialtyID` (`SpecialtyID`);

--
-- Indexes for table `tblspecialty`
--
ALTER TABLE `tblspecialty`
  ADD PRIMARY KEY (`SpecialtyID`),
  ADD UNIQUE KEY `Specialty` (`Specialty`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  MODIFY `AccountID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key for accounts', AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `tblappointmenthistory`
--
ALTER TABLE `tblappointmenthistory`
  MODIFY `HistoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblappointments`
--
ALTER TABLE `tblappointments`
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `tblaudittrails`
--
ALTER TABLE `tblaudittrails`
  MODIFY `AuditLogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `tbldoctors`
--
ALTER TABLE `tbldoctors`
  MODIFY `DoctorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tblpatients`
--
ALTER TABLE `tblpatients`
  MODIFY `PatientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tblservice`
--
ALTER TABLE `tblservice`
  MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tblspecialty`
--
ALTER TABLE `tblspecialty`
  MODIFY `SpecialtyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblservice`
--
ALTER TABLE `tblservice`
  ADD CONSTRAINT `tblservice_ibfk_1` FOREIGN KEY (`SpecialtyID`) REFERENCES `tblspecialty` (`SpecialtyID`);
--
-- Database: `dbpharmascan`
--
CREATE DATABASE IF NOT EXISTS `dbpharmascan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `dbpharmascan`;

-- --------------------------------------------------------

--
-- Table structure for table `tblaccountarchive`
--

CREATE TABLE `tblaccountarchive` (
  `ArchivedAccountID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `Username` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT NULL,
  `ArchivedBy` int(11) DEFAULT NULL,
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblaccounts`
--

INSERT INTO `tblaccounts` (`AccountID`, `EmployeeID`, `Username`, `Password`, `Email`, `ICCode`, `CreatedAt`) VALUES
(1, 1, 'zarcangel', '$2y$10$LtD6vhOe7LIYp8e2aKJUl.wckzPOfHSw5UIEzozfuwP2aVeeiH8Ou', 'z.arcangel@pharma.org', '1189972885', '2025-09-25 17:59:48'),
(2, 2, 'rgumatay', 'rgumatay', 'r.gumatay@pharma.org', '1190137861', '2025-09-25 17:59:48'),
(3, 3, 'carsenal', '$2y$10$8h/pJGSDOTS6v7RVKl4PYOQRHsIeujNRhpf.RBuzAXnOq89Y6qTS2', 'c.arsenal@pharma.org', '1188328581', '2025-09-25 17:59:48'),
(4, 4, 'larsenal', '$2y$10$2ItFprkoReiIuMzL8huEvOOVazCC9PsRDWaLn.L7cdtlPl4X18vO2', 'l.arsenal@pharma.org', '1189916485', '2025-09-25 17:59:48'),
(5, 5, 'rpanes', '$2y$10$hDoJFnYDu9QBFCfFximHWuJe98ZGw8gXOeW1WNjrU3R0sORG64ZWG', 'r.panes@pharma.org', '', '2025-09-25 17:59:48'),
(6, 6, 'mlabradores', 'mlabradores', 'm.labradores@pharma.org', '1226357045', '2025-09-25 17:59:48'),
(7, 7, 'Zhann123', '$2y$10$6rlsRlJZECxEbYD3Tr2CEOihfdkp8jLLULIX2vn3hbrGTkXmG9RL.', 'zhannthony1108@gmail.com', NULL, '2025-09-26 11:02:51');

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
(37, 3, 'Schedule the weekly team meeting', '2025-10-18', '2025-10-20 10:17:54', 'Medium', 'Not Started', 'Aim for Friday afternoon.'),
(42, 1, 'Organize the dispensary storage shelves', '2025-10-18', '2025-10-25 10:17:54', 'Low', 'Not Started', NULL),
(43, 2, 'Calibrate the digital weighing scales', '2025-10-18', '2025-11-18 10:17:54', 'Medium', 'Not Started', 'Annual requirement.'),
(44, 3, 'Review and approve vacation requests', '2025-10-18', '2025-10-21 10:17:54', 'High', 'Pending', NULL),
(46, 5, 'Complete the mandatory online compliance training', '2025-10-18', '2025-10-30 10:17:54', 'High', 'Not Started', 'Module 3 is now available.'),
(47, 6, 'Check stock levels for over-the-counter allergy medication', '2025-10-18', '2025-10-19 00:00:00', 'Medium', 'Pending', 'Pollen season is starting.'),
(49, 1, 'Generate weekly sales performance summary', '2025-10-18', '2025-10-19 10:17:54', 'High', 'Not Started', NULL),
(50, 3, 'asd', '2025-10-18', '2025-10-04 00:00:00', 'High', 'Pending', ''),
(51, 4, 'asd', '2025-10-18', '2025-10-24 00:00:00', 'Medium', 'Not Started', '');

-- --------------------------------------------------------

--
-- Table structure for table `tblagendaarchive`
--

CREATE TABLE `tblagendaarchive` (
  `ArchivedAgendaID` int(11) NOT NULL,
  `AgendaID` int(11) NOT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `Task` text DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Deadline` datetime DEFAULT NULL,
  `Priority` enum('Low','Medium','High','Critical') DEFAULT NULL,
  `Status` enum('Not Started','Pending','Completed','Overdue') DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `ArchivedBy` int(11) DEFAULT NULL,
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblagendaarchive`
--

INSERT INTO `tblagendaarchive` (`ArchivedAgendaID`, `AgendaID`, `AccountID`, `Task`, `Date`, `Deadline`, `Priority`, `Status`, `Remarks`, `ArchivedBy`, `ArchivedAt`) VALUES
(16, 35, 1, 'Finalize Q3 financial report', '2025-10-18', '2025-10-23 10:17:54', 'High', 'Pending', 'Waiting for final sales figures.', 1, '2025-10-18 02:19:12'),
(17, 36, 2, 'Restock inventory for antibiotics', '2025-10-18', '2025-10-21 10:17:54', 'High', 'Not Started', NULL, 1, '2025-10-18 02:19:12'),
(18, 38, 4, 'Follow up with supplier about delayed shipment', '2025-10-18', '2025-10-19 10:17:54', 'Critical', 'Pending', NULL, 1, '2025-10-18 02:19:12'),
(19, 39, 5, 'Update the employee emergency contact list', '2025-10-18', '2025-10-25 10:17:54', 'Low', 'Not Started', NULL, 1, '2025-10-18 02:19:12'),
(20, 40, 6, 'Prepare presentation for the monthly review', '2025-10-18', '2025-10-28 10:17:54', 'Medium', 'Not Started', NULL, 1, '2025-10-18 02:19:12'),
(21, 41, 7, 'Process all pending invoices from last week', '2025-10-18', '2025-10-22 10:17:54', 'Medium', 'Not Started', 'Cross-reference with purchase orders.', 1, '2025-10-18 02:19:12'),
(22, 45, 4, 'Draft the new work shift schedule for next month', '2025-10-18', '2025-11-01 10:17:54', 'Medium', 'Not Started', NULL, 1, '2025-10-18 02:19:12'),
(23, 48, 7, 'Call IT support regarding the slow network connection', '2025-10-18', '2025-10-18 16:17:54', 'Medium', 'Pending', NULL, 1, '2025-10-18 02:19:12');

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
(52, 1, '2025-10-21 18:14:27', '2025-10-21 18:14:53', '2025-10-21', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblattendancearchive`
--

CREATE TABLE `tblattendancearchive` (
  `ArchivedAttendanceID` int(11) NOT NULL,
  `AttendanceID` int(11) NOT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `TimeIn` datetime DEFAULT NULL,
  `TimeOut` datetime DEFAULT NULL,
  `AttendanceDate` date DEFAULT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `ArchiveDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `EmploymentStatus` enum('Active','On Leave','On Vacation','Terminated') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblemployees`
--

INSERT INTO `tblemployees` (`EmployeeID`, `PersonalID`, `DepartmentID`, `Position`, `EmploymentStatus`) VALUES
(1, 1, 1, 'IT Administrator', 'Active'),
(2, 2, 2, 'Head Pharmacist', 'Active'),
(3, 3, 2, 'Pharmacist', 'Active'),
(4, 4, 2, 'Pharmacy Technician', 'Active'),
(5, 5, 3, 'HR Manager', 'Active'),
(6, 6, 2, 'Pharmacy Assistant', 'On Leave'),
(7, 7, 1, 'IT Support Staff', 'Active');

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
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblleaveform`
--

INSERT INTO `tblleaveform` (`LeaveID`, `AccountID`, `ScheduledLeave`, `ScheduledReturn`, `Reason`, `LeaveStatus`, `Remarks`) VALUES
(1, 1, '0007-07-25', '0007-08-25', 'VACATION', '', 'APPROVED');

-- --------------------------------------------------------

--
-- Table structure for table `tblleaveformarchive`
--

CREATE TABLE `tblleaveformarchive` (
  `ArchivedLeaveID` int(11) NOT NULL,
  `LeaveID` int(11) NOT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `ScheduledLeave` date DEFAULT NULL,
  `ScheduledReturn` date DEFAULT NULL,
  `Reason` text DEFAULT NULL,
  `LeaveStatus` varchar(50) DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `ArchivedBy` int(11) DEFAULT NULL,
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblpersonalinfo`
--

CREATE TABLE `tblpersonalinfo` (
  `PersonalID` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `Age` int(10) DEFAULT NULL,
  `Gender` varchar(50) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpersonalinfo`
--

INSERT INTO `tblpersonalinfo` (`PersonalID`, `FirstName`, `LastName`, `MiddleName`, `Age`, `Gender`, `ContactNumber`) VALUES
(1, 'Zhann', 'Arcangel', 'Llyn Anthony', 28, 'Male', '09171234567'),
(2, 'Ralph', 'Gumatay', 'Christpher', 34, 'Male', '09228765432'),
(3, 'Christian', 'Arsenal', 'Dave', 25, 'Male', '09981122334'),
(4, 'Luke', 'Arsenal', 'July', 22, 'Male', '09457890123'),
(5, 'Red', 'Panes', NULL, 29, 'Female', '09334455667'),
(6, 'Mark', 'Labradores', 'John', 31, 'Male', '09182345678'),
(7, 'as', 'dasd', 'asd', 30, NULL, '3213123123');

-- --------------------------------------------------------

--
-- Table structure for table `tblpersonalinfoarchive`
--

CREATE TABLE `tblpersonalinfoarchive` (
  `ArchivedPersonalID` int(11) NOT NULL,
  `PersonalID` int(11) NOT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `Age` int(10) DEFAULT NULL,
  `Gender` varchar(50) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `ArchivedBy` int(11) DEFAULT NULL,
  `ArchivedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`ArchivedAgendaID`);

--
-- Indexes for table `tblattendance`
--
ALTER TABLE `tblattendance`
  ADD PRIMARY KEY (`AttendanceID`);

--
-- Indexes for table `tblattendancearchive`
--
ALTER TABLE `tblattendancearchive`
  ADD PRIMARY KEY (`ArchivedAttendanceID`);

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
-- Indexes for table `tblpersonalinfoarchive`
--
ALTER TABLE `tblpersonalinfoarchive`
  ADD PRIMARY KEY (`ArchivedPersonalID`);

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
  MODIFY `AccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblagenda`
--
ALTER TABLE `tblagenda`
  MODIFY `AgendaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `tblagendaarchive`
--
ALTER TABLE `tblagendaarchive`
  MODIFY `ArchivedAgendaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tblattendance`
--
ALTER TABLE `tblattendance`
  MODIFY `AttendanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `tblattendancearchive`
--
ALTER TABLE `tblattendancearchive`
  MODIFY `ArchivedAttendanceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldepartment`
--
ALTER TABLE `tbldepartment`
  MODIFY `DepartmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblemployees`
--
ALTER TABLE `tblemployees`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblleaveform`
--
ALTER TABLE `tblleaveform`
  MODIFY `LeaveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblleaveformarchive`
--
ALTER TABLE `tblleaveformarchive`
  MODIFY `ArchivedLeaveID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblpersonalinfo`
--
ALTER TABLE `tblpersonalinfo`
  MODIFY `PersonalID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblpersonalinfoarchive`
--
ALTER TABLE `tblpersonalinfoarchive`
  MODIFY `ArchivedPersonalID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblemployees`
--
ALTER TABLE `tblemployees`
  ADD CONSTRAINT `fk_employees_personalinfo` FOREIGN KEY (`PersonalID`) REFERENCES `tblpersonalinfo` (`PersonalID`) ON DELETE CASCADE;
--
-- Database: `infotech2025`
--
CREATE DATABASE IF NOT EXISTS `infotech2025` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `infotech2025`;

-- --------------------------------------------------------

--
-- Table structure for table `infotech_professors`
--

CREATE TABLE `infotech_professors` (
  `ProfessorID` int(11) NOT NULL,
  `ProfFirstName` varchar(50) DEFAULT NULL,
  `ProfLastName` varchar(50) DEFAULT NULL,
  `Department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `infotech_professors`
--

INSERT INTO `infotech_professors` (`ProfessorID`, `ProfFirstName`, `ProfLastName`, `Department`) VALUES
(1, 'Santiago', 'Mendoza', 'Diploma'),
(2, 'Victoria Isabela', 'Aquino', 'Computer Science'),
(3, 'Luna', 'Bonifacio', 'Diploma'),
(4, 'Regina', 'Salazar', 'Information Technology'),
(5, 'Mayari', 'Garcia', 'Information Technology');

-- --------------------------------------------------------

--
-- Table structure for table `infotech_students`
--

CREATE TABLE `infotech_students` (
  `StudentID` int(11) NOT NULL,
  `StudLastName` varchar(50) DEFAULT NULL,
  `StudFirstName` varchar(50) DEFAULT NULL,
  `Major` varchar(50) DEFAULT NULL,
  `SubjectID` int(11) DEFAULT NULL,
  `EnrollmentStatus` varchar(20) DEFAULT NULL,
  `EnrollmentDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `infotech_students`
--

INSERT INTO `infotech_students` (`StudentID`, `StudLastName`, `StudFirstName`, `Major`, `SubjectID`, `EnrollmentStatus`, `EnrollmentDate`) VALUES
(1, 'Santos', 'Miguel Garcia', 'IT', 5, 'Enrolled', '2023-02-10'),
(2, 'Bautista', 'Angela Mae', 'ComSci', 3, 'Enrolled', '2022-08-30'),
(3, 'Mendoza', 'Juan', 'Diploma', NULL, 'Not yet Enrolled', '2022-09-01'),
(4, 'Reyes', 'Jasmine Anne', 'IT', 2, 'Enrolled', '2023-01-20'),
(6, 'David', 'Christian James', 'ComSci', 1, 'Enrolled', '2023-08-15'),
(7, 'Gonzales', 'Sofia Louise', 'IT', 2, 'Enrolled', '2022-01-18'),
(8, 'Ramos', 'Mark Anthony', 'ComSci', 3, 'Enrolled', '2022-08-28'),
(9, 'Villanueva', 'Daniel Salazar', 'Diploma', NULL, 'Not yet Enrolled', '2023-09-05'),
(10, 'Santos', 'Althea Marie', 'Diploma', 4, 'Enrolled', '2023-01-25'),
(11, 'Garcia', 'Jose Antonio', 'IT', 5, 'Enrolled', '2022-08-29');

-- --------------------------------------------------------

--
-- Table structure for table `infotech_subjects`
--

CREATE TABLE `infotech_subjects` (
  `SubjectID` int(11) NOT NULL,
  `SubjectCode` varchar(10) DEFAULT NULL,
  `SubjectTitle` varchar(100) DEFAULT NULL,
  `ProfessorID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `infotech_subjects`
--

INSERT INTO `infotech_subjects` (`SubjectID`, `SubjectCode`, `SubjectTitle`, `ProfessorID`) VALUES
(1, 'IT101', 'Introduction to Computing', 1),
(2, 'CS203', 'Data Structures and Algorithms', 2),
(3, 'DIP301', 'Networking 1', 3),
(4, 'IT103', 'Computer Programming 3', 4),
(5, 'CS205', 'Object Oriented Programming', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `infotech_professors`
--
ALTER TABLE `infotech_professors`
  ADD PRIMARY KEY (`ProfessorID`);

--
-- Indexes for table `infotech_students`
--
ALTER TABLE `infotech_students`
  ADD PRIMARY KEY (`StudentID`);

--
-- Indexes for table `infotech_subjects`
--
ALTER TABLE `infotech_subjects`
  ADD PRIMARY KEY (`SubjectID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `infotech_professors`
--
ALTER TABLE `infotech_professors`
  MODIFY `ProfessorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `infotech_students`
--
ALTER TABLE `infotech_students`
  MODIFY `StudentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `infotech_subjects`
--
ALTER TABLE `infotech_subjects`
  MODIFY `SubjectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Database: `leave_system`
--
CREATE DATABASE IF NOT EXISTS `leave_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `leave_system`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$dC1YcZADDW9u4LRCkSqB/OLz4ZdnWXPfT.QyafdJFioKN6EF.uBk.', '2025-09-26 14:02:09');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actioned_by` varchar(50) DEFAULT NULL,
  `actioned_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

--
-- Dumping data for table `pma__designer_settings`
--

INSERT INTO `pma__designer_settings` (`username`, `settings_data`) VALUES
('root', '{\"angular_direct\":\"direct\",\"snap_to_grid\":\"off\",\"relation_lines\":\"true\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"dbpharmascan\",\"table\":\"tblattendance\"},{\"db\":\"dbpharmascan\",\"table\":\"tblleaveform\"},{\"db\":\"dbpharmascan\",\"table\":\"tblaccounts\"},{\"db\":\"dbpharmascan\",\"table\":\"tblpersonalinfo\"},{\"db\":\"dbpharmascan\",\"table\":\"tblagenda\"},{\"db\":\"dbpharmascan\",\"table\":\"tblagendaarchive\"},{\"db\":\"dbpharmascan\",\"table\":\"tbldepartment\"},{\"db\":\"dbpharmascan\",\"table\":\"tblaccountarchive\"},{\"db\":\"dbhavencrest\",\"table\":\"tbldoctors\"},{\"db\":\"dbhavencrest\",\"table\":\"tblaccounts\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

--
-- Dumping data for table `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'dbhavencrest', 'tblappointments', '{\"sorted_col\":\"`DoctorID` DESC\",\"CREATE_TIME\":\"2025-04-30 00:38:12\"}', '2025-05-30 03:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-08-23 03:27:31', '{\"Console\\/Mode\":\"collapse\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
