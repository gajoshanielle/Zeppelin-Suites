
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `reservation_documents_table`;
DROP TABLE IF EXISTS `reservation_table`;
DROP TABLE IF EXISTS `owner_approval_table`;
DROP TABLE IF EXISTS `unit_blocked_dates_table`;
DROP TABLE IF EXISTS `unit_ownership_history_table`;
DROP TABLE IF EXISTS `maintenance_table`;
DROP TABLE IF EXISTS `inquiry_table`;
DROP TABLE IF EXISTS `units_table`;
DROP TABLE IF EXISTS `users_table`;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 01:49 PM
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
-- Database: `zepellin_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `inquiry_table`
--

CREATE TABLE `inquiry_table` (
  `inq_id` int(11) NOT NULL,
  `sender_name` varchar(150) NOT NULL,
  `sender_email` varchar(150) NOT NULL,
  `sender_contact` varchar(50) DEFAULT NULL,
  `inquiry_type` varchar(150) NOT NULL,
  `Preferred_unit_id` varchar(100) DEFAULT NULL,
  `preferred_move_in_time` varchar(100) DEFAULT NULL,
  `lease_duration` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','responded','onhold','declined','reservation submitted','officially booked') NOT NULL DEFAULT 'pending',
  `approval_status` enum('not_requested','requested','approved','declined') NOT NULL DEFAULT 'not_requested',
  `approved_unit_id` int(11) DEFAULT NULL,
  `approval_approved_at` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `reservation_token` varchar(64) DEFAULT NULL,
  `reservation_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiry_table`
--

INSERT INTO `inquiry_table` (`inq_id`, `sender_name`, `sender_email`, `sender_contact`, `inquiry_type`, `Preferred_unit_id`, `preferred_move_in_time`, `lease_duration`, `message`, `status`, `approval_status`, `approved_unit_id`, `approval_approved_at`, `timestamp`, `reservation_token`, `reservation_token_expires_at`) VALUES
(66, 'aug 10-', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately (Within 30 days)', '3 months', 'dsdsd', 'reservation submitted', 'approved', 1, '2026-08-11 00:21:29', '2026-08-10 15:53:45', '89ed5a8b09bb7c0cfa592b9dadd83ec6312ace9bff9d24571db81fef66de8434', '2026-09-09 18:21:29'),
(67, 'nov 13', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'In 3-6 Months', '3 months', 'sddds', 'pending', 'not_requested', NULL, NULL, '2026-08-10 17:23:42', NULL, NULL),
(68, 'nov 13', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Flexible / Not sure yet', '3 months', 'dcfdf', 'responded', 'approved', 1, '2026-08-11 01:42:48', '2026-08-10 17:24:56', '945bf48044421efdc124ddef0923dd622ecd49c8a99b4788f904ab77d8259449', '2026-09-09 19:42:48'),
(69, 'Shanielle Manalac Gajo', 'maddysilvano@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'Studio Type A', NULL, NULL, 'testing resale', 'pending', 'approved', NULL, '2026-10-08 20:15:57', '2026-08-23 12:39:31', '89e32ca880c1b53f62a3df352ba94fac9394ab98834c826a571d3e5800bcf857', '2026-11-07 14:15:57'),
(70, 'testing unit reservation', 'gajoshanielle@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately (Within 30 days)', '3 months', 'testing unit reservation', 'officially booked', 'approved', 1, '2026-08-25 22:58:31', '2026-08-23 13:10:29', '09969356af7ccacab87e54715e848af5b96658f1fedfed434ea7ffef394d1189', '2026-09-24 16:58:31'),
(71, 'Shanielle Manalac Gajo', 'delarosa2@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately (Within 30 days)', '3 months', 'august 26-nov 26', 'officially booked', 'approved', 3, '2026-08-26 01:00:45', '2026-08-25 16:56:05', '79471827d3397039d648b4ff0dcec351ba5497ec5983f0fe0f98e12e936bba81', '2026-09-24 19:00:45'),
(72, 'Shanielle Manalac Gajo', 'maddysilvano@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'Two Bedroom', NULL, NULL, 'resale testing', 'responded', 'approved', 20, '2026-09-01 11:45:54', '2026-09-01 03:01:58', 'dc10c28f085b4dda69fb05ae9cd11599f94eaac4fd7af5307a23eb1c352ce561', '2026-10-01 05:45:54'),
(73, 'testing reservation payment', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately (Within 30 days)', '3 months', 'testing testing', 'pending', 'approved', 4, '2026-09-06 16:46:04', '2026-09-03 11:51:22', '8742d0e40439aac20439e319f5511aba6eced0c7be51a6866d079a7245ff567b', '2026-10-06 10:46:04'),
(74, 'testing another reservation', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately (Within 30 days)', '2 years', 'testing testing', 'responded', 'approved', 4, '2026-09-04 18:04:30', '2026-09-04 09:58:09', '531ad88243c8e76c045b591f116268023d9e46bd3e5b1fb3cefde3027085b904', '2026-10-04 12:04:30'),
(75, 'testing testing v4', 'gajoshanielle@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'One Bedroom', 'Immediately (Within 30 days)', '6 months', 'testinggggggggggggg', 'officially booked', 'approved', 11, '2026-09-06 17:09:37', '2026-09-06 09:03:39', '5a7cede040a82b44d72de94503006c41d5dcce8bc252672778f23563004c1da6', '2026-10-06 11:09:37'),
(76, 'Shanielle Manalac Gajo', 'maddysilvano@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'Two Bedroom', NULL, NULL, 'TESTING TESTING', 'pending', 'requested', NULL, NULL, '2026-09-06 16:06:07', NULL, NULL),
(79, 'GCash Applicant', 'gcashapplicant@example.com', '09181234567', 'Unit Lease / Rental Reservation', NULL, 'Immediately (Within 30 days)', '1 year', '', 'pending', 'approved', 11, NULL, '2026-09-06 16:17:31', 'test_token_07f34322b0bb7b472622ca31e8064a65', '2026-10-06 18:17:31'),
(86, 'Maria Santos (Test)', 'mariasantos.test@gmail.com', '09175551234', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately (Within 30 days)', '6 months', 'Testing lease reservation: 6 months immediate move-in, fixed signing date, short payment.', 'reservation submitted', 'approved', 4, '2026-10-06 05:14:47', '2026-10-05 21:14:47', 'add87b2fb7f980fd2e91a9e3055cfe9ed4f1a9cb664916ed36c8c9dee17c764b', '2026-11-07 13:06:31'),
(87, 'testing 6-10-2026', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Next Month (1-2 months)', '1 year', 'testing', 'responded', 'approved', 1, '2026-10-06 05:34:35', '2026-10-05 21:28:26', '2d1e7864978f719b64a80587731ffbb492ead4cac508d616fa058b7be169ea93', '2026-11-04 23:34:35'),
(88, 'testing', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type B', 'In 3-6 Months', '6 months', 'testing testing', 'pending', 'approved', 8, '2026-10-08 17:23:48', '2026-10-08 08:45:00', 'ef980f52b93ffa89d0ad114439a6bad67dbd6628446f733d5083875bffce9ba8', '2026-11-07 12:49:12'),
(89, 'tesitng', 'maddysilvano@gmail.com', '09707568287', 'General Inquiry & Amenities', NULL, NULL, NULL, 'sdsda', 'responded', 'not_requested', NULL, NULL, '2026-10-08 09:42:37', NULL, NULL),
(90, 'testing reservation 2.1', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type B', 'Next Month (1-2 months)', '1 year', 'sdsda', 'pending', 'not_requested', NULL, NULL, '2026-10-08 10:00:28', NULL, NULL),
(93, 'testing resale', 'maddysilvano@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'One Bedroom', NULL, NULL, 'testing testing', 'officially booked', 'approved', 12, '2026-10-08 20:20:00', '2026-10-08 11:33:46', 'a414aedaa8e8817c9989d9725f4e9369c33458553e711e1e55aefdd0ea8095d0', '2026-11-07 14:20:00'),
(94, 'Franz Delastelle', 'franzdelastelle17@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Two Bedroom', 'Flexible / Not sure yet', 'Not sure yet', 'testing Unit lease', 'responded', 'declined', NULL, NULL, '2026-10-09 07:29:28', NULL, NULL),
(95, 'gajo shanielle', 'gajoshanielle@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'Two Bedroom', NULL, NULL, 'testing unit resale', 'pending', 'approved', NULL, '2026-10-09 15:32:56', '2026-10-09 07:30:50', 'ce793c90191994780cc0800605fc85553271dd434718e76627d162418e31cc88', '2026-11-08 09:32:56'),
(96, 'maddy Silvano', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Next Month (1-2 months)', '2 years', 'testing', 'declined', 'declined', NULL, NULL, '2026-10-09 07:50:08', NULL, NULL),
(97, 'unit resale decline', 'maddysilvano@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'Two Bedroom', NULL, NULL, 'testing decline', 'responded', 'approved', NULL, '2026-10-09 16:11:13', '2026-10-09 07:59:38', 'bab503c06d144a0e2ae4ba5a01aab0fa965989a9e54b0e4517d17306c643a64d', '2026-11-08 10:11:13'),
(98, 'maddy Silvano', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately (Within 30 days)', '3 months', 'testing', 'responded', 'approved', NULL, '2026-10-09 17:08:01', '2026-10-09 09:04:23', '8ad4621975cde6ba078cb162e491b036d9cf1f799c72829d6e7cb4b626663f23', '2026-11-08 11:08:01'),
(99, 'testing resale 12-10-2026', 'maddysilvano@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'Studio Type B', NULL, NULL, 'heheye', 'reservation submitted', 'approved', 10, '2026-10-12 11:08:43', '2026-10-12 03:05:06', 'dc36ad2cb74a79f17d32612cd47a428c427241aa73377422057151a0702e5c14', '2026-11-11 05:08:43'),
(100, 'testing FCFS', 'franzdelastelle17@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'Studio Type B', NULL, NULL, 'testing testing', 'reservation submitted', 'approved', 7, '2026-10-12 11:23:40', '2026-10-12 03:17:35', '6e3e1e1d1bea918b83490c1b80d6c627247dd7693a8362db8aeae9ba7be6ca54', '2026-11-11 05:23:40'),
(101, 'testing payment proof', 'maddysilvano@gmail.com', '09707568287', 'Buy / Purchase a Unit (Resale)', 'Studio Type A', NULL, NULL, 'sdsad', 'reservation submitted', 'approved', 2, '2026-10-12 13:58:28', '2026-10-12 05:57:58', '9668dd6a68a454cf953e6302a45f8f02b8e1e3c2085e8f0e23c977afbad9c51b', '2026-11-11 07:58:28'),
(102, 'maddy Silvano', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately (Within 30 days)', '6 months', 'testing testing', 'pending', 'not_requested', NULL, NULL, '2026-10-12 07:42:28', NULL, NULL),
(103, 'maddy Silvano', 'maddysilvano@gmail.com', '09707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Next Month (1-2 months)', '3 months', 'testing', 'responded', 'approved', 1, '2026-10-12 16:21:05', '2026-10-12 08:18:47', 'b5a59064e77991ce1d6ed13444ef3efb51a11531fef849f4b5c2bb9334c6eb5e', '2026-11-11 10:21:05'),
(104, 'testing', 'maddysilvano@gmail.com', '+63 9707568287', 'Unit Lease / Rental Reservation', 'Studio Type A', 'In 2-3 Months', '3 months', 'hg', 'responded', 'approved', NULL, '2026-10-16 07:20:34', '2026-10-15 22:50:57', 'b397ac5672923473b140790f959501261d2835c25be30fff96d75caa94b6ac3b', '2026-11-15 01:20:34'),
(105, 'Existing Tenant (Overlap Test)', 'tenant.overlap@example.com', '+63 9123456789', 'Unit Lease / Rental Reservation', 'Studio Type A', 'Immediately', '3 months', 'Mock reservation for overlap testing', 'reservation submitted', 'approved', 5, NULL, '2026-10-15 23:41:44', NULL, NULL),
(106, 'testing reservation 16/10', 'maddysilvano@gmail.com', '+63 9707568287', 'Unit Lease / Rental Reservation', 'Studio Type B', 'Immediately (Within 30 days)', '6 months', 'testing', 'officially booked', 'approved', 6, '2026-10-16 15:21:17', '2026-10-16 07:18:26', 'cc83c63d5516fcf08608147b118031764b8665c2634666cd63f25c459995f53e', '2026-11-15 09:21:17'),
(107, 'testing resale', 'maddysilvano@gmail.com', '+63 9707568287', 'Buy / Purchase a Unit (Resale)', 'Studio Type A', NULL, NULL, 'dfd', 'pending', 'not_requested', NULL, NULL, '2026-10-16 07:18:48', NULL, NULL),
(108, 'testing reservatain', 'maddysilvano@gmail.com', '+63 9707568287', 'Unit Lease / Rental Reservation', 'Studio Type B', 'Immediately (Within 30 days)', '6 months', 'fgfg', 'responded', 'approved', NULL, '2026-10-16 15:43:18', '2026-10-16 07:42:32', '9e377231f182b923de3d81fc64be168607875dc0bbfaddb62b7bf55b33b0dae1', '2026-11-15 09:43:18'),
(109, 'Elena Rostova', 'elena.tenant@gmail.com', '09175554321', 'Rental Inquiry', '8', 'Morning (9:00 AM - 12:00 PM)', '12 Months', 'Hello, I would like to inquire and reserve Unit B206 for a 1-year lease starting November 1, 2026.', 'officially booked', 'approved', 8, '2026-10-22 14:27:40', '2026-10-22 06:27:40', NULL, NULL),
(110, 'Gajo Chantel', 'chantelgajo@gmail.com', '+63 9707568287', 'Unit Lease / Rental Reservation', 'Two Bedroom', 'Immediately (Within 30 days)', '3 months', 'testing', 'reservation submitted', 'approved', 19, '2026-10-22 15:07:45', '2026-10-22 07:05:31', 'c5924c70e52377c048a789d6962e52304810da2e2c802abc2bd15cd379bd6f06', '2026-11-21 09:07:45'),
(111, 'Block Test User', 'blocktest@example.com', '09123456789', 'Lease Inquiry', NULL, NULL, NULL, 'Testing block', 'pending', 'approved', 9, NULL, '2026-10-22 09:54:49', 'test_token_1d8035cc64f3aab4093b1cbd60bd2f20', '2026-10-29 17:54:49'),
(112, 'Block Test User', 'blocktest@example.com', '09123456789', 'Lease Inquiry', NULL, NULL, NULL, 'Testing block', 'pending', 'approved', 9, NULL, '2026-10-22 09:55:14', 'test_token_0cc03ee047abb627dad9cdd6460153af', '2026-10-29 17:55:14'),
(113, 'Block Test User', 'blocktest@example.com', '09123456789', 'Lease Inquiry', NULL, NULL, NULL, 'Testing block', 'pending', 'approved', 9, NULL, '2026-10-22 09:55:48', 'test_token_79a6186319c4fea5b6ecf96a7ebb4a65', '2026-10-29 17:55:48'),
(126, 'Shanielle Manalac Gajo', 'maddysilvano@gmail.com', '+63 9707568287', 'Buy / Purchase a Unit (Resale)', 'Studio Type A', NULL, NULL, 'edde', 'responded', 'not_requested', NULL, NULL, '2026-10-22 10:04:32', NULL, NULL),
(127, 'Gajo Chantel', 'chantelgajo@gmail.com', '+63 9707568287', 'Buy / Purchase a Unit (Resale)', 'Two Bedroom', NULL, NULL, 'hhbh', 'reservation submitted', 'approved', 17, '2026-10-22 18:07:13', '2026-10-22 10:06:31', '4c14f853367ee96cfd74178f32958adf363ee40d4981bed288b55a2143a21e1b', '2026-11-21 12:07:13');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_table`
--

