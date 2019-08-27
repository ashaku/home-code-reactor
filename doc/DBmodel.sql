-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mar 27 Août 2019 à 10:47
-- Version du serveur :  5.6.15-log
-- Version de PHP :  5.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `forge`
--

-- --------------------------------------------------------

--
-- Structure de la table `projets`
--

CREATE TABLE IF NOT EXISTS `projets` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `type` enum('JS','PHP') NOT NULL,
  `date_creation` date NOT NULL,
  `name` varchar(60) NOT NULL,
  `folder` varchar(60) NOT NULL,
  `page` varchar(60) NOT NULL,
  `desc` text NOT NULL,
  `status` enum('run','pause','stop') NOT NULL,
  `date_modif` date NOT NULL,
  `db_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

--
-- Structure de la table `tests`
--

CREATE TABLE IF NOT EXISTS `tests` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `project_id` int(3) NOT NULL,
  `type` enum('unitaire','fonctionnel','metier','critique') NOT NULL,
  `name` varchar(64) NOT NULL,
  `desc` text NOT NULL,
  `last_run` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `status` enum('OK','KO','NR') NOT NULL DEFAULT 'NR',
  `debug` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=410 ;

--
-- Structure de la table `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `project_id` int(3) NOT NULL,
  `type` enum('evolution','correctif','refactoring') NOT NULL,
  `name` varchar(60) NOT NULL,
  `desc` text NOT NULL,
  `date_open` datetime DEFAULT NULL,
  `date_close` datetime DEFAULT NULL,
  `version` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=132 ;

--
-- Structure de la table `versions`
--

CREATE TABLE IF NOT EXISTS `versions` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `project_id` int(3) NOT NULL,
  `num_version` varchar(16) NOT NULL,
  `date` date NOT NULL,
  `notes` text NOT NULL,
  `code` tinyint(1) NOT NULL DEFAULT '0',
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `nb_func` int(3) NOT NULL,
  `nb_bug` int(3) NOT NULL,
  `nb_dette` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
