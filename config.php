<?php
// Session démarrée en tout premier
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Connexion à la base de données
$host     = 'localhost';
$dbname   = 'xibaar_yi';
$user     = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>