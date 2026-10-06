<?php
require_once __DIR__ . '/../includes/config.php';

// Accès direct à la page (sans formulaire) -> retour au contact
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('contact.php'));
    exit();
}

// Anti-spam 1 : champ piège "site_web", caché par le CSS (comme pour les commentaires).
// Un robot le remplit : on fait comme si tout s'était bien passé, sans rien enregistrer.
if (!empty($_POST['site_web'])) {
    header('Location: ' . url('contact.php?envoi=ok'));
    exit();
}

$nom     = trim($_POST['nom'] ?? '');
$email   = trim($_POST['email'] ?? '');
$sujet   = $_POST['sujet'] ?? '';
$message = trim($_POST['message'] ?? '');

// Les sujets proposés dans la liste déroulante de contact.php
$sujets_autorises = ['info', 'technique', 'publicite', 'autre'];

// Vérifications côté serveur (le "required" HTML peut être contourné)
// mb_check_encoding : texte UTF-8 valide, sinon MySQL refuse l'insertion et la page plante
if (!mb_check_encoding($nom . $email . $message, 'UTF-8')
    || $nom === '' || mb_strlen($nom) > 150
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150
    || !in_array($sujet, $sujets_autorises, true)
    || $message === '' || mb_strlen($message) > 5000) {
    // On garde la saisie pour la réafficher : le visiteur ne perd pas son message
    if (mb_check_encoding($nom . $email . $message, 'UTF-8')) {
        $_SESSION['contact_saisie'] = compact('nom', 'email', 'sujet', 'message');
    }
    header('Location: ' . url('contact.php?envoi=erreur'));
    exit();
}

// Anti-spam 2 : un message par minute maximum par visiteur
if (time() - ($_SESSION['dernier_contact'] ?? 0) < 60) {
    $_SESSION['contact_saisie'] = compact('nom', 'email', 'sujet', 'message');
    header('Location: ' . url('contact.php?envoi=trop_rapide'));
    exit();
}

$stmt = $pdo->prepare("INSERT INTO messages_contact (nom, email, sujet, message) VALUES (?, ?, ?, ?)");
$stmt->execute([$nom, $email, $sujet, $message]);

$_SESSION['dernier_contact'] = time();

header('Location: ' . url('contact.php?envoi=ok'));
exit();
