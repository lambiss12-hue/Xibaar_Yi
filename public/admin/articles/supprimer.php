<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['editeur', 'administrateur']);

    if (!isset($_GET['id'])) {
        header('Location: ' . url('index.php'));
        exit;
    }

    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("DELETE  FROM articles WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ' . url('index.php'));
    exit;

?>
        
