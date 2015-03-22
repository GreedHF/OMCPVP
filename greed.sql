-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2015 at 03:38 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `greed`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `regip` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `password` varchar(128) NOT NULL,
  `lastlogin` int(11) NOT NULL,
  `sessions` int(11) NOT NULL,
  `privileges` int(11) NOT NULL,
  `salt` varchar(12) NOT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `accounts`
--

-- --------------------------------------------------------

--
-- Table structure for table `balances`
--

CREATE TABLE IF NOT EXISTS `balances` (
  `username` varchar(40) NOT NULL,
  `balance` float NOT NULL DEFAULT '100',
  PRIMARY KEY (`username`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `balances`
--

-- --------------------------------------------------------

--
-- Table structure for table `bets`
--

CREATE TABLE IF NOT EXISTS `bets` (
  `id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `result` int(11) NOT NULL,
  `challenger` varchar(40) NOT NULL,
  `challenged` varchar(40) NOT NULL,
  `roll` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `id_2` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bets`
--

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

CREATE TABLE IF NOT EXISTS `challenges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `challenger` varchar(40) NOT NULL,
  `challenged` varchar(40) NOT NULL,
  `amount` float NOT NULL,
  `time` int(11) NOT NULL,
  `accepted` int(11) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

--
-- Dumping data for table `challenges`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE IF NOT EXISTS `chat` (
  `user` varchar(40) NOT NULL,
  `msg` varchar(100) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `chat`
--

-- --------------------------------------------------------

--
-- Table structure for table `misery4crackers`
--

CREATE TABLE IF NOT EXISTS `misery4crackers` (
  `ip` int(11) NOT NULL,
  `cookie` varchar(32) NOT NULL,
  `lastlogin` int(11) NOT NULL,
  `attempts` int(11) NOT NULL,
  PRIMARY KEY (`ip`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `misery4crackers`
--

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `ip` int(11) NOT NULL,
  `lastactive` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `csrf` char(32) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  `cookie` varchar(32) NOT NULL,
  PRIMARY KEY (`cookie`),
  UNIQUE KEY `cookie` (`cookie`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sessions`
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
