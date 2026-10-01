-- À exécuter une seule fois sur une base créée avant le compteur de vues des articles.

ALTER TABLE articles ADD COLUMN vues INT NOT NULL DEFAULT 0;
