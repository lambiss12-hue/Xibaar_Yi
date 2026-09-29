-- À exécuter une seule fois sur une base créée avant le compteur de vues des articles.

USE xibaar_yi;

ALTER TABLE articles ADD COLUMN vues INT NOT NULL DEFAULT 0;
