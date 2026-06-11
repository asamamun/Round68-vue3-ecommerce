-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2026 at 05:00 PM
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
-- Database: `r68_vue3_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `total_items` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `items`, `total_items`, `total_price`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, '[{\"id\":1,\"title\":\"Essence Mascara Lash Princess\",\"price\":9.99,\"quantity\":4,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/essence-mascara-lash-princess\\/thumbnail.webp\"},{\"id\":2,\"title\":\"Eyeshadow Palette with Mirror\",\"price\":19.99,\"quantity\":3,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/eyeshadow-palette-with-mirror\\/thumbnail.webp\"},{\"id\":3,\"title\":\"Powder Canister\",\"price\":14.99,\"quantity\":2,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/powder-canister\\/thumbnail.webp\"}]', 9, 129.91, 'pending', '2026-06-11 16:34:10', '2026-06-11 16:34:10'),
(2, 5, '[{\"id\":4,\"title\":\"Red Lipstick\",\"price\":12.99,\"quantity\":1,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/red-lipstick\\/thumbnail.webp\"},{\"id\":5,\"title\":\"Red Nail Polish\",\"price\":8.99,\"quantity\":2,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/red-nail-polish\\/thumbnail.webp\"},{\"id\":6,\"title\":\"Calvin Klein CK One\",\"price\":49.99,\"quantity\":3,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/fragrances\\/calvin-klein-ck-one\\/thumbnail.webp\"},{\"id\":7,\"title\":\"Chanel Coco Noir Eau De\",\"price\":129.99,\"quantity\":4,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/fragrances\\/chanel-coco-noir-eau-de\\/thumbnail.webp\"},{\"id\":8,\"title\":\"Dior J\'adore\",\"price\":89.99,\"quantity\":5,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/fragrances\\/dior-j\'adore\\/thumbnail.webp\"},{\"id\":9,\"title\":\"Dolce Shine Eau de\",\"price\":69.99,\"quantity\":6,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/fragrances\\/dolce-shine-eau-de\\/thumbnail.webp\"}]', 21, 1570.79, 'pending', '2026-06-11 16:35:04', '2026-06-11 16:35:04'),
(3, 5, '[{\"id\":1,\"title\":\"Essence Mascara Lash Princess\",\"price\":9.99,\"quantity\":3,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/essence-mascara-lash-princess\\/thumbnail.webp\"},{\"id\":2,\"title\":\"Eyeshadow Palette with Mirror\",\"price\":19.99,\"quantity\":1,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/eyeshadow-palette-with-mirror\\/thumbnail.webp\"}]', 4, 49.96, 'pending', '2026-06-11 16:35:50', '2026-06-11 16:35:50'),
(4, 3, '[{\"id\":1,\"title\":\"Essence Mascara Lash Princess\",\"price\":9.99,\"quantity\":1,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/essence-mascara-lash-princess\\/thumbnail.webp\"},{\"id\":2,\"title\":\"Eyeshadow Palette with Mirror\",\"price\":19.99,\"quantity\":1,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/eyeshadow-palette-with-mirror\\/thumbnail.webp\"},{\"id\":3,\"title\":\"Powder Canister\",\"price\":14.99,\"quantity\":1,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/powder-canister\\/thumbnail.webp\"}]', 3, 44.97, 'pending', '2026-06-11 16:56:28', '2026-06-11 16:56:28'),
(5, 6, '[{\"id\":4,\"title\":\"Red Lipstick\",\"price\":12.99,\"quantity\":1,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/red-lipstick\\/thumbnail.webp\"},{\"id\":5,\"title\":\"Red Nail Polish\",\"price\":8.99,\"quantity\":1,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/beauty\\/red-nail-polish\\/thumbnail.webp\"},{\"id\":6,\"title\":\"Calvin Klein CK One\",\"price\":49.99,\"quantity\":1,\"thumbnail\":\"https:\\/\\/cdn.dummyjson.com\\/product-images\\/fragrances\\/calvin-klein-ck-one\\/thumbnail.webp\"}]', 3, 71.97, 'pending', '2026-06-11 16:58:52', '2026-06-11 16:58:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `refresh_token` varchar(512) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `role` set('user','admin') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `refresh_token`, `token_expires_at`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'admin', 'admin@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$NnBBWFRLWmR0My5jQUxJTA$Ng1Zb3u+EnCZQ7CaIshR/oYsbBzNdK8ER3KzMqGXbqo', '2faa1cd25728fd238a715bd46231c700ade9bf1237fc4f359343ce9b4b82d033', '2026-06-18 16:55:56', 'user', 1, '2026-06-11 16:21:39', '2026-06-11 16:55:56'),
(4, 'mamun', 'mamun@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$U2EuaGdySFpGM1JNRExVZQ$nTecnOXx2zYr40gzV0tl6F/jYANaBLM6wdSEtsmGWm8', NULL, NULL, 'user', 1, '2026-06-11 16:23:12', '2026-06-11 16:23:12'),
(5, 'idb', 'idb@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$MGpNZG42WXRPVjcvOURxMw$kfYLJKD6OF84Iq7KHCQ+RU608dajFHsHKCsvqq9qwbY', '62332deacecc5519ab928d22cbdcf5bbcdd95458520acb8a5696bc038ad8a04f', '2026-06-18 16:28:38', 'user', 1, '2026-06-11 16:27:05', '2026-06-11 16:28:38'),
(6, 'jahanara', 'jahanara@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$SXprOTFnY1JCRTB5SkJqdg$B4P4rq0L906uH/UuVGuJ6itNLuDJM8ouJ4WGxtYwFVs', 'b8562546bce52798b5470dfda076b1c7200fc2077d55a82f871d55000c04a218', '2026-06-18 16:58:29', 'user', 1, '2026-06-11 16:58:29', '2026-06-11 16:58:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_refresh` (`refresh_token`(64));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
