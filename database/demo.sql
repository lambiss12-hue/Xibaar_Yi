-- Données de démonstration (pour la soutenance) — à importer APRÈS database.sql :
--   mysql -u root < database/demo.sql
--
-- Ajoute 2 éditeurs, 15 articles, des commentaires (validés et en attente),
-- des messages de contact et des inscrits à la newsletter.
-- Les identifiants commencent à 101 : rien n'écrase les données existantes,
-- et relancer le script ne crée pas de doublons (INSERT IGNORE).
--
-- Comptes éditeurs : login "fatou" ou "moussa", mot de passe "redac123"
-- Pour tout retirer : voir la section "Nettoyage" à la fin du fichier.

USE xibaar_yi;

INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, telephone, login, mot_de_passe, role) VALUES
    (101, 'Ndiaye', 'Fatou',  'fatou.ndiaye@xibaar.sn', '77 123 45 67', 'fatou',  '$2y$10$XePVZZD0PWrfVF2r3WyQ7.S9rcx7uF4Op/VuR/HifuhNc3dw87axK', 'editeur'),
    (102, 'Fall',   'Moussa', 'moussa.fall@xibaar.sn',  '76 987 65 43', 'moussa', '$2y$10$XePVZZD0PWrfVF2r3WyQ7.S9rcx7uF4Op/VuR/HifuhNc3dw87axK', 'editeur');

-- Catégories : 1 Politique, 2 Sport, 3 Culture, 4 Education, 5 Technologie
INSERT IGNORE INTO articles (id, titre, description_courte, contenu, image, id_categorie, id_auteur, vues, date_publication) VALUES
    (101, 'Budget 2027 : les grandes priorités présentées aux députés',
          'Santé, éducation et infrastructures concentrent l''essentiel des hausses.',
          'Le projet de loi de finances a été présenté cette semaine à l''Assemblée nationale.\n\nLes débats en commission commenceront la semaine prochaine, avant un vote prévu en décembre.',
          'politique.png', 1, 101, 184, NOW() - INTERVAL 14 DAY),
    (102, 'Collectivités locales : plus de moyens pour les communes rurales',
          'Un nouveau fonds doit financer routes, forages et centres de santé.',
          'Les maires des communes rurales accueillent favorablement cette annonce, tout en demandant un calendrier précis.',
          'politique.png', 1, 102, 97, NOW() - INTERVAL 9 DAY),
    (103, 'Jeunesse : un programme national pour l''emploi des diplômés',
          'Stages, formations et aide à la création d''entreprise au programme.',
          'Le programme vise plusieurs milliers de jeunes diplômés dès l''année prochaine.\n\nLes inscriptions se feront en ligne.',
          'politique.png', 1, 101, 256, NOW() - INTERVAL 3 DAY),
    (104, 'Lutte : le grand combat de la saison fixé au mois de janvier',
          'Les deux champions se retrouveront à l''arène nationale.',
          'Le promoteur a confirmé la date lors d''une conférence de presse.\n\nLa billetterie ouvrira début décembre.',
          'sport.png', 2, 102, 412, NOW() - INTERVAL 12 DAY),
    (105, 'Basket : les Lionnes qualifiées pour la phase finale',
          'Une victoire nette qui confirme la bonne forme de l''équipe.',
          'Portées par une défense solide, les Lionnes ont dominé leur adversaire du début à la fin du match.',
          'sport.png', 2, 101, 233, NOW() - INTERVAL 6 DAY),
    (106, 'Marathon de Dakar : record de participants cette année',
          'Plus de coureurs que jamais au départ de la Corniche.',
          'Les organisateurs saluent une édition réussie et annoncent déjà la prochaine.',
          'sport.png', 2, 102, 78, NOW() - INTERVAL 1 DAY),
    (107, 'Biennale de Dakar : les artistes sélectionnés dévoilés',
          'Peinture, sculpture et art numérique à l''honneur.',
          'La liste des artistes retenus pour l''exposition internationale a été publiée.\n\nL''événement attend des visiteurs du monde entier.',
          'culture.png', 3, 101, 145, NOW() - INTERVAL 11 DAY),
    (108, 'Musique : le mbalax fait son retour sur les grandes scènes',
          'Une nouvelle génération d''artistes remet le genre à la mode.',
          'Concerts complets et millions d''écoutes en ligne : le mbalax séduit à nouveau les jeunes.',
          'culture.png', 3, 102, 301, NOW() - INTERVAL 5 DAY),
    (109, 'Cinéma : trois films sénégalais en compétition à l''étranger',
          'Une belle vitrine pour le cinéma national.',
          'Les réalisateurs espèrent que cette visibilité attirera de nouveaux financements.',
          'culture.png', 3, 101, 64, NOW() - INTERVAL 2 DAY),
    (110, 'Baccalauréat : un taux de réussite en hausse',
          'Les résultats progressent dans la majorité des académies.',
          'Le ministère salue le travail des enseignants et des élèves.\n\nLes sessions de rattrapage commencent la semaine prochaine.',
          'education.png', 4, 102, 520, NOW() - INTERVAL 13 DAY),
    (111, 'Universités : de nouvelles places pour les bacheliers',
          'Des amphithéâtres supplémentaires ouvrent à la rentrée.',
          'L''objectif est de réduire les effectifs trop chargés dans les premières années.',
          'education.png', 4, 101, 188, NOW() - INTERVAL 7 DAY),
    (112, 'Le numérique entre dans les salles de classe',
          'Des tablettes distribuées dans plusieurs écoles pilotes.',
          'Enseignants et élèves sont formés à l''utilisation de ces nouveaux outils.',
          'education.png', 4, 102, 92, NOW() - INTERVAL 4 HOUR),
    (113, 'Paiement mobile : de plus en plus de commerçants équipés',
          'Même les petits commerces adoptent le paiement par téléphone.',
          'La simplicité et les faibles frais expliquent ce succès.\n\nLes marchés de Dakar sont en première ligne.',
          'technologie.png', 5, 101, 367, NOW() - INTERVAL 10 DAY),
    (114, 'Fibre optique : de nouvelles villes raccordées',
          'Le très haut débit arrive dans plusieurs régions.',
          'Les opérateurs promettent des offres adaptées aux particuliers comme aux entreprises.',
          'technologie.png', 5, 102, 129, NOW() - INTERVAL 8 DAY),
    (115, 'Intelligence artificielle : un hackathon réunit 300 étudiants',
          'Trois jours pour inventer des solutions aux problèmes locaux.',
          'Agriculture, santé et transport : les projets gagnants seront accompagnés par des incubateurs.',
          'technologie.png', 5, 101, 210, NOW() - INTERVAL 20 HOUR);

