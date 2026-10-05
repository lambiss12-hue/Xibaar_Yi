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

    // Compte démo (portfolio) : il peut OUVRIR toutes les pages du back-office,
    // mais tout envoi de formulaire (ajout, modification, suppression...) est refusé ici,
    // avant même que la page ne traite quoi que ce soit.
    if (est_demo()) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['message_demo'] = true;

            // Retour sur la page où était le formulaire (même site uniquement)
            $retour = $_SERVER['HTTP_REFERER'] ?? '';
            $notre_hote = parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
            if ($retour === '' || parse_url($retour, PHP_URL_HOST) !== $notre_hote) {
                $retour = url('admin/articles/liste.php');
            }
            header('Location: ' . $retour);
            exit;
        }
        return;
    }

    // Connecté mais sans le bon rôle -> accueil
    if (!in_array($_SESSION['user_role'], $roles_autorises, true)) {
        header('Location: ' . url('index.php'));
        exit;
    }
}

// true si l'utilisateur connecté est le compte de démonstration (lecture seule)
function est_demo()
{
    return ($_SESSION['user_role'] ?? '') === 'demo';
}

// Membre de la rédaction, ou visiteur du compte démo : voit le back-office
function voit_back_office()
{
    return in_array($_SESSION['user_role'] ?? '', ['editeur', 'administrateur', 'demo'], true);
}

// Le compte démo est public : il ne doit pas voir les vraies coordonnées
// des visiteurs (commentaires, messages) ni celles de la rédaction.
// "moussa.fall@xibaar.sn" -> "m•••@xibaar.sn", "77 123 45 67" -> "•• ••• •• 67"
function masquer_si_demo($texte, $type = 'email')
{
    if (!est_demo() || $texte === null || $texte === '') {
        return $texte;
    }
    if ($type === 'email' && strpos($texte, '@') !== false) {
        [$nom, $domaine] = explode('@', $texte, 2);
        return mb_substr($nom, 0, 1) . '•••@' . $domaine;
    }
    return preg_replace('/\d(?=(?:\D*\d){2})/u', '•', $texte);
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

// Pour les formulaires d'ajout / modification : true si le jeton envoyé est le bon
function csrf_valide()
{
    return hash_equals(csrf_token(), $_POST['csrf_token'] ?? '');
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
