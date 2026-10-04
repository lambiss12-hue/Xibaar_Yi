<?php
// ------------------------------------------------------------
// Réglages par défaut = développement local (Laragon, XAMPP...)
// ------------------------------------------------------------
$config = [
    'db_hote'          => 'localhost',
    'db_port'          => getenv('DB_PORT') ?: '3306',
    'db_nom'           => 'xibaar_yi',
    'db_utilisateur'   => 'root',
    'db_mot_de_passe'  => '',
    'afficher_erreurs' => true,   // false en ligne : les visiteurs ne voient pas les erreurs PHP
];

// En ligne : includes/config.local.php (jamais envoyé sur GitHub, voir .gitignore)
// remplace ces valeurs. Modèle : includes/config.local.exemple.php
if (is_file(__DIR__ . '/config.local.php')) {
    $config = array_merge($config, require __DIR__ . '/config.local.php');
}

ini_set('display_errors', $config['afficher_erreurs'] ? '1' : '0');
ini_set('log_errors', '1');

// Heure de Dakar (UTC+0, sans heure d'été), quel que soit le fuseau du serveur
date_default_timezone_set('Africa/Dakar');

// ------------------------------------------------------------
// Session : cookie protégé
//  - httponly : illisible par JavaScript (limite les dégâts d'une faille XSS)
//  - samesite : pas envoyé depuis les formulaires d'autres sites (en plus du jeton CSRF)
//  - secure   : seulement en HTTPS, quand le site est en HTTPS
// ------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host={$config['db_hote']};port={$config['db_port']};dbname={$config['db_nom']};charset=utf8mb4",
        $config['db_utilisateur'],
        $config['db_mot_de_passe']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Même fuseau pour MySQL (NOW(), CURRENT_TIMESTAMP) que pour PHP (date())
    $pdo->exec("SET time_zone = '+00:00'");
} catch (PDOException $e) {
    // Le détail technique (serveur, utilisateur...) va dans le journal d'erreurs de PHP,
    // pas à l'écran : un visiteur n'a pas à le voir.
    error_log("Xibaar Yi - connexion BDD impossible : " . $e->getMessage());
    http_response_code(503);
    die("Le site est momentanément indisponible. Merci de réessayer dans quelques instants.");
}

// URL de base du site (le dossier public/ vu depuis le navigateur).
// Calculée automatiquement : le site marche quel que soit le nom du dossier
// (Laragon, XAMPP, php -S...). Pas besoin de chemin en dur.
$racine_web     = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$dossier_public = str_replace('\\', '/', (string) realpath(__DIR__ . '/../public'));

$base_url = '';
if ($racine_web !== '' && stripos($dossier_public, $racine_web) === 0) {
    $base_url = substr($dossier_public, strlen($racine_web));
    $base_url = implode('/', array_map('rawurlencode', explode('/', $base_url)));
}
define('BASE_URL', rtrim($base_url, '/'));

// Utilisateur connecté : on relit son compte à chaque page.
// Si un administrateur change son rôle ou supprime son compte,
// l'effet est immédiat (pas besoin d'attendre qu'il se déconnecte).
if (isset($_SESSION['user_id'])) {
    $stmt_session = $pdo->prepare("SELECT login, role, prenom, nom FROM utilisateurs WHERE id = ?");
    $stmt_session->execute([$_SESSION['user_id']]);
    $compte_session = $stmt_session->fetch(PDO::FETCH_ASSOC);

    if ($compte_session) {
        $_SESSION['user_login'] = $compte_session['login'];
        $_SESSION['user_role']  = $compte_session['role'];
        $_SESSION['user_nom']   = $compte_session['prenom'] . ' ' . $compte_session['nom'];
    } else {
        // Compte supprimé : on vide la session (le visiteur redevient anonyme)
        unset($_SESSION['user_id'], $_SESSION['user_login'], $_SESSION['user_role'], $_SESSION['user_nom']);
    }
}

// Date en français : date_fr('2026-10-04 10:00:00') -> "4 octobre 2026"
function date_fr($date)
{
    $mois = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
             'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $timestamp = strtotime($date);

    return date('j', $timestamp) . ' ' . $mois[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

// Prépare un mot-clé pour une recherche LIKE : % et _ sont des jokers en SQL,
// on les échappe pour que "100%" cherche vraiment "100%" et pas tout.
function motif_like($texte)
{
    return '%' . addcslashes($texte, '\\%_') . '%';
}

// Construit un lien vers une page du site : url('admin/articles/ajouter.php')
function url($chemin = '')
{
    return BASE_URL . '/' . ltrim($chemin, '/');
}
