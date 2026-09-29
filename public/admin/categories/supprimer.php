<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['editeur', 'administrateur']);

    // Suppression uniquement via le formulaire (POST + jeton CSRF)
    exiger_post_csrf('admin/categories/liste.php');

    $id = (int) ($_POST['id'] ?? 0);

    // Supprimer une catégorie supprimerait aussi tous ses articles (ON DELETE CASCADE) :
    // on refuse tant qu'elle contient des articles.
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE id_categorie = ?");
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() > 0) {
        header('Location: ' . url('admin/categories/liste.php?erreur=articles'));
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ' . url('admin/categories/liste.php'));
    exit;
