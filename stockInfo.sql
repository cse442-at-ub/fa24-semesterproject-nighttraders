-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 29, 2024 at 06:52 PM
-- Server version: 8.0.39-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dlincogn_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `stockInfo`
--

CREATE TABLE `stockInfo` (
  `Symbol` VARCHAR(10) NOT NULL,
  `Name` VARCHAR(255) NOT NULL,
  `Exchange` VARCHAR(50),
  `Sector` VARCHAR(100),
  `Industry` VARCHAR(100),
  `EPS` DOUBLE,
  `LatestQuarter` DATE,
  `52WeekHigh` DOUBLE,
  `52WeekLow` DOUBLE,
  `AnalystTargetPrice` DOUBLE,
  `TimeSeries` LONGTEXT,
  PRIMARY KEY (`Symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stockInfo`
--

INSERT INTO `stockInfo` (`Symbol`, `Name`, `Exchange`, `Sector`, `Industry`, `EPS`, `LatestQuarter`, `52WeekHigh`, `52WeekLow`, `AnalystTargetPrice`, `TimeSeries`) VALUES
('IBM', 'International Business Machines', 'NYSE', 'TECHNOLOGY', 'COMPUTER & OFFICE EQUIPMENT', 9.08, '2024-06-30', 237.37, 136.30, 212.63, NULL);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
