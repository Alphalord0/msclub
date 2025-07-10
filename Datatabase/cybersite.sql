-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2025 at 02:22 AM
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
-- Database: `cybersite`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent') NOT NULL DEFAULT 'Absent',
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocked`
--

CREATE TABLE `blocked` (
  `user_id` int(11) NOT NULL,
  `unique_id` int(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `year` int(1) NOT NULL,
  `class` varchar(255) NOT NULL,
  `cnumber` varchar(2) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL DEFAULT 'elias62c19f6f3e2d06.81665529.jpg',
  `role` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inf`
--

CREATE TABLE `inf` (
  `n_id` int(11) NOT NULL,
  `notifications_name` text NOT NULL,
  `message` text NOT NULL,
  `active` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `msg_id` int(11) NOT NULL,
  `incoming_msg_id` int(255) NOT NULL,
  `outgoing_msg_id` int(255) NOT NULL,
  `msg` varchar(1000) NOT NULL,
  `viewed` enum('1','0') NOT NULL DEFAULT '0',
  `message_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `position`
--

CREATE TABLE `position` (
  `unique_id` int(255) NOT NULL,
  `postiton` varchar(255) NOT NULL,
  `max_value` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position`
--

INSERT INTO `position` (`unique_id`, `postiton`, `max_value`) VALUES
(1, 'H.O.D', 1),
(2, 'patron 1', 1),
(3, 'patron 2', 1),
(4, 'president', 1),
(5, '1st vice', 1),
(6, '2nd vice', 1),
(7, 'secretary', 1),
(8, 'Ass.secretary', 1),
(9, 'organizer', 1),
(10, 'Ass.organizer', 1),
(11, 'welfare', 1),
(12, 'treasurer ', 1),
(13, 'developer', 1),
(14, 'member', 10000000),
(15, 'Vice.Patron', 1);

-- --------------------------------------------------------

--
-- Table structure for table `rejected`
--

CREATE TABLE `rejected` (
  `user_id` int(11) NOT NULL,
  `unique_id` int(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `year` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `cnumber` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `terms` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `user_id` int(11) NOT NULL,
  `unique_id` int(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `year` int(1) NOT NULL,
  `class` varchar(20) NOT NULL,
  `cnumber` varchar(2) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL DEFAULT 'elias62c19f6f3e2d06.81665529.jpg',
  `status` varchar(255) NOT NULL,
  `terms` varchar(10) NOT NULL,
  `message` varchar(255) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `unique_id` int(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `year` varchar(1) NOT NULL,
  `class` varchar(255) NOT NULL,
  `cnumber` varchar(2) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `position` int(1) NOT NULL,
  `img` varchar(255) NOT NULL DEFAULT 'elias62c1925ef0e8f7.58402794.jpg',
  `status` varchar(255) NOT NULL,
  `terms` varchar(10) NOT NULL DEFAULT 'I agree'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `unique_id`, `fname`, `username`, `gender`, `email`, `password`, `year`, `class`, `cnumber`, `phone`, `role`, `position`, `img`, `status`, `terms`) VALUES
(8, 1540106807, 'David Ajao', 'Alpha0', 'male', 'davidadeajao@gmail.com', '$2y$10$SNJuNkyWHWHprO1oHKoHN.ILdOimDPd4aecOr3ysTtKQ4B.rwjBoi', '2', 'general arts', '9', '0549326436', 'admin', 13, '17491437251748002323lamborghini-si-n-4000x2000-10362.jpg', 'Offline now', 'I agree'),
(10, 134253970, 'Shobai Nii Nai', 'Emperor', 'male', 'singularityyue@proton.me', '$2y$10$luW.ePs47NNKold9wwDsTOx315qP6ygFCzAPhq2CmBSfsYzeJt/s.', '3', 'science', '11', '0536971421', 'admin', 4, '17501942551746208434incog.png', 'Offline now', 'I agree'),
(17, 241235565, 'Cyril Donkoh', 'xp1ain', 'male', 'mishimacyril@gmail.com', '$2y$10$Pp.9TnM2AcES5nd20i4NJei9POQT0fGLstxDhGBDKjks3vN06bYOK', '3', 'general arts', '9', '0536050845', 'admin', 9, '1748739605Picture6.png', 'Offline now', 'I agree'),
(21, 470430773, ' Kporsu Patrick', 'rapperpat', 'male', 'rapperpat@yahoo.com', '$2y$10$R1JNKJbi5WPWLiKyzTucqeKK3zsp8wcnE8jF96fC3FULJyr3pEoUC', '0', 'Null', 'Nu', '0244282039', 'admin', 1, '17501948561747163615elias62c1925ef0e8f7.58402794.jpg', 'Offline now', 'I agree'),
(22, 494794930, 'patron 2', 'patron 2', 'male', 'tron@gmail.com', '$2y$10$EIWjjTnJHGIwxC4b6ZtIhezUkhy7A1z9lukx5s.wblTkg8ar7DU9y', '0', 'Null', 'Nu', '04859493484', 'admin', 2, '17501949001747997916img (3).png', 'Offline now', 'I agree'),
(23, 753131594, 'Andy Aaron Mensah Hanyabui', 'Andyoneonone', 'male', 'andyoneonone@gmail.com', '$2y$10$4biCDR1gDwWPj6XrP7f8xen85ld/0XQMDpSIwwW7lZ7xqheG0Z.Ou', '0', 'Null', 'Nu', '0243450093', 'admin', 3, '17501949571747997445elias62c19f6f3e2d06.81665529.jpg', 'Offline now', 'I agree'),
(27, 535478411, 'Richard Scottflame', '4Rin3R', 'male', 'sflame@gmail.com', '', '3', 'science', '11', '0548937234923', 'admin', 10, '17504694921747317222IMG-20240513-WA0028.jpg', 'Offline now', 'I agree'),
(29, 1229235298, 'Morgan Gillman', 'soul_marker0', 'male', 'morgankgillman@gmail.com', '$2y$10$62iDR/NFwDudDsKJluRJk.QpH70/.v3dRBLpLOYD7hjcQDpHojHc.', '3', 'business', '3', '0501860774', 'admin', 12, '17491437871748923124images1684221851_Tokyo REvengers (3).jpg', 'Offline now', 'I agree'),
(30, 671280084, 'Margret Adzo', 'Empress', 'female', 'empress@gmail.com', '', '3', 'general arts', '9', '0530717574', 'admin', 7, '17504687431748000535neon-13.jpg', 'Offline now', 'I agree'),
(32, 731013662, 'Portia', 'Porsche', 'female', 'blackred@gmail.com', '$2y$10$89JizHGR/C0RLaDq6y1UGews8LLRJRxxsCupFnTwFWqU7Ob51bJh2', '3', 'general arts', '9', '05353345354', 'admin', 8, '1749405946Screenshot 2024-12-21 120958.png', 'Offline now', 'I agree'),
(33, 1156761196, 'Quist Winfred', 'cur1ent', 'male', 'amenu066@gmail.com', '$2y$10$siGMD56CK6CoGlU7tIqnN.R8/6wxOG7v9VvqPNj9w9mlKm6NaKXkO', '3', 'science', '11', '0596149461', 'user', 14, '17504698541748002323lamborghini-si-n-4000x2000-10362.jpg', 'Offline now', 'I agree'),
(34, 239392619, 'Simon Lomotey', 'LeChef', 'male', 'fiifi.lomotey@gmail.com', '$2y$10$ktOrPUn1b0dzUS/O5Bq5COEQq6bh.khR3zyQ2tnZnvoN8i2zF0oHu', '3', 'general arts', '9', '0503110896', 'admin', 6, '1750470483img (3).png', 'Offline now', 'I agree'),
(35, 433700090, 'Turawah Yushau Imam-', 'knightmare_640', 'male', 'muslimturawah@gmail.com', '$2y$10$bs4JRx84.zBaRT5QYyH0S.IzF2gSvrVlaoK3izCgdTfJiOCZJIUoO', '3', 'science', '11', '0596648226', 'user', 14, '1750470674Screenshot 2024-12-21 121110.png', 'Offline now', 'I agree'),
(36, 692835309, 'Brendiel Akoumany', 'itzdell', 'female', 'itzdell@gmail.com', '$2y$10$oQiUv5gBQ3SNikXtqwWkmuvekiRqv0FdWsEF6gj6Gr64kQZGJN6ai', '3', 'science', '5', '052554335', 'admin', 11, '17504711271749132480Screenshot 2024-12-21 121531.png', 'Offline now', 'I agree'),
(37, 355652177, 'Bright Asiam', 'Proxy lynx', '--Sele', 'proxy@gmail.com', '$2y$10$WJJc27YQD3hLDLbGzM2rde5C72Bshnvoda.kgMtHqfTfVz2B4jpS.', '3', 'general arts', '9', '05463757453', 'admin', 5, '175047130717482868691cfdcbd8243e8923a04e77b073804df0.jpg', 'Offline now', 'I agree'),
(38, 219361419, 'DONKOR RAYMOND', 'RayRoy', 'male', 'raymonddonkor52@gmail.com', '$2y$10$WH2P4jUFdtTlVA4b1Gh35urfV6BEbYkAbEkq4.4H32OSxtf.bnv0q', '0', 'Null', 'Nu', '233546335198', 'admin', 15, '17508963491747999108img (3).png', 'Offline now', 'I agree');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance_per_day` (`user_id`,`attendance_date`);

--
-- Indexes for table `blocked`
--
ALTER TABLE `blocked`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `inf`
--
ALTER TABLE `inf`
  ADD PRIMARY KEY (`n_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`msg_id`);

--
-- Indexes for table `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`unique_id`);

--
-- Indexes for table `rejected`
--
ALTER TABLE `rejected`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `positon_link` (`position`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blocked`
--
ALTER TABLE `blocked`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `inf`
--
ALTER TABLE `inf`
  MODIFY `n_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `unique_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `rejected`
--
ALTER TABLE `rejected`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `positon_link` FOREIGN KEY (`position`) REFERENCES `position` (`unique_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
