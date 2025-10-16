-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2025 at 11:45 AM
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
-- Database: `core1_freight_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bills_of_lading`
--

CREATE TABLE `bills_of_lading` (
  `id` int(11) NOT NULL,
  `bl_number` varchar(50) NOT NULL,
  `type` enum('HBL','MBL') NOT NULL,
  `shipper` varchar(255) DEFAULT NULL,
  `consignee` varchar(255) DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consolidations`
--

CREATE TABLE `consolidations` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consolidations`
--

INSERT INTO `consolidations` (`id`, `created_at`) VALUES
(4, '2025-09-05 07:13:47'),
(5, '2025-09-05 07:46:30');

-- --------------------------------------------------------

--
-- Table structure for table `consolidation_shipments`
--

CREATE TABLE `consolidation_shipments` (
  `id` int(11) NOT NULL,
  `consolidation_id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consolidation_shipments`
--

INSERT INTO `consolidation_shipments` (`id`, `consolidation_id`, `shipment_id`) VALUES
(4, 4, 19),
(5, 5, 18);

-- --------------------------------------------------------

--
-- Table structure for table `deconsolidations`
--

CREATE TABLE `deconsolidations` (
  `id` int(11) NOT NULL,
  `consolidation_id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `deconsolidated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `house_bills`
--

CREATE TABLE `house_bills` (
  `id` int(11) NOT NULL,
  `hbl_number` varchar(50) NOT NULL,
  `mbl_id` int(11) NOT NULL,
  `shipper` varchar(255) NOT NULL,
  `consignee` varchar(255) NOT NULL,
  `cargo_info` text DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `volume` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_bills`
--

CREATE TABLE `master_bills` (
  `id` int(11) NOT NULL,
  `mbl_number` varchar(50) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `carrier` varchar(100) DEFAULT NULL,
  `voyage` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` enum('shipment','purchase_order','system') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier` varchar(100) NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `origin` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `cargo_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `supplier`, `order_date`, `status`, `origin`, `destination`, `cargo_info`) VALUES
(13, 'PO-2025-345345343534535', 'Kent', '2025-09-04', 'Pending', 'Pampanga', 'Manila', '20 Boxes'),
(14, 'PO-2025-2025345345343534536', 'samuel', '2025-09-04', 'Pending', 'Caloocan ', 'Manila', 'Computer Set'),
(15, 'PO-2025-9.2233720368548E+18', 'Hesyo', '2025-09-04', 'Pending', 'Bataan', 'Manila', 'Gaming Chair'),
(16, 'PO-2025-9.2233720368548E+18', 'Asmo', '2025-09-04', 'Pending', 'Bataan', 'Manila', 'Oven'),
(17, 'PO-2025-9.2233720368548E+18', 'Kulas', '2025-09-05', 'Pending', 'Caloocan ', 'Manila', 'HollowBlocks');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `status` enum('Pending','In Transit','Delivered','Ready','Approved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `consolidated` tinyint(1) DEFAULT 0,
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `po_id`, `driver_name`, `vehicle_number`, `status`, `created_at`, `consolidated`, `archived`) VALUES
(15, 13, 'Khem Gicana', 'NHJ 123', 'Ready', '2025-09-04 13:20:24', 0, 0),
(16, 16, 'Cedriq Barro', 'YUI 546', 'Ready', '2025-09-04 14:04:27', 0, 0),
(17, 15, 'Cedriq Barro', 'YUI 546', 'Ready', '2025-09-04 14:04:38', 0, 0),
(18, 14, 'Khem Gicana', 'NHJ 123', 'In Transit', '2025-09-04 14:04:46', 1, 0),
(19, 13, 'Cedriq Barro', 'YUI 546', 'Delivered', '2025-09-04 14:05:00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `shipment_bookings`
--

CREATE TABLE `shipment_bookings` (
  `id` int(11) NOT NULL,
  `booking_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `booking_date` date NOT NULL,
  `status` enum('Pending','In Transit','Delivered','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipment_tracking`
--

CREATE TABLE `shipment_tracking` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `status` enum('At Origin','In Transit','At Warehouse','Delivered','Delayed') DEFAULT 'In Transit',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bills_of_lading`
--
ALTER TABLE `bills_of_lading`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_id` (`shipment_id`);

--
-- Indexes for table `consolidations`
--
ALTER TABLE `consolidations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `consolidation_shipments`
--
ALTER TABLE `consolidation_shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consolidation_id` (`consolidation_id`),
  ADD KEY `shipment_id` (`shipment_id`);

--
-- Indexes for table `deconsolidations`
--
ALTER TABLE `deconsolidations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consolidation_id` (`consolidation_id`),
  ADD KEY `shipment_id` (`shipment_id`);

--
-- Indexes for table `house_bills`
--
ALTER TABLE `house_bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hbl_number` (`hbl_number`),
  ADD KEY `mbl_id` (`mbl_id`);

--
-- Indexes for table `master_bills`
--
ALTER TABLE `master_bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mbl_number` (`mbl_number`),
  ADD KEY `shipment_id` (`shipment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `shipment_bookings`
--
ALTER TABLE `shipment_bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipment_tracking`
--
ALTER TABLE `shipment_tracking`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bills_of_lading`
--
ALTER TABLE `bills_of_lading`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consolidations`
--
ALTER TABLE `consolidations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `consolidation_shipments`
--
ALTER TABLE `consolidation_shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `deconsolidations`
--
ALTER TABLE `deconsolidations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `house_bills`
--
ALTER TABLE `house_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_bills`
--
ALTER TABLE `master_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `shipment_bookings`
--
ALTER TABLE `shipment_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipment_tracking`
--
ALTER TABLE `shipment_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bills_of_lading`
--
ALTER TABLE `bills_of_lading`
  ADD CONSTRAINT `bills_of_lading_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `consolidation_shipments`
--
ALTER TABLE `consolidation_shipments`
  ADD CONSTRAINT `consolidation_shipments_ibfk_1` FOREIGN KEY (`consolidation_id`) REFERENCES `consolidations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consolidation_shipments_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deconsolidations`
--
ALTER TABLE `deconsolidations`
  ADD CONSTRAINT `deconsolidations_ibfk_1` FOREIGN KEY (`consolidation_id`) REFERENCES `consolidations` (`id`),
  ADD CONSTRAINT `deconsolidations_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`);

--
-- Constraints for table `house_bills`
--
ALTER TABLE `house_bills`
  ADD CONSTRAINT `house_bills_ibfk_1` FOREIGN KEY (`mbl_id`) REFERENCES `master_bills` (`id`);

--
-- Constraints for table `master_bills`
--
ALTER TABLE `master_bills`
  ADD CONSTRAINT `master_bills_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`);

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
