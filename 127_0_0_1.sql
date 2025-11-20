-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 20 nov. 2025 à 18:53
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `album_photo`
--
CREATE DATABASE IF NOT EXISTS `album_photo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `album_photo`;

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

DROP TABLE IF EXISTS `commentaire`;
CREATE TABLE IF NOT EXISTS `commentaire` (
  `id_commentaire` int NOT NULL AUTO_INCREMENT,
  `texte_commentaire` text NOT NULL,
  `date_commentaire` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_photo` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `statut_suppression` enum('ACTIF','ARCHIVE') DEFAULT 'ACTIF',
  `date_archivage` datetime DEFAULT NULL,
  PRIMARY KEY (`id_commentaire`),
  KEY `id_photo` (`id_photo`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `page`
--

DROP TABLE IF EXISTS `page`;
CREATE TABLE IF NOT EXISTS `page` (
  `id_page` int NOT NULL AUTO_INCREMENT,
  `intitule_page` varchar(100) NOT NULL,
  PRIMARY KEY (`id_page`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `page`
--

INSERT INTO `page` (`id_page`, `intitule_page`) VALUES
(1, 'Voyages'),
(2, 'Compétitions Sportives');

-- --------------------------------------------------------

--
-- Structure de la table `photo`
--

DROP TABLE IF EXISTS `photo`;
CREATE TABLE IF NOT EXISTS `photo` (
  `id_photo` int NOT NULL AUTO_INCREMENT,
  `emplacement_photo` varchar(255) NOT NULL,
  `legende_photo` varchar(255) DEFAULT NULL,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_utilisateur` int NOT NULL,
  `id_page` int NOT NULL,
  `statut_suppression` enum('ACTIF','ARCHIVE') DEFAULT 'ACTIF',
  `date_archivage` datetime DEFAULT NULL,
  PRIMARY KEY (`id_photo`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_page` (`id_page`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `photo`
--

INSERT INTO `photo` (`id_photo`, `emplacement_photo`, `legende_photo`, `date_ajout`, `id_utilisateur`, `id_page`, `statut_suppression`, `date_archivage`) VALUES
(1, 'Minecraft.jpg', 'Hub principal de mon serveur minecraft', '2025-11-19 18:20:47', 2, 1, 'ACTIF', NULL),
(2, 'logo2.png', 'test', '2025-11-19 18:29:19', 4, 2, 'ACTIF', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `est_administrateur` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `email`, `mot_de_passe`, `est_administrateur`) VALUES
(1, 'Admin M2L', 'admin@m2l.fr', 'b6edd10559b20cb0a3ddaeb15e5267cc', 1),
(2, 'Florian', 'florian.simon32320@gmail.com', '57f1a091860b520b0e3f04ab951b9b0b', 0),
(3, 'Florian', 'florian.simon@gmail.com', '57f1a091860b520b0e3f04ab951b9b0b', 0),
(4, 'Admin1', 'admin@gmail.fr', '81dc9bdb52d04dc20036dbd8313ed055', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
