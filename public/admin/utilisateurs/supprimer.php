<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['administrateur']);

    // Suppression uniquement via le formulaire (POST + jeton CSRF)
    exiger_post_csrf('admin/utilisateurs/liste.php');

    $id = (int) ($_POST['id'] ?? 0);

    // On ne peut pas supprimer son propre compte
    if ($id === (int) $_SESSION['user_id']) {
        header('Location: ' . url('admin/utilisateurs/liste.php'));
        exit;
    }

    // Supprimer un utilisateur supprimerait aussi tous ses articles (ON DELETE CASCADE) :
    // on refuse tant qu'il est l'auteur d'articles.
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE id_auteur = ?");
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() > 0) {
        header('Location: ' . url('admin/utilisateurs/liste.php?erreur=articles'));
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ' . url('admin/utilisateurs/liste.php'));
    exit;
