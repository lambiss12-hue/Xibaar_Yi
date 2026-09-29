<?php
// Enregistre un commentaire envoyé depuis la page d'un article.
// Le commentaire est mis "en attente" : il n'apparaît qu'après validation par la rédaction.
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('index.php'));
    exit();
}

$id_article = (int) ($_POST['id_article'] ?? 0);
$nom        = trim($_POST['nom'] ?? '');
$email      = trim($_POST['email'] ?? '');
$contenu    = trim($_POST['contenu'] ?? '');

// Retour vers l'article, à la section commentaires
function retour_article($id_article, $resultat)
{
    header('Location: ' . url('article.php?id=' . $id_article . '&commentaire=' . $resultat . '#commentaires'));
    exit();
}

// L'article doit exister
$stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ?");
$stmt->execute([$id_article]);
if (!$stmt->fetch()) {
    header('Location: ' . url('index.php'));
    exit();
}

// Anti-spam 1 : champ piège "site_web", caché par le CSS.
// Un humain ne le voit pas, mais les robots remplissent tous les champs.
// On fait comme si tout s'était bien passé, sans rien enregistrer.
if (!empty($_POST['site_web'])) {
    retour_article($id_article, 'attente');
}

// Vérifications (mb_check_encoding : texte UTF-8 valide, sinon MySQL refuse l'insertion)
if (!mb_check_encoding($nom . $email . $contenu, 'UTF-8')
    || $nom === '' || mb_strlen($nom) > 100
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150
    || mb_strlen($contenu) < 2 || mb_strlen($contenu) > 2000) {
    retour_article($id_article, 'erreur');
}

// Anti-spam 2 : un commentaire toutes les 30 secondes maximum par visiteur
$dernier = $_SESSION['dernier_commentaire'] ?? 0;
if (time() - $dernier < 30) {
    retour_article($id_article, 'trop_rapide');
}

$stmt = $pdo->prepare("INSERT INTO commentaires (id_article, nom, email, contenu) VALUES (?, ?, ?, ?)");
$stmt->execute([$id_article, $nom, $email, $contenu]);

$_SESSION['dernier_commentaire'] = time();

retour_article($id_article, 'attente');
