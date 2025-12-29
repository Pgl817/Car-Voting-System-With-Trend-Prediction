-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for carvoteddb
CREATE DATABASE IF NOT EXISTS `carvoteddb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `carvoteddb`;

-- Dumping structure for table carvoteddb.cars
CREATE TABLE IF NOT EXISTS `cars` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL DEFAULT '0',
  `username` varchar(100) DEFAULT NULL,
  `car_name` varchar(100) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `image_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `approval_status` enum('Pending','Approved','Rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table carvoteddb.cars: ~7 rows (approximately)
INSERT INTO `cars` (`id`, `user_id`, `username`, `car_name`, `description`, `image_path`, `uploaded_at`, `approval_status`) VALUES
	(9, 5, 'K.', 'Honda Civic FE', 'Honda Civic FE\r\n-Custom Downpipe\r\n-R18 with BBS custom forged rim\r\n-Including full ambient light', NULL, NULL, 'Approved'),
	(10, 5, 'K.', 'Lexus IS300', 'Air Suspension', NULL, NULL, 'Rejected'),
	(11, 5, 'K.', 'Lexus IS300', 'Lexus IS 300\r\n-Custom Air Suspension\r\n-6 pot Brake Kit\r\n-Full Exhaust System \r\n-R17 with Custom Forged Rim', NULL, NULL, 'Approved'),
	(12, 3, 'Boss', 'Porsche GT3 Boxster', 'Custom Full Exhaust System\r\nEndless brake kit\r\nCustom Rim', NULL, NULL, 'Approved'),
	(20, 3, 'Boss', 'GTR R35', 'GTR R35\r\n-Full Custom Widebody\r\n-R18 with custom Forged Rim\r\n-Full Exhaust system\r\n-Carbon fiber GT Wing\r\n-Nissan 6 pot Brake Kit', 'uploads/1766812854_4044_G.jpg', NULL, 'Approved'),
	(21, 6, 'GT3', 'Porsche GT3 RS', 'Porsche GT3 RS\r\n- R18 with custom BBS rim\r\n- Carbon Fiber GT wing\r\n- Recaro Seat modification\r\n- Full exhaust system\r\n- Inspeed Brake kit', 'uploads/1766895818_8136_af7feea0-3ede-431f-ad85-bd0ada8d1c21.jpg', NULL, 'Approved'),
	(22, 7, 'Supra', 'Supra 2.0', 'Supra 2.0\r\n-Custom exhaust\r\n-4 pot brake kit\r\n-Matte Grey Wrapping', 'uploads/1766896716_4422_fd3717f9-57d0-4789-b6c8-1e734797b97d.jpg', NULL, 'Approved'),
	(23, 7, 'Supra', 'Supra 3.0', 'Supra 3.0\r\n-Full CF Trim\r\n-Varis GT wing\r\n-R20 with Advan GT beyond Wheels\r\n-HKS Intake, TUrbo Muffler\r\n-Valenti Tail Lamp', 'uploads/1766896956_4199_6009f778-efd6-47b0-afb2-47c2dadf3a86.jpg', NULL, 'Approved');

-- Dumping structure for table carvoteddb.car_images
CREATE TABLE IF NOT EXISTS `car_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `car_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__cars` (`car_id`),
  CONSTRAINT `FK__cars` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table carvoteddb.car_images: ~31 rows (approximately)
INSERT INTO `car_images` (`id`, `car_id`, `image_path`) VALUES
	(1, 9, 'uploads/1765270184_0_5390_1.jpg'),
	(2, 9, 'uploads/1765270184_1_5390_2.jpg'),
	(3, 9, 'uploads/1765270184_2_5390.jpg'),
	(4, 10, 'uploads/1765472770_8086_lexus.jpg'),
	(5, 10, 'uploads/1765472770_2693_lexus1.jpg'),
	(6, 10, 'uploads/1765472770_7183_lexus2.jpg'),
	(7, 10, 'uploads/1765472770_7648_lexus3.jpg'),
	(8, 10, 'uploads/1765472770_9086_lexus4.jpg'),
	(9, 11, 'uploads/1765473888_8548_lexus.jpg'),
	(10, 11, 'uploads/1765473888_7703_lexus1.jpg'),
	(11, 11, 'uploads/1765473888_9399_lexus2.jpg'),
	(12, 11, 'uploads/1765473888_1009_lexus3.jpg'),
	(13, 11, 'uploads/1765473888_5016_lexus4.jpg'),
	(14, 12, 'uploads/1766455090_9330_P.jpg'),
	(15, 12, 'uploads/1766455090_9816_P1.jpg'),
	(16, 12, 'uploads/1766455090_9860_P2.jpg'),
	(17, 12, 'uploads/1766455090_1746_P3.jpg'),
	(53, 20, 'uploads/1766812854_8534_0746bd65-d174-4057-9ac8-f609c2400bc5.jpg'),
	(54, 20, 'uploads/1766812854_9396_3849167e-bb33-45da-8ac0-8498b9cfb3e9.jpg'),
	(55, 20, 'uploads/1766812854_9651_b1d01967-802e-4e39-acfa-cf463aab5524.jpg'),
	(56, 20, 'uploads/1766812854_6740_e2c9e565-3175-4d0b-b028-5683ac8eb2a3.jpg'),
	(57, 20, 'uploads/1766812854_4044_G.jpg'),
	(58, 21, 'uploads/1766895818_3148_97f04219-1e6c-4a37-a833-790985e2b5f6.jpg'),
	(59, 21, 'uploads/1766895818_8136_af7feea0-3ede-431f-ad85-bd0ada8d1c21.jpg'),
	(60, 21, 'uploads/1766895818_4772_b8754ad8-f65b-45d1-b746-153e7cc4063b.jpg'),
	(61, 21, 'uploads/1766895818_1192_e21a224e-5c87-4be5-8003-6d766f6d8691.jpg'),
	(62, 22, 'uploads/1766896716_6829_9d19874e-74f8-44c5-9d08-af755c1719c1.jpg'),
	(63, 22, 'uploads/1766896716_3897_225fcb69-3217-4a03-8c0c-354d2f6120ce.jpg'),
	(64, 22, 'uploads/1766896716_3438_bd1d1e5b-15f2-4a59-9ca4-311ed2bafcda.jpg'),
	(65, 22, 'uploads/1766896716_4422_fd3717f9-57d0-4789-b6c8-1e734797b97d.jpg'),
	(66, 23, 'uploads/1766896956_4199_6009f778-efd6-47b0-afb2-47c2dadf3a86.jpg'),
	(67, 23, 'uploads/1766896956_9906_3059459d-27e5-447c-ad63-360785269925.jpg'),
	(68, 23, 'uploads/1766896956_6701_aaf1f161-65be-4bc9-bc8d-b49c5098c6d3.jpg'),
	(69, 23, 'uploads/1766896956_8646_cc8aef83-6e59-46e1-8bc6-ad5015ec1f8c.jpg'),
	(70, 23, 'uploads/1766896956_2326_cf63569e-6aaf-46d3-8e30-77a773e71899.jpg');

-- Dumping structure for table carvoteddb.comments
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `username` varchar(100) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `comment_car` (`post_id`),
  KEY `comment_user` (`user_id`),
  CONSTRAINT `comment_car` FOREIGN KEY (`post_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table carvoteddb.comments: ~0 rows (approximately)
INSERT INTO `comments` (`id`, `post_id`, `user_id`, `username`, `comment`, `created_at`) VALUES
	(4, 9, 3, '', 'Good Car', '2025-12-15 16:15:49'),
	(5, 12, 5, '', 'Nice Car', '2025-12-27 03:58:29');

-- Dumping structure for table carvoteddb.likes
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Index 1` (`post_id`,`user_id`),
  KEY `FK_likes_users` (`user_id`),
  CONSTRAINT `FK_likes_cars` FOREIGN KEY (`post_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_likes_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table carvoteddb.likes: ~1 rows (approximately)
INSERT INTO `likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
	(50, 9, 3, NULL),
	(51, 12, 3, NULL);

-- Dumping structure for table carvoteddb.posts
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `handle` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_post_user` (`user_id`),
  CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table carvoteddb.posts: ~0 rows (approximately)

-- Dumping structure for table carvoteddb.scoretb
CREATE TABLE IF NOT EXISTS `scoretb` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `car_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table carvoteddb.scoretb: ~0 rows (approximately)

-- Dumping structure for table carvoteddb.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT 'default.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table carvoteddb.users: ~7 rows (approximately)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `profile_pic`) VALUES
	(1, 'Lin', 'linlin@gmail.com', '$2y$10$nAEvPPV70JUFb17gdsCBtuEe1WO3XGLXzCWMI8cVLtBMvTBLewlXG', 'user', 'default.png'),
	(2, 'Student', 'b230077A@sc.edu.my', '$2y$10$M8o.PVz4hlcTILzWfCGCBOYxmCDRR/RqhSN37voej3bVnnXDTgAna', 'admin', 'default.png'),
	(3, 'Boss', 'boss666@gmail.com', '$2y$10$ROLWkcQkEFCX9NtT8f756.qTjZJsF88xLb.h3kQqp2wjtbreGDBwu', 'user', 'uploads/profile/3_1764606246_Gg.png'),
	(4, 'Adam', 'adam7@gmail.com', '$2y$10$RCASvo3xmrqZ11XZBSNQHuVJuWeGx3HC5H3rC/7jvWBTr904Xw.9.', 'user', 'default.png'),
	(5, 'K.', 'K5390@gmail.com', '$2y$10$ELO7hSaOrxx0rbipPZtLCujsv/TDGPSdMgCdACqUz8mvNoF0gcqTO', 'user', 'default.png'),
	(6, 'GT3', 'porsche@gmail.com', '$2y$10$6NveD8zdM15Jv1DaVMtiHuhGkkQ3hwDzCursBjKcIqPSQ7flyIaia', 'user', 'default.png'),
	(7, 'Supra', 'supra@gmail.com', '$2y$10$nb.DqM.1XQUwg6xG4RPaX.WQTJ8k2cPjgHsXeQhs9EzMmB.4wAb.K', 'user', 'default.png'),
	(8, 'User 1', 'user@gmail.com', '$2y$10$cHGZtIVHmRJ.TmUCiBbtJ.aX3mxHTgzTQBzleu749sJGoN/6VEWuW', 'user', 'default.png');

-- Dumping structure for table carvoteddb.votes
CREATE TABLE IF NOT EXISTS `votes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_votes_cars` (`post_id`),
  KEY `FK_votes_users` (`user_id`),
  CONSTRAINT `FK_votes_cars` FOREIGN KEY (`post_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_votes_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table carvoteddb.votes: ~2 rows (approximately)
INSERT INTO `votes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
	(65, 11, 3, NULL),
	(67, 9, 3, NULL),
	(73, 20, 6, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
