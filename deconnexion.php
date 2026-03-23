<?php
    if(session_status()===PHP_SESSION_NONE){session_start();}

    //on detruit toutes les data de session
    $_SESSION = [];
    session_destroy();

    //on redirige vers l'acceuil
    header('Location: /xibaar_yi/accueil.php');
    exit;


?>