<?php
// Actions sur un message de contact : marquer lu / non lu, supprimer
require_once __DIR__ . '/../../../includes/auth.php';

exiger_role(['editeur', 'administrateur']);

// Uniquement via les boutons de la liste (POST + jeton CSRF)
exiger_post_csrf('admin/messages/liste.php');

$id     = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($action === 'lu' || $action === 'non_lu') {
    $stmt = $pdo->prepare("UPDATE messages_contact SET lu = ? WHERE id = ?");
    $stmt->execute([$action === 'lu' ? 1 : 0, $id]);
} elseif ($action === 'supprimer') {
    $stmt = $pdo->prepare("DELETE FROM messages_contact WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: ' . url('admin/messages/liste.php'));
exit;
