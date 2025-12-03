-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 03, 2025 at 04:20 PM
-- Server version: 8.2.0
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pointshift_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

DROP TABLE IF EXISTS `batches`;
CREATE TABLE IF NOT EXISTS `batches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `stock_quantity` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_batches_product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `product_id`, `batch_number`, `expiry_date`, `stock_quantity`, `created_at`) VALUES
(1, 1, '3', '2025-12-05', 10, '2025-12-01 23:36:27'),
(2, 6, 'VC2026A', '2026-06-30', 500, '2025-12-01 23:49:23'),
(3, 7, 'AMX-45', '2027-01-15', 300, '2025-12-01 23:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Medications', 'Prescription and over-the-counter medications', '2025-12-01 22:29:30'),
(2, 'Supplements', 'Vitamins and dietary supplements', '2025-12-01 22:29:30'),
(3, 'Medical Supplies', 'Bandages, syringes, etc.', '2025-12-01 22:29:30'),
(4, 'Personal Care', 'Soap, shampoo, etc.', '2025-12-01 22:29:30'),
(5, 'packet', NULL, '2025-12-01 22:56:39'),
(6, 'Vitamins', 'Essential nutrients and health boosters.', '2025-12-01 23:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `job_title` varchar(50) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `hiring_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `full_name`, `phone`, `job_title`, `salary`, `hiring_date`, `is_active`, `created_at`) VALUES
(1, 'System Administrator', '0000000000', 'Administrator', 0.00, '2025-12-02', 1, '2025-12-01 22:29:30'),
(2, 'Test Employee', '1234567890', 'Pharmacist', 5000.00, '2025-12-02', 1, '2025-12-01 22:29:30'),
(3, 'Alice Smith', '9876543210', 'Cashier', 3500.00, '2025-10-15', 1, '2025-12-01 23:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `expense_type_id` int DEFAULT NULL,
  `created_by_user_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` text,
  `expense_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_expenses_expense_type_id` (`expense_type_id`),
  KEY `fk_expenses_created_by_user_id` (`created_by_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `expense_type_id`, `created_by_user_id`, `amount`, `note`, `expense_date`, `created_at`) VALUES
(1, 6, 1, 45.99, 'Purchase of printing paper and ink cartridges.', '2025-11-28', '2025-12-01 23:49:23'),
(2, 7, 3, 120.00, 'Repair of cash register display screen.', '2025-12-01', '2025-12-01 23:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `expense_types`
--

DROP TABLE IF EXISTS `expense_types`;
CREATE TABLE IF NOT EXISTS `expense_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `expense_types`
--

INSERT INTO `expense_types` (`id`, `name`, `created_at`) VALUES
(1, 'Rent', '2025-12-01 22:29:30'),
(2, 'Electricity', '2025-12-01 22:29:30'),
(3, 'Water', '2025-12-01 22:29:30'),
(4, 'Internet', '2025-12-01 22:29:30'),
(5, 'Phone', '2025-12-01 22:29:30'),
(6, 'Supplies', '2025-12-01 22:29:30'),
(7, 'Maintenance', '2025-12-01 22:29:30'),
(8, 'Other', '2025-12-01 22:29:30'),
(9, 'diner', '2025-12-01 22:56:12');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_products_category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `created_at`) VALUES
(1, NULL, 'panadol', NULL, '2025-12-01 22:39:39'),
(2, NULL, 'sole', NULL, '2025-12-01 22:41:11'),
(3, NULL, 'pp', NULL, '2025-12-01 22:43:34'),
(4, NULL, 'pp', NULL, '2025-12-01 23:35:12'),
(5, NULL, 'pp', NULL, '2025-12-01 23:35:48'),
(6, NULL, 'packt2', NULL, '2025-12-01 23:46:07'),
(7, 6, 'Vitamin C Tablet', 'Boosts immune system', '2025-12-01 23:49:23'),
(8, 1, 'Amoxicillin Capsule', 'Broad-spectrum antibiotic', '2025-12-01 23:49:23'),
(9, NULL, 'ppp', NULL, '2025-12-01 23:54:48');

-- --------------------------------------------------------

--
-- Table structure for table `product_units`
--

DROP TABLE IF EXISTS `product_units`;
CREATE TABLE IF NOT EXISTS `product_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity_in_unit` int NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_product_units_product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_units`
--

INSERT INTO `product_units` (`id`, `product_id`, `unit_name`, `price`, `quantity_in_unit`, `is_default`, `created_at`) VALUES
(1, 6, 'Jar (50 tabs)', 12.50, 50, 1, '2025-12-01 23:49:23'),
(2, 6, 'Bulk Bag (1000 tabs)', 200.00, 1000, 0, '2025-12-01 23:49:23'),
(3, 7, 'Strip (10 caps)', 8.00, 10, 1, '2025-12-01 23:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

DROP TABLE IF EXISTS `returns`;
CREATE TABLE IF NOT EXISTS `returns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL,
  `sale_item_id` int NOT NULL,
  `product_id` int NOT NULL,
  `batch_id` int DEFAULT NULL,
  `unit_returned` varchar(50) DEFAULT NULL,
  `quantity` int NOT NULL,
  `price_at_return` decimal(10,2) NOT NULL,
  `return_reason` text,
  `returned_by_user_id` int DEFAULT NULL,
  `return_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_returns_sale_id` (`sale_id`),
  KEY `fk_returns_sale_item_id` (`sale_item_id`),
  KEY `fk_returns_product_id` (`product_id`),
  KEY `fk_returns_batch_id` (`batch_id`),
  KEY `fk_returns_returned_by_user_id` (`returned_by_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `sale_id`, `sale_item_id`, `product_id`, `batch_id`, `unit_returned`, `quantity`, `price_at_return`, `return_reason`, `returned_by_user_id`, `return_date`, `created_at`) VALUES
(1, 3, 3, 1, 1, 'Box (100 tabs)', 1, 25.00, 'Customer purchased too much, returned half of the box value.', 3, '2025-12-02', '2025-12-01 23:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `return_amount` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `fk_sales_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `total_amount`, `created_at`, `return_amount`) VALUES
(1, 1, 1.00, '2025-12-01 23:38:34', 0.00),
(2, 3, 20.00, '2025-12-01 23:49:23', 0.00),
(3, 1, 35.00, '2025-12-01 23:49:23', 25.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL,
  `product_id` int NOT NULL,
  `batch_id` int DEFAULT NULL,
  `unit_sold` varchar(50) DEFAULT NULL,
  `quantity` int NOT NULL,
  `price_at_moment` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sale_items_sale_id` (`sale_id`),
  KEY `fk_sale_items_product_id` (`product_id`),
  KEY `fk_sale_items_batch_id` (`batch_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `batch_id`, `unit_sold`, `quantity`, `price_at_moment`) VALUES
(1, 1, 1, 3, NULL, 1, 10.00),
(2, 2, 6, 2, 'Jar (50 tabs)', 1, 12.50),
(3, 2, 7, 3, 'Strip (10 caps)', 1, 8.00),
(4, 3, 1, 1, 'Box (100 tabs)', 1, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `company_name`, `phone`, `created_at`) VALUES
(1, 'VitaCorp Nutrition', '1800-VITA-C', '2025-12-01 23:49:23'),
(2, 'Generic Meds Ltd.', '1800-GENMED', '2025-12-01 23:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Employee') NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_users_employee_id` (`employee_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `username`, `password_hash`, `role`, `last_login`, `created_at`) VALUES
(1, 1, 'admin', 'admin123', 'Admin', '2025-12-03 13:23:43', '2025-12-01 22:29:30'),
(2, 2, 'employee', 'emp123', 'Employee', '2025-12-01 23:32:35', '2025-12-01 22:29:30'),
(3, 3, 'alice', 'alicepass', 'Employee', '2025-12-01 22:00:00', '2025-12-01 23:49:23');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
