-- À exécuter une seule fois, après 009_actualites_importees.sql.
-- Titres importés : adresse de la photo publiée par le média (image du flux RSS,
-- ou image d'aperçu "og:image" de la page de l'article). L'image reste chez le média.
--   NULL = pas encore cherchée, '' = le média n'en propose pas
-- La colonne "image" garde l'image de la rubrique, affichée si la photo du média ne s'affiche pas.

ALTER TABLE articles
    ADD COLUMN image_url VARCHAR(500) NULL AFTER image;
