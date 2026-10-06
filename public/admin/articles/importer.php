<?php
// Bouton "Actualiser maintenant" de la liste des articles :
// importe tout de suite les nouveaux titres des flux RSS (voir includes/actualites.php).
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/actualites.php';

exiger_role(['editeur', 'administrateur']);

// Uniquement via le bouton (POST + jeton CSRF)
exiger_post_csrf('admin/articles/liste.php');

// Session libérée pendant l'import (quelques secondes) : les autres onglets restent utilisables
session_write_close();

try {
    $resultat = importer_actualites($pdo);
} catch (Throwable $e) {
    error_log('Xibaar Yi - import RSS : ' . $e->getMessage());
    // Message technique montré tel quel : seule la rédaction voit cette page,
    // et sans lui impossible de savoir ce qui bloque chez l'hébergeur
    $resultat = ['ajoutes' => 0, 'erreurs' => ["L'import a échoué : " . get_class($e) . ' — ' . $e->getMessage()
                                               . ' (' . basename($e->getFile()) . ', ligne ' . $e->getLine() . ')']];
}

if ($resultat === null) {
    $rapport = ['message' => "Un import est déjà en cours. Réessayez dans quelques secondes.", 'erreurs' => []];
} else {
    $rapport = [
        'message' => $resultat['ajoutes'] . " nouveau(x) titre(s) importé(s)."
                   . ($resultat['erreurs'] ? " Sources qui n'ont pas répondu :" : ''),
        'erreurs' => $resultat['erreurs'],
    ];
}

session_start();
$_SESSION['rapport_import'] = $rapport;

header('Location: ' . url('admin/articles/liste.php'));
exit;
