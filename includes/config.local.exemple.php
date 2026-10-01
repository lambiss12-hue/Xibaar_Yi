<?php
// MODÈLE — copier ce fichier en "config.local.php" (dans ce même dossier includes/)
// puis remplacer les valeurs par celles données par l'hébergeur.
//
// config.local.php est ignoré par Git : le mot de passe de la base en ligne
// ne se retrouve jamais sur GitHub.

return [
    'db_hote'          => 'sql000.hebergeur.com',   // "Serveur MySQL" / "Host" chez l'hébergeur
    'db_port'          => '3306',
    'db_nom'           => 'nom_de_la_base',         // souvent préfixé, ex. "if0_12345678_xibaar"
    'db_utilisateur'   => 'utilisateur_mysql',
    'db_mot_de_passe'  => 'mot_de_passe_mysql',
    'afficher_erreurs' => false,                    // jamais true sur un site public
];
