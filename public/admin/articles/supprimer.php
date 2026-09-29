<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['editeur', 'administrateur']);

    // Suppression uniquement via le formulaire (POST + jeton CSRF)
    exiger_post_csrf('index.php');

    $id = (int) ($_POST['id'] ?? 0);

    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ' . url('index.php'));
    exit;
