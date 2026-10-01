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
