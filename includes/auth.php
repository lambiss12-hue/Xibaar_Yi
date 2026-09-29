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
