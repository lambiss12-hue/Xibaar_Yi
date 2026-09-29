<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['administrateur']);

    // Suppression uniquement via le formulaire (POST + jeton CSRF)
    exiger_post_csrf('admin/utilisateurs/liste.php');

    $id = (int) ($_POST['id'] ?? 0);

    // On ne peut pas supprimer son propre compte
    if ($id !== (int) $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
    }

    header('Location: ' . url('admin/utilisateurs/liste.php'));
    exit;
