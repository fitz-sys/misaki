-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2026 at 09:21 AM
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
-- Database: `misaki`
--

-- --------------------------------------------------------

--
-- Table structure for table `addon`
--

CREATE TABLE `addon` (
  `addon_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addon`
--

INSERT INTO `addon` (`addon_id`, `name`, `price`, `is_active`) VALUES
(1, 'Printed Photo', 5.00, 1),
(2, 'Acrylic Dedication', 5.00, 1),
(3, 'Fairy Light', 20.00, 1),
(4, 'Letter', 25.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

CREATE TABLE `admin_user` (
  `admin_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(60) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_user`
--

INSERT INTO `admin_user` (`admin_id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$MpWnrvxc9C6FL1uReQxUS.zdxzjNcr0ksRxRdhqGXyTHR8.xfASiO', '2026-05-08 16:19:55');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `delivery_name` varchar(120) DEFAULT NULL,
  `delivery_phone` varchar(20) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `address_label` enum('Home','Someone Else') DEFAULT 'Home',
  `status` enum('pending','paid','fulfilled','cancelled') NOT NULL DEFAULT 'paid',
  `payment_method` enum('cash','gcash') NOT NULL DEFAULT 'cash',
  `payment_proof` varchar(255) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `user_id`, `delivery_name`, `delivery_phone`, `delivery_address`, `address_label`, `status`, `payment_method`, `payment_proof`, `total`, `created_at`) VALUES
(12, 1, 'Get our step-by-step guide at:', '1111', 'awdawwadwadqawdawd', 'Home', 'pending', 'cash', NULL, 24680.00, '2026-05-11 20:46:46'),
(13, 1, 'Get our step-by-step guide at:', '1111', 'qeqeq2e', 'Home', 'pending', 'cash', NULL, 1244.00, '2026-05-11 20:47:47'),
(14, 1, 'Get our step-by-step guide at:', '1111', 'asadwda', 'Home', 'pending', 'cash', NULL, 185250.00, '2026-05-12 13:09:26'),
(15, 1, 'Get our step-by-step guide at:', '1111', 'ewan', 'Home', 'cancelled', 'cash', NULL, 62.00, '2026-05-12 20:21:06');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `qty` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `product_id`, `qty`, `unit_price`, `line_total`) VALUES
(13, 12, 9, 20, 1234.00, 24680.00),
(14, 13, 9, 1, 1234.00, 1244.00),
(15, 14, 13, 15, 12345.00, 185250.00),
(16, 15, 2, 1, 62.00, 62.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_item_addon`
--

CREATE TABLE `order_item_addon` (
  `order_item_id` int(10) UNSIGNED NOT NULL,
  `addon_id` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_item_addon`
--

INSERT INTO `order_item_addon` (`order_item_id`, `addon_id`, `unit_price`) VALUES
(14, 1, 5.00),
(14, 2, 5.00),
(15, 1, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(120) NOT NULL,
  `name` varchar(120) NOT NULL,
  `jp_name` varchar(60) NOT NULL DEFAULT '',
  `type_id` int(10) UNSIGNED NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `badge` varchar(40) DEFAULT NULL,
  `description` text NOT NULL,
  `sales` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `slug`, `name`, `jp_name`, `type_id`, `price`, `image`, `badge`, `description`, `sales`, `is_visible`, `created_at`) VALUES
(1, 'lorem-blush', 'Lorem Blush', '桃の夢', 1, 48.00, 'images/product-1.jpg', 'Bestseller', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hand-tied with garden roses and seasonal foliage, wrapped in unbleached kraft.', 341, 1, '2026-05-08 16:19:55'),
(2, 'lorem-aurora', 'Lorem Aurora', '白の静寂', 2, 62.00, 'images/product-2.jpg', 'New', 'Lorem ipsum dolor sit amet. White peonies and baby\'s breath in a hand-thrown ceramic vessel, made for quiet rooms.', 124, 1, '2026-05-08 16:19:55'),
(3, 'lorem-amber', 'Lorem Amber', '枯れ草', 3, 36.00, 'images/product-3.jpg', 'Limited', 'Lorem ipsum. Pampas and dried lavender bound with twine — lasts a full season, no water needed.', 230, 1, '2026-05-08 16:19:55'),
(6, 'lorem-meadow', 'Lorem Meadow', '野の花', 1, 42.00, 'images/product-6.jpg', 'New', 'Lorem ipsum dolor sit amet. Wild chamomile, cosmos and ferns gathered loosely — feels like a walk through a meadow.', 199, 1, '2026-05-08 16:19:55'),
(9, 'yes', 'Nike Air Jordan', 'tiao', 1, 1234.00, 'images/prod_1778503560_6a01cf884a115.png', NULL, 'qwdadwqdwdad', 21, 1, '2026-05-11 20:46:00'),
(13, 'awad', 'qdef', 'awfwf', 3, 12345.00, 'images/prod_1778562532_6a02b5e4bade2.png', NULL, 'qdqwdfes', 15, 1, '2026-05-12 13:08:52');

-- --------------------------------------------------------

--
-- Table structure for table `product_type`
--

CREATE TABLE `product_type` (
  `type_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_type`
--

INSERT INTO `product_type` (`type_id`, `name`) VALUES
(2, 'Arrangements'),
(1, 'Bouquets'),
(3, 'Dried'),
(4, 'Seasonal');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 1 and 5),
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `address` text DEFAULT NULL,
  `address_label` enum('Home','Someone Else') DEFAULT 'Home',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `email`, `phone`, `password_hash`, `full_name`, `address`, `address_label`, `created_at`) VALUES
(1, 'kristiamaeorbe@gmail.com', '1111', '$2y$10$iV4EOCqTrfLZocrL0.1GEOA70fEmJVu3saigF9jMoAC.e7tUyQMjK', 'Get our step-by-step guide at:', '1583 A. Mendoza St. Brgy Carmona Makati City', 'Home', '2026-05-09 11:43:18'),
(2, 'raidensantos17@gmail.com', NULL, '$2y$10$lGhA40t2JArfRB5TzEYmPuQKD/7E1oBjbKiXDaMECZ5nGv/URomOq', 'Raiden Santos', NULL, 'Home', '2026-05-11 12:32:18'),
(3, 'esdfsdfs@yahoo.com', '09303060298', '$2y$10$luged279krNncnYXRIuD0uZ5qpEF5rD2TQXPTzOC2ckfwrmCAJKKi', 'Emily Sicatsss111', 'ewfwefwe', 'Home', '2026-05-11 12:51:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `address_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `label` enum('Home','Someone Else') NOT NULL DEFAULT 'Home',
  `full_name` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_text` text NOT NULL,
  `city` varchar(80) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`address_id`, `user_id`, `label`, `full_name`, `phone`, `address_text`, `city`, `is_default`, `created_at`) VALUES
(1, 1, 'Someone Else', 'fitzgerald aclan', '9303060298', '1583 A. Mendoza St. Brgy Carmona Makati City', 'Manila', 0, '2026-05-14 08:08:30'),
(2, 3, 'Home', 'Emily Sicatsss111', '09303060298', 'ewfwefwe', 'Manila', 1, '2026-05-14 08:08:30'),
(8, 1, 'Home', 'sefsfsef', 'sefefss', 'sffsf', 'Quezon City', 0, '2026-05-14 09:17:56'),
(9, 1, 'Someone Else', 'awdawdadwad', 'aawdadawdawd', 'zsadawdawdawdawdawd', 'San Juan', 0, '2026-05-14 09:18:09'),
(11, 1, 'Home', 'awdawdadawd', 'awfawfawf', 'fawfasafvsda', 'San Juan', 1, '2026-05-14 09:18:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addon`
--
ALTER TABLE `addon`
  ADD PRIMARY KEY (`addon_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_user` (`user_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `fk_oi_order` (`order_id`),
  ADD KEY `fk_oi_product` (`product_id`);

--
-- Indexes for table `order_item_addon`
--
ALTER TABLE `order_item_addon`
  ADD PRIMARY KEY (`order_item_id`,`addon_id`),
  ADD KEY `fk_oia_addon` (`addon_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_product_type` (`type_id`);

--
-- Indexes for table `product_type`
--
ALTER TABLE `product_type`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uniq_review_per_order_product` (`order_id`,`product_id`),
  ADD KEY `fk_review_product` (`product_id`),
  ADD KEY `fk_review_user` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `fk_ua_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addon`
--
ALTER TABLE `addon`
  MODIFY `addon_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_type`
--
ALTER TABLE `product_type`
  MODIFY `type_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `review_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `order_item_addon`
--
ALTER TABLE `order_item_addon`
  ADD CONSTRAINT `fk_oia_addon` FOREIGN KEY (`addon_id`) REFERENCES `addon` (`addon_id`),
  ADD CONSTRAINT `fk_oia_oi` FOREIGN KEY (`order_item_id`) REFERENCES `order_item` (`order_item_id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_type` FOREIGN KEY (`type_id`) REFERENCES `product_type` (`type_id`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `fk_review_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `fk_ua_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
