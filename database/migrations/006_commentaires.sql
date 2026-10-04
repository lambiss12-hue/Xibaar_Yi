-- À exécuter une seule fois sur une base créée avant l'ajout des commentaires.
-- Commentaires des visiteurs : publiés seulement après validation (statut 'approuve').
-- Supprimer un article supprime aussi ses commentaires (ON DELETE CASCADE).

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
