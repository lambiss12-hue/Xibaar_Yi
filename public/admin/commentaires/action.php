<?php
// Modération d'un commentaire : approuver, masquer (remettre en attente), supprimer
require_once __DIR__ . '/../../../includes/auth.php';

exiger_role(['editeur', 'administrateur']);

// On revient sur le même onglet de la liste
$onglets = ['en_attente', 'approuve', 'tous'];
$retour  = 'admin/commentaires/liste.php?statut='
         . (in_array($_POST['retour'] ?? '', $onglets, true) ? $_POST['retour'] : 'en_attente');

// Uniquement via les boutons de la liste (POST + jeton CSRF)
exiger_post_csrf($retour);

$id     = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($action === 'approuver' || $action === 'masquer') {
    $stmt = $pdo->prepare("UPDATE commentaires SET statut = ? WHERE id = ?");
    $stmt->execute([$action === 'approuver' ? 'approuve' : 'en_attente', $id]);
} elseif ($action === 'supprimer') {
    $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: ' . url($retour));
exit;
