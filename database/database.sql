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
    role         ENUM('editeur', 'administrateur', 'demo') NOT NULL DEFAULT 'editeur'
);

CREATE TABLE IF NOT EXISTS articles (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    titre              VARCHAR(255) NOT NULL,
    description_courte TEXT NOT NULL,
    contenu            LONGTEXT NOT NULL,
    image              VARCHAR(255) DEFAULT '',
    image_url          VARCHAR(500) NULL,  -- titre importé : photo du média (NULL = pas cherchée, '' = aucune)
    date_publication   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    vues               INT NOT NULL DEFAULT 0,
    id_categorie       INT NOT NULL,
    id_auteur          INT NULL,           -- NULL pour un titre importé d'un flux RSS
    source_nom         VARCHAR(100) NULL,  -- titre importé : nom du média (APS, Le Soleil...)
    source_url         VARCHAR(500) NULL,  -- titre importé : lien vers l'article original
    UNIQUE INDEX uniq_articles_source_url (source_url(191)),
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

-- Historique des imports de titres d'actualité (voir includes/actualites.php)
CREATE TABLE IF NOT EXISTS imports_rss (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    date_import DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nb_ajoutes  INT NOT NULL DEFAULT 0,
    erreurs     TEXT
);

-- Données de départ
INSERT IGNORE INTO categories (id, nom) VALUES
    (1, 'Politique'), (2, 'Sport'), (3, 'Culture'), (4, 'Education'), (5, 'Technologie');

-- Compte admin : login "admin", mot de passe "admin123" (hash généré avec password_hash)
INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, telephone, login, mot_de_passe, role) VALUES
    (1, 'Admin', 'Xibaar', 'admin@xibaar.sn', '', 'admin', '$2y$10$Bg0oIp.tAJt5hq8njUCo0.TydQPgntFgHy1lEEbUqNacBfYmojjKK', 'administrateur');

-- Compte de démonstration public : login "demo", mot de passe "demo1234".
-- Voit tout le back-office mais ne peut rien modifier (voir exiger_role() dans includes/auth.php).
INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, telephone, login, mot_de_passe, role) VALUES
    (900, 'Démo', 'Visiteur', '', '', 'demo', '$2y$10$puCMpfcJWdeSJM.3EIOQeO4uN0WiB7J4U8CcJ0AhfGv3FIoo2WCMu', 'demo');

-- Pas d'articles d'exemple : les titres d'actualité sont importés automatiquement
-- à la première visite du site (flux RSS de médias sénégalais, voir includes/actualites.php).
