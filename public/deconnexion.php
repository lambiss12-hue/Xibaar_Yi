<?php
    require_once __DIR__ . '/../includes/config.php';

    // On vide le tableau de session
    $_SESSION = [];

    // On détruit la session sur le serveur
    session_destroy();
    header('Location: ' . url('index.php'));
    exit;
?>
