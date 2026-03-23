<?php

  
    require_once '../config.php';

    if(session_status()==PHP_SESSION_NONE){session_start();}

    //verifions si connecter
    if(!isset($SESSION['user_role'])){
        header('Location: /xibaar_yi/connexion.php');
        exit;
    }

    //verifions admin
    if($_SESSION['user_role'] !== 'administrateur'){
        header('Location: /xibaar_yi/accueil.php');
        exit;
    }

?>