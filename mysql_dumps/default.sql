-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 08. Jun 2014 um 14:06
-- Server Version: 5.5.37
-- PHP-Version: 5.5.12-2+deb.sury.org~precise+1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `buch`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `benutzer`
--

CREATE TABLE IF NOT EXISTS `benutzer` (
  `bnz_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bnz_benutzername` varchar(45) NOT NULL,
  `bnz_kennworthash` varchar(45) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`bnz_id`),
  UNIQUE KEY `bnz_benutzername_ui` (`bnz_benutzername`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eintrag`
--

CREATE TABLE IF NOT EXISTS `eintrag` (
  `etg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `knt_id` int(10) unsigned NOT NULL,
  `ett_id` int(10) unsigned NOT NULL,
  `etg_wert` varchar(100) NOT NULL,
  PRIMARY KEY (`etg_id`),
  KEY `FK_eintrag_kontakt` (`knt_id`),
  KEY `FK_eintrag_eitragstyp` (`ett_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eintragstyp`
--

CREATE TABLE IF NOT EXISTS `eintragstyp` (
  `ett_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ett_name` varchar(45) NOT NULL,
  `ett_eindeutig` tinyint(1) NOT NULL,
  `ett_reihenfolge` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ett_id`),
  UNIQUE KEY `ett_name_ui` (`ett_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `eintragstyp`
--

INSERT INTO `eintragstyp` (`ett_id`, `ett_name`, `ett_eindeutig`, `ett_reihenfolge`) VALUES
(1, 'Vorname', 1, 1),
(2, 'Nachname', 1, 2),
(3, 'Firmenname', 1, 3),
(4, 'Telefonnummer', 0, 4),
(5, 'E-Mail', 0, 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kontakt`
--

CREATE TABLE IF NOT EXISTS `kontakt` (
  `knt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bnz_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`knt_id`),
  KEY `FK_kontakt_benutzer` (`bnz_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `eintrag`
--
ALTER TABLE `eintrag`
  ADD CONSTRAINT `FK_eintrag_eitragstyp` FOREIGN KEY (`ett_id`) REFERENCES `eintragstyp` (`ett_id`),
  ADD CONSTRAINT `FK_eintrag_kontakt` FOREIGN KEY (`knt_id`) REFERENCES `kontakt` (`knt_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `kontakt`
--
ALTER TABLE `kontakt`
  ADD CONSTRAINT `FK_kontakt_benutzer` FOREIGN KEY (`bnz_id`) REFERENCES `benutzer` (`bnz_id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
