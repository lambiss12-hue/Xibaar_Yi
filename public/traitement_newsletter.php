<?php
require_once __DIR__ . '/../includes/config.php';

// On récupère l'email envoyé par le formulaire via $_POST
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// filter_var vérifie que c'est bien un email valide
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . url('index.php?newsletter=erreur'));
    exit();
}

// On insère l'email dans la base de données
$stmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (:email)");
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();

// On redirige avec un message de succès
header('Location: ' . url('index.php?newsletter=ok'));
exit();
?>