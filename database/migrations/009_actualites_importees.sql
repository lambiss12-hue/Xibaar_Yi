-- À exécuter une seule fois sur une base existante.
-- Titres d'actualité importés depuis les flux RSS des médias sénégalais
-- (voir includes/actualites.php) :
--   - un article importé n'a pas d'auteur de la rédaction, mais une source (nom du média + lien)
--   - imports_rss garde l'historique des imports (date, nombre de titres ajoutés, erreurs)
--   - les 20 articles d'exemple inventés sont supprimés (leurs commentaires aussi)

ALTER TABLE articles
    MODIFY id_auteur INT NULL,
    ADD COLUMN source_nom VARCHAR(100) NULL,
    ADD COLUMN source_url VARCHAR(500) NULL,
    ADD UNIQUE INDEX uniq_articles_source_url (source_url(191)); -- 191 : longueur indexable sur toutes les versions de MySQL/MariaDB

CREATE TABLE IF NOT EXISTS imports_rss (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    date_import DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nb_ajoutes  INT NOT NULL DEFAULT 0,
    erreurs     TEXT
);

DELETE FROM articles WHERE id IN (1, 2, 3, 4, 5) OR id BETWEEN 101 AND 115;
