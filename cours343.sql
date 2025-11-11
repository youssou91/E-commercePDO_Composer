-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 29 sep. 2025 à 00:18
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cours343`
--

-- --------------------------------------------------------

--
-- Structure de la table `adresse`
--

CREATE TABLE `adresse` (
  `id_adresse` int(11) NOT NULL,
  `rue` varchar(100) NOT NULL,
  `ville` varchar(50) NOT NULL,
  `code_postal` varchar(10) NOT NULL,
  `pays` varchar(50) DEFAULT 'Canada',
  `numero` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `adresse`
--

INSERT INTO `adresse` (`id_adresse`, `rue`, `ville`, `code_postal`, `pays`, `numero`, `province`) VALUES
(3, 'Saint-Laurent', 'Repentigny', 'J5Z5C1', 'Canada', '309', 'Quebec'),
(4, 'Montreal', 'Montreal', 'N3K4S9', 'Canada', '100', 'Quebec'),
(5, 'QWERTY', 'Montreal', 'H1W 1G2', 'Canada', '12345', 'Québec'),
(6, 'fkfkf', 'Montreal', 'H1W 1G2', 'Canada', '220', 'Québec'),
(7, 'qwert', 'Montreal', 'H1W 1G2', 'Canada', '1234', 'Québec'),
(8, 'Saint-Laurent', 'Montreal', 'A1B 2C3', 'Canada', '309', 'QC');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom_categorie` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `nom_categorie`) VALUES
(1, 'Telephones'),
(2, 'Ordinateurs'),
(3, 'Tablettes'),
(4, 'Accessoires');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id_commande` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `date_commande` date NOT NULL,
  `prix_total` varchar(10) NOT NULL,
  `statut` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id_commande`, `id_utilisateur`, `date_commande`, `prix_total`, `statut`) VALUES
(18, 1, '2024-12-03', '15000', 'Livrée'),
(19, 1, '2024-12-03', '6006.5', 'Annulée'),
(20, 1, '2024-12-03', '3916.5', 'Livrée'),
(21, 1, '2024-12-03', '1577', 'Annulée'),
(22, 1, '2024-12-03', '3600', 'Livrée'),
(23, 2, '2024-12-03', '6210', 'Annulée'),
(24, 2, '2024-12-04', '4500', 'En traitement'),
(25, 1, '2024-12-04', '6949.5', 'En attente'),
(26, 2, '2024-12-04', '1500', 'En attente'),
(27, 2, '2024-12-04', '1500', 'En attente'),
(28, 1, '2024-12-04', '4306.5', 'Livrée'),
(29, 1, '2024-12-05', '3035.5', 'Annulée'),
(30, 2, '2025-09-25', '3100', 'En attente'),
(31, 8, '2025-09-25', '1600', 'En attente'),
(32, 8, '2025-09-25', '3300', 'Annulée'),
(33, 8, '2025-09-27', '8600', 'En attente');

-- --------------------------------------------------------

--
-- Structure de la table `image`
--