CREATE TABLE `maintenance_table` (
  `maintenance_id` int(11) NOT NULL,
  `submitted_by_user_id` int(11) DEFAULT NULL,
  `submitted_by_role` enum('tenant','unit owner','admin') NOT NULL DEFAULT 'unit owner',
  `unit_owner_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','normal','urgent') NOT NULL DEFAULT 'normal',
  `status` enum('pending','in progress','resolved','cancelled') NOT NULL DEFAULT 'pending',
  `photo_paths` text DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `admin_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_table`
--

INSERT INTO `maintenance_table` (`maintenance_id`, `submitted_by_user_id`, `submitted_by_role`, `unit_owner_id`, `unit_id`, `subject`, `category`, `description`, `priority`, `status`, `photo_paths`, `submitted_at`, `updated_at`, `resolved_at`, `admin_remarks`) VALUES
(1, 2, 'unit owner', 2, 1, 'Leaking faucet', 'Plumbing', 'The faucet in the bathroom sink is continuously leaking even when fully closed. Water is dripping under the sink area and causing the cabinet surface to become wet. Please inspect the faucet, valve, and pipe connection as soon as possible.', 'normal', 'pending', '[\"uploads\\/maintenance\\/maintenance_1780907097_7a53ee9d9ceed821.jpg\"]', '2026-06-08 16:24:57', '2026-10-12 12:38:56', NULL, 'okay'),
(2, 2, 'unit owner', 2, 1, 'Leaking faucet', 'Plumbing', 'sjdsdklsakdmasd', 'normal', 'resolved', '[\"uploads\\/maintenance\\/maintenance_1782307562_c41091ceb8343aee.jpg\"]', '2026-06-24 21:26:02', '2026-07-13 19:37:37', '2026-07-13 19:37:37', 'Hello! We have spoken to the plumber; they will arrive tomorrow. :)'),
(3, 7, 'unit owner', 7, 10, 'cleaning', 'Cleaning', 'hello need cleaning by 4 pm', 'low', 'resolved', NULL, '2026-10-12 12:38:38', '2026-10-12 12:40:08', '2026-10-12 12:40:08', 'okay'),
(4, 4, 'unit owner', 4, 2, 'ZSIRAAAAA', 'Plumbing', 'sira gripo', 'normal', 'pending', 'images/maintenance/maintenance_own_1791790754_592a5a78f80a937f.jpg', '2026-10-12 15:39:14', '2026-10-12 15:39:57', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `owner_approval_table`
--

CREATE TABLE `owner_approval_table` (
  `request_id` int(11) NOT NULL,
  `inq_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `unit_owner_id` int(11) NOT NULL,
  `request_status` enum('pending','approved','declined','expired') NOT NULL DEFAULT 'pending',
  `owner_remarks` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_approval_table`
--

INSERT INTO `owner_approval_table` (`request_id`, `inq_id`, `unit_id`, `unit_owner_id`, `request_status`, `owner_remarks`, `requested_at`, `responded_at`) VALUES
(102, 66, 3, 5, 'declined', NULL, '2026-08-10 16:17:11', '2026-08-11 00:17:30'),
(103, 66, 1, 2, 'approved', NULL, '2026-08-10 16:20:19', '2026-08-11 00:21:29'),
(104, 68, 1, 2, 'approved', NULL, '2026-08-10 17:42:32', '2026-08-11 01:42:48'),
(105, 69, 2, 4, 'approved', 'okay', '2026-08-23 13:04:41', '2026-10-08 20:15:57'),
(106, 70, 1, 2, 'approved', NULL, '2026-08-23 13:12:05', '2026-08-25 22:58:31'),
(107, 71, 5, 8, 'expired', NULL, '2026-08-25 16:59:33', '2026-08-26 01:00:45'),
(108, 71, 3, 5, 'approved', NULL, '2026-08-25 17:00:19', '2026-08-26 01:00:45'),
(110, 72, 20, 7, 'approved', NULL, '2026-09-01 03:19:16', '2026-09-01 11:45:54'),
(111, 73, 4, 6, 'approved', 'N/A', '2026-09-03 11:54:11', '2026-09-06 16:46:04'),
(112, 74, 4, 6, 'approved', 'testing testing', '2026-09-04 09:58:41', '2026-09-04 18:04:30'),
(113, 75, 11, 2, 'approved', 'okay', '2026-09-06 09:04:14', '2026-09-06 17:09:37'),
(114, 76, 20, 7, 'pending', NULL, '2026-09-06 16:06:27', NULL),
(115, 87, 1, 2, 'approved', '', '2026-10-05 21:31:05', '2026-10-06 05:34:35'),
(116, 87, 3, 5, 'declined', 'I declined', '2026-10-05 21:31:05', '2026-10-06 05:33:43'),
(117, 88, 6, 2, 'declined', '', '2026-10-08 08:58:35', '2026-10-08 17:23:23'),
(118, 88, 8, 6, 'approved', '', '2026-10-08 08:59:59', '2026-10-08 17:23:48'),
(119, 88, 9, 6, 'declined', '', '2026-10-08 08:59:59', '2026-10-08 17:23:41'),
(125, 93, 12, 4, 'approved', '', '2026-10-08 12:19:20', '2026-10-08 20:20:00'),
(126, 93, 15, 7, 'approved', '', '2026-10-08 12:19:20', '2026-10-08 20:20:32'),
(127, 93, 21, 4, 'approved', '', '2026-10-08 12:19:20', '2026-10-08 20:20:03'),
(129, 95, 20, 7, 'approved', 'no', '2026-10-09 07:31:19', '2026-10-09 15:32:56'),
(130, 94, 16, 2, 'declined', '', '2026-10-09 07:35:41', '2026-10-09 15:36:05'),
(131, 96, 1, 2, 'declined', '', '2026-10-09 07:50:24', '2026-10-09 15:50:48'),
(132, 97, 17, 4, 'declined', '', '2026-10-09 07:59:49', '2026-10-09 16:00:11'),
(133, 97, 17, 4, 'approved', '', '2026-10-09 08:09:05', '2026-10-09 16:11:13'),
(134, 97, 20, 7, 'pending', NULL, '2026-10-09 08:09:05', NULL),
(135, 98, 1, 2, 'approved', '', '2026-10-09 09:06:17', '2026-10-09 17:08:01'),
(136, 98, 3, 5, 'approved', '', '2026-10-09 09:06:17', '2026-10-09 17:08:54'),
(137, 98, 5, 8, 'pending', NULL, '2026-10-09 09:06:17', NULL),
(138, 99, 7, 4, 'declined', '', '2026-10-12 03:07:10', '2026-10-12 11:09:22'),
(139, 99, 10, 7, 'approved', 'nothing', '2026-10-12 03:07:10', '2026-10-12 11:08:43'),
(140, 100, 7, 4, 'approved', '', '2026-10-12 03:17:50', '2026-10-12 11:23:40'),
(141, 100, 10, 7, 'approved', '', '2026-10-12 03:17:50', '2026-10-12 11:23:50'),
(142, 101, 2, 4, 'approved', '', '2026-10-12 05:58:08', '2026-10-12 13:58:28'),
(144, 103, 1, 2, 'approved', '', '2026-10-12 08:19:43', '2026-10-12 16:21:05'),
(145, 103, 3, 5, 'approved', '', '2026-10-12 08:19:43', '2026-10-12 16:21:42'),
(146, 103, 5, 8, 'pending', NULL, '2026-10-12 08:19:43', NULL),
(147, 104, 5, 8, 'approved', '', '2026-10-15 23:00:20', '2026-10-16 07:20:34'),
(148, 106, 6, 2, 'approved', '', '2026-10-16 07:20:09', '2026-10-16 15:21:43'),
(149, 106, 8, 6, 'declined', '', '2026-10-16 07:20:09', '2026-10-16 15:21:11'),
(150, 106, 9, 6, 'approved', '', '2026-10-16 07:20:09', '2026-10-16 15:21:17'),
(151, 108, 9, 6, 'approved', '', '2026-10-16 07:43:05', '2026-10-16 15:43:18'),
(152, 109, 8, 6, 'approved', 'Approved for 1-year tenancy.', '2026-10-22 06:27:40', '2026-10-22 14:27:40'),
(153, 110, 16, 2, 'declined', '', '2026-10-22 07:06:40', '2026-10-22 15:07:25'),
(154, 110, 18, 5, 'approved', '', '2026-10-22 07:06:40', '2026-10-22 15:07:45'),
(155, 110, 19, 6, 'approved', '', '2026-10-22 07:06:40', '2026-10-22 15:07:56'),
(156, 112, 9, 3, 'approved', NULL, '2026-10-22 09:55:14', '2026-10-22 17:55:14'),
(157, 113, 9, 3, 'approved', NULL, '2026-10-22 09:55:48', '2026-10-22 17:55:48'),
(162, 127, 17, 4, 'approved', '', '2026-10-22 10:06:40', '2026-10-22 18:07:13');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_documents_table`
--

CREATE TABLE `reservation_documents_table` (
  `document_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `document_key` varchar(50) NOT NULL COMMENT 'stable identifier, e.g. valid_id_1, tin_number',
  `document_name` varchar(150) NOT NULL COMMENT 'display label, e.g. Valid ID #1',
  `status` enum('pending','complete') NOT NULL DEFAULT 'pending',
  `document_link` varchar(500) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_documents_table`
--

INSERT INTO `reservation_documents_table` (`document_id`, `reservation_id`, `document_key`, `document_name`, `status`, `document_link`, `updated_by`, `updated_at`) VALUES
(73, 27, 'valid_id_1', 'Valid ID #1', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_27_1792644765_62dda1d0.jpg', 1, '2026-10-22 06:52:46'),
(74, 27, 'valid_id_2', 'Valid ID #2', 'complete', 'https://dropbox.com/test/valid_id_2', 2, '2026-10-08 19:18:47'),
(75, 27, 'tin_number', 'TIN Number', 'complete', 'https://dropbox.com/test/tin_number', 2, '2026-10-08 19:18:47'),
(76, 27, 'reservation_agreement', 'Reservation Agreement', 'complete', 'https://dropbox.com/test/reservation_agreement', 2, '2026-10-08 19:18:47'),
(77, 28, 'valid_id_1', 'Valid ID #1', 'complete', NULL, 1, '2026-08-26 01:03:19'),
(78, 28, 'valid_id_2', 'Valid ID #2', 'complete', NULL, 1, '2026-08-26 01:03:19'),
(79, 28, 'tin_number', 'TIN Number', 'complete', NULL, 1, '2026-08-26 01:03:19'),
(80, 28, 'reservation_agreement', 'Reservation Agreement', 'complete', NULL, 1, '2026-08-26 01:03:19'),
(81, 32, 'valid_id_1', 'Valid ID #1', 'complete', 'sdsda', 2, '2026-09-07 00:55:31'),
(82, 32, 'valid_id_2', 'Valid ID #2', 'complete', 'sds', 2, '2026-09-07 00:55:31'),
(83, 32, 'tin_number', 'TIN Number', 'complete', 'sds', 2, '2026-09-07 00:55:31'),
(84, 32, 'reservation_agreement', 'Reservation Agreement', 'complete', 'sds', 2, '2026-09-07 00:55:31'),
(129, 33, 'valid_id_1', 'Valid ID #1', 'complete', 'http://localhost/Zeppelin-Suites/owner/reservations/view?reservation_id=33#documents', 6, '2026-10-09 11:23:51'),
(130, 33, 'valid_id_2', 'Valid ID #2', 'complete', 'http://localhost/Zeppelin-Suites/owner/reservations/view?reservation_id=33#documents', 6, '2026-10-09 11:23:51'),
(131, 33, 'tin_number', 'TIN Number', 'complete', 'http://localhost/Zeppelin-Suites/owner/reservations/view?reservation_id=33#documents', 6, '2026-10-09 11:23:51'),
(132, 33, 'reservation_agreement', 'Reservation Agreement', 'complete', 'http://localhost/Zeppelin-Suites/owner/reservations/view?reservation_id=33#documents', 6, '2026-10-09 11:23:51'),
(149, 35, 'valid_id_1', 'Valid ID #1', 'complete', 'https://drive.google.com/file/d/1Opg6zpOvf4gKj2HDZkSabaeKXW9MBm40/view?usp=drive_link', 4, '2026-10-08 20:16:13'),
(150, 35, 'valid_id_2', 'Valid ID #2', 'complete', 'https://drive.google.com/file/d/1J-MfioI-k065Ef67mjQVA8CfpYv38lsG/view?usp=drive_link', 4, '2026-10-08 20:16:13'),
(151, 35, 'tin_number', 'TIN Number', 'complete', 'https://drive.google.com/file/d/1mc4EGOBc1b3Vyz1PL-O2UDuueRBNq8E1/view?usp=drive_link', 4, '2026-10-08 20:16:13'),
(152, 35, 'reservation_agreement', 'Reservation Agreement', 'complete', 'https://drive.google.com/file/d/1k7JrvWXN7L-bApHoHvOIcx2h6VMQ8xHz/view?usp=drive_link', 4, '2026-10-08 20:16:13'),
(153, 36, 'valid_id_1', 'Valid ID #1', 'pending', NULL, NULL, NULL),
(154, 36, 'valid_id_2', 'Valid ID #2', 'pending', NULL, NULL, NULL),
(155, 36, 'tin_number', 'TIN Number', 'pending', NULL, NULL, NULL),
(156, 36, 'reservation_agreement', 'Reservation Agreement', 'pending', NULL, NULL, NULL),
(157, 37, 'valid_id_1', 'Valid ID #1', 'pending', NULL, NULL, NULL),
(158, 37, 'valid_id_2', 'Valid ID #2', 'pending', NULL, NULL, NULL),
(159, 37, 'tin_number', 'TIN Number', 'pending', NULL, NULL, NULL),
(160, 37, 'reservation_agreement', 'Reservation Agreement', 'pending', NULL, NULL, NULL),
(161, 38, 'valid_id_1', 'Valid ID #1', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_38_1791790578_c1761814.pdf', 1, '2026-10-12 09:36:18'),
(162, 38, 'valid_id_2', 'Valid ID #2', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_38_1791790587_8aa235c6.jpg', 1, '2026-10-12 09:36:28'),
(163, 38, 'tin_number', 'TIN Number', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/tin_number/doc_res_38_1791790602_5bcc2f3c.jpg', 1, '2026-10-12 09:36:42'),
(164, 38, 'reservation_agreement', 'Reservation Agreement', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/agreement_forms/doc_res_38_1791790606_53626727.pdf', 1, '2026-10-12 09:36:47'),
(169, 41, 'valid_id_1', 'Valid ID #1', 'pending', NULL, NULL, NULL),
(170, 41, 'valid_id_2', 'Valid ID #2', 'pending', NULL, NULL, NULL),
(171, 41, 'tin_number', 'TIN Number', 'pending', NULL, NULL, NULL),
(172, 41, 'reservation_agreement', 'Reservation Agreement', 'pending', NULL, NULL, NULL),
(173, 43, 'reservation_agreement', 'Reservation Agreement', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/agreement_forms/doc_res_38_1791790606_53626727.pdf', 1, '2026-10-22 14:27:40'),
(174, 43, 'valid_id_1', 'Valid ID #1 (Passport)', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_27_1792644765_62dda1d0.jpg', 1, '2026-10-22 14:27:40'),
(175, 43, 'valid_id_2', 'Valid ID #2 (Driver License)', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_27_1792644765_62dda1d0.jpg', 1, '2026-10-22 14:27:40'),
(176, 43, 'tin_number', 'TIN Verification Document', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/agreement_forms/doc_res_38_1791790606_53626727.pdf', 1, '2026-10-22 14:27:40'),
(181, 45, 'valid_id_1', 'Valid ID #1', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_45_1792659126_7e27c13b.jpg', 6, '2026-10-22 10:52:07'),
(182, 45, 'valid_id_2', 'Valid ID #2', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_45_1792659140_ec2e3f74.jpg', 6, '2026-10-22 10:52:20'),
(183, 45, 'tin_number', 'TIN Number', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/tin_number/doc_res_45_1792659949_09489b4e.pdf', 6, '2026-10-22 11:05:50'),
(184, 45, 'reservation_agreement', 'Reservation Agreement', 'complete', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/agreement_forms/doc_res_45_1792659973_52f01956.webp', 6, '2026-10-22 11:06:14'),
(185, 55, 'valid_id_1', 'Valid ID #1', 'pending', NULL, NULL, NULL),
(186, 55, 'valid_id_2', 'Valid ID #2', 'pending', NULL, NULL, NULL),
(187, 55, 'tin_number', 'TIN Number', 'pending', NULL, NULL, NULL),
(188, 55, 'reservation_agreement', 'Reservation Agreement', 'pending', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reservation_table`
--

CREATE TABLE `reservation_table` (
  `reservation_id` int(11) NOT NULL,
  `inq_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `client_email` varchar(150) NOT NULL,
  `client_contact` varchar(50) NOT NULL,
  `client_sex` varchar(20) DEFAULT NULL,
  `client_nationality` varchar(50) DEFAULT NULL,
  `transaction_type` varchar(50) DEFAULT NULL,
  `move_in_date` date DEFAULT NULL,
  `move_out_date` date DEFAULT NULL,
  `lease_signing_date` varchar(255) DEFAULT NULL,
  `is_flexible_signing` tinyint(1) NOT NULL DEFAULT 0,
  `lease_signed_at` datetime DEFAULT NULL,
  `price_basis` decimal(12,2) NOT NULL,
  `required_amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'GCash QR',
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending review','verified','rejected') NOT NULL DEFAULT 'pending review',
  `reservation_status` varchar(50) NOT NULL DEFAULT 'submitted',
  `client_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_reviewed_at` datetime DEFAULT NULL,
  `officially_booked_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `cancellation_requested_by` int(11) DEFAULT NULL,
  `cancellation_requested_at` datetime DEFAULT NULL,
  `client_cancel_token` varchar(255) DEFAULT NULL,
  `client_cancel_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_table`
--

INSERT INTO `reservation_table` (`reservation_id`, `inq_id`, `unit_id`, `client_name`, `client_email`, `client_contact`, `client_sex`, `client_nationality`, `transaction_type`, `move_in_date`, `move_out_date`, `lease_signing_date`, `is_flexible_signing`, `lease_signed_at`, `price_basis`, `required_amount`, `payment_method`, `payment_proof`, `payment_status`, `reservation_status`, `client_remarks`, `created_at`, `payment_reviewed_at`, `officially_booked_at`, `cancelled_at`, `cancellation_reason`, `cancellation_requested_by`, `cancellation_requested_at`, `client_cancel_token`, `client_cancel_token_expires_at`) VALUES
(27, 70, 1, 'testing unit reservation', 'gajoshanielle@gmail.com', '09707568287', NULL, NULL, 'Unit Leasing', '2026-09-01', '2026-11-01', NULL, 0, NULL, 18000.00, 6300.00, 'GCash QR', 'uploads/payment_proofs/payment_70_1787675927_cc72da2135a5.jpg', 'verified', 'handover', NULL, '2026-08-25 16:38:47', '2026-08-26 00:39:59', '2026-08-26 00:41:46', NULL, NULL, NULL, NULL, 'ebf904744b81d3c28530ac452384501188de7d4a420fa08b07fce2cc05aa071f', '2026-09-24 18:39:59'),
(28, 71, 3, 'Shanielle Manalac Gajo', 'delarosa2@gmail.com', '09707568287', NULL, NULL, 'Unit Leasing', '2026-08-26', '2026-11-01', NULL, 0, NULL, 19000.00, 19000.00, 'GCash QR', 'uploads/payment_proofs/payment_71_1787677350_7ee3724941d3.jpg', 'verified', 'handover', NULL, '2026-08-25 17:02:30', '2026-08-26 01:02:57', '2026-08-26 01:03:26', NULL, NULL, NULL, NULL, '0b2f716d192c983fbea0b584bd0ae64cc2b8123330aa8fbe227341b28ee1df8f', '2026-09-24 19:02:57'),
(32, 75, 11, 'testing testing v4', 'gajoshanielle@gmail.com', '09707568287', 'Female', 'Filipino', 'Unit Leasing', '2026-09-22', '2027-03-22', NULL, 1, '2026-09-06 18:55:10', 28000.00, 9800.00, 'In-House', NULL, 'verified', 'reserved', 'testing testing', '2026-09-06 16:30:50', '2026-09-07 00:54:53', '2026-10-05 01:09:51', NULL, NULL, NULL, NULL, 'd494ce817b646542019554291d0a014ecc7ada8b7fe72c07bee676ba4984baef', '2026-10-06 18:54:53'),
(33, 86, 4, 'Maria Santos (Test)', 'mariasantos.test@gmail.com', '09175551234', 'Female', 'Filipino', 'Unit Leasing', '2026-10-07', '2027-04-07', '2026-10-10', 0, '2026-10-09 11:22:27', 19500.00, 6825.00, 'GCash QR', 'uploads/receipts/sample_gcash.png', 'verified', 'reserved', NULL, '2026-10-05 21:14:47', '2026-10-09 11:21:56', '2026-10-09 11:23:51', NULL, NULL, NULL, NULL, NULL, NULL),
(35, 93, 12, 'testing resale', 'maddysilvano@gmail.com', '09707568287', 'Male', 'Filipino', 'Unit Resale', NULL, NULL, '2026-10-08', 1, '2026-10-08 20:14:57', 140000.00, 49000.00, 'GCash QR', 'images/payment_proofs/payment_93_1791472703_f7cc140e4fee.jpg', 'verified', 'handover', '', '2026-10-08 15:18:23', '2026-10-08 20:14:23', '2026-10-08 20:16:13', NULL, NULL, NULL, NULL, NULL, NULL),
(36, 99, 10, 'testing resale 12-10-2026', 'maddysilvano@gmail.com', '09707568287', 'Female', 'Filipino', 'Unit Resale', NULL, NULL, NULL, 1, NULL, 23000.00, 8050.00, 'In-House', NULL, 'pending review', 'cancelled', '', '2026-10-12 03:29:03', NULL, NULL, '2026-10-12 05:49:10', 'cant', 7, '2026-10-12 05:44:46', NULL, NULL),
(37, 100, 7, 'testing FCFS', 'franzdelastelle17@gmail.com', '09707568287', 'Male', 'Filipino', 'Unit Resale', NULL, NULL, NULL, 1, NULL, 21500.00, 7525.00, 'In-House', NULL, 'pending review', 'cancelled', '', '2026-10-12 03:31:37', NULL, NULL, '2026-10-12 07:24:02', 'gyy', 4, '2026-10-12 07:23:46', NULL, NULL),
(38, 101, 2, 'testing payment proof', 'maddysilvano@gmail.com', '09707568287', 'Male', 'Filipino', 'Unit Resale', NULL, NULL, '2026-10-15, 2026-10-16, 2026-10-17', 0, '2026-10-12 09:26:05', 50000.00, 37500.00, 'GCash QR', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/payment_proofs/payment_inq_101_1791789660_067d51a6.jpg', 'verified', 'reserved', '', '2026-10-12 07:21:00', '2026-10-12 09:24:37', '2026-10-12 09:39:31', NULL, NULL, NULL, NULL, NULL, NULL),
(40, 105, 5, 'Active Tenant (Overlap Test)', 'tenant.overlap@example.com', '+63 9123456789', 'Female', 'Filipino', 'Lease Inquiry', '2026-12-01', '2027-03-01', '2026-11-20', 0, NULL, 20000.00, 20000.00, 'GCash QR', NULL, 'verified', 'reserved', 'Overlap test seed reservation', '2026-10-15 23:41:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 106, 6, 'testing reservation 16/10', 'maddysilvano@gmail.com', '+63 9707568287', 'Female', 'Filipino', 'Unit Leasing', '2026-10-22', '2027-04-22', '2026-10-17', 0, '2026-10-16 09:29:58', 21000.00, 7350.00, 'GCash QR', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/payment_proofs/payment_inq_106_1792135633_b0e74405.jpg', 'verified', 'handover', '', '2026-10-16 07:27:15', '2026-10-16 09:29:14', '2026-10-16 15:31:46', NULL, NULL, NULL, NULL, NULL, NULL),
(43, 109, 8, 'Elena Rostova', 'elena.tenant@gmail.com', '09175554321', 'Female', 'Filipino', 'Long Term Lease', '2026-11-01', '2027-10-31', '2026-10-22', 0, '2026-10-22 14:27:40', 22000.00, 22000.00, 'GCash QR', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/payment_proofs/payment_inq_101_1791789660_067d51a6.jpg', 'verified', 'reserved', 'First month deposit and advance paid via GCash.', '2026-10-22 06:27:40', '2026-10-22 14:27:40', '2026-10-22 14:27:40', NULL, NULL, NULL, NULL, NULL, NULL),
(45, 110, 19, 'Gajo Chantel', 'chantelgajo@gmail.com', '+63 9707568287', 'Male', 'Filipino', 'Unit Leasing', '2026-10-25', '2027-01-25', '2026-10-25', 1, '2026-10-22 10:51:57', 47000.00, 16450.00, 'In-House', NULL, 'verified', 'reserved', '', '2026-10-22 07:53:03', '2026-10-22 10:33:05', '2026-10-22 11:06:14', NULL, 'bjhbj', NULL, '2026-10-22 16:03:24', NULL, NULL),
(55, 127, 17, 'Gajo Chantel', 'chantelgajo@gmail.com', '+63 9707568287', 'Male', 'Filipino', 'Unit Resale', NULL, NULL, NULL, 1, NULL, 43500.00, 15225.00, 'In-House', NULL, 'pending review', 'cancelled', '', '2026-10-22 10:08:28', NULL, NULL, '2026-10-22 12:12:33', '[PREV_STATUS:submitted] mg', NULL, '2026-10-22 18:11:17', NULL, NULL);

--
-- Triggers `reservation_table`
--



-- --------------------------------------------------------

--
-- Table structure for table `units_table`
--

CREATE TABLE `units_table` (
  `unit_id` int(11) NOT NULL,
  `unit_type` enum('Studio Type A','Studio Type B','One Bedroom','Two Bedroom') NOT NULL,
  `sqm` decimal(6,2) NOT NULL DEFAULT 0.00,
  `unit_number` varchar(20) NOT NULL,
  `floor_number` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `lease_rate` decimal(10,2) DEFAULT NULL,
  `reselling_price` decimal(15,2) DEFAULT NULL,
  `listing_type` enum('For Lease','Resale','Not Available') NOT NULL DEFAULT 'For Lease',
  `stay_category` enum('Short term','Long term') NOT NULL DEFAULT 'Long term',
  `unit_owner_id` int(11) DEFAULT NULL,
  `unit_current_status` enum('Ready for Occupancy','For Resale','Resale','On Hold','Reserved','Occupied','Under maintenance','Not Available') NOT NULL DEFAULT 'Not Available',
  `auto_approve_inquiries` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units_table`
--

INSERT INTO `units_table` (`unit_id`, `unit_type`, `sqm`, `unit_number`, `floor_number`, `lease_rate`, `reselling_price`, `listing_type`, `stay_category`, `unit_owner_id`, `unit_current_status`, `created_at`) VALUES
(1, 'Studio Type A', 37.00, 'A104', 1, 18000.00, 2500000.00, 'For Lease', 'Long term', 7, 'Occupied', '2026-06-03 15:17:34'),
(2, 'Studio Type A', 37.00, 'A105', 1, 18500.00, 50000.00, 'Resale', 'Long term', 4, 'Reserved', '2026-06-03 15:17:34'),
(3, 'Studio Type A', 37.00, 'A106', 1, 19000.00, NULL, 'For Lease', 'Long term', 5, 'Occupied', '2026-06-03 15:17:34'),
(4, 'Studio Type A', 37.00, 'A107', 2, 19500.00, NULL, 'For Lease', 'Long term', 6, 'Reserved', '2026-06-03 15:17:34'),
(5, 'Studio Type A', 37.00, 'A108', 2, 20000.00, NULL, 'For Lease', 'Long term', 8, 'Reserved', '2026-06-03 15:17:34'),
(6, 'Studio Type B', 40.65, 'B204', 1, 21000.00, NULL, 'For Lease', 'Long term', 2, 'Occupied', '2026-06-03 15:17:34'),
(7, 'Studio Type B', 40.65, 'B205', 1, 21500.00, NULL, 'Resale', 'Long term', 4, 'For Resale', '2026-06-03 15:17:34'),
(8, 'Studio Type B', 40.65, 'B206', 2, 22000.00, NULL, 'For Lease', 'Long term', 6, 'Reserved', '2026-06-03 15:17:34'),
(9, 'Studio Type B', 40.65, 'B207', 2, 22500.00, NULL, 'For Lease', 'Long term', 6, 'Ready for Occupancy', '2026-06-03 15:17:34'),
(10, 'Studio Type B', 40.65, 'B208', 3, 23000.00, NULL, 'Resale', 'Long term', 7, 'For Resale', '2026-06-03 15:17:34'),
(11, 'One Bedroom', 75.64, 'C204', 3, 28000.00, NULL, 'For Lease', 'Long term', 2, 'Reserved', '2026-06-03 15:17:34'),
(12, 'One Bedroom', 75.64, 'C205', 3, 28500.00, NULL, 'Not Available', 'Long term', 10, 'Ready for Occupancy', '2026-06-03 15:17:34'),
(13, 'One Bedroom', 75.64, 'C206', 3, 30000.00, NULL, 'For Lease', 'Long term', 5, 'Ready for Occupancy', '2026-06-03 15:17:34'),
(14, 'One Bedroom', 75.64, 'C207', 4, 31000.00, NULL, 'For Lease', 'Long term', 6, 'Ready for Occupancy', '2026-06-03 15:17:34'),
(15, 'One Bedroom', 75.64, 'C208', 4, 32000.00, NULL, 'Resale', 'Long term', 7, 'For Resale', '2026-06-03 15:17:34'),
(16, 'Two Bedroom', 113.00, 'D304', 4, 42000.00, NULL, 'For Lease', 'Long term', 2, 'Ready for Occupancy', '2026-06-03 15:17:34'),
(17, 'Two Bedroom', 113.00, 'D305', 4, 43500.00, NULL, 'Resale', 'Long term', 4, 'For Resale', '2026-06-03 15:17:34'),
(18, 'Two Bedroom', 113.00, 'D306', 4, 45000.00, NULL, 'For Lease', 'Long term', 5, 'Ready for Occupancy', '2026-06-03 15:17:34'),
(19, 'Two Bedroom', 113.00, 'D307', 5, 47000.00, NULL, 'For Lease', 'Long term', 6, 'Reserved', '2026-06-03 15:17:34'),
(20, 'Two Bedroom', 113.00, 'D308', 5, 49000.00, NULL, 'Not Available', 'Long term', 7, 'On Hold', '2026-06-03 15:17:34'),
(21, 'One Bedroom', 75.64, 'C209', 5, 38000.00, NULL, 'Resale', 'Long term', 4, 'For Resale', '2026-06-03 15:18:50');

-- --------------------------------------------------------

--
-- Table structure for table `unit_blocked_dates_table`
--

CREATE TABLE `unit_blocked_dates_table` (
  `block_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `block_type` enum('Not Available','Maintenance') NOT NULL DEFAULT 'Not Available',
  `remarks` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_by_role` enum('admin','unit owner') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit_blocked_dates_table`
--

INSERT INTO `unit_blocked_dates_table` (`block_id`, `unit_id`, `start_date`, `end_date`, `block_type`, `remarks`, `created_by_user_id`, `created_by_role`, `created_at`) VALUES
(1, 6, '2026-09-01', '2026-09-07', 'Not Available', 'testing', 1, 'admin', '2026-09-06 15:48:08'),
(4, 6, '2026-10-10', '2026-10-13', 'Not Available', NULL, 1, 'admin', '2026-10-09 08:59:39'),
(5, 2, '2026-10-16', '2026-10-18', 'Not Available', NULL, 1, 'admin', '2026-10-15 16:01:51'),
(6, 8, '2026-10-16', '2026-10-19', 'Maintenance', NULL, 1, 'admin', '2026-10-16 07:34:29'),
(9, 9, '2026-11-01', '2026-11-11', 'Maintenance', 'Auditing block', 1, 'admin', '2026-10-22 09:55:14'),
(10, 9, '2026-11-01', '2026-11-11', 'Maintenance', 'Auditing block', 1, 'admin', '2026-10-22 09:55:48');

-- --------------------------------------------------------

--
-- Table structure for table `unit_ownership_history_table`
--

CREATE TABLE `unit_ownership_history_table` (
  `history_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `ownership_status` enum('active','transferred','past') NOT NULL DEFAULT 'active',
  `transfer_type` varchar(100) DEFAULT 'Initial Assignment',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit_ownership_history_table`
--

INSERT INTO `unit_ownership_history_table` (`history_id`, `unit_id`, `owner_id`, `start_date`, `end_date`, `ownership_status`, `transfer_type`, `remarks`, `created_at`) VALUES
(1, 1, 2, '2026-06-03', '2026-10-15', 'transferred', 'Current Owner', 'Backfilled active unit owner [Transferred on 2026-10-15]', '2026-09-05 15:53:15'),
(2, 2, 4, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(3, 3, 5, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(4, 4, 6, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(5, 5, 8, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(6, 6, 2, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(7, 7, 4, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(8, 8, 6, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(9, 9, 6, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(10, 10, 7, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(11, 11, 2, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(12, 12, 4, '2026-06-03', '2026-10-08', 'transferred', 'Current Owner', 'Backfilled active unit owner [Transferred on 2026-10-08]', '2026-09-05 15:53:15'),
(13, 13, 5, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(14, 14, 6, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(15, 15, 7, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(16, 16, 2, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(17, 17, 4, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(18, 18, 5, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(19, 19, 6, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(20, 20, 7, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(21, 21, 4, '2026-06-03', NULL, 'active', 'Current Owner', 'Backfilled active unit owner', '2026-09-05 15:53:15'),
(22, 12, 10, '2026-10-08', NULL, 'active', 'Resale', 'Ownership transferred upon completed resale', '2026-10-09 05:54:22'),
(23, 1, 7, '2026-10-15', NULL, 'active', '', 'Ownership updated by Administrator', '2026-10-15 16:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `users_table`
--

CREATE TABLE `users_table` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `contact` varchar(30) DEFAULT NULL,
  `additional_email` varchar(100) DEFAULT NULL,
  `user_role` enum('admin','unit owner','tenant','superadmin') NOT NULL DEFAULT 'tenant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resident_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `gcash_QR` text DEFAULT NULL,
  `reset_code_hash` varchar(255) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `reset_attempts` int(11) NOT NULL DEFAULT 0,
  `reset_requested_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_table`
--

INSERT INTO `users_table` (`user_id`, `full_name`, `email`, `password`, `contact`, `additional_email`, `user_role`, `created_at`, `resident_status`, `gcash_QR`, `reset_code_hash`, `reset_expires_at`, `reset_attempts`, `reset_requested_at`) VALUES
(1, 'Admin User', 'admin@gmail.com', '$2y$10$lueRsg3lvmlxd6P4qkVi7OM7lr4ESCfo0TwC/0TZ1J6rjiQlzaWrG', '0922384834834848834843', 'maddysilvano@gmail.com', 'admin', '2026-06-03 15:08:44', 'Active', NULL, NULL, NULL, 0, NULL),
(2, 'Unit Owner User', 'owner@gmail.com', '$2y$10$vF.PS79ebAsXug6KckhOXOsuGhm/tVcFzllry.mpKobdDSd6H1JZC', '09222222222', NULL, 'unit owner', '2026-06-03 15:08:44', 'Active', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/payment_qr/qr_owner_2_1791793874_e1500dd6.jpg', NULL, NULL, 0, NULL),
(3, 'Tenant User', 'tenant@gmail.com', '$2y$10$Dj5SN0SCSwJbKqUBhtCgAOuCF2o6fM5to3hnIwcPp1GdEUYZvQ5De', '09333333333', NULL, 'tenant', '2026-06-03 15:08:44', 'Active', NULL, NULL, NULL, 0, NULL),
(4, 'Soren Lia', 'soren@gmail.com', '$2y$10$3DkUoCLN4kZZXq2MWmTluuAwthjWl6tXM6BagYmdNcOsxTO0jLyFu', '0997562819', NULL, 'unit owner', '2026-06-03 15:18:50', 'Active', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/payment_qr/qr_owner_4_1791787624_c2133f47.jpg', NULL, NULL, 0, NULL),
(5, 'Mika Reyes', 'mika.owner@gmail.com', '$2y$10$cj24SOxF2ufWpR49rG37auKxxtFA16X1rQj5RASfr.RAsVrFHrt9i', '09111111111', NULL, 'unit owner', '2026-06-07 16:10:11', 'Active', 'images/payment_qr/gcash_qr_5_1791465903_194cd3d9.png', NULL, NULL, 0, NULL),
(6, 'Carlos Santos', 'carlos.owner@gmail.com', '$2y$10$sHLZvBi5Xnx4Vmktb9gIhOktRPHpLnUjTXpiiQzC9unGH4PNOnZj.', '09222222222', NULL, 'unit owner', '2026-06-07 16:10:11', 'Active', NULL, NULL, NULL, 0, NULL),
(7, 'Alyssa Cruz', 'alyssa.owner@gmail.com', '$2y$10$VD26QufESc7nhgvSdjz7O.e2Mg3pR.trMIgTpbYxCc4KaUJmBBnpG', '09333333333', NULL, 'unit owner', '2026-06-07 16:10:11', 'Active', 'https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/payment_qr/qr_owner_7_1792103699_50e534c0.jpg', NULL, NULL, 0, NULL),
(8, 'Shanielle Gajo', 'gajoshanielle@gmail.com', '$2y$10$COtOCVeU9hPvtgAVcHbwDe9OSfKjZDH9Uep0oIrkL51TUfII8AzS6', '09707568287', NULL, 'unit owner', '2026-07-19 06:12:05', 'Active', NULL, NULL, NULL, 0, NULL),
(10, 'maddy silvano', 'maddysilvano@gmail.com', '$2y$10$S12Iktdy5M/WBj5scbgVL.YdklGklU7Xn6h6dhPsPEuLtEL70NGSa', '', NULL, 'unit owner', '2026-10-08 19:07:07', 'Active', NULL, NULL, NULL, 0, NULL),
(15, 'Secret Admin', 'secretadmin@gmail.com', '$2y$10$eniI2fCpZZXbzqOqxZeHW.7wcMF5FSGhayzwas8vanb7SurAgzvLO', '09123456789', NULL, 'superadmin', '2026-10-22 05:50:11', 'Active', NULL, NULL, NULL, 0, NULL),
(16, 'Elena Rostova', 'elena.tenant@gmail.com', '$2y$10$OmB30Uag3m9YccdRCgnshOkIsoVXJAXoEowjWqdPd2Ir/ECSVe4B2', '09175554321', 'elena.work@gmail.com', 'tenant', '2026-10-22 06:27:40', 'Active', NULL, NULL, NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inquiry_table`
--
ALTER TABLE `inquiry_table`
  ADD PRIMARY KEY (`inq_id`),
  ADD KEY `fk_inquiry_approved_unit` (`approved_unit_id`);

--
-- Indexes for table `maintenance_table`
--
ALTER TABLE `maintenance_table`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `unit_owner_id` (`unit_owner_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `status` (`status`),
  ADD KEY `fk_maintenance_submitter` (`submitted_by_user_id`);

--
-- Indexes for table `owner_approval_table`
--
ALTER TABLE `owner_approval_table`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_owner_request_inquiry` (`inq_id`),
  ADD KEY `fk_owner_request_unit` (`unit_id`);

--
-- Indexes for table `reservation_documents_table`
--
ALTER TABLE `reservation_documents_table`
  ADD PRIMARY KEY (`document_id`),
  ADD UNIQUE KEY `uniq_reservation_doc` (`reservation_id`,`document_key`),
  ADD KEY `idx_reservation_id` (`reservation_id`),
  ADD KEY `idx_resdoc_updated_by` (`updated_by`);

--
-- Indexes for table `reservation_table`
--
ALTER TABLE `reservation_table`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_reservation_inquiry` (`inq_id`),
  ADD KEY `fk_reservation_unit` (`unit_id`);

--
-- Indexes for table `units_table`
--
ALTER TABLE `units_table`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `unit_number` (`unit_number`),
  ADD KEY `fk_units_owner` (`unit_owner_id`);

--
-- Indexes for table `unit_blocked_dates_table`
--
ALTER TABLE `unit_blocked_dates_table`
  ADD PRIMARY KEY (`block_id`),
  ADD KEY `idx_unit_dates` (`unit_id`,`start_date`,`end_date`);

--
-- Indexes for table `unit_ownership_history_table`
--
ALTER TABLE `unit_ownership_history_table`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `users_table`
--
ALTER TABLE `users_table`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inquiry_table`
--
ALTER TABLE `inquiry_table`
  MODIFY `inq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `maintenance_table`
--
ALTER TABLE `maintenance_table`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `owner_approval_table`
--
ALTER TABLE `owner_approval_table`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `reservation_documents_table`
--
ALTER TABLE `reservation_documents_table`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `reservation_table`
--
ALTER TABLE `reservation_table`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `units_table`
--
ALTER TABLE `units_table`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `unit_blocked_dates_table`
--
ALTER TABLE `unit_blocked_dates_table`
  MODIFY `block_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `unit_ownership_history_table`
--
ALTER TABLE `unit_ownership_history_table`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users_table`
--
ALTER TABLE `users_table`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inquiry_table`
--
ALTER TABLE `inquiry_table`
  ADD CONSTRAINT `fk_inquiry_approved_unit` FOREIGN KEY (`approved_unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `maintenance_table`
--
ALTER TABLE `maintenance_table`
  ADD CONSTRAINT `fk_maintenance_owner` FOREIGN KEY (`unit_owner_id`) REFERENCES `users_table` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_submitter` FOREIGN KEY (`submitted_by_user_id`) REFERENCES `users_table` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_unit` FOREIGN KEY (`unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `owner_approval_table`
--
ALTER TABLE `owner_approval_table`
  ADD CONSTRAINT `fk_owner_request_inquiry` FOREIGN KEY (`inq_id`) REFERENCES `inquiry_table` (`inq_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_owner_request_unit` FOREIGN KEY (`unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reservation_documents_table`
--
ALTER TABLE `reservation_documents_table`
  ADD CONSTRAINT `fk_resdoc_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservation_table` (`reservation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resdoc_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users_table` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `reservation_table`
--
ALTER TABLE `reservation_table`
  ADD CONSTRAINT `fk_reservation_inquiry` FOREIGN KEY (`inq_id`) REFERENCES `inquiry_table` (`inq_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reservation_unit` FOREIGN KEY (`unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `units_table`
--
ALTER TABLE `units_table`
  ADD CONSTRAINT `fk_units_owner` FOREIGN KEY (`unit_owner_id`) REFERENCES `users_table` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `unit_blocked_dates_table`
--
ALTER TABLE `unit_blocked_dates_table`
  ADD CONSTRAINT `fk_blocked_unit` FOREIGN KEY (`unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Triggers
CREATE TRIGGER `trg_prevent_reservation_overlap_before_insert` BEFORE INSERT ON `reservation_table` FOR EACH ROW BEGIN
    DECLARE overlap_count INT DEFAULT 0;

    IF NEW.reservation_status NOT IN ('cancelled', 'rejected')
       AND NEW.move_in_date IS NOT NULL
       AND (
           NEW.transaction_type IN ('Unit Leasing', 'Lease Inquiry', 'Unit Reservation', 'lease', 'Lease')
           OR NEW.transaction_type LIKE '%Leas%'
           OR NEW.transaction_type LIKE '%Rental%'
           OR NEW.transaction_type IS NULL
           OR NEW.transaction_type = ''
       ) THEN

        SELECT COUNT(*) INTO overlap_count
        FROM reservation_table
        WHERE unit_id = NEW.unit_id
          AND reservation_status NOT IN ('cancelled', 'rejected')
          AND (
              transaction_type IN ('Unit Leasing', 'Lease Inquiry', 'Unit Reservation', 'lease', 'Lease')
              OR transaction_type LIKE '%Leas%'
              OR transaction_type LIKE '%Rental%'
              OR transaction_type IS NULL
              OR transaction_type = ''
          )
          AND move_in_date IS NOT NULL
          AND move_in_date <= COALESCE(NEW.move_out_date, NEW.move_in_date)
          AND COALESCE(move_out_date, move_in_date) >= NEW.move_in_date;

        IF overlap_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: An active reservation already exists for this unit during the specified date range.';
        END IF;
    END IF;
END;

CREATE TRIGGER `trg_prevent_reservation_overlap_before_update` BEFORE UPDATE ON `reservation_table` FOR EACH ROW BEGIN
    DECLARE overlap_count INT DEFAULT 0;

    IF NEW.reservation_status NOT IN ('cancelled', 'rejected')
       AND NEW.move_in_date IS NOT NULL
       AND (
           NEW.transaction_type IN ('Unit Leasing', 'Lease Inquiry', 'Unit Reservation', 'lease', 'Lease')
           OR NEW.transaction_type LIKE '%Leas%'
           OR NEW.transaction_type LIKE '%Rental%'
           OR NEW.transaction_type IS NULL
           OR NEW.transaction_type = ''
       ) THEN

        SELECT COUNT(*) INTO overlap_count
        FROM reservation_table
        WHERE unit_id = NEW.unit_id
          AND reservation_id != NEW.reservation_id
          AND reservation_status NOT IN ('cancelled', 'rejected')
          AND (
              transaction_type IN ('Unit Leasing', 'Lease Inquiry', 'Unit Reservation', 'lease', 'Lease')
              OR transaction_type LIKE '%Leas%'
              OR transaction_type LIKE '%Rental%'
              OR transaction_type IS NULL
              OR transaction_type = ''
          )
          AND move_in_date IS NOT NULL
          AND move_in_date <= COALESCE(NEW.move_out_date, NEW.move_in_date)
          AND COALESCE(move_out_date, move_in_date) >= NEW.move_in_date;

        IF overlap_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: An active reservation already exists for this unit during the specified date range.';
        END IF;
    END IF;
END;

SET FOREIGN_KEY_CHECKS = 1;
