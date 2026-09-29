-- À exécuter une seule fois sur une base créée AVANT le passage à password_hash().
-- Les hash password_hash() font 60 caractères (et peuvent grandir à l'avenir),
-- alors qu'un SHA-256 n'en faisait que 64 : on élargit la colonne par sécurité.
--
-- Les anciens mots de passe SHA-256 continuent de fonctionner :
-- ils sont convertis automatiquement à la prochaine connexion de chaque utilisateur.

USE xibaar_yi;

ALTER TABLE utilisateurs MODIFY mot_de_passe VARCHAR(255) NOT NULL;
