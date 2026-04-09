-- Product Catalog Database Schema
-- MySQL Database Dump

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `product_catalog`
--
CREATE DATABASE IF NOT EXISTS `product_catalog` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `product_catalog`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_price` (`price`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Default admin user
-- Username: admin
-- Password: admin123
--

INSERT INTO `users` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');

--
-- Sample categories
--

INSERT INTO `categories` (`name`, `parent_id`) VALUES
('Electronics', NULL),
('Smartphones', 1),
('Laptops', 1),
('Clothing', NULL),
('Men', 4),
('Women', 4),
('Books', NULL),
('Fiction', 7),
('Non-Fiction', 7);

--
-- Sample products (optional)
--

INSERT INTO `products` (`name`, `description`, `price`, `category_id`) VALUES
('iPhone 15 Pro', 'Latest flagship smartphone with advanced camera system and A17 Pro chip.', 999.00, 2),
('Samsung Galaxy S24', 'Premium Android smartphone with stunning display and powerful performance.', 899.00, 2),
('MacBook Pro 16"', 'Professional laptop with M3 Max chip, perfect for developers and creators.', 2499.00, 3),
('Dell XPS 15', 'High-performance laptop with beautiful InfinityEdge display.', 1699.00, 3),
('Classic Cotton T-Shirt', 'Comfortable everyday t-shirt made from 100% organic cotton.', 29.99, 5),
('Slim Fit Jeans', 'Modern fit denim jeans with stretch comfort.', 79.99, 5),
('Summer Dress', 'Elegant floral print dress perfect for warm weather.', 89.99, 6),
('The Great Gatsby', 'Classic American novel by F. Scott Fitzgerald.', 14.99, 8),
('Atomic Habits', 'Practical guide to building good habits and breaking bad ones.', 24.99, 9);