-- Commentaires : validés (visibles) et en attente (à modérer pendant la démo)
INSERT IGNORE INTO commentaires (id, id_article, nom, email, contenu, statut, date_envoi) VALUES
    (101, 104, 'Cheikh',    'cheikh@exemple.sn', 'Vivement janvier, ce combat va être historique !', 'approuve', NOW() - INTERVAL 11 DAY),
    (102, 104, 'Aïssatou',  'aissatou@exemple.sn', 'Les billets vont partir très vite, il faudra être rapide.', 'approuve', NOW() - INTERVAL 10 DAY),
    (103, 110, 'Mamadou',   'mamadou@exemple.sn', 'Félicitations à tous les nouveaux bacheliers !', 'approuve', NOW() - INTERVAL 12 DAY),
    (104, 113, 'Awa',       'awa@exemple.sn', 'Je paie tout avec mon téléphone maintenant, c''est tellement pratique.', 'approuve', NOW() - INTERVAL 9 DAY),
    (105, 108, 'Ousmane',   'ousmane@exemple.sn', 'Le mbalax n''est jamais vraiment parti ;)', 'approuve', NOW() - INTERVAL 4 DAY),
    (106, 115, 'Khady',     'khady@exemple.sn', 'Est-ce que les projets seront présentés au public ?', 'en_attente', NOW() - INTERVAL 3 HOUR),
    (107, 103, 'Ibou',      'ibou@exemple.sn', 'Comment faire pour s''inscrire au programme ?', 'en_attente', NOW() - INTERVAL 2 HOUR),
    (108, 105, 'Robot',     'promo@spam.example', 'Gagnez de l''argent facilement en cliquant ici !!!', 'en_attente', NOW() - INTERVAL 1 HOUR);

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
--   DELETE FROM commentaires     WHERE id BETWEEN 101 AND 199;
--   DELETE FROM articles         WHERE id BETWEEN 101 AND 199;
--   DELETE FROM utilisateurs     WHERE id BETWEEN 101 AND 199;
--   DELETE FROM messages_contact WHERE id BETWEEN 101 AND 199;
--   DELETE FROM newsletter       WHERE id BETWEEN 101 AND 199;
-- ------------------------------------------------------------
