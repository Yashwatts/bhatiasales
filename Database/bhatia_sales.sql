-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 05:09 PM
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
-- Database: `bhatia_sales`
--
CREATE DATABASE IF NOT EXISTS `bhatia_sales` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bhatia_sales`;

-- --------------------------------------------------------

--
-- Table structure for table `accessories`
--

CREATE TABLE `accessories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `type` enum('Helmet','Spare Parts','Accessories') NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accessories`
--

INSERT INTO `accessories` (`id`, `name`, `price`, `color`, `type`, `image`, `created_at`) VALUES
(2, 'Helmet', 1000.00, 'black', 'Helmet', '1743876948_img1.jpg', '2025-04-05 18:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `accessory_orders`
--

CREATE TABLE `accessory_orders` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `items` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accessory_orders`
--

INSERT INTO `accessory_orders` (`id`, `user_name`, `contact`, `items`, `total`, `status`, `created_at`) VALUES
(1, 'Yash', '8968532929', '{\"2\":{\"id\":\"2\",\"name\":\"Helmet\",\"price\":\"1000.00\",\"color\":\"black\",\"type\":\"Helmet\",\"image\":\"1743876948_img1.jpg\",\"quantity\":2}}', 2000.00, 'Confirmed', '2025-04-05 18:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `profile_image`) VALUES
(1, 'admin', '$2y$10$iTQeF.bF1LrIHtqWEZhRouYECLyQAKGKVdrXdblWZqaJ2zVBuTXPW', '67f6c7004cf39.png'),
(2, 'admin2', '$2y$10$X6t0G.VCtcx2DXYfGHxJxufM4B6N8XG9HievcTJKjwp0lYshF614y', 'default.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `applicable_to` enum('Products','Accessories','All') NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `subcategory` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `code`, `percentage`, `applicable_to`, `item_id`, `status`, `created_at`, `expires_at`, `subcategory`) VALUES
(1, 'BIKE50', 25.00, 'Products', 17, 'Active', '2025-04-09 19:04:47', '2025-04-10 00:34:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `customer_name`, `email`, `phone`, `message`, `submitted_at`, `status`) VALUES
(2, 'Yash', 'yashwatts2005@gmail.com', '8968532929', 'Hello, I need help', '2025-04-04 17:06:11', 'resolved'),
(3, 'Yash Watts', 'yashwatts21@gmail.com', '7897894561', 'Hello!!!!!\r\nHello!!!!\r\nHello!!!!', '2025-04-04 17:14:58', 'resolved'),
(4, 'Ridhi', 'ridhbht4@gmail.com', '9817245943', 'Hello Help me', '2025-04-04 17:19:12', 'resolved'),
(5, 'Ridhii', 'ridhi@gmail.com', '9817245943', 'Hello', '2025-04-05 17:21:00', 'resolved'),
(6, 'Yash', 'yash@gmail.com', '8968532929', 'Hello', '2025-04-06 17:05:22', 'resolved');

-- --------------------------------------------------------

--
-- Table structure for table `online_bookings`
--

CREATE TABLE `online_bookings` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `online_bookings`
--

INSERT INTO `online_bookings` (`id`, `product_id`, `user_name`, `contact`, `booking_date`, `status`) VALUES
(1, 8, 'Yash', '8968532929', '2025-04-04 19:39:16', 'Confirmed'),
(2, 18, 'Yash Watts', '7894561233', '2025-04-04 19:46:12', 'Cancelled'),
(3, 3, 'Ridhii', '9817245943', '2025-04-05 17:18:51', 'Confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `image`, `description`) VALUES
(2, 'Superbike', 'bike', 100000.00, 'product_1743338574.jpg', 'Superbike\r\nMileage\r\nKMPH'),
(3, 'Bike2', 'bike', 80000.00, 'product_1743338864.jpg', 'Bike number 2'),
(4, 'Bike3', 'bike', 120000.00, 'product_1743423954.jpg', 'Bike number 2'),
(5, 'Bike4', 'bike', 200000.00, 'product_1743423989.jpg', 'Bike number 4'),
(6, 'Bike5', 'bike', 250000.00, 'product_1743424014.jpg', 'Bike number 5'),
(7, 'Bike6', 'bike', 75000.00, 'product_1743424048.jpg', 'Bike number 6'),
(8, 'Bike7', 'bike', 150000.00, 'product_1743424075.jpg', 'Bike number 7'),
(10, 'Scooty', 'scooter', 75000.00, 'product_1743424130.jpg', 'Scooty number 1'),
(11, 'Scooty2', 'scooter', 50000.00, 'product_1743424157.jpg', 'Scooty number 2'),
(12, 'Scooty3', 'scooter', 80000.00, 'product_1743424210.jpg', 'Scooty number 3'),
(13, 'Scooty 4', 'scooter', 30000.00, 'product_1743424243.jpg', 'Scooty number 4'),
(14, 'Scooty5', 'scooter', 100000.00, 'product_1743424276.jpg', 'Scooty number 5'),
(15, 'Scooty6', 'scooter', 90000.00, 'product_1743424321.jpg', 'Scooty number 6'),
(16, 'Scooty7', 'scooter', 120000.00, 'product_1743424350.jpg', 'Scooty number 7'),
(17, 'EV1', 'ev', 10000.00, 'product_1743424373.jpg', 'EV1'),
(18, 'EV2', 'ev', 20000.00, 'product_1743424396.jpg', 'EV2'),
(19, 'EV3', 'ev', 30000.00, 'product_1743424413.jpg', 'EV3'),
(20, 'EV4', 'ev', 40000.00, 'product_1743424445.jpg', 'EV4'),
(21, 'EV5', 'ev', 50000.00, 'product_1743424470.jpg', 'EV5'),
(22, 'EV6', 'ev', 60000.00, 'product_1743424489.jpg', 'EV6'),
(23, 'EV7', 'ev', 70000.00, 'product_1743424511.jpg', 'EV7');

-- --------------------------------------------------------

--
-- Table structure for table `second_hand_vehicles`
--

CREATE TABLE `second_hand_vehicles` (
  `id` int(11) NOT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `vehicle_type` enum('Bike','Scooty','EV') DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `travelled_km` int(11) DEFAULT NULL,
  `asking_price` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `vehicle_image` varchar(255) DEFAULT NULL,
  `registered_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Declined','Sold') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `second_hand_vehicles`
--

INSERT INTO `second_hand_vehicles` (`id`, `user_name`, `vehicle_type`, `company`, `model`, `color`, `travelled_km`, `asking_price`, `location`, `contact`, `vehicle_image`, `registered_on`, `status`) VALUES
(1, 'yash', 'Bike', 'Honda', 'Activa', 'Grey', 1000, 50000.00, 'Zirakpur', '8968532929', NULL, '2025-04-04 17:55:29', 'Sold'),
(2, 'Yash Watts', 'Bike', 'Suzuki', '100', 'Black', 500, 70000.00, 'Chandigarh', '7894561233', 'admin/uploads/1743792763_img2.jpg', '2025-04-04 18:52:43', 'Declined'),
(3, 'Yash Watts', 'Bike', 'Suzuki', '100', 'Black', 500, 70000.00, 'Chandigarh', '7894561233', '1743793218_img2.jpg', '2025-04-04 19:00:18', 'Sold'),
(4, 'Yash Watts', 'Bike', 'Suzuki', '100', 'Black', 500, 70000.00, 'Chandigarh', '7894561233', '1743793251_img2.jpg', '2025-04-04 19:00:51', 'Declined');

-- --------------------------------------------------------

--
-- Table structure for table `service_bookings`
--

CREATE TABLE `service_bookings` (
  `id` int(11) NOT NULL,
  `service_type` enum('Repairing','Washing') NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `booking_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Confirmed','Completed','Declined') DEFAULT 'Pending',
  `queue_position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_bookings`
--

INSERT INTO `service_bookings` (`id`, `service_type`, `user_name`, `contact`, `booking_date`, `status`, `queue_position`) VALUES
(1, 'Repairing', 'Yash', '8968532929', '2025-04-05 20:16:07', 'Completed', 1),
(2, 'Repairing', 'Ridhii', '9817245943', '2025-04-05 20:16:21', 'Completed', 1),
(3, 'Washing', 'Yash Watts', '8968532929', '2025-04-05 20:16:33', 'Completed', 1),
(4, 'Washing', 'Ridhii', '9817245943', '2025-04-05 20:16:43', 'Confirmed', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accessories`
--
ALTER TABLE `accessories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `accessory_orders`
--
ALTER TABLE `accessory_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `online_bookings`
--
ALTER TABLE `online_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `second_hand_vehicles`
--
ALTER TABLE `second_hand_vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_bookings`
--
ALTER TABLE `service_bookings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accessories`
--
ALTER TABLE `accessories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `accessory_orders`
--
ALTER TABLE `accessory_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `online_bookings`
--
ALTER TABLE `online_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `second_hand_vehicles`
--
ALTER TABLE `second_hand_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_bookings`
--
ALTER TABLE `service_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `online_bookings`
--
ALTER TABLE `online_bookings`
  ADD CONSTRAINT `online_bookings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
