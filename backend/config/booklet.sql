-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Feb 09, 2024 at 07:46 AM
-- Server version: 5.7.39
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `booklet`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `album_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `available` tinyint(4) NOT NULL DEFAULT '0',
  `price` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `album_pages`
--

CREATE TABLE `album_pages` (
  `page_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `page_num` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `site_variables`
--

CREATE TABLE `site_variables` (
  `site_key` varchar(32) NOT NULL,
  `site_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stickers`
--

CREATE TABLE `stickers` (
  `sticker_id` int(11) NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `page_id` int(11) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `img_path` text NOT NULL,
  `img_path_secret` text NOT NULL,
  `rarity_lvl` int(11) NOT NULL DEFAULT '0',
  `obtainable` tinyint(1) NOT NULL DEFAULT '0',
  `pos_x` float NOT NULL DEFAULT '50',
  `pos_y` float NOT NULL DEFAULT '50',
  `scale` float NOT NULL DEFAULT '30',
  `rotation` float NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(254) NOT NULL,
  `role` enum('user','mod','admin') NOT NULL DEFAULT 'user',
  `is_creator` tinyint(4) NOT NULL DEFAULT '0',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `avatar` varchar(128) DEFAULT NULL,
  `passhash` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_email_confirmation`
--

CREATE TABLE `user_email_confirmation` (
  `confirm_id` int(11) NOT NULL,
  `code` varchar(8) NOT NULL,
  `email` varchar(254) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT '0',
  `validated` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_addr` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_logins`
--

CREATE TABLE `user_logins` (
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip_addr` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_rel_albums`
--

CREATE TABLE `user_rel_albums` (
  `rel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `obtained_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_rel_stickers`
--

CREATE TABLE `user_rel_stickers` (
  `rel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sticker_id` int(11) NOT NULL,
  `is_sticked` tinyint(1) NOT NULL DEFAULT '0',
  `obtained_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`album_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `album_pages`
--
ALTER TABLE `album_pages`
  ADD PRIMARY KEY (`page_id`),
  ADD KEY `album_id` (`album_id`);

--
-- Indexes for table `site_variables`
--
ALTER TABLE `site_variables`
  ADD UNIQUE KEY `site_key` (`site_key`);

--
-- Indexes for table `stickers`
--
ALTER TABLE `stickers`
  ADD PRIMARY KEY (`sticker_id`),
  ADD KEY `stickers_ibfk_1` (`album_id`),
  ADD KEY `stickers_ibfk_2` (`page_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_email_confirmation`
--
ALTER TABLE `user_email_confirmation`
  ADD PRIMARY KEY (`confirm_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `ip_addr` (`ip_addr`);

--
-- Indexes for table `user_logins`
--
ALTER TABLE `user_logins`
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_rel_albums`
--
ALTER TABLE `user_rel_albums`
  ADD PRIMARY KEY (`rel_id`),
  ADD KEY `user_rel_albums_ibfk_1` (`album_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_rel_stickers`
--
ALTER TABLE `user_rel_stickers`
  ADD PRIMARY KEY (`rel_id`),
  ADD KEY `sticker_user_collected_ibfk_1` (`user_id`),
  ADD KEY `sticker_id` (`sticker_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `album_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `album_pages`
--
ALTER TABLE `album_pages`
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stickers`
--
ALTER TABLE `stickers`
  MODIFY `sticker_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_email_confirmation`
--
ALTER TABLE `user_email_confirmation`
  MODIFY `confirm_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_rel_albums`
--
ALTER TABLE `user_rel_albums`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_rel_stickers`
--
ALTER TABLE `user_rel_stickers`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `albums`
--
ALTER TABLE `albums`
  ADD CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `album_pages`
--
ALTER TABLE `album_pages`
  ADD CONSTRAINT `album_pages_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`album_id`) ON DELETE CASCADE;

--
-- Constraints for table `stickers`
--
ALTER TABLE `stickers`
  ADD CONSTRAINT `stickers_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`album_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stickers_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `album_pages` (`page_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_email_confirmation`
--
ALTER TABLE `user_email_confirmation`
  ADD CONSTRAINT `user_email_confirmation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_logins`
--
ALTER TABLE `user_logins`
  ADD CONSTRAINT `user_logins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_rel_albums`
--
ALTER TABLE `user_rel_albums`
  ADD CONSTRAINT `user_rel_albums_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`album_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_rel_albums_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_rel_stickers`
--
ALTER TABLE `user_rel_stickers`
  ADD CONSTRAINT `user_rel_stickers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_rel_stickers_ibfk_2` FOREIGN KEY (`sticker_id`) REFERENCES `stickers` (`sticker_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
