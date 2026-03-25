<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // On vide le tableau de session
    $_SESSION = [];

    // On détruit la session sur le serveur
    session_destroy();

    // CORRECTION DU CHEMIN : 
    // On repart de la racine "/" pour être sûr de trouver le fichier
    header('Location: /xibaar_yi/accueil.php');
    exit;
?>
