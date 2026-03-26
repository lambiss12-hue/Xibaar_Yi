<?php

    //always verifier qui est connecter
    require_once '../config.php';

    if (!isset($_SESSION['user_role'])) {
        header('Location: /xibaar_yi/connexion.php');
        exit;
    }

    if ($_SESSION['user_role'] !== 'editeur' && $_SESSION['user_role'] !== 'administrateur') {
        header('Location: /xibaar_yi/accueil.php');
        exit;
    }

    if (!isset($_GET['id'])) {
        header('Location: /xibaar_yi/accueil.php');
        exit;
    }

    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("DELETE  FROM articles WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: /xibaar_yi/accueil.php');
    exit;

?>
        
