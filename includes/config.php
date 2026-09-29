<?php
// Session démarrée en tout premier
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Connexion à la base de données
$host     = 'localhost';
$port     = getenv('DB_PORT') ?: '3306';
$dbname   = 'xibaar_yi';
$user     = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
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

// Construit un lien vers une page du site : url('admin/articles/ajouter.php')
function url($chemin = '')
{
    return BASE_URL . '/' . ltrim($chemin, '/');
}
