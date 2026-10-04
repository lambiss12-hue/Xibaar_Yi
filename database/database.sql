-- Base de données Xibaar Yi : création des tables + données de départ.
--
-- Ce script ne crée pas la base elle-même : il remplit la base choisie.
-- Il marche donc aussi chez un hébergeur, qui impose le nom de la base.
--
-- En local :
--   mysql -u root -e "CREATE DATABASE IF NOT EXISTS xibaar_yi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
--   mysql -u root xibaar_yi < database/database.sql
-- Avec phpMyAdmin : sélectionner la base à gauche, puis onglet Importer.

CREATE TABLE IF NOT EXISTS categories (
    id  INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS utilisateurs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nom          VARCHAR(100) NOT NULL,
    prenom       VARCHAR(100) NOT NULL,
    email        VARCHAR(150),
    telephone    VARCHAR(30),
    login        VARCHAR(50)  NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role         ENUM('editeur', 'administrateur') NOT NULL DEFAULT 'editeur'
);

CREATE TABLE IF NOT EXISTS articles (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    titre              VARCHAR(255) NOT NULL,
    description_courte TEXT NOT NULL,
    contenu            LONGTEXT NOT NULL,
    image              VARCHAR(255) DEFAULT '',
    date_publication   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    vues               INT NOT NULL DEFAULT 0,
    id_categorie       INT NOT NULL,
    id_auteur          INT NOT NULL,
    FOREIGN KEY (id_categorie) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (id_auteur)    REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    email            VARCHAR(255) NOT NULL UNIQUE,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages_contact (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(150) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    sujet      VARCHAR(30)  NOT NULL,
    message    TEXT NOT NULL,
    date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    lu         TINYINT(1) NOT NULL DEFAULT 0
);

-- Commentaires des visiteurs : publiés seulement après validation (statut 'approuve')
CREATE TABLE IF NOT EXISTS commentaires (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_article    INT NOT NULL,
    nom           VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    contenu       TEXT NOT NULL,
    date_envoi    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut        ENUM('en_attente', 'approuve') NOT NULL DEFAULT 'en_attente',
    FOREIGN KEY (id_article) REFERENCES articles(id) ON DELETE CASCADE
);

-- Échecs de connexion (adresse IP + date) : après 5 échecs en 15 minutes,
-- la même adresse IP doit attendre avant de réessayer
CREATE TABLE IF NOT EXISTS tentatives_connexion (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    ip             VARCHAR(45) NOT NULL,
    date_tentative DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentatives_ip_date (ip, date_tentative)
);

-- Données de départ
INSERT IGNORE INTO categories (id, nom) VALUES
    (1, 'Politique'), (2, 'Sport'), (3, 'Culture'), (4, 'Education'), (5, 'Technologie');

-- Compte admin : login "admin", mot de passe "admin123" (hash généré avec password_hash)
INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, telephone, login, mot_de_passe, role) VALUES
    (1, 'Admin', 'Xibaar', 'admin@xibaar.sn', '', 'admin', '$2y$10$Bg0oIp.tAJt5hq8njUCo0.TydQPgntFgHy1lEEbUqNacBfYmojjKK', 'administrateur');

INSERT IGNORE INTO articles (id, titre, description_courte, contenu, image, id_categorie, id_auteur, date_publication) VALUES
    (1, 'Session parlementaire : les grands dossiers de la rentrée', 'Les députés reprennent les travaux avec un calendrier chargé.', 'Les députés ont ouvert la nouvelle session avec plusieurs projets de loi à l''ordre du jour.', 'politique.png', 1, 1, NOW() - INTERVAL 5 DAY),
    (2, 'Les Lions en route pour la prochaine CAN', 'Victoire convaincante de l''équipe nationale en match de qualification.', 'L''équipe nationale a signé une belle victoire et se rapproche de la qualification.', 'sport.png', 2, 1, NOW() - INTERVAL 4 DAY),
    (3, 'Festival de Saint-Louis : une édition record', 'Artistes et visiteurs venus du monde entier pour célébrer la musique.', 'Le festival a rassemblé un public nombreux pendant plusieurs jours de concerts.', 'culture.png', 3, 1, NOW() - INTERVAL 3 DAY),
    (4, 'Rentrée scolaire : ce qui change cette année', 'Nouveaux programmes et nouvelles écoles pour les élèves.', 'Le ministère a présenté les principales nouveautés de la rentrée scolaire.', 'education.png', 4, 1, NOW() - INTERVAL 2 DAY),
    (5, 'Dakar, nouveau pôle des startups africaines', 'L''écosystème tech sénégalais attire de plus en plus d''investisseurs.', 'Les startups dakaroises multiplient les levées de fonds et les partenariats.', 'technologie.png', 5, 1, NOW() - INTERVAL 1 DAY);

INSERT IGNORE INTO commentaires (id, id_article, nom, email, contenu, statut, date_envoi) VALUES
    (1, 4, 'Fatou Sow', 'fatou@exemple.sn', 'Merci pour ces informations, très utile pour les parents !', 'approuve', NOW() - INTERVAL 1 DAY),
    (2, 4, 'Ibrahima', 'ibrahima@exemple.sn', 'Et pour les écoles privées, qu''est-ce qui change ?', 'approuve', NOW() - INTERVAL 20 HOUR);
