-- phpMyAdmin SQL Dump
-- version 4.4.15.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2016 at 04:41 PM
-- Server version: 5.6.30
-- PHP Version: 7.0.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `codeigniter`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint(10) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL,
  `first_name` varchar(200) DEFAULT NULL,
  `last_name` varchar(200) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `token` text,
  `date_create` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `role` varchar(100) DEFAULT 'unknown',
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(10) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL,
  `first_name` varchar(200) DEFAULT NULL,
  `last_name` varchar(200) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `token` text,
  `date_create` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `role` varchar(100) DEFAULT 'subscriber',
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE IF NOT EXISTS `options` (
  `id` bigint(10) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `options_name` varchar(255) NOT NULL,
  `options_value` longtext NOT NULL,
  `options_autoload` varchar(3) NOT NULL DEFAULT 'no',
  UNIQUE KEY `options_name` (`options_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(40) PRIMARY KEY NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `data` blob NOT NULL,
  KEY `sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

