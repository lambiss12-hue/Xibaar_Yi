-- À exécuter une seule fois. Retire les dernières données inventées (anciennes données de démo).
-- Chaque suppression vise des lignes précises (identifiant ET adresse inventée) :
-- les vrais messages, inscrits et commentaires reçus depuis la mise en ligne ne sont pas touchés.

-- Faux messages de contact et faux inscrits à la newsletter
DELETE FROM messages_contact WHERE id BETWEEN 101 AND 103 AND email LIKE '%@exemple.sn';
DELETE FROM newsletter       WHERE id BETWEEN 101 AND 103 AND email LIKE '%@exemple.sn';

-- Faux commentaires qui resteraient (normalement déjà supprimés avec les faux articles, migration 009)
DELETE FROM commentaires WHERE email LIKE '%@exemple.sn' OR email = 'promo@spam.example';

-- Comptes rédacteurs fictifs "fatou" et "moussa" : seulement s'ils n'ont écrit aucun article
-- (supprimer un auteur supprimerait aussi ses articles)
DELETE FROM utilisateurs
WHERE id IN (101, 102) AND login IN ('fatou', 'moussa')
  AND id NOT IN (SELECT id_auteur FROM (SELECT DISTINCT id_auteur FROM articles WHERE id_auteur IS NOT NULL) AS auteurs);

-- Email inventé du compte administrateur
UPDATE utilisateurs SET email = '' WHERE id = 1 AND email = 'admin@xibaar.sn';
