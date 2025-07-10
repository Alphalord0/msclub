-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 05:22 PM
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
-- Database: `cyber_community_chat`
--

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `type` enum('group','private') NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `type`, `name`, `created_at`) VALUES
(1, 'group', 'General Chat', '2025-06-03 21:02:11'),
(2, 'private', NULL, '2025-06-03 21:26:16'),
(3, 'private', NULL, '2025-06-03 21:26:54'),
(4, 'private', NULL, '2025-06-03 21:26:54'),
(5, 'private', NULL, '2025-06-03 21:32:34'),
(6, 'private', NULL, '2025-06-03 22:11:04'),
(7, 'group', 'Admin Chat', '2025-06-03 22:40:40'),
(8, 'private', NULL, '2025-06-05 05:25:38'),
(9, 'private', NULL, '2025-06-06 04:58:09'),
(10, 'private', NULL, '2025-06-07 13:13:31'),
(11, 'private', NULL, '2025-06-11 08:45:03'),
(12, 'group', NULL, '2025-06-24 02:53:13');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation_participants`
--

INSERT INTO `conversation_participants` (`conversation_id`, `user_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(3, 1),
(3, 3),
(4, 1),
(4, 4),
(5, 1),
(5, 5),
(6, 2),
(6, 5),
(7, 1),
(7, 2),
(7, 3),
(7, 4),
(7, 6),
(8, 2),
(8, 3),
(9, 2),
(9, 6),
(10, 1),
(10, 6),
(11, 4),
(11, 6);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  `message_text` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `edited` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_reads`
--

CREATE TABLE `message_reads` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `img` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `img`, `username`, `password_hash`, `created_at`) VALUES
(1, '', 'Fishman', '$2y$10$fDEZ1pNYXeOBFLDogMUFFun7mrVSeuE8MlGDaz5ARt9eB7DrlA3Qi', '2025-06-03 21:03:00'),
(2, '', 'Alpha0', '$2y$10$1j6eAZQCVCub.1mjesivxeT1m4Mo28FnCGe6NxET3Kp39uVdamJAC', '2025-06-03 21:26:12'),
(3, '', 'Emperor', '$2y$10$gJzZVIO20sTbhSy6P7o3IeHBQ/Cwd98WFEk0ZRVcX9W21xmmgwhgi', '2025-06-03 21:26:12'),
(4, '', 'Empress', '$2y$10$WGd.UXR06nhCde3WiRP0ZOx4WM//La4FspoIyTmd9udaK0940vR8m', '2025-06-03 21:26:12'),
(5, '', 'sflame', '$2y$10$v0as9O2Le0PNxEK0eeDjIeDoqdlJ9CM68g87PbO4O9X40wQ32mh5e', '2025-06-03 21:26:13'),
(6, '', 'Dlaw', '$2y$10$X7jyIH29lB0B6JVKwjeEA.xVtJt1LmDPFr8wZ3RWOZM6YQO8bETzm', '2025-06-06 04:50:28'),
(7, '', 'Porsche', '$2y$10$WRRL2/y1Ov1/xiZ3zVHJSOcYgijdOScWL8ccvzGwgL/VFGTrYkRUK', '2025-06-11 08:21:18'),
(8, '', 'patron 1', '$2y$10$foWTR27UFwXkNlIW82BHE.Qy8MLWBTj2QpboZQ4rM21JEO/zxIbby', '2025-06-17 21:16:18'),
(9, '', 'patron 2', '$2y$10$kSvsO5n4cvJwXOOBGP7hQut4tF8sFIGfsqZE7jeSCJsRq.z.qHvNS', '2025-06-17 21:18:16'),
(10, '', 'patron 3', '$2y$10$T/YB.yYg76tewBjlAXiBmOL2zg6Kh3MCM74atsx2CfuWw0KLbrx2i', '2025-06-17 21:18:22'),
(11, '', 'try', '$2y$10$kh0MF61d6Q3RPOtmd2pYVeWZ5rDkyDh0GFA8BFCCPBf7ajOXAVery', '2025-06-17 21:22:03'),
(12, '', 'oo', '$2y$10$/UXNlVXDXJ3yiRMLIOHxv.3kEbAHh8Dpuvu.cXQvmdU4i41fNxzL.', '2025-06-17 21:23:53'),
(13, '', 'Head of Department', '$2y$10$wrAbtfdp26p36ANRK1MfxewOYPS3/WPsIuoQvFTKDxWjAk2wGf5rO', '2025-06-20 23:09:28'),
(14, '', 'male', '$2y$10$/NOe/JJ/6NjbYXkhq/8K6.w3mmgJhITNM2mhBFojj6Zay6msBZxUy', '2025-06-21 00:02:09'),
(15, '', 'female', '$2y$10$5QkR2GHW.FfVFbBswnu1ye7ucBjQIFwrqxqrPK93dLnRDRDzqgl2i', '2025-06-21 00:02:25'),
(16, '', 'Margaret', '$2y$10$y4IDnZrewuBb/LPc7TzpO.sz6dPMBtVqIJWM8olQf8qJXHYjaF2HG', '2025-06-21 00:04:52'),
(17, '', '4Rin3R', '$2y$10$olOMs.C.7TqB81Z/Aa3lk.8RwzKXN8dGKWFXIo1fUsxxXLjklUoJi', '2025-06-21 01:31:32'),
(18, '', 'cur1ent', '$2y$10$Sea0GpsRQoFhX3SnGMYXuub6HMqy46EpAyyzbA3YeWcaCaWlUAJ6C', '2025-06-21 01:38:28'),
(19, '', 'xp1ain', '$2y$10$9N794geU5X2XsY3NZ/GXz.SYZpJOiKJkhApOCR6cevZWLg3QXaDcC', '2025-06-21 01:40:31'),
(20, '', 'soul_marker0', '$2y$10$1ROkhmWUmt.7nxpuxWpiP.d22IsViO/Ux4sgpandN5iHdXIHsM8Vi', '2025-06-21 01:45:02'),
(21, '', 'LeChef', '$2y$10$c2T9jVrqDC5RRKld5v3bsuNKfahdp1f3nwke5QX/dNHBNCPgbOMeO', '2025-06-21 01:48:18'),
(22, '', 'knightmare_640', '$2y$10$b5JTMI/2NwU8pdqmYEAU9OqcDMJfQ9Yrh2x0U7rQaDG5txWVeHKG6', '2025-06-21 01:51:32'),
(23, '', 'itzdell', '$2y$10$PidltIxvIHDVHhX8t7mN0uEjJzwp9PgHWVdZTPe/mQMUpdK9sc7wO', '2025-06-21 01:58:57'),
(24, '', 'Proxy lynx', '$2y$10$KKoguCDH64wgTCPPqM4DDOnEhCCt5Q10Yl6Jd/23LWSl..1eNXcWm', '2025-06-21 02:01:55'),
(25, '', 'Andyoneonone', '$2y$10$oMKUcAnZQmkpNpt94mpyue43JSlqg5DcrD1.aQawlogWc9s8LQZwe', '2025-06-21 02:06:38'),
(26, '', 'rapperpat', '$2y$10$A9mfM2aJKI1A3D2O0739VuEUsGjvJi8aSj3Wwcw2h7aB.lE86GEeq', '2025-06-21 02:18:54'),
(27, '', 'RayRoy', '$2y$10$q8uot21qRly0ShcxXpqufOUJlFmlvt5Gb4uq4I58qtYxFBW4v1Hgi', '2025-06-27 13:39:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`conversation_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `message_reads`
--
ALTER TABLE `message_reads`
  ADD PRIMARY KEY (`message_id`,`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `conversation_participants_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
