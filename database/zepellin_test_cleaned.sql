-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: zepellin_test
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `zepellin_test`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `zepellin_test` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `zepellin_test`;

--
-- Table structure for table `inquiry_table`
--

DROP TABLE IF EXISTS `inquiry_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inquiry_table` (
  `inq_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `reservation_token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`inq_id`),
  KEY `fk_inquiry_approved_unit` (`approved_unit_id`),
  CONSTRAINT `fk_inquiry_approved_unit` FOREIGN KEY (`approved_unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inquiry_table`
--

LOCK TABLES `inquiry_table` WRITE;
/*!40000 ALTER TABLE `inquiry_table` DISABLE KEYS */;
INSERT INTO `inquiry_table` VALUES (66,'aug 10-','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Immediately (Within 30 days)','3 months','dsdsd','reservation submitted','approved','2026-08-11 00:20:19',1,NULL,'2026-08-11 00:21:29','2026-08-10 15:53:45','89ed5a8b09bb7c0cfa592b9dadd83ec6312ace9bff9d24571db81fef66de8434','2026-09-09 18:21:29','2026-08-11 00:21:55'),(67,'nov 13','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','In 3-6 Months','3 months','sddds','pending','not_requested',NULL,NULL,NULL,NULL,'2026-08-10 17:23:42',NULL,NULL,NULL),(68,'nov 13','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Flexible / Not sure yet','3 months','dcfdf','responded','approved','2026-08-11 01:42:32',1,NULL,'2026-08-11 01:42:48','2026-08-10 17:24:56','945bf48044421efdc124ddef0923dd622ecd49c8a99b4788f904ab77d8259449','2026-09-09 19:42:48','2026-08-11 01:57:40'),(69,'Shanielle Manalac Gajo','maddysilvano@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','Studio Type A',NULL,NULL,'testing resale','pending','approved','2026-08-23 21:04:41',NULL,'okay','2026-10-08 20:15:57','2026-08-23 12:39:31','89e32ca880c1b53f62a3df352ba94fac9394ab98834c826a571d3e5800bcf857','2026-11-07 14:15:57',NULL),(70,'testing unit reservation','gajoshanielle@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Immediately (Within 30 days)','3 months','testing unit reservation','officially booked','approved','2026-08-23 21:12:05',1,NULL,'2026-08-25 22:58:31','2026-08-23 13:10:29','09969356af7ccacab87e54715e848af5b96658f1fedfed434ea7ffef394d1189','2026-09-24 16:58:31','2026-08-26 00:34:30'),(71,'Shanielle Manalac Gajo','delarosa2@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Immediately (Within 30 days)','3 months','august 26-nov 26','officially booked','approved','2026-08-26 01:00:19',3,NULL,'2026-08-26 01:00:45','2026-08-25 16:56:05','79471827d3397039d648b4ff0dcec351ba5497ec5983f0fe0f98e12e936bba81','2026-09-24 19:00:45','2026-08-26 01:01:40'),(72,'Shanielle Manalac Gajo','maddysilvano@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','Two Bedroom',NULL,NULL,'resale testing','responded','approved','2026-09-01 11:19:16',20,NULL,'2026-09-01 11:45:54','2026-09-01 03:01:58','dc10c28f085b4dda69fb05ae9cd11599f94eaac4fd7af5307a23eb1c352ce561','2026-10-01 05:45:54','2026-09-01 11:50:11'),(73,'testing reservation payment','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Immediately (Within 30 days)','3 months','testing testing','pending','approved','2026-09-03 19:54:11',4,'N/A','2026-09-06 16:46:04','2026-09-03 11:51:22','8742d0e40439aac20439e319f5511aba6eced0c7be51a6866d079a7245ff567b','2026-10-06 10:46:04',NULL),(74,'testing another reservation','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Immediately (Within 30 days)','2 years','testing testing','responded','approved','2026-09-04 17:58:41',4,'testing testing','2026-09-04 18:04:30','2026-09-04 09:58:09','531ad88243c8e76c045b591f116268023d9e46bd3e5b1fb3cefde3027085b904','2026-10-04 12:04:30','2026-09-04 18:05:42'),(75,'testing testing v4','gajoshanielle@gmail.com','09707568287','Unit Lease / Rental Reservation','One Bedroom','Immediately (Within 30 days)','6 months','testinggggggggggggg','officially booked','approved','2026-09-06 17:04:14',11,'okay','2026-09-06 17:09:37','2026-09-06 09:03:39','5a7cede040a82b44d72de94503006c41d5dcce8bc252672778f23563004c1da6','2026-10-06 11:09:37','2026-09-06 17:21:25'),(76,'Shanielle Manalac Gajo','maddysilvano@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','Two Bedroom',NULL,NULL,'TESTING TESTING','pending','requested','2026-09-07 00:06:27',NULL,NULL,NULL,'2026-09-06 16:06:07',NULL,NULL,NULL),(79,'GCash Applicant','gcashapplicant@example.com','09181234567','Unit Lease / Rental Reservation',NULL,'Immediately (Within 30 days)','1 year','','pending','approved',NULL,11,NULL,NULL,'2026-09-06 16:17:31','test_token_07f34322b0bb7b472622ca31e8064a65','2026-10-06 18:17:31',NULL),(86,'Maria Santos (Test)','mariasantos.test@gmail.com','09175551234','Unit Lease / Rental Reservation','Studio Type A','Immediately (Within 30 days)','6 months','Testing lease reservation: 6 months immediate move-in, fixed signing date, short payment.','reservation submitted','approved',NULL,4,NULL,'2026-10-06 05:14:47','2026-10-05 21:14:47','add87b2fb7f980fd2e91a9e3055cfe9ed4f1a9cb664916ed36c8c9dee17c764b','2026-11-07 13:06:31',NULL),(87,'testing 6-10-2026','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Next Month (1-2 months)','1 year','testing','responded','approved','2026-10-06 05:31:05',1,'','2026-10-06 05:34:35','2026-10-05 21:28:26','2d1e7864978f719b64a80587731ffbb492ead4cac508d616fa058b7be169ea93','2026-11-04 23:34:35','2026-10-06 06:00:18'),(88,'testing','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type B','In 3-6 Months','6 months','testing testing','pending','approved','2026-10-08 16:59:59',8,'','2026-10-08 17:23:48','2026-10-08 08:45:00','ef980f52b93ffa89d0ad114439a6bad67dbd6628446f733d5083875bffce9ba8','2026-11-07 12:49:12',NULL),(89,'tesitng','maddysilvano@gmail.com','09707568287','General Inquiry & Amenities',NULL,NULL,NULL,'sdsda','pending','not_requested',NULL,NULL,NULL,NULL,'2026-10-08 09:42:37',NULL,NULL,NULL),(90,'testing reservation 2.1','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type B','Next Month (1-2 months)','1 year','sdsda','pending','not_requested',NULL,NULL,NULL,NULL,'2026-10-08 10:00:28',NULL,NULL,NULL),(93,'testing resale','maddysilvano@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','One Bedroom',NULL,NULL,'testing testing','officially booked','approved','2026-10-08 20:19:20',12,'','2026-10-08 20:20:00','2026-10-08 11:33:46','a414aedaa8e8817c9989d9725f4e9369c33458553e711e1e55aefdd0ea8095d0','2026-11-07 14:20:00','2026-10-08 20:21:57'),(94,'Franz Delastelle','franzdelastelle17@gmail.com','09707568287','Unit Lease / Rental Reservation','Two Bedroom','Flexible / Not sure yet','Not sure yet','testing Unit lease','pending','declined','2026-10-09 15:35:41',NULL,'',NULL,'2026-10-09 07:29:28',NULL,NULL,NULL),(95,'gajo shanielle','gajoshanielle@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','Two Bedroom',NULL,NULL,'testing unit resale','pending','approved','2026-10-09 15:31:19',NULL,'no','2026-10-09 15:32:56','2026-10-09 07:30:50','ce793c90191994780cc0800605fc85553271dd434718e76627d162418e31cc88','2026-11-08 09:32:56',NULL),(96,'maddy Silvano','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Next Month (1-2 months)','2 years','testing','declined','declined','2026-10-09 15:50:24',NULL,'',NULL,'2026-10-09 07:50:08',NULL,NULL,NULL),(97,'unit resale decline','maddysilvano@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','Two Bedroom',NULL,NULL,'testing decline','pending','approved','2026-10-09 16:09:05',NULL,'','2026-10-09 16:11:13','2026-10-09 07:59:38','bab503c06d144a0e2ae4ba5a01aab0fa965989a9e54b0e4517d17306c643a64d','2026-11-08 10:11:13',NULL),(98,'maddy Silvano','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Immediately (Within 30 days)','3 months','testing','responded','approved','2026-10-09 17:06:17',NULL,'','2026-10-09 17:08:01','2026-10-09 09:04:23','8ad4621975cde6ba078cb162e491b036d9cf1f799c72829d6e7cb4b626663f23','2026-11-08 11:08:01','2026-10-09 17:09:46'),(99,'testing resale 12-10-2026','maddysilvano@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','Studio Type B',NULL,NULL,'heheye','reservation submitted','approved','2026-10-12 11:07:10',10,'nothing','2026-10-12 11:08:43','2026-10-12 03:05:06','dc36ad2cb74a79f17d32612cd47a428c427241aa73377422057151a0702e5c14','2026-11-11 05:08:43','2026-10-12 11:13:52'),(100,'testing FCFS','franzdelastelle17@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','Studio Type B',NULL,NULL,'testing testing','reservation submitted','approved','2026-10-12 11:17:50',7,'','2026-10-12 11:23:40','2026-10-12 03:17:35','6e3e1e1d1bea918b83490c1b80d6c627247dd7693a8362db8aeae9ba7be6ca54','2026-11-11 05:23:40','2026-10-12 11:24:06'),(101,'testing payment proof','maddysilvano@gmail.com','09707568287','Buy / Purchase a Unit (Resale)','Studio Type A',NULL,NULL,'sdsad','reservation submitted','approved','2026-10-12 13:58:08',2,'','2026-10-12 13:58:28','2026-10-12 05:57:58','9668dd6a68a454cf953e6302a45f8f02b8e1e3c2085e8f0e23c977afbad9c51b','2026-11-11 07:58:28','2026-10-12 13:58:50'),(102,'maddy Silvano','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Immediately (Within 30 days)','6 months','testing testing','pending','not_requested',NULL,NULL,NULL,NULL,'2026-10-12 07:42:28',NULL,NULL,NULL),(103,'maddy Silvano','maddysilvano@gmail.com','09707568287','Unit Lease / Rental Reservation','Studio Type A','Next Month (1-2 months)','6 months','testing','responded','approved','2026-10-12 16:19:43',NULL,'','2026-10-12 16:21:05','2026-10-12 08:18:47','b5a59064e77991ce1d6ed13444ef3efb51a11531fef849f4b5c2bb9334c6eb5e','2026-11-11 10:21:05','2026-10-12 16:23:27');
/*!40000 ALTER TABLE `inquiry_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_table`
--

DROP TABLE IF EXISTS `maintenance_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_table` (
  `maintenance_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `admin_remarks` text DEFAULT NULL,
  PRIMARY KEY (`maintenance_id`),
  KEY `unit_owner_id` (`unit_owner_id`),
  KEY `unit_id` (`unit_id`),
  KEY `status` (`status`),
  KEY `fk_maintenance_submitter` (`submitted_by_user_id`),
  CONSTRAINT `fk_maintenance_owner` FOREIGN KEY (`unit_owner_id`) REFERENCES `users_table` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_maintenance_submitter` FOREIGN KEY (`submitted_by_user_id`) REFERENCES `users_table` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_maintenance_unit` FOREIGN KEY (`unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_table`
--

LOCK TABLES `maintenance_table` WRITE;
/*!40000 ALTER TABLE `maintenance_table` DISABLE KEYS */;
INSERT INTO `maintenance_table` VALUES (1,2,'unit owner',2,1,'Leaking faucet','Plumbing','The faucet in the bathroom sink is continuously leaking even when fully closed. Water is dripping under the sink area and causing the cabinet surface to become wet. Please inspect the faucet, valve, and pipe connection as soon as possible.','normal','pending','[\"uploads\\/maintenance\\/maintenance_1780907097_7a53ee9d9ceed821.jpg\"]','2026-06-08 16:24:57','2026-10-12 12:38:56',NULL,'okay'),(2,2,'unit owner',2,1,'Leaking faucet','Plumbing','sjdsdklsakdmasd','normal','resolved','[\"uploads\\/maintenance\\/maintenance_1782307562_c41091ceb8343aee.jpg\"]','2026-06-24 21:26:02','2026-07-13 19:37:37','2026-07-13 19:37:37','Hello! We have spoken to the plumber; they will arrive tomorrow. :)'),(3,7,'unit owner',7,10,'cleaning','Cleaning','hello need cleaning by 4 pm','low','resolved',NULL,'2026-10-12 12:38:38','2026-10-12 12:40:08','2026-10-12 12:40:08','okay'),(4,4,'unit owner',4,2,'ZSIRAAAAA','Plumbing','sira gripo','normal','pending','images/maintenance/maintenance_own_1791790754_592a5a78f80a937f.jpg','2026-10-12 15:39:14','2026-10-12 15:39:57',NULL,'');
/*!40000 ALTER TABLE `maintenance_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `owner_approval_table`
--

DROP TABLE IF EXISTS `owner_approval_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `owner_approval_table` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `inq_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `unit_owner_id` int(11) NOT NULL,
  `request_status` enum('pending','approved','declined','expired') NOT NULL DEFAULT 'pending',
  `owner_remarks` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `fk_owner_request_inquiry` (`inq_id`),
  KEY `fk_owner_request_unit` (`unit_id`),
  CONSTRAINT `fk_owner_request_inquiry` FOREIGN KEY (`inq_id`) REFERENCES `inquiry_table` (`inq_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_owner_request_unit` FOREIGN KEY (`unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `owner_approval_table`
--

LOCK TABLES `owner_approval_table` WRITE;
/*!40000 ALTER TABLE `owner_approval_table` DISABLE KEYS */;
INSERT INTO `owner_approval_table` VALUES (102,66,3,5,'declined',NULL,'2026-08-10 16:17:11','2026-08-11 00:17:30'),(103,66,1,2,'approved',NULL,'2026-08-10 16:20:19','2026-08-11 00:21:29'),(104,68,1,2,'approved',NULL,'2026-08-10 17:42:32','2026-08-11 01:42:48'),(105,69,2,4,'approved','okay','2026-08-23 13:04:41','2026-10-08 20:15:57'),(106,70,1,2,'approved',NULL,'2026-08-23 13:12:05','2026-08-25 22:58:31'),(107,71,5,8,'expired',NULL,'2026-08-25 16:59:33','2026-08-26 01:00:45'),(108,71,3,5,'approved',NULL,'2026-08-25 17:00:19','2026-08-26 01:00:45'),(110,72,20,7,'approved',NULL,'2026-09-01 03:19:16','2026-09-01 11:45:54'),(111,73,4,6,'approved','N/A','2026-09-03 11:54:11','2026-09-06 16:46:04'),(112,74,4,6,'approved','testing testing','2026-09-04 09:58:41','2026-09-04 18:04:30'),(113,75,11,2,'approved','okay','2026-09-06 09:04:14','2026-09-06 17:09:37'),(114,76,20,7,'pending',NULL,'2026-09-06 16:06:27',NULL),(115,87,1,2,'approved','','2026-10-05 21:31:05','2026-10-06 05:34:35'),(116,87,3,5,'declined','I declined','2026-10-05 21:31:05','2026-10-06 05:33:43'),(117,88,6,2,'declined','','2026-10-08 08:58:35','2026-10-08 17:23:23'),(118,88,8,6,'approved','','2026-10-08 08:59:59','2026-10-08 17:23:48'),(119,88,9,6,'declined','','2026-10-08 08:59:59','2026-10-08 17:23:41'),(125,93,12,4,'approved','','2026-10-08 12:19:20','2026-10-08 20:20:00'),(126,93,15,7,'approved','','2026-10-08 12:19:20','2026-10-08 20:20:32'),(127,93,21,4,'approved','','2026-10-08 12:19:20','2026-10-08 20:20:03'),(129,95,20,7,'approved','no','2026-10-09 07:31:19','2026-10-09 15:32:56'),(130,94,16,2,'declined','','2026-10-09 07:35:41','2026-10-09 15:36:05'),(131,96,1,2,'declined','','2026-10-09 07:50:24','2026-10-09 15:50:48'),(132,97,17,4,'declined','','2026-10-09 07:59:49','2026-10-09 16:00:11'),(133,97,17,4,'approved','','2026-10-09 08:09:05','2026-10-09 16:11:13'),(134,97,20,7,'pending',NULL,'2026-10-09 08:09:05',NULL),(135,98,1,2,'approved','','2026-10-09 09:06:17','2026-10-09 17:08:01'),(136,98,3,5,'approved','','2026-10-09 09:06:17','2026-10-09 17:08:54'),(137,98,5,8,'pending',NULL,'2026-10-09 09:06:17',NULL),(138,99,7,4,'declined','','2026-10-12 03:07:10','2026-10-12 11:09:22'),(139,99,10,7,'approved','nothing','2026-10-12 03:07:10','2026-10-12 11:08:43'),(140,100,7,4,'approved','','2026-10-12 03:17:50','2026-10-12 11:23:40'),(141,100,10,7,'approved','','2026-10-12 03:17:50','2026-10-12 11:23:50'),(142,101,2,4,'approved','','2026-10-12 05:58:08','2026-10-12 13:58:28'),(144,103,1,2,'approved','','2026-10-12 08:19:43','2026-10-12 16:21:05'),(145,103,3,5,'approved','','2026-10-12 08:19:43','2026-10-12 16:21:42'),(146,103,5,8,'pending',NULL,'2026-10-12 08:19:43',NULL);
/*!40000 ALTER TABLE `owner_approval_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_documents_table`
--

DROP TABLE IF EXISTS `reservation_documents_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation_documents_table` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `document_key` varchar(50) NOT NULL COMMENT 'stable identifier, e.g. valid_id_1, tin_number',
  `document_name` varchar(150) NOT NULL COMMENT 'display label, e.g. Valid ID #1',
  `status` enum('pending','complete') NOT NULL DEFAULT 'pending',
  `document_link` varchar(500) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`document_id`),
  UNIQUE KEY `uniq_reservation_doc` (`reservation_id`,`document_key`),
  KEY `idx_reservation_id` (`reservation_id`),
  KEY `idx_resdoc_updated_by` (`updated_by`),
  CONSTRAINT `fk_resdoc_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservation_table` (`reservation_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_resdoc_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users_table` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_documents_table`
--

LOCK TABLES `reservation_documents_table` WRITE;
/*!40000 ALTER TABLE `reservation_documents_table` DISABLE KEYS */;
INSERT INTO `reservation_documents_table` VALUES (73,27,'valid_id_1','Valid ID #1','complete','https://dropbox.com/test/valid_id_1',2,'2026-10-08 19:18:47'),(74,27,'valid_id_2','Valid ID #2','complete','https://dropbox.com/test/valid_id_2',2,'2026-10-08 19:18:47'),(75,27,'tin_number','TIN Number','complete','https://dropbox.com/test/tin_number',2,'2026-10-08 19:18:47'),(76,27,'reservation_agreement','Reservation Agreement','complete','https://dropbox.com/test/reservation_agreement',2,'2026-10-08 19:18:47'),(77,28,'valid_id_1','Valid ID #1','complete',NULL,1,'2026-08-26 01:03:19'),(78,28,'valid_id_2','Valid ID #2','complete',NULL,1,'2026-08-26 01:03:19'),(79,28,'tin_number','TIN Number','complete',NULL,1,'2026-08-26 01:03:19'),(80,28,'reservation_agreement','Reservation Agreement','complete',NULL,1,'2026-08-26 01:03:19'),(81,32,'valid_id_1','Valid ID #1','complete','sdsda',2,'2026-09-07 00:55:31'),(82,32,'valid_id_2','Valid ID #2','complete','sds',2,'2026-09-07 00:55:31'),(83,32,'tin_number','TIN Number','complete','sds',2,'2026-09-07 00:55:31'),(84,32,'reservation_agreement','Reservation Agreement','complete','sds',2,'2026-09-07 00:55:31'),(129,33,'valid_id_1','Valid ID #1','complete','http://localhost/Zeppelin-Suites/owner/reservations/view?reservation_id=33#documents',6,'2026-10-09 11:23:51'),(130,33,'valid_id_2','Valid ID #2','complete','http://localhost/Zeppelin-Suites/owner/reservations/view?reservation_id=33#documents',6,'2026-10-09 11:23:51'),(131,33,'tin_number','TIN Number','complete','http://localhost/Zeppelin-Suites/owner/reservations/view?reservation_id=33#documents',6,'2026-10-09 11:23:51'),(132,33,'reservation_agreement','Reservation Agreement','complete','http://localhost/Zeppelin-Suites/owner/reservations/view?reservation_id=33#documents',6,'2026-10-09 11:23:51'),(149,35,'valid_id_1','Valid ID #1','complete','https://drive.google.com/file/d/1Opg6zpOvf4gKj2HDZkSabaeKXW9MBm40/view?usp=drive_link',4,'2026-10-08 20:16:13'),(150,35,'valid_id_2','Valid ID #2','complete','https://drive.google.com/file/d/1J-MfioI-k065Ef67mjQVA8CfpYv38lsG/view?usp=drive_link',4,'2026-10-08 20:16:13'),(151,35,'tin_number','TIN Number','complete','https://drive.google.com/file/d/1mc4EGOBc1b3Vyz1PL-O2UDuueRBNq8E1/view?usp=drive_link',4,'2026-10-08 20:16:13'),(152,35,'reservation_agreement','Reservation Agreement','complete','https://drive.google.com/file/d/1k7JrvWXN7L-bApHoHvOIcx2h6VMQ8xHz/view?usp=drive_link',4,'2026-10-08 20:16:13'),(153,36,'valid_id_1','Valid ID #1','pending',NULL,NULL,NULL),(154,36,'valid_id_2','Valid ID #2','pending',NULL,NULL,NULL),(155,36,'tin_number','TIN Number','pending',NULL,NULL,NULL),(156,36,'reservation_agreement','Reservation Agreement','pending',NULL,NULL,NULL),(157,37,'valid_id_1','Valid ID #1','pending',NULL,NULL,NULL),(158,37,'valid_id_2','Valid ID #2','pending',NULL,NULL,NULL),(159,37,'tin_number','TIN Number','pending',NULL,NULL,NULL),(160,37,'reservation_agreement','Reservation Agreement','pending',NULL,NULL,NULL),(161,38,'valid_id_1','Valid ID #1','complete','https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_38_1791790578_c1761814.pdf',1,'2026-10-12 09:36:18'),(162,38,'valid_id_2','Valid ID #2','complete','https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/valid_ids/doc_res_38_1791790587_8aa235c6.jpg',1,'2026-10-12 09:36:28'),(163,38,'tin_number','TIN Number','complete','https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/tin_number/doc_res_38_1791790602_5bcc2f3c.jpg',1,'2026-10-12 09:36:42'),(164,38,'reservation_agreement','Reservation Agreement','complete','https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/documents/agreement_forms/doc_res_38_1791790606_53626727.pdf',1,'2026-10-12 09:36:47');
/*!40000 ALTER TABLE `reservation_documents_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_table`
--

DROP TABLE IF EXISTS `reservation_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation_table` (
  `reservation_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `client_cancel_token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`reservation_id`),
  KEY `fk_reservation_inquiry` (`inq_id`),
  KEY `fk_reservation_unit` (`unit_id`),
  CONSTRAINT `fk_reservation_inquiry` FOREIGN KEY (`inq_id`) REFERENCES `inquiry_table` (`inq_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservation_unit` FOREIGN KEY (`unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_table`
--

LOCK TABLES `reservation_table` WRITE;
/*!40000 ALTER TABLE `reservation_table` DISABLE KEYS */;
INSERT INTO `reservation_table` VALUES (27,70,1,'testing unit reservation','gajoshanielle@gmail.com','09707568287',NULL,NULL,'Unit Leasing','2026-09-01','2026-11-01',NULL,0,NULL,18000.00,6300.00,'GCash QR','uploads/payment_proofs/payment_70_1787675927_cc72da2135a5.jpg','verified','reserved',NULL,'2026-08-25 16:38:47','2026-08-26 00:39:59','2026-08-26 00:41:46',NULL,NULL,NULL,NULL,'ebf904744b81d3c28530ac452384501188de7d4a420fa08b07fce2cc05aa071f','2026-09-24 18:39:59'),(28,71,3,'Shanielle Manalac Gajo','delarosa2@gmail.com','09707568287',NULL,NULL,'Unit Leasing','2026-08-26','2026-11-01',NULL,0,NULL,19000.00,19000.00,'GCash QR','uploads/payment_proofs/payment_71_1787677350_7ee3724941d3.jpg','verified','handover',NULL,'2026-08-25 17:02:30','2026-08-26 01:02:57','2026-08-26 01:03:26',NULL,NULL,NULL,NULL,'0b2f716d192c983fbea0b584bd0ae64cc2b8123330aa8fbe227341b28ee1df8f','2026-09-24 19:02:57'),(32,75,11,'testing testing v4','gajoshanielle@gmail.com','09707568287','Female','Filipino','Unit Leasing','2026-09-22','2027-03-22',NULL,1,'2026-09-06 18:55:10',28000.00,9800.00,'In-House',NULL,'verified','reserved','testing testing','2026-09-06 16:30:50','2026-09-07 00:54:53','2026-10-05 01:09:51',NULL,NULL,NULL,NULL,'d494ce817b646542019554291d0a014ecc7ada8b7fe72c07bee676ba4984baef','2026-10-06 18:54:53'),(33,86,4,'Maria Santos (Test)','mariasantos.test@gmail.com','09175551234','Female','Filipino','Unit Leasing','2026-10-07','2027-04-07','2026-10-10',0,'2026-10-09 11:22:27',19500.00,6825.00,'GCash QR','uploads/receipts/sample_gcash.png','verified','reserved',NULL,'2026-10-05 21:14:47','2026-10-09 11:21:56','2026-10-09 11:23:51',NULL,NULL,NULL,NULL,NULL,NULL),(35,93,12,'testing resale','maddysilvano@gmail.com','09707568287','Male','Filipino','Unit Resale',NULL,NULL,'2026-10-08',1,'2026-10-08 20:14:57',140000.00,49000.00,'GCash QR','images/payment_proofs/payment_93_1791472703_f7cc140e4fee.jpg','verified','handover','','2026-10-08 15:18:23','2026-10-08 20:14:23','2026-10-08 20:16:13',NULL,NULL,NULL,NULL,NULL,NULL),(36,99,10,'testing resale 12-10-2026','maddysilvano@gmail.com','09707568287','Female','Filipino','Unit Resale',NULL,NULL,NULL,1,NULL,23000.00,8050.00,'In-House',NULL,'pending review','cancelled','','2026-10-12 03:29:03',NULL,NULL,'2026-10-12 05:49:10','cant',7,'2026-10-12 05:44:46',NULL,NULL),(37,100,7,'testing FCFS','franzdelastelle17@gmail.com','09707568287','Male','Filipino','Unit Resale',NULL,NULL,NULL,1,NULL,21500.00,7525.00,'In-House',NULL,'pending review','cancelled','','2026-10-12 03:31:37',NULL,NULL,'2026-10-12 07:24:02','gyy',4,'2026-10-12 07:23:46',NULL,NULL),(38,101,2,'testing payment proof','maddysilvano@gmail.com','09707568287','Male','Filipino','Unit Resale',NULL,NULL,'2026-10-15, 2026-10-16, 2026-10-17',0,'2026-10-12 09:26:05',50000.00,37500.00,'GCash QR','https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/reservations/payment_proofs/payment_inq_101_1791789660_067d51a6.jpg','verified','reserved','','2026-10-12 07:21:00','2026-10-12 09:24:37','2026-10-12 09:39:31',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `reservation_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_blocked_dates_table`
--

DROP TABLE IF EXISTS `unit_blocked_dates_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_blocked_dates_table` (
  `block_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `block_type` enum('Not Available','Maintenance') NOT NULL DEFAULT 'Not Available',
  `remarks` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_by_role` enum('admin','unit owner') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`block_id`),
  KEY `idx_unit_dates` (`unit_id`,`start_date`,`end_date`),
  CONSTRAINT `fk_blocked_unit` FOREIGN KEY (`unit_id`) REFERENCES `units_table` (`unit_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_blocked_dates_table`
--

LOCK TABLES `unit_blocked_dates_table` WRITE;
/*!40000 ALTER TABLE `unit_blocked_dates_table` DISABLE KEYS */;
INSERT INTO `unit_blocked_dates_table` VALUES (1,6,'2026-09-01','2026-09-07','Not Available','testing',1,'admin','2026-09-06 15:48:08'),(3,2,'2026-10-09','2026-10-11','Maintenance',NULL,4,'','2026-10-08 18:26:06'),(4,6,'2026-10-10','2026-10-13','Not Available',NULL,1,'admin','2026-10-09 08:59:39');
/*!40000 ALTER TABLE `unit_blocked_dates_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_ownership_history_table`
--

DROP TABLE IF EXISTS `unit_ownership_history_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_ownership_history_table` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `ownership_status` enum('active','transferred','past') NOT NULL DEFAULT 'active',
  `transfer_type` varchar(100) DEFAULT 'Initial Assignment',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `unit_id` (`unit_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_ownership_history_table`
--

LOCK TABLES `unit_ownership_history_table` WRITE;
/*!40000 ALTER TABLE `unit_ownership_history_table` DISABLE KEYS */;
INSERT INTO `unit_ownership_history_table` VALUES (1,1,2,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(2,2,4,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(3,3,5,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(4,4,6,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(5,5,8,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(6,6,2,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(7,7,4,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(8,8,6,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(9,9,6,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(10,10,7,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(11,11,2,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(12,12,4,'2026-06-03','2026-10-08','transferred','Current Owner','Backfilled active unit owner [Transferred on 2026-10-08]','2026-09-05 15:53:15'),(13,13,5,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(14,14,6,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(15,15,7,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(16,16,2,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(17,17,4,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(18,18,5,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(19,19,6,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(20,20,7,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(21,21,4,'2026-06-03',NULL,'active','Current Owner','Backfilled active unit owner','2026-09-05 15:53:15'),(22,12,10,'2026-10-08',NULL,'active','Resale','Ownership transferred upon completed resale','2026-10-09 05:54:22');
/*!40000 ALTER TABLE `unit_ownership_history_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units_table`
--

DROP TABLE IF EXISTS `units_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units_table` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_type` enum('Studio Type A','Studio Type B','One Bedroom','Two Bedroom') NOT NULL,
  `sqm` decimal(6,2) NOT NULL DEFAULT 0.00,
  `unit_number` varchar(20) NOT NULL,
  `floor_number` tinyint(2) unsigned NOT NULL DEFAULT 1,
  `lease_rate` decimal(10,2) DEFAULT NULL,
  `reselling_price` decimal(15,2) DEFAULT NULL,
  `listing_type` enum('For Lease','Resale','Not Available') NOT NULL DEFAULT 'For Lease',
  `stay_category` enum('Short term','Long term') NOT NULL DEFAULT 'Long term',
  `unit_owner_id` int(11) DEFAULT NULL,
  `unit_current_status` enum('Ready for Occupancy','For Resale','Resale','On Hold','Reserved','Occupied','Under maintenance') NOT NULL DEFAULT 'Ready for Occupancy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`unit_id`),
  UNIQUE KEY `unit_number` (`unit_number`),
  KEY `fk_units_owner` (`unit_owner_id`),
  CONSTRAINT `fk_units_owner` FOREIGN KEY (`unit_owner_id`) REFERENCES `users_table` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units_table`
--

LOCK TABLES `units_table` WRITE;
/*!40000 ALTER TABLE `units_table` DISABLE KEYS */;
INSERT INTO `units_table` VALUES (1,'Studio Type A',37.00,'A104',1,18000.00,NULL,'For Lease','Long term',2,'Reserved','2026-06-03 15:17:34'),(2,'Studio Type A',37.00,'A105',1,18500.00,50000.00,'Resale','Long term',4,'Reserved','2026-06-03 15:17:34'),(3,'Studio Type A',37.00,'A106',1,19000.00,NULL,'For Lease','Long term',5,'Occupied','2026-06-03 15:17:34'),(4,'Studio Type A',37.00,'A107',2,19500.00,NULL,'For Lease','Long term',6,'Reserved','2026-06-03 15:17:34'),(5,'Studio Type A',37.00,'A108',2,20000.00,NULL,'For Lease','Long term',8,'Ready for Occupancy','2026-06-03 15:17:34'),(6,'Studio Type B',40.65,'B204',1,21000.00,NULL,'For Lease','Long term',2,'Ready for Occupancy','2026-06-03 15:17:34'),(7,'Studio Type B',40.65,'B205',1,21500.00,NULL,'Resale','Long term',4,'For Resale','2026-06-03 15:17:34'),(8,'Studio Type B',40.65,'B206',2,22000.00,NULL,'For Lease','Long term',6,'Ready for Occupancy','2026-06-03 15:17:34'),(9,'Studio Type B',40.65,'B207',2,22500.00,NULL,'For Lease','Long term',6,'Ready for Occupancy','2026-06-03 15:17:34'),(10,'Studio Type B',40.65,'B208',3,23000.00,NULL,'Resale','Long term',7,'For Resale','2026-06-03 15:17:34'),(11,'One Bedroom',75.64,'C204',3,28000.00,NULL,'For Lease','Long term',2,'Reserved','2026-06-03 15:17:34'),(12,'One Bedroom',75.64,'C205',3,28500.00,140000.00,'Resale','Long term',10,'Ready for Occupancy','2026-06-03 15:17:34'),(13,'One Bedroom',75.64,'C206',3,30000.00,NULL,'For Lease','Long term',5,'Ready for Occupancy','2026-06-03 15:17:34'),(14,'One Bedroom',75.64,'C207',4,31000.00,NULL,'For Lease','Long term',6,'Ready for Occupancy','2026-06-03 15:17:34'),(15,'One Bedroom',75.64,'C208',4,32000.00,NULL,'Resale','Long term',7,'For Resale','2026-06-03 15:17:34'),(16,'Two Bedroom',113.00,'D304',4,42000.00,NULL,'For Lease','Long term',2,'Ready for Occupancy','2026-06-03 15:17:34'),(17,'Two Bedroom',113.00,'D305',4,43500.00,NULL,'Resale','Long term',4,'For Resale','2026-06-03 15:17:34'),(18,'Two Bedroom',113.00,'D306',4,45000.00,NULL,'For Lease','Long term',5,'Ready for Occupancy','2026-06-03 15:17:34'),(19,'Two Bedroom',113.00,'D307',5,47000.00,NULL,'For Lease','Long term',6,'Ready for Occupancy','2026-06-03 15:17:34'),(20,'Two Bedroom',113.00,'D308',5,49000.00,NULL,'Resale','Long term',7,'For Resale','2026-06-03 15:17:34'),(21,'One Bedroom',75.64,'C209',5,38000.00,NULL,'Resale','Long term',4,'For Resale','2026-06-03 15:18:50');
/*!40000 ALTER TABLE `units_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_table`
--

DROP TABLE IF EXISTS `users_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_table` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `contact` varchar(30) DEFAULT NULL,
  `additional_email` varchar(100) DEFAULT NULL,
  `user_role` enum('admin','unit owner','tenant') NOT NULL DEFAULT 'tenant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resident_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `gcash_QR` text DEFAULT NULL,
  `reset_code_hash` varchar(255) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `reset_attempts` int(11) NOT NULL DEFAULT 0,
  `reset_requested_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_table`
--

LOCK TABLES `users_table` WRITE;
/*!40000 ALTER TABLE `users_table` DISABLE KEYS */;
INSERT INTO `users_table` VALUES (1,'Admin User','admin@gmail.com','$2y$10$2lyItzoDb82iwIvA5If8e.jS8I4qVCYHwuLL/EVXvbzSLMke4GZQm','0922384834834848834843',NULL,NULL,'maddysilvano@gmail.com','admin','2026-06-03 15:08:44','Active',NULL),(2,'Unit Owner User','owner@gmail.com','$2y$10$vF.PS79ebAsXug6KckhOXOsuGhm/tVcFzllry.mpKobdDSd6H1JZC','09222222222',NULL,NULL,NULL,'unit owner','2026-06-03 15:08:44','Active','https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/payment_qr/qr_owner_2_1791793874_e1500dd6.jpg'),(3,'Tenant User','tenant@gmail.com','$2y$10$L7fvJzfR29.JSlHrUvK7VuW5.SgOd2K8wKTRUa5fxvO5wswXYhNh.','09333333333',NULL,NULL,NULL,'tenant','2026-06-03 15:08:44','Active',NULL),(4,'Soren Lia','soren@gmail.com','$2y$10$3DkUoCLN4kZZXq2MWmTluuAwthjWl6tXM6BagYmdNcOsxTO0jLyFu','0997562819',NULL,NULL,NULL,'unit owner','2026-06-03 15:18:50','Active','https://nviojvrjgvyyveyyxgmr.supabase.co/storage/v1/object/public/zepellin-buckets/payment_qr/qr_owner_4_1791787624_c2133f47.jpg'),(5,'Mika Reyes','mika.owner@gmail.com','$2y$10$cj24SOxF2ufWpR49rG37auKxxtFA16X1rQj5RASfr.RAsVrFHrt9i','09111111111',NULL,NULL,NULL,'unit owner','2026-06-07 16:10:11','Active','images/payment_qr/gcash_qr_5_1791465903_194cd3d9.png'),(6,'Carlos Santos','carlos.owner@gmail.com','$2y$10$sHLZvBi5Xnx4Vmktb9gIhOktRPHpLnUjTXpiiQzC9unGH4PNOnZj.','09222222222',NULL,NULL,NULL,'unit owner','2026-06-07 16:10:11','Active',NULL),(7,'Alyssa Cruz','alyssa.owner@gmail.com','$2y$10$VD26QufESc7nhgvSdjz7O.e2Mg3pR.trMIgTpbYxCc4KaUJmBBnpG','09333333333',NULL,NULL,NULL,'unit owner','2026-06-07 16:10:11','Active',NULL),(8,'Shanielle Gajo','gajoshanielle@gmail.com','Sha123','09707568287',NULL,NULL,NULL,'unit owner','2026-07-19 06:12:05','Inactive',NULL),(10,'testing resale','maddysilvano@gmail.com','$2y$10$6YMSDZX8lviZos7XDp6t.O6EFqpQTs9CIR9477ILDoQaBeN.isn0a','09707568287',NULL,NULL,NULL,'unit owner','2026-10-08 19:07:07','Active',NULL);
/*!40000 ALTER TABLE `users_table` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-13  9:27:51
