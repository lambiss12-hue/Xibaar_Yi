<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';
    require_once __DIR__ . '/../../../includes/upload.php';

    exiger_role(['editeur', 'administrateur']);

    // Page où revenir après la suppression (liste fermée : pas de redirection vers un autre site)
    $pages_retour = ['index.php', 'admin/articles/liste.php'];
    $retour = in_array($_POST['retour'] ?? '', $pages_retour, true) ? $_POST['retour'] : 'index.php';

    // Suppression uniquement via le formulaire (POST + jeton CSRF)
    exiger_post_csrf($retour);

    $id = (int) ($_POST['id'] ?? 0);

    // On récupère l'image avant de supprimer l'article
    $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $image = (string) $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$id]);

    // On supprime aussi le fichier image s'il n'est plus utilisé (voir includes/upload.php)
    supprimer_image_inutilisee($pdo, $image);

    header('Location: ' . url($retour));
    exit;
