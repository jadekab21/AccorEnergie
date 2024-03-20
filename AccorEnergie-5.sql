-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Mar 20, 2024 at 03:04 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `AccorEnergie`
--

-- --------------------------------------------------------

--
-- Table structure for table `Comments`
--

CREATE TABLE `Comments` (
  `comment_id` int NOT NULL,
  `intervention_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `content` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `Comments`
--

INSERT INTO `Comments` (`comment_id`, `intervention_id`, `user_id`, `content`, `created_at`) VALUES
(2, 1, 8, 'cc', '2024-03-13 00:29:55'),
(4, 1, 8, 'salut', '2024-03-13 00:38:45'),
(5, 1, 8, 'f', '2024-03-13 00:44:38'),
(6, 1, 8, 'é', '2024-03-13 09:52:49'),
(8, 1, NULL, 'ferf', '2024-03-14 01:02:02'),
(9, 1, NULL, 'gfr', '2024-03-14 01:08:37'),
(10, 1, 8, 'rfe', '2024-03-14 01:10:39'),
(11, 1, 6, 'cf', '2024-03-14 01:11:29'),
(12, 19, 21, 's', '2024-03-15 12:45:20'),
(13, 19, 21, 'djza', '2024-03-16 21:33:04'),
(14, 19, 6, 'test', '2024-03-19 13:17:29'),
(15, 19, 8, 'ece', '2024-03-20 14:13:03');

-- --------------------------------------------------------

--
-- Table structure for table `InterventionIntervenants`
--

CREATE TABLE `InterventionIntervenants` (
  `intervention_id` int NOT NULL,
  `intervenant_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `InterventionIntervenants`
--

INSERT INTO `InterventionIntervenants` (`intervention_id`, `intervenant_id`) VALUES
(1, 6),
(19, 6),
(20, 6),
(21, 6),
(27, 6);

-- --------------------------------------------------------

--
-- Table structure for table `Interventions`
--

CREATE TABLE `Interventions` (
  `intervention_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `client_id` int DEFAULT NULL,
  `ids` int DEFAULT NULL,
  `date_planned` datetime DEFAULT NULL,
  `idu` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `Interventions`
--

INSERT INTO `Interventions` (`intervention_id`, `title`, `description`, `client_id`, `ids`, `date_planned`, `idu`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'New inter', 'descriptionfre yerf', NULL, 1, '2024-03-13 00:00:00', 2, '2024-01-17 01:31:32', '2024-03-19 10:57:19', 7),
(19, 'final test', 'df jknbkj', 21, 2, '2024-03-08 01:21:00', 9, '2024-03-15 00:21:29', '2024-03-20 14:15:39', 7),
(20, 'test', 'kjh', 8, 2, '2024-03-22 02:07:00', 6, '2024-03-17 01:07:57', '2024-03-19 11:20:32', 7),
(21, 'kl,', 'kj', NULL, 13, '2024-03-29 04:22:00', 13, '2024-03-17 03:22:40', '2024-03-20 14:19:25', NULL),
(27, 'test', 'dzadza', NULL, 7, '2024-03-24 15:16:00', 9, '2024-03-20 14:16:08', '2024-03-20 14:19:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE `Roles` (
  `role_id` int NOT NULL,
  `role_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `Roles`
--

INSERT INTO `Roles` (`role_id`, `role_name`) VALUES
(1, 'client'),
(2, 'admin'),
(3, 'intervenant'),
(4, 'standardiste');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `ids` int NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`ids`, `status`) VALUES
(2, 'Annulée'),
(7, 'cloturée'),
(8, 'en cours'),
(13, 'Nouvelle'),
(14, 'En attente'),
(15, 'Completée'),
(16, 'Reportée');

-- --------------------------------------------------------

--
-- Table structure for table `urgences`
--

CREATE TABLE `urgences` (
  `idu` int NOT NULL,
  `urgency_level` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `urgences`
--

INSERT INTO `urgences` (`idu`, `urgency_level`) VALUES
(2, 'extreme'),
(6, 'critique'),
(9, 'extreme'),
(13, 'faible');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `username`, `password`, `role_id`, `email`, `created_at`, `updated_at`) VALUES
(6, 'testinter', '$2y$10$6tkTKuB.ON0HjTze/e/ka.ot1z532xkajUdrXZD1rENsKNkijCaIq', 3, 'intervenant@gmail.com', '2024-01-17 00:36:17', '2024-01-17 00:37:12'),
(7, 'standardiste', '$2y$10$CcNPwy0B7fdoNSqYFEQc8u4iPBP/PTdi1mzRpd5jzgLbBGEqCSxn.', 4, 'stand@gmail.COM', '2024-01-17 02:37:14', '2024-01-17 02:38:15'),
(8, 'admin', '$2y$10$GNykYeAZVz53I.19e3L82eWUqYUkYckcGIF7yHSuVVrVF12N0UAYq', 2, 'admin@jn.c', '2024-01-17 03:06:32', '2024-01-17 03:07:00'),
(21, 'yas', '$2y$10$w1Gnf6S4s5YGkLUMNBSMOuhCtROPvup3k4I1UhTpMCNTfoPtsBC6.', 1, 'yas@gmail.com', '2024-03-14 23:44:15', '2024-03-14 23:44:15'),
(39, 'ece', '$2y$10$UXy.9yqVhOv1oLEr.zNSqu3Bm5C9NE/1bo0kxfw51S3RVNyesnqSq', 3, 'redz@ee.dez', '2024-03-19 21:57:40', '2024-03-20 14:13:37'),
(47, 'test', '$2y$10$XrQ1XUTXtRAsRMAucT6mFem.O2wMYrFq5EJBmxc6of2YsHqoqaMIq', 1, 'testtestt@ece.fr', '2024-03-20 14:11:21', '2024-03-20 14:11:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Comments`
--
ALTER TABLE `Comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `intervention_id` (`intervention_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `InterventionIntervenants`
--
ALTER TABLE `InterventionIntervenants`
  ADD PRIMARY KEY (`intervention_id`,`intervenant_id`),
  ADD KEY `fk_intervenant` (`intervenant_id`);

--
-- Indexes for table `Interventions`
--
ALTER TABLE `Interventions`
  ADD PRIMARY KEY (`intervention_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`ids`);

--
-- Indexes for table `urgences`
--
ALTER TABLE `urgences`
  ADD PRIMARY KEY (`idu`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Comments`
--
ALTER TABLE `Comments`
  MODIFY `comment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `Interventions`
--
ALTER TABLE `Interventions`
  MODIFY `intervention_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `role_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `ids` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `urgences`
--
ALTER TABLE `urgences`
  MODIFY `idu` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`intervention_id`) REFERENCES `Interventions` (`intervention_id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `InterventionIntervenants`
--
ALTER TABLE `InterventionIntervenants`
  ADD CONSTRAINT `fk_intervenant` FOREIGN KEY (`intervenant_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_intervention` FOREIGN KEY (`intervention_id`) REFERENCES `Interventions` (`intervention_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Interventions`
--
ALTER TABLE `Interventions`
  ADD CONSTRAINT `interventions_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
