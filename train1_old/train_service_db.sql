-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2025 at 11:57 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `train_service_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(30) NOT NULL,
  `category` varchar(250) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category`, `status`, `date_created`) VALUES
(1, 'long-distance trains', 1, '2025-05-20 16:22:41'),
(2, 'high-speed rail', 1, '2025-05-20 16:22:41'),
(3, 'inter-city trains', 1, '2025-05-20 16:22:41'),
(4, 'short-distance trains', 1, '2025-05-20 16:22:41'),
(15, '\" OR 1 = 1 -- -', 0, '2025-06-18 23:23:21');

-- --------------------------------------------------------

--
-- Table structure for table `mechanics_list`
--

CREATE TABLE `mechanics_list` (
  `id` int(30) NOT NULL,
  `mechanic` text NOT NULL,
  `contact` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mechanics_list`
--

INSERT INTO `mechanics_list` (`id`, `mechanic`, `contact`, `email`, `status`, `date_created`) VALUES
(1, 'HARIS', '01159268338', 'izzemir02@gmail.com', 1, '2025-01-09 11:37:58'),
(2, 'Nora', '9121832123', 'nora@gmail.com', 1, '2025-01-09 17:22:51');

-- --------------------------------------------------------

--
-- Table structure for table `service_list`
--

CREATE TABLE `service_list` (
  `id` int(30) NOT NULL,
  `service` text NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `service_list`
--

INSERT INTO `service_list` (`id`, `service`, `description`, `status`, `date_created`) VALUES
(4, 'Fluid Levels Checking', 'Checking and replenishing fluids like engine coolant, hydraulic oil, and brake fluid.', 1, '2025-05-20 18:22:39'),
(6, 'Filter Replacements', 'Changing air filters, fuel filters, and other filters to maintain optimal engine performance and protect sensitive components.', 1, '2025-05-20 18:22:39'),
(8, 'Repairing or replacing defective components', 'This can range from fixing minor electrical issues to replacing major components like engines or transmissions.', 1, '2025-05-20 18:22:39'),
(10, 'Upgrades and Modernization', 'Implementing technological advancements and renovations to improve train performance, comfort, and passenger experience.', 1, '2025-05-20 18:22:39'),
(14, 'Brake and Wheel Assembly Cleaning', 'Removing accumulated grease, dirt, and brake dust from wheelsets and braking systems to ensure proper mechanical function.\r\n\r\n', 1, '2025-06-15 21:16:36');

-- --------------------------------------------------------

--
-- Table structure for table `service_request`
--

CREATE TABLE `service_request` (
  `request_id` int(11) NOT NULL,
  `train_type_id` int(11) DEFAULT NULL,
  `train_brand` varchar(255) DEFAULT NULL,
  `train_registration_number` varchar(255) DEFAULT NULL,
  `train_model` varchar(255) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `assigned_to_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `service_request`
--

INSERT INTO `service_request` (`request_id`, `train_type_id`, `train_brand`, `train_registration_number`, `train_model`, `service_id`, `assigned_to_id`, `status`, `created_at`, `user_id`) VALUES
(1, 4, 'A', '213232', 'ets', 10, 1, 1, '2025-01-08 22:32:19', 2),
(3, 4, 'C', '65765757', 'KTM', 4, 2, 2, '2025-01-09 04:04:44', 2),
(4, 2, 'lrt', 'aaa111', 'aaa', 8, 1, 1, '2025-01-15 23:20:01', 9),
(5, 4, 'bmw', '123344', 'ktm', 6, NULL, 0, '2025-01-16 04:46:00', 11),
(6, 1, 'asd', 'sadsd', 'fdfsd', 10, NULL, 0, '2025-06-18 14:51:45', 24),
(8, 1, 'asd', 'asdasd', 'asd', 4, NULL, 0, '2025-06-18 15:22:50', 25),
(9, 1, 'saas', 'asd', 'asdsa', 4, NULL, 0, '2025-06-18 15:37:09', 25),
(10, 1, 'asd', 'asd', 'asd', 4, NULL, 0, '2025-06-18 20:38:38', 24);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_salt` varchar(255) DEFAULT NULL,
  `verification_code` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `otp` int(6) UNSIGNED DEFAULT NULL,
  `otp_expiration` timestamp NULL DEFAULT NULL,
  `identity` varchar(255) DEFAULT 'staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `password_salt`, `verification_code`, `is_verified`, `created_at`, `otp`, `otp_expiration`, `identity`) VALUES
(17, 'haris', 'harismaidin10@gmail.com', '$2y$10$hZw9NhQewegf0tQhYOU8AOuog6P2sasK33B.Ebzk1yUTE.Rix7Rxm', NULL, '14a0092541590a9287aa07024ae32d11', 1, '2025-06-15 12:08:50', 475210, '2025-06-15 13:22:09', 'staff'),
(18, 'abu', 'ci230124@student.uthm.edu.my', '$2y$10$dNT.eCXWdqaDMsV6WTugxeJL8jnowXHlr4uZ7N1/.3.twuF0kTpP.', NULL, '46679ed022c7324c5c2e9aac1830c583', 1, '2025-06-15 12:29:22', 290656, '2025-06-15 12:36:36', 'admin'),
(25, 'admin', 'emirizzat0565@gmail.com', '$2y$10$bjc3.D3Z4jbBaY4zHJRYyuja3M0y4tvGpu3XttBKzGGlqgHCKngzu', NULL, 'b9482d2f68d4ce30886b13a5f527919f', 1, '2025-06-18 15:17:13', 361891, '2025-06-18 21:19:27', 'admin'),
(26, 'aaa', 'izzemir02@gmail.com', '$2y$10$ouNH11SpFrcbIuaee.6PZeUtO5TKuubznWRxA2ucRd/pQKVYc8c7K', '04bf9934d2337347a26e7e8c40726278', '61eb0d09067534a66e8d95aeb982e501', 1, '2025-06-18 21:39:39', 983253, '2025-06-18 21:46:01', 'staff'),
(27, 'ortu', 'tazz11247@gmail.com', '$2y$10$qjWvvpAkFIMHbR/.IktoE.Y/pyvikct/0mxpsxC1/cygEWte8e2DW', 'bbf8260dcc1ff2492ff42259b0f2830d', '6409a280560f897054d68aefe0ad9057', 1, '2025-06-18 21:42:21', 415384, '2025-06-18 21:48:08', 'staff'),
(28, '&quot; OR 1 = 1 -- -', 'sadsad@gmail.com', '$2y$10$kr4ikFkmXYE72cOLc2X7NuiBvNMYnzMBQVTNDC4nsfuRxOis3sGTW', 'bab4f329971c5f21e851e319611c680b', '4029052c988144aef8c1e4d0fbe1cdf1', 0, '2025-06-18 21:52:11', NULL, NULL, 'staff');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mechanics_list`
--
ALTER TABLE `mechanics_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_list`
--
ALTER TABLE `service_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_request`
--
ALTER TABLE `service_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `train_type_id` (`train_type_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `assigned_to_id` (`assigned_to_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `mechanics_list`
--
ALTER TABLE `mechanics_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_list`
--
ALTER TABLE `service_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `service_request`
--
ALTER TABLE `service_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `service_request`
--
ALTER TABLE `service_request`
  ADD CONSTRAINT `service_request_ibfk_1` FOREIGN KEY (`train_type_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `service_request_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service_list` (`id`),
  ADD CONSTRAINT `service_request_ibfk_3` FOREIGN KEY (`assigned_to_id`) REFERENCES `mechanics_list` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
