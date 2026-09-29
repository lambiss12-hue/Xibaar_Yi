<?php
// Contrôle d'accès des pages d'administration.
// Utilisation : exiger_role(['editeur', 'administrateur']);
require_once __DIR__ . '/config.php';

function exiger_role(array $roles_autorises)
{
    // Pas connecté -> page de connexion
    if (!isset($_SESSION['user_role'])) {
        header('Location: ' . url('connexion.php'));
        exit;
    }

    // Connecté mais sans le bon rôle -> accueil
    if (!in_array($_SESSION['user_role'], $roles_autorises, true)) {
        header('Location: ' . url('index.php'));
        exit;
    }
}

// Jeton CSRF : prouve que le formulaire vient bien de notre site.
// Un autre site ne connaît pas ce jeton, il ne peut donc pas déclencher une suppression.
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Champ caché à mettre dans chaque formulaire POST sensible
function csrf_champ()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

// Pour les pages de suppression : accepte uniquement un POST avec un jeton valide.
// Sinon, redirige vers $redirection sans rien supprimer.
function exiger_post_csrf($redirection)
{
    $jeton = $_POST['csrf_token'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals(csrf_token(), $jeton)) {
        header('Location: ' . url($redirection));
        exit;
    }
}
