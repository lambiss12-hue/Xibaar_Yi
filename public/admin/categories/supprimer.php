<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['editeur', 'administrateur']);

    if (!isset($_GET['id'])) {
        header('Location: ' . url('admin/categories/liste.php'));
        exit;
    }

    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("DELETE  FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ' . url('admin/categories/liste.php'));
    exit;

?>
        
