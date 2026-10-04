<?php
    require_once __DIR__ . '/../includes/auth.php';

    // Déconnexion uniquement via le bouton de l'en-tête (POST + jeton CSRF) :
    // un lien ou une image sur un autre site ne peut pas déconnecter l'utilisateur.
    exiger_post_csrf('index.php');

    // On vide le tableau de session
    $_SESSION = [];

    // On détruit la session sur le serveur
    session_destroy();
    header('Location: ' . url('index.php'));
    exit;
?>
