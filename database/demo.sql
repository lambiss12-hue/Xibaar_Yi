-- Données de démonstration (pour la soutenance) — à importer APRÈS database.sql :
--   mysql -u root xibaar_yi < database/demo.sql   (ou phpMyAdmin > Importer)
--
-- Ajoute 2 éditeurs, des messages de contact et des inscrits à la newsletter.
-- (Les articles sont les titres d'actualité importés automatiquement, voir includes/actualites.php.)
-- Les identifiants commencent à 101 : rien n'écrase les données existantes,
-- et relancer le script ne crée pas de doublons (INSERT IGNORE).
--
-- Comptes éditeurs : login "fatou" ou "moussa", mot de passe "redac123"
-- Pour tout retirer : voir la section "Nettoyage" à la fin du fichier.

INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, telephone, login, mot_de_passe, role) VALUES
    (101, 'Ndiaye', 'Fatou',  'fatou.ndiaye@xibaar.sn', '77 123 45 67', 'fatou',  '$2y$10$XePVZZD0PWrfVF2r3WyQ7.S9rcx7uF4Op/VuR/HifuhNc3dw87axK', 'editeur'),
    (102, 'Fall',   'Moussa', 'moussa.fall@xibaar.sn',  '76 987 65 43', 'moussa', '$2y$10$XePVZZD0PWrfVF2r3WyQ7.S9rcx7uF4Op/VuR/HifuhNc3dw87axK', 'editeur');

-- Messages de contact : 2 non lus, 1 lu
INSERT IGNORE INTO messages_contact (id, nom, email, sujet, message, lu, date_envoi) VALUES
    (101, 'Aminata Sy',   'aminata@exemple.sn', 'info',      'Bonjour, un festival de musique se prépare à Ziguinchor, ça pourrait vous intéresser.', 0, NOW() - INTERVAL 5 HOUR),
    (102, 'Pape Diouf',   'pape@exemple.sn',    'publicite', 'Nous souhaitons annoncer sur votre site. Quels sont vos tarifs ?', 0, NOW() - INTERVAL 1 DAY),
    (103, 'Mariama Ba',   'mariama@exemple.sn', 'technique', 'Les images ne s''affichaient pas hier soir sur mon téléphone.', 1, NOW() - INTERVAL 3 DAY);

INSERT IGNORE INTO newsletter (id, email, date_inscription) VALUES
    (101, 'lecteur1@exemple.sn', NOW() - INTERVAL 20 DAY),
    (102, 'lecteur2@exemple.sn', NOW() - INTERVAL 8 DAY),
    (103, 'lecteur3@exemple.sn', NOW() - INTERVAL 1 DAY);

-- ------------------------------------------------------------
-- Nettoyage : pour retirer toutes les données de démo, exécuter :
--   DELETE FROM utilisateurs     WHERE id BETWEEN 101 AND 199;
--   DELETE FROM messages_contact WHERE id BETWEEN 101 AND 199;
--   DELETE FROM newsletter       WHERE id BETWEEN 101 AND 199;
-- ------------------------------------------------------------
