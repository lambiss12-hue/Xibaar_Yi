<?php

    //always verifier qui est connecter
    require_once __DIR__ . '/../../../includes/auth.php';

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

    // On supprime aussi le fichier image s'il n'est plus utilisé par aucun article.
    // Seulement les images envoyées depuis le back-office (nom aléatoire en hexadécimal) :
    // les images d'exemple du projet (sport.png...) sont suivies par Git, on n'y touche pas.
    if (preg_match('/^[a-f0-9]{13,16}\.(jpg|jpeg|png|webp)$/', $image)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE image = ?");
        $stmt->execute([$image]);

        $fichier = __DIR__ . '/../../uploads/' . basename($image);
        if ($stmt->fetchColumn() == 0 && is_file($fichier)) {
            unlink($fichier);
        }
    }

    header('Location: ' . url($retour));
    exit;
