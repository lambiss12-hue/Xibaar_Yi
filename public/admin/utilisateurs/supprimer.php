<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['administrateur']);

    if (!isset($_GET['id'])) {
        header('Location: ' . url('admin/utilisateurs/liste.php'));
        exit;
    }

    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("DELETE  FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ' . url('admin/utilisateurs/liste.php'));
    exit;

?>
        
