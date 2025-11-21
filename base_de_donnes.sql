-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 21 nov. 2025 à 14:30
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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id_commentaire`, `texte_commentaire`, `date_commentaire`, `id_photo`, `id_utilisateur`, `statut_suppression`, `date_archivage`) VALUES
(4, 'test', '2025-11-20 21:49:43', 1, 1, 'ACTIF', NULL),
(5, 'Allez les filles ! :)', '2025-11-21 13:48:56', 13, 1, 'ACTIF', NULL),
(6, 'Bravoo !', '2025-11-21 14:17:43', 20, 8, 'ACTIF', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `page`
--

DROP TABLE IF EXISTS `page`;
CREATE TABLE IF NOT EXISTS `page` (
  `id_page` int NOT NULL AUTO_INCREMENT,
  `intitule_page` varchar(100) NOT NULL,
  PRIMARY KEY (`id_page`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `page`
--

INSERT INTO `page` (`id_page`, `intitule_page`) VALUES
(1, 'Athlétisme'),
(2, 'Football'),
(3, 'Basket');

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
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `photo`
--

INSERT INTO `photo` (`id_photo`, `emplacement_photo`, `legende_photo`, `date_ajout`, `id_utilisateur`, `id_page`, `statut_suppression`, `date_archivage`) VALUES
(16, 'photo_692063b6117a14.50389556.jpg', 'L\'arceau, mon seul objectif', '2025-11-21 14:05:58', 1, 3, 'ACTIF', NULL),
(12, 'photo_69205ef361f826.81732420.jpg', 'La vitesse en pleine action !', '2025-11-21 13:45:39', 8, 1, 'ACTIF', NULL),
(13, 'photo_69205f67bdce61.60225803.jpg', 'Sprint vers le but !!', '2025-11-21 13:47:35', 8, 2, 'ACTIF', NULL),
(17, 'photo_69206589b206e8.42720086.jpg', 'La beauté du jeu réside dans l\'imprévisibilité.', '2025-11-21 14:13:45', 1, 2, 'ACTIF', NULL),
(20, 'photo_692066470d5380.68772720.jpg', 'Un seul ballon, six cœurs qui battent.', '2025-11-21 14:16:55', 1, 3, 'ACTIF', NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `email`, `mot_de_passe`, `est_administrateur`) VALUES
(2, 'Florian', 'florian.simon32320@gmail.com', '57f1a091860b520b0e3f04ab951b9b0b', 0),
(3, 'Florian', 'florian.simon@gmail.com', '57f1a091860b520b0e3f04ab951b9b0b', 0),
(8, 'Florian', 'florian@local.fr', '6ca08d884fb94df9600a84debc33ca5c', 0),
(5, 'Test', 'test@local.fr', '098f6bcd4621d373cade4e832627b4f6', 0),
(1, 'Admin M2L', 'adminM2L@local.fr', '21232f297a57a5a743894a0e4a801fc3', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
