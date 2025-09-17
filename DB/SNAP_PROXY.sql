-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 16, 2025 at 06:38 PM
-- Server version: 9.1.0
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payment`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_account`
--

DROP TABLE IF EXISTS `api_account`;
CREATE TABLE IF NOT EXISTS `api_account` (
  `id_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_account`
--

INSERT INTO `api_account` (`id_account`, `user_key`, `secret_key`) VALUES
('IvC7C70wOTs4FiHRhs02M1hdy3CaBHceKNnh', '3mQKUd4ikicxxG3EQHVy6LcjSiHV8IlRXYgP', '$2y$10$dTe82x.WulTdjrvDiuX2z.Bl0Zt2gHNgsb6exvB3YA4S4KXMCJbJC');

-- --------------------------------------------------------

--
-- Table structure for table `api_token`
--

DROP TABLE IF EXISTS `api_token`;
CREATE TABLE IF NOT EXISTS `api_token` (
  `id_api_token` int NOT NULL AUTO_INCREMENT,
  `id_account` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'x-token',
  `datetime_creat` timestamp NOT NULL,
  `datetime_expired` timestamp NOT NULL,
  PRIMARY KEY (`id_api_token`),
  KEY `id_account` (`id_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_payment`
--

DROP TABLE IF EXISTS `log_payment`;
CREATE TABLE IF NOT EXISTS `log_payment` (
  `id_log_payment` int NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_time` datetime NOT NULL,
  `status_code` tinyint DEFAULT NULL,
  `payment_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gross_amount` int DEFAULT NULL,
  `fraud_status` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_log_payment`),
  KEY `kode_transaksi` (`kode_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_transaksi`
--

DROP TABLE IF EXISTS `order_transaksi`;
CREATE TABLE IF NOT EXISTS `order_transaksi` (
  `id_order_transaksi` int NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(225) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `order_id` varchar(225) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'order_id',
  `datetime` datetime NOT NULL,
  `ServerKey` text NOT NULL,
  `Production` char(4) NOT NULL,
  `gross_amount` int NOT NULL,
  `name` text NOT NULL,
  `email` text NOT NULL,
  `phone` varchar(225) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `snapToken` varchar(225) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id_order_transaksi`),
  KEY `kode_transaksi` (`kode_transaksi`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `setting_payment`
--

DROP TABLE IF EXISTS `setting_payment`;
CREATE TABLE IF NOT EXISTS `setting_payment` (
  `id_setting_payment` int NOT NULL AUTO_INCREMENT,
  `id_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `env_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urll_call_back` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL pada aplikasi anda untuk mendapatkan calback',
  `url_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL ke midtrans untuk cek status transaksi',
  `id_marchant` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL untuk melakkukan snap',
  `production` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'true/false',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_setting_payment`),
  KEY `id_account` (`id_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_token`
--
ALTER TABLE `api_token`
  ADD CONSTRAINT `token_to_account` FOREIGN KEY (`id_account`) REFERENCES `api_account` (`id_account`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `setting_payment`
--
ALTER TABLE `setting_payment`
  ADD CONSTRAINT `setting_to_account` FOREIGN KEY (`id_account`) REFERENCES `api_account` (`id_account`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
