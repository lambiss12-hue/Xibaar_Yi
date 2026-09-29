<?php
require_once __DIR__ . '/../includes/config.php';

// Page d'où vient le formulaire (liste fermée pour éviter les redirections vers un autre site)
$pages_retour = ['index.php', 'contact.php'];
$retour = in_array($_POST['retour'] ?? '', $pages_retour, true) ? $_POST['retour'] : 'index.php';

// On récupère l'email envoyé par le formulaire via $_POST
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// filter_var vérifie que c'est bien un email valide
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
    header('Location: ' . url($retour . '?newsletter=erreur'));
    exit();
}

// Email déjà inscrit ? (on ignore les majuscules : Moussa@x.sn = moussa@x.sn)
$email = mb_strtolower($email);

$stmt = $pdo->prepare("SELECT id FROM newsletter WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    header('Location: ' . url($retour . '?newsletter=deja'));
    exit();
}

// On insère l'email dans la base de données
try {
    $stmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)");
    $stmt->execute([$email]);
} catch (PDOException $e) {
    // 23000 = doublon refusé par l'index UNIQUE (deux envois presque simultanés)
    if ($e->getCode() === '23000') {
        header('Location: ' . url($retour . '?newsletter=deja'));
        exit();
    }
    throw $e;
}

// On redirige avec un message de succès
header('Location: ' . url($retour . '?newsletter=ok'));
exit();
