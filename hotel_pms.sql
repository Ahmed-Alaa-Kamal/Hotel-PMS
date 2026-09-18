-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 05:02 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_pms`
--
create database hotel_pms;
use hotel_pms;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(64) DEFAULT NULL,
  `entity` varchar(64) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'abdalla_3110', 'abood12244556@gmail.com', 'س', 'نتالؤرتنم', 1, '2026-05-09 05:25:55');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `guest_id`, `reservation_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 5, 'جامد\r\n', '2026-05-09 05:25:06');

-- --------------------------------------------------------

--
-- Table structure for table `folios`
--

CREATE TABLE `folios` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `status` enum('open','closed','disputed') DEFAULT 'open',
  `total_charges` decimal(10,2) DEFAULT 0.00,
  `total_payments` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `folios`
--

INSERT INTO `folios` (`id`, `reservation_id`, `guest_id`, `status`, `total_charges`, `total_payments`, `balance`, `created_at`, `closed_at`) VALUES
(1, 1, 1, 'closed', 2052.00, 0.00, 2052.00, '2026-05-09 04:51:01', '2026-05-09 05:01:01'),
(2, 2, 1, 'closed', 10270.00, 10270.00, 0.00, '2026-05-09 04:57:34', '2026-05-09 05:00:21'),
(3, 3, 2, 'closed', 912.00, 912.00, 0.00, '2026-05-09 05:07:33', '2026-05-09 05:08:20'),
(4, 4, 1, 'closed', 3830.40, 638.40, 3830.40, '2026-05-09 05:15:57', '2026-05-09 05:16:34'),
(5, 5, 1, 'closed', 1254.00, 1254.00, 0.00, '2026-05-09 05:23:26', '2026-05-09 05:23:43'),
(6, 6, 3, 'open', 1846.80, 369.36, 1477.44, '2026-05-09 05:52:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `folio_charges`
--

CREATE TABLE `folio_charges` (
  `id` int(11) NOT NULL,
  `folio_id` int(11) NOT NULL,
  `charge_type` varchar(32) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `qty` int(11) DEFAULT 1,
  `posted_by` int(11) DEFAULT NULL,
  `posted_at` datetime DEFAULT current_timestamp(),
  `voided` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `folio_charges`
--

INSERT INTO `folio_charges` (`id`, `folio_id`, `charge_type`, `description`, `amount`, `qty`, `posted_by`, `posted_at`, `voided`) VALUES
(1, 1, 'room', 'Room charge for 1 night(s)', 1500.00, 1, NULL, '2026-05-09 04:51:01', 0),
(2, 1, 'tax', 'VAT (14% on room charges)', 210.00, 1, NULL, '2026-05-09 04:51:01', 0),
(3, 2, 'room', 'Room charge for 1 night(s)', 5500.00, 1, NULL, '2026-05-09 04:57:34', 0),
(4, 2, 'tax', 'VAT (14% on room charges)', 770.00, 1, NULL, '2026-05-09 04:57:34', 0),
(5, 2, 'service', 'سبا', 4000.00, 1, 2, '2026-05-09 04:58:20', 0),
(6, 1, 'penalty', 'No‑Show Penalty', 342.00, 1, NULL, '2026-05-09 05:01:01', 0),
(7, 3, 'room', 'Room charge for 1 night(s)', 800.00, 1, NULL, '2026-05-09 05:07:33', 0),
(8, 3, 'tax', 'VAT (14% on room charges)', 112.00, 1, NULL, '2026-05-09 05:07:33', 0),
(9, 4, 'room', 'Room charge for 1 night(s)', 2800.00, 1, NULL, '2026-05-09 05:15:57', 0),
(10, 4, 'tax', 'VAT (14% on room charges)', 392.00, 1, NULL, '2026-05-09 05:15:57', 0),
(11, 4, 'penalty', 'No‑Show Penalty (forfeited Pre‑Auth)', 638.40, 1, NULL, '2026-05-09 05:16:34', 0),
(12, 5, 'room', 'Room charge for 1 night(s)', 5500.00, 1, NULL, '2026-05-09 05:23:26', 0),
(13, 5, 'tax', 'VAT (14% on room charges)', 770.00, 1, NULL, '2026-05-09 05:23:26', 0),
(14, 5, 'discount', 'Room & Tax Reversal (No‑Show)', -6270.00, 1, NULL, '2026-05-09 05:23:43', 0),
(15, 5, 'penalty', 'No‑Show Penalty (forfeited Pre‑Auth)', 1254.00, 1, NULL, '2026-05-09 05:23:43', 0),
(16, 6, 'room', 'Room charge for 1 night(s)', 1620.00, 1, NULL, '2026-05-09 05:52:15', 0),
(17, 6, 'tax', 'VAT (14% on room charges)', 226.80, 1, NULL, '2026-05-09 05:52:15', 0);

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(128) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `doc_type` varchar(32) DEFAULT NULL,
  `doc_number` varchar(64) DEFAULT NULL,
  `nationality` varchar(64) DEFAULT NULL,
  `vip` tinyint(1) DEFAULT 0,
  `loyalty_points` int(11) DEFAULT 0,
  `loyalty_tier` enum('bronze','silver','gold','platinum') DEFAULT 'bronze',
  `preferences` text DEFAULT NULL,
  `blacklisted` tinyint(1) DEFAULT 0,
  `ban_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `user_id`, `full_name`, `email`, `phone`, `doc_type`, `doc_number`, `nationality`, `vip`, `loyalty_points`, `loyalty_tier`, `preferences`, `blacklisted`, `ban_reason`, `created_at`) VALUES
(1, 5, 'abdalla sayed mohamed', 'a.b.s.m12244556@gmail.com', '01022674536', 'passport', '2222222222', 'مصري', 0, 12162, 'platinum', 'بحب الشوكولاته', 0, '', '2026-05-09 03:30:16'),
(2, 6, 'عبدالله سيد', 'abdo@gmail.com', '01000000001', '', '', '', 0, 911, 'silver', NULL, 0, NULL, '2026-05-09 03:51:53'),
(3, 7, 'Ahmed ', 'melojo4743@4heats.com', '56758697', NULL, NULL, NULL, 0, 369, 'bronze', NULL, 0, NULL, '2026-05-09 03:55:44');

-- --------------------------------------------------------

--
-- Table structure for table `hk_tasks`
--

CREATE TABLE `hk_tasks` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `task_type` enum('clean','inspect','turn_down','deep_clean') DEFAULT 'clean',
  `assigned_to` int(11) DEFAULT NULL,
  `status` enum('pending','assigned','in_progress','done','inspected','rejected') DEFAULT 'pending',
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `inspected_at` datetime DEFAULT NULL,
  `inspected_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hk_tasks`
--

INSERT INTO `hk_tasks` (`id`, `room_id`, `task_type`, `assigned_to`, `status`, `priority`, `notes`, `created_at`, `started_at`, `completed_at`, `inspected_at`, `inspected_by`) VALUES
(1, 1, 'clean', 4, 'inspected', 'normal', '', '2026-05-09 04:28:01', '2026-05-09 04:28:08', '2026-05-09 04:28:36', '2026-05-09 04:28:46', 3),
(2, 2, 'clean', 4, 'inspected', 'high', '', '2026-05-09 04:38:28', '2026-05-09 04:39:23', '2026-05-09 04:39:26', '2026-05-09 04:39:43', 3),
(3, 9, 'clean', NULL, 'pending', 'normal', NULL, '2026-05-09 05:00:21', NULL, NULL, NULL, NULL),
(4, 1, 'clean', NULL, 'pending', 'normal', NULL, '2026-05-09 05:08:20', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lost_found`
--

CREATE TABLE `lost_found` (
  `id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `status` enum('held','returned','disposed') DEFAULT 'held',
  `found_by` int(11) DEFAULT NULL,
  `returned_to` varchar(120) DEFAULT NULL,
  `found_at` datetime DEFAULT current_timestamp(),
  `returned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lost_found`
--

INSERT INTO `lost_found` (`id`, `room_id`, `description`, `status`, `found_by`, `returned_to`, `found_at`, `returned_at`) VALUES
(1, 4, 'تم فقد ريموت تكييف', 'returned', 3, 'ahmed', '2026-05-09 04:40:31', '2026-05-09 04:40:35');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_transactions`
--

CREATE TABLE `loyalty_transactions` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `type` enum('earn','redeem','adjust','referral') NOT NULL,
  `reference` varchar(128) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loyalty_transactions`
--

INSERT INTO `loyalty_transactions` (`id`, `guest_id`, `points`, `type`, `reference`, `created_at`) VALUES
(1, 1, 1254, 'earn', 'Payment of 1254 EGP on Folio 2', '2026-05-09 04:57:34'),
(2, 1, 9016, 'earn', 'Payment of 9016 EGP on Folio 2', '2026-05-09 04:58:35'),
(3, 2, 182, 'earn', 'Payment of 182.4 EGP on Folio 3', '2026-05-09 05:07:33'),
(4, 2, 729, 'earn', 'Payment of 729.6 EGP on Folio 3', '2026-05-09 05:08:11'),
(5, 1, 638, 'earn', 'Payment of 638.4 EGP on Folio 4', '2026-05-09 05:15:57'),
(6, 1, 1254, 'earn', 'Payment of 1254 EGP on Folio 5', '2026-05-09 05:23:26'),
(7, 3, 369, 'earn', 'Payment of 369.36 EGP on Folio 6', '2026-05-09 05:52:15');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `status` enum('open','in_progress','resolved','cancelled') DEFAULT 'open',
  `reported_at` datetime DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  `resolution` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`id`, `room_id`, `reported_by`, `description`, `priority`, `status`, `reported_at`, `resolved_at`, `resolution`) VALUES
(1, 2, 3, 'انانا', 'high', 'resolved', '2026-05-09 04:38:03', '2026-05-09 04:38:28', 'مناما');

-- --------------------------------------------------------

--
-- Table structure for table `minibar_stock`
--

CREATE TABLE `minibar_stock` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `minibar_stock`
--

INSERT INTO `minibar_stock` (`id`, `item_name`, `qty`, `unit_price`) VALUES
(1, 'vhv', 8, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `night_audit`
--

CREATE TABLE `night_audit` (
  `id` int(11) NOT NULL,
  `audit_date` date NOT NULL,
  `total_revenue` decimal(12,2) DEFAULT 0.00,
  `total_payments` decimal(12,2) DEFAULT 0.00,
  `rooms_occupied` int(11) DEFAULT 0,
  `rooms_available` int(11) DEFAULT 0,
  `no_shows` int(11) DEFAULT 0,
  `run_by` int(11) DEFAULT NULL,
  `run_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(200) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 4, 'New task assigned: Room 101 - clean', '/hotel_pms/Views/Hk/task_detail.php?id=1', 1, '2026-05-09 01:28:01'),
(2, 3, 'Task completed: Room 101 – clean is waiting for inspection.', '/hotel_pms/Views/HkSup/inspection.php', 1, '2026-05-09 01:28:36'),
(3, 2, 'Room Unknown is now ready for check‑in.', '/hotel_pms/Views/Frontdesk/reservations.php', 1, '2026-05-09 01:28:46'),
(4, 4, 'Your task for Room 101 has been approved. The room is ready.', '/hotel_pms/Views/Hk/task_detail.php?id=1', 1, '2026-05-09 01:28:46'),
(5, 4, 'New task assigned: Room 102 - clean', '/hotel_pms/Views/Hk/task_detail.php?id=2', 1, '2026-05-09 01:39:04'),
(6, 3, 'Task completed: Room 102 – clean is waiting for inspection.', '/hotel_pms/Views/HkSup/inspection.php', 1, '2026-05-09 01:39:26'),
(7, 2, 'Room 102 is now ready for check‑in.', '/hotel_pms/Views/Frontdesk/reservations.php', 1, '2026-05-09 01:39:43'),
(8, 4, 'Your task for Room 102 has been approved. The room is ready.', '/hotel_pms/Views/Hk/task_detail.php?id=2', 0, '2026-05-09 01:39:43');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `folio_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('cash','card','transfer','points') NOT NULL,
  `reference` varchar(128) DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `received_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `folio_id`, `amount`, `method`, `reference`, `received_by`, `received_at`) VALUES
(1, 2, 1254.00, 'card', 'Pre‑Authorization Hold (20%)', 1, '2026-05-09 04:57:34'),
(2, 2, 9016.00, 'cash', '', 2, '2026-05-09 04:58:35'),
(3, 3, 182.40, 'cash', 'Pre-Authorization Hold', 2, '2026-05-09 05:07:33'),
(4, 3, 729.60, 'cash', '', 2, '2026-05-09 05:08:11'),
(5, 4, 638.40, 'card', 'Pre‑Authorization Hold (20%)', 1, '2026-05-09 05:15:57'),
(6, 5, 1254.00, 'card', 'Pre‑Authorization Hold (20%)', 1, '2026-05-09 05:23:26'),
(7, 6, 369.36, 'cash', 'Pre-Authorization Hold', 2, '2026-05-09 05:52:15');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `room_type_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `adults` int(11) DEFAULT 1,
  `children` int(11) DEFAULT 0,
  `rate` decimal(10,2) NOT NULL,
  `status` enum('pending','pending_payment','pending_blacklist','booked','checked_in','checked_out','cancelled','no_show') DEFAULT 'pending',
  `source` varchar(32) DEFAULT 'direct',
  `pre_auth_amount` decimal(10,2) DEFAULT 0.00,
  `pre_auth_status` enum('none','held','captured','released','charged') DEFAULT 'none',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `guest_id`, `room_id`, `room_type_id`, `check_in`, `check_out`, `adults`, `children`, `rate`, `status`, `source`, `pre_auth_amount`, `pre_auth_status`, `notes`, `created_at`) VALUES
(1, 1, NULL, 2, '2026-05-09', '2026-05-10', 1, 0, 1500.00, 'no_show', 'website', 342.00, 'charged', NULL, '2026-05-09 04:50:15'),
(2, 1, 9, 5, '2026-05-09', '2026-05-10', 1, 0, 5500.00, 'checked_out', 'website', 1254.00, 'held', NULL, '2026-05-09 04:57:01'),
(3, 2, 1, 1, '2026-05-09', '2026-05-10', 1, 0, 800.00, 'checked_out', 'walk_in', 182.40, 'held', NULL, '2026-05-09 05:07:28'),
(4, 1, NULL, 3, '2026-05-09', '2026-05-10', 1, 0, 2800.00, 'no_show', 'website', 638.40, 'charged', NULL, '2026-05-09 05:15:46'),
(5, 1, NULL, 5, '2026-05-09', '2026-05-10', 1, 0, 5500.00, 'no_show', 'website', 1254.00, 'charged', NULL, '2026-05-09 05:23:22'),
(6, 3, 6, 4, '2026-05-09', '2026-05-10', 1, 0, 1620.00, 'booked', 'walk_in', 369.36, 'held', NULL, '2026-05-09 05:52:04'),
(8, 3, 7, 3, '2026-05-09', '2026-05-10', 1, 0, 2520.00, 'pending', 'walk_in', 0.00, 'none', NULL, '2026-05-09 05:52:56');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_no` varchar(16) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `floor` int(11) DEFAULT 1,
  `occupancy` enum('available','occupied') DEFAULT 'available',
  `status` enum('clean','dirty','maintenance','out_of_order') DEFAULT 'clean',
  `notes` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_no`, `room_type_id`, `floor`, `occupancy`, `status`, `notes`, `image`) VALUES
(1, '101', 1, 1, 'available', 'dirty', 'Standard room - first floor', NULL),
(2, '102', 1, 1, 'available', 'clean', 'Standard room - first floor', NULL),
(3, '103', 1, 1, 'available', 'clean', 'Upgraded standard room', NULL),
(4, '201', 2, 2, 'available', 'clean', 'Deluxe room with sea view', NULL),
(5, '202', 2, 2, 'available', 'clean', 'Deluxe room with sea view', NULL),
(6, '203', 4, 2, 'available', 'clean', 'Family room', NULL),
(7, '301', 3, 3, 'available', 'clean', 'Luxury suite', NULL),
(8, '302', 3, 3, 'available', 'clean', 'Luxury suite', NULL),
(9, '401', 5, 4, 'available', 'dirty', 'Penthouse with private pool', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `room_types`
--

CREATE TABLE `room_types` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `max_occupancy` int(11) DEFAULT 2,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`id`, `name`, `base_price`, `max_occupancy`, `description`) VALUES
(1, 'Standard', 800.00, 2, 'Standard room with garden view'),
(2, 'Deluxe', 1500.00, 2, 'Deluxe room with sea view and more space'),
(3, 'Suite', 2800.00, 4, 'Luxury suite with separate lounge and jacuzzi'),
(4, 'Family', 1800.00, 4, 'Family room with two large beds'),
(5, 'Penthouse', 5500.00, 6, 'Penthouse with private rooftop pool');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(128) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `role` enum('manager','frontdesk','hk','hksup','guest') NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `active`, `created_at`) VALUES
(1, 'manager', '$2y$10$pc5HdLNE.bsKw7htjeJHYugSqSEyUEnr/IRBtTh91R2AyHurr5Dqq', 'Ahmed Manager', 'manager@hotel.com', '01000000001', 'manager', 1, '2026-05-09 03:08:07'),
(2, 'frontdesk', '$2y$10$pc5HdLNE.bsKw7htjeJHYugSqSEyUEnr/IRBtTh91R2AyHurr5Dqq', 'Sara Reception', 'frontdesk@hotel.com', '01000000002', 'frontdesk', 1, '2026-05-09 03:08:07'),
(3, 'hksup', '$2y$10$pc5HdLNE.bsKw7htjeJHYugSqSEyUEnr/IRBtTh91R2AyHurr5Dqq', 'Mohamed Supervisor', 'hksup@hotel.com', '01000000003', 'hksup', 1, '2026-05-09 03:08:07'),
(4, 'hk', '$2y$10$iDJYYipoDlXvgqY9zV6KBeLOnxIMXQzM10qPWHxhykuwZXJzUYYrO', 'Mona Housekeeper', 'hk1@hotel.com', '01000000004', 'hk', 1, '2026-05-09 03:08:07'),
(5, 'guest', '$2y$10$JREy73pm2sacDSgVg0szQ.FxifzAHujflbv4JcVpkcG9jBC7Zs48m', 'abdalla sayed mohamed', 'a.b.s.m12244556@gmail.com', '01022674536', 'guest', 1, '2026-05-09 03:30:16'),
(6, 'abdo@gmail.com', '$2y$10$bohQUDvfi20gZG8zoYKN.eGuam0g.Iehnk.kmOH.S.1TyPadqacZe', 'عبدالله سيد', 'abdo@gmail.com', '01000000001', 'guest', 1, '2026-05-09 03:51:53'),
(7, 'guest2', '$2y$10$0BXjnYNmHKAVnDt5sMEwxetNZXlOir.pfnc9FbLvGaoNe3kdhBE/W', 'Ahmed ', 'melojo4743@4heats.com', '56758697', 'guest', 1, '2026-05-09 03:55:44'),
(8, 'hk2', '$2y$10$289wDxslEPm3wa4PiKUNuuyJ.FAagXRG0zNEJioUDEXuzK9BdzfoW', 'hk', 'abood12244556@gmail.com', '01022674536', 'hk', 1, '2026-05-09 03:57:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_log_user_id` (`user_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_feedback_guest_id` (`guest_id`),
  ADD KEY `fk_feedback_reservation_id` (`reservation_id`);

--
-- Indexes for table `folios`
--
ALTER TABLE `folios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_folios_reservation_id` (`reservation_id`),
  ADD KEY `fk_folios_guest_id` (`guest_id`);

--
-- Indexes for table `folio_charges`
--
ALTER TABLE `folio_charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_folio_charges_folio_id` (`folio_id`),
  ADD KEY `fk_folio_charges_posted_by` (`posted_by`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_guests_user_id` (`user_id`);

--
-- Indexes for table `hk_tasks`
--
ALTER TABLE `hk_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hk_tasks_room_id` (`room_id`),
  ADD KEY `fk_hk_tasks_assigned_to` (`assigned_to`),
  ADD KEY `fk_hk_tasks_inspected_by` (`inspected_by`);

--
-- Indexes for table `lost_found`
--
ALTER TABLE `lost_found`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lost_found_room_id` (`room_id`),
  ADD KEY `fk_lost_found_found_by` (`found_by`);

--
-- Indexes for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_loyalty_transactions_guest_id` (`guest_id`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_maintenance_room_id` (`room_id`),
  ADD KEY `fk_maintenance_reported_by` (`reported_by`);

--
-- Indexes for table `minibar_stock`
--
ALTER TABLE `minibar_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_name` (`item_name`);

--
-- Indexes for table `night_audit`
--
ALTER TABLE `night_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_night_audit_run_by` (`run_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_folio_id` (`folio_id`),
  ADD KEY `fk_payments_received_by` (`received_by`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reservations_guest_id` (`guest_id`),
  ADD KEY `fk_reservations_room_id` (`room_id`),
  ADD KEY `fk_reservations_roomtype_id` (`room_type_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_no` (`room_no`),
  ADD KEY `fk_rooms_room_type_id` (`room_type_id`);

--
-- Indexes for table `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `folios`
--
ALTER TABLE `folios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `folio_charges`
--
ALTER TABLE `folio_charges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hk_tasks`
--
ALTER TABLE `hk_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lost_found`
--
ALTER TABLE `lost_found`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `minibar_stock`
--
ALTER TABLE `minibar_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `night_audit`
--
ALTER TABLE `night_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `room_types`
--
ALTER TABLE `room_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_log_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_guest_id` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  ADD CONSTRAINT `fk_feedback_reservation_id` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `folios`
--
ALTER TABLE `folios`
  ADD CONSTRAINT `fk_folios_guest_id` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  ADD CONSTRAINT `fk_folios_reservation_id` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`);

--
-- Constraints for table `folio_charges`
--
ALTER TABLE `folio_charges`
  ADD CONSTRAINT `fk_folio_charges_folio_id` FOREIGN KEY (`folio_id`) REFERENCES `folios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_folio_charges_posted_by` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `guests`
--
ALTER TABLE `guests`
  ADD CONSTRAINT `fk_guests_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hk_tasks`
--
ALTER TABLE `hk_tasks`
  ADD CONSTRAINT `fk_hk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hk_tasks_inspected_by` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hk_tasks_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `lost_found`
--
ALTER TABLE `lost_found`
  ADD CONSTRAINT `fk_lost_found_found_by` FOREIGN KEY (`found_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_lost_found_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD CONSTRAINT `fk_loyalty_transactions_guest_id` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`);

--
-- Constraints for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `fk_maintenance_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_maintenance_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `night_audit`
--
ALTER TABLE `night_audit`
  ADD CONSTRAINT `fk_night_audit_run_by` FOREIGN KEY (`run_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_folio_id` FOREIGN KEY (`folio_id`) REFERENCES `folios` (`id`),
  ADD CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservations_guest_id` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  ADD CONSTRAINT `fk_reservations_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reservations_roomtype_id` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`);

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_rooms_room_type_id` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