CREATE TABLE `image` (
  `id_image` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `chemin_image` text DEFAULT NULL,
  `nom_image` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `image`
--

INSERT INTO `image` (`id_image`, `id_produit`, `chemin_image`, `nom_image`) VALUES
(14, 56, 'images/Image 010.jpeg', ''),
(15, 57, 'images/images (6).jpg', ''),
(16, 58, 'images/images (5).jpg', ''),
(17, 59, 'images/49.jpg', ''),
(18, 60, 'images/bf7e75f8488443a5a6c3b96452b3fc69.jpg', ''),
(19, 61, 'images/Blog-Atelier-Invitation-Instagram-publication-85.png', ''),
(20, 62, 'images/Image 006.jpeg', ''),
(21, 63, 'images/93a1743c5bed28289dbdff0114e3f61d.jpg', ''),
(22, 67, 'images/8712-b626hw-300x300.jpg', ''),
(24, 70, 'images/A4BF9S3X-large.jpg', ''),
(25, 71, 'images/il_570xN.2830509194_2fzf.jpg', ''),
(26, 72, 'images/66855df5b8b642a19a90f11de82f679e.jpg', '');

-- --------------------------------------------------------

--
-- Structure de la table `produitpromotion`
--

CREATE TABLE `produitpromotion` (
  `id_produit` int(11) NOT NULL,
  `id_promotion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produitpromotion`
--

INSERT INTO `produitpromotion` (`id_produit`, `id_promotion`) VALUES
(2, 7),
(3, 11),
(5, 8),
(7, 6),
(7, 10),
(10, 9);

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id_produit` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prix_unitaire` float NOT NULL,
  `description` text DEFAULT NULL,
  `courte_description` varchar(250) NOT NULL,
  `quantite` int(11) DEFAULT 0,
  `id_categorie` int(11) NOT NULL,
  `chemin_image` varchar(50) DEFAULT NULL,
  `couleurs` varchar(255) DEFAULT NULL,
  `model` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id_produit`, `nom`, `prix_unitaire`, `description`, `courte_description`, `quantite`, `id_categorie`, `chemin_image`, `couleurs`, `model`) VALUES
(2, 'MackBook Air', 1900, 'La combinaison parfaite de puissance, de portabilité et de style. Conçu pour les utilisateurs polyvalents, le MacBook Air offre des performances incroyables dans un design léger et fin, idéal pour les déplacements.', 'ordinateurs portables', 80, 2, 'uploads/1732998139_Mack Book Air.jpeg', '[\"Bleu\",\"Noir\",\"Blanc\",\"Gris\"]', 'Air slim'),
(3, 'Dell', 1500, 'Un ordinateur portable moderne et polyvalent conçu pour répondre aux besoins des professionnels et des passionnés de technologie. Alliant puissance, durabilité et design, il s\'adapte parfaitement à vos activités professionnelles ou personnelles.', 'ordinateurs portables', 92, 2, 'uploads/1732998179_Dell24.jpg', '[\"Bleu\",\"Noir\",\"Blanc\",\"Gris\"]', 'slim'),
(4, 'HP', 1600, 'Le HP 2024 est un ordinateur portable innovant, pensé pour répondre aux exigences des professionnels, des étudiants et des créateurs. Alliant élégance, performances et durabilité, ce modèle représente le parfait équilibre entre technologie avancée et design soigné.', 'ordinateurs portables', 221, 2, 'uploads/1732998230_hp-laptop-1706112445.jpg', '[\"Bleu\",\"Noir\",\"Blanc\",\"Gris\",\"Jaune\"]', 'slim'),
(5, 'Asus', 1650, 'Le Asus 2024 est un ordinateur portable polyvalent, conçu pour offrir des performances exceptionnelles, un design élégant et une expérience utilisateur intuitive. Il s\'adapte parfaitement aux besoins des professionnels, des étudiants et des gamers occasionnels.', 'ordinateurs portables', 193, 2, 'uploads/1732998275_Asus 24.jpg', '[\"Bleu\",\"Noir\",\"Blanc\",\"Gris\",\"Rose\",\"Marron\"]', 'slim'),
(6, 'Iphone16', 1000, 'L\'iPhone 16 incarne l\'avenir de la technologie mobile avec un design épuré, des performances impressionnantes et des fonctionnalités innovantes. Ce modèle réunit tout ce que les utilisateurs attendent d\'un iPhone, avec des améliorations sur tous les aspects clés.', 'Telephone  de haute qulite', 70, 1, 'uploads/1732998341_iPhone-16-Pro.jpg', '[\"Rouge\",\"Bleu\",\"Vert\",\"Noir\",\"Blanc\",\"Gris\",\"Rose\"]', 'Pro Max'),
(7, 'Iphone15', 850, 'L\'iPhone 15 est un appareil mobile d\'exception qui allie puissance, performance et design sophistiqué. Conçu pour offrir une expérience utilisateur encore plus fluide et enrichissante, il propose des caractéristiques avancées adaptées aux besoins modernes.', 'Telephone  de haute qulite', 62, 1, 'uploads/1732998384_Iphone 15 pro max.jpg', '[\"Rouge\",\"Bleu\",\"Vert\",\"Noir\",\"Blanc\",\"Gris\",\"Jaune\",\"Rose\",\"Marron\"]', 'Pro Max'),
(8, 'Iphone14', 700, 'L\'iPhone 14 combine design élégant, performances exceptionnelles et fonctionnalités innovantes. Conçu pour répondre aux besoins des utilisateurs modernes, cet appareil offre une expérience utilisateur fluide, de la photographie avancée aux capacités de performance accrues.', 'Telephone  de haute qulite', 130, 1, 'uploads/1732998424_original 14 pro max.png', '[\"Rouge\",\"Noir\",\"Blanc\",\"Gris\",\"Jaune\",\"Rose\"]', 'Pro Max'),
(9, 'Redmi 2024', 1100, 'Le Redmi 2024 est un smartphone performant à un prix abordable, parfait pour ceux qui recherchent une excellente expérience mobile sans se ruiner. Avec ses caractéristiques haut de gamme à un tarif accessible, il répond à tous les besoins des utilisateurs modernes, que ce soit pour la photographie, la performance, ou la consommation multimédia.', 'Telephone  de haute qulite', 127, 1, 'uploads/1732998493_Redmi-12-5G.png', '[\"Rouge\",\"Vert\",\"Noir\",\"Blanc\",\"Gris\",\"Jaune\",\"Rose\",\"Marron\"]', 'Pro'),
(10, 'Samsung S24 ', 950, 'Le Samsung Galaxy S24 est l\'un des smartphones phares de la série Galaxy S, offrant une combinaison parfaite de puissance, de design élégant et de technologies de pointe. Conçu pour les utilisateurs exigeants, ce modèle propose des performances exceptionnelles, un affichage de qualité supérieure et une photographie avancée, le tout dans un appareil mince et léger.', 'Telephone  de haute qulite', 80, 1, 'uploads/1732998534_S24.png', '[\"Bleu\",\"Noir\",\"Blanc\",\"Gris\",\"Rose\"]', 'Pro'),
(11, 'Samsung S22', 800, 'Le Samsung Galaxy S22 fait partie de la série phare de smartphones de Samsung, offrant des performances exceptionnelles, un design raffiné et une caméra de haute qualité. Conçu pour les utilisateurs modernes, ce modèle allie style, puissance et technologies avancées.', 'Telephone  de haute qulite', 65, 1, 'uploads/1732998585_S22.jpg', '[\"Rouge\",\"Bleu\",\"Noir\",\"Blanc\",\"Gris\",\"Rose\"]', 'Pro'),
(12, 'MackBook PRO', 1200, 'Les MacBook Pro récents sont équipés des puces Apple Silicon M1, M2 ou M3, qui offrent des performances exceptionnelles par rapport aux précédents processeurs Intel. Ces puces sont spécialement conçues pour optimiser l\'efficacité énergétique, la rapidité du traitement des données et l\'intégration des composants.', 'ordinateurs portables', 93, 2, 'uploads/1733278988_Mack Book Pro.jpg', '[\"Noir\",\"Blanc\",\"Gris\"]', 'Pro'),
(13, 'MackBook', 50, 'ghghgh', 'tablettes pour enfants', 13, 1, 'uploads/1733338472_S24.png', '[\"Bleu\",\"Vert\",\"Blanc\",\"Gris\"]', 'Pro'),
(14, 'Tablette', 50, 'dsd', 'tablettes pour enfants', 13, 3, 'uploads/1733599895_montre6.jpg', '[\"Rouge\",\"Blanc\",\"Jaune\",\"Rose\",\"Marron\"]', 'Simple');

-- --------------------------------------------------------

--
-- Structure de la table `produit_commande`
--

CREATE TABLE `produit_commande` (
  `id_commande` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `quantite` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit_commande`
--

INSERT INTO `produit_commande` (`id_commande`, `id_produit`, `quantite`) VALUES
(13, 2, 1),
(14, 5, 1),
(15, 5, 1),
(16, 2, 1),
(17, 11, 1),
(18, 2, 2),
(18, 4, 5),
(18, 11, 4),
(19, 5, 3),
(19, 7, 2),
(20, 2, 1),
(20, 4, 1),
(20, 7, 1),
(21, 2, 1),
(22, 12, 3),
(23, 2, 3),
(23, 7, 2),
(24, 3, 3),
(25, 2, 3),
(25, 7, 3),
(26, 3, 1),
(27, 3, 1),
(28, 5, 3),
(29, 4, 1),
(29, 5, 1),
(30, 3, 1),
(30, 4, 1),
(31, 4, 1),
(32, 9, 3),
(33, 3, 2),
(33, 11, 1),
(33, 12, 4);

-- --------------------------------------------------------

--
-- Structure de la table `promotions`
--

CREATE TABLE `promotions` (
  `id_promotion` int(11) NOT NULL,
  `code_promotion` varchar(50) DEFAULT NULL,
  `type` enum('pourcentage','montant') NOT NULL,
  `valeur` decimal(10,2) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `promotions`
--

INSERT INTO `promotions` (`id_promotion`, `code_promotion`, `type`, `valeur`, `date_debut`, `date_fin`) VALUES
(6, NULL, 'pourcentage', 13.00, '2024-12-03', '2024-12-18'),
(7, NULL, 'pourcentage', 17.00, '2024-12-03', '2024-12-10'),
(8, NULL, 'pourcentage', 13.00, '2024-12-04', '2024-12-18'),
(9, NULL, 'pourcentage', 12.00, '2024-12-12', '2024-12-27'),
(10, NULL, 'pourcentage', 7.00, '2025-03-10', '2025-03-22'),
(11, NULL, 'pourcentage', 3.00, '2025-09-27', '2025-10-11');

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `id_role` int(11) NOT NULL,
  `description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`id_role`, `description`) VALUES
(1, 'admin'),
(2, 'client');

-- --------------------------------------------------------

--
-- Structure de la table `role_utilisateur`
--

CREATE TABLE `role_utilisateur` (
  `id_role` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `role_utilisateur`
--

INSERT INTO `role_utilisateur` (`id_role`, `id_utilisateur`) VALUES
(1, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 6),
(2, 8);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_utilisateur` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `couriel` varchar(250) NOT NULL,
  `mot_de_pass` text NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `statut` varchar(20) NOT NULL,
  `est_actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom_utilisateur`, `prenom`, `date_naissance`, `couriel`, `mot_de_pass`, `telephone`, `statut`, `est_actif`) VALUES
(1, 'GNING', 'Youssou', '1995-03-24', 'youssougning@gmail.com', '$2y$10$C40QnxxJPqBQwaisLqirjOKXwg.qLDQGeQsu87O/VyEC6fHbNg62O', '5147483630', 'actif', 1),
(2, 'TEST', 'TESLA', '2000-10-29', 'qwerty@gmail.com', '$2y$10$ehE6rPNvAeeMpqmzFkekiOZMcDgzXodlhOvumX3oD0ZkOqkXf3Rzi', '5147483647', 'Actif', 1),
(3, 'GNING', 'Youssou', '2003-06-10', 'youssouqwerty@gmail.com', '$2y$10$5oSs.kFMTQdih9J2ZLIMYuMbx5mEmfQiIFOUqlYceibZntOGbAe52', '5146019995', 'Actif', 1),
(4, 'test1', 'test2', '2001-07-15', 'test@gmail.com', '$2y$10$EeVwQ8mL4RmRudmbLraWdOWGY8RPvT4K8utvyfrpy7hwaub0rBrRe', '5146019995', 'Actif', 1),
(6, 'Test1', 'Test2', '2003-08-11', 'tester@gmail.com', '$2y$10$rvy7q73YaVGkxVBw8mlftORL1r86pUIh/Mwq2/kT.t7lzxSoUAv4W', '5146019995', 'Actif', 1),
(8, 'Ndiaye', 'Tapha', '2004-08-19', 'tapha@gmail.com', '$2y$10$7b4DCEVJTbI9EGosNAaMXe0TkkuDXeqakoNNZqlR8ricN7H2UzK4i', '2029384775', 'Actif', 1);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur_adresse`
--

CREATE TABLE `utilisateur_adresse` (
  `id_utilisateur` int(11) NOT NULL,
  `id_adresse` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur_adresse`
--

INSERT INTO `utilisateur_adresse` (`id_utilisateur`, `id_adresse`) VALUES
(1, 3),
(2, 4),
(3, 5),
(4, 6),
(6, 7),
(8, 8);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `adresse`
--
ALTER TABLE `adresse`
  ADD PRIMARY KEY (`id_adresse`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `produitpromotion`
--
ALTER TABLE `produitpromotion`
  ADD PRIMARY KEY (`id_produit`,`id_promotion`),
  ADD KEY `id_promotion` (`id_promotion`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `fk_p_c` (`id_categorie`);

--
-- Index pour la table `produit_commande`
--
ALTER TABLE `produit_commande`
  ADD PRIMARY KEY (`id_commande`,`id_produit`),
  ADD KEY `id_produit` (`id_produit`);

--
-- Index pour la table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id_promotion`),
  ADD UNIQUE KEY `code_promotion` (`code_promotion`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`);

--
-- Index pour la table `role_utilisateur`
--
ALTER TABLE `role_utilisateur`
  ADD PRIMARY KEY (`id_role`,`id_utilisateur`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `couriel` (`couriel`);

--
-- Index pour la table `utilisateur_adresse`
--
ALTER TABLE `utilisateur_adresse`
  ADD PRIMARY KEY (`id_utilisateur`,`id_adresse`),
  ADD KEY `id_adresse` (`id_adresse`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `adresse`
--
ALTER TABLE `adresse`
  MODIFY `id_adresse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `id_commande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id_promotion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
