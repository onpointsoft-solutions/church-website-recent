-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 09:09 AM
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
-- Database: `bs_cefc`
--

-- --------------------------------------------------------

--
-- Table structure for table `bs_users`
--

CREATE TABLE `bs_users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','coordinator','leader','member') DEFAULT 'member',
  `age_group` enum('youth','young_adult','adult','senior') DEFAULT 'adult',
  `group_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `verified` tinyint(1) DEFAULT 0,
  `otp` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_users`
--

INSERT INTO `bs_users` (`id`, `name`, `email`, `phone`, `password`, `role`, `age_group`, `group_id`, `status`, `verified`, `otp`, `created_at`) VALUES
(2, 'Test Admin', 'testadmin@gmail.com', NULL, '$2y$10$hmNjINITbbmK20RV0738BeLWzk7XrQCNS3avcDlxbY6PtFJju73FG', 'admin', 'adult', NULL, 'active', 1, NULL, '2026-02-26 07:39:29'),
(5, 'Onduso Bonface', 'bonniecomputerhub24@gmail.com', '+254729820689', '$2y$10$yo5qrM9p84zuYzO0MKbQ9.qf8dp5BEImfqch6yh5EMEzsEs/I74f.', 'member', 'youth', NULL, 'active', 0, '467266', '2026-02-26 08:08:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bs_users`
--
ALTER TABLE `bs_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_group_id` (`group_id`),
  ADD KEY `idx_age_group` (`age_group`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bs_users`
--
ALTER TABLE `bs_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
