<?php

    //always verifier qui est connecter
    require_once '../config.php';

    if (!isset($_SESSION['user_role'])) {
        header('Location: /Projet back-end/Xibaar_Yi/connexion.php');
        exit;
    }

    if ($_SESSION['user_role'] !== 'administrateur') {
        header('Location: /Projet back-end/Xibaar_Yi/accueil.php');
        exit;
    }

    if (!isset($_GET['id'])) {
        header('Location: /Projet back-end/Xibaar_Yi/utilisateurs/liste.php');
        exit;
    }

    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("DELETE  FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: /Projet back-end/Xibaar_Yi/utilisateurs/liste.php');
    exit;

?>
        
