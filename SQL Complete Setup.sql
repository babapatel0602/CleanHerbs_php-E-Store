-- This SQL script creates the entire database structure and sample data
-- for the 'Clean Herbs' project.
--
-- How to use this file:
-- 1. Open XAMPP and start Apache and MySQL.
-- 2. Go to http://localhost/phpmyadmin/
-- 3. Click the "Import" tab at the top.
-- 4. Click "Choose File" and select this `database_setup.sql` file.
-- 5. Click "Go" at the bottom.
-- The database will be created and ready to use.

-- --------------------------------------------------------

--
-- Step 1: Create the database
--
DROP DATABASE IF EXISTS `cleanherbs_db`;
CREATE DATABASE `cleanherbs_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cleanherbs_db`;

-- --------------------------------------------------------

--
-- Step 2: Create Table Structures
-- (Tables are created in order to satisfy foreign key dependencies)
--

--
-- Table structure for table `users`
-- (Depends on nothing)
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `products`
-- (Depends on nothing)
--
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 10,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `orders`
-- (Depends on `users`)
--
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `order_items`
-- (Depends on `orders` and `products`)
--
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Step 3: Insert Sample Data
--

--
-- Dumping data for table `products`
--
INSERT INTO `products` (`name`, `description`, `price`, `image`, `stock`) VALUES
('Triphala Powder 100g', 'A classic Ayurvedic formula for detoxification and rejuvenation. Supports digestion and gentle cleansing.', '140.00', 'triphala.jpg', 50),
('Ashwagandha Powder 100g', 'A powerful adaptogen used to help the body resist stressors. Promotes vitality, energy, and a calm mind.', '220.00', 'ashwagandha.jpg', 50),
('Amla Powder 100g', 'An excellent source of Vitamin C. Amla is a potent antioxidant that supports immune function and healthy skin.', '199.00', 'amla.jpg', 50),
('Safed Musli Powder 100g', 'A traditional herb known for its revitalizing and strengthening properties. Often used as an aphrodisiac.', '18.99', 'safed_musli.jpg', 50),
('Moringa Powder 100g', 'A nutrient-dense superfood packed with vitamins, minerals, and antioxidants. Supports overall health and energy levels.', '210.00', 'moringa.jpg', 50);
