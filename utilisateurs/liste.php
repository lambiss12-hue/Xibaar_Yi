<?php
require_once '../config.php';

if (!isset($_SESSION['user_role'])) {
    header('Location: /xibaar_yi/connexion.php');
    exit;
}

if ($_SESSION['user_role'] !== 'administrateur') {
    header('Location: /xibaar_yi/accueil.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY nom ASC");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../entete.php';
?>

<div style="max-width:1100px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Gestion des utilisateurs</div>
        <a href="/xibaar_yi/utilisateurs/ajouter.php" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouvel utilisateur
        </a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nom complet</th>
                <th>Login</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= htmlspecialchars($u['login']) ?></td>
                <td>
                    <span class="badge">
                        <?= htmlspecialchars($u['role']) ?>
                    </span>
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="/xibaar_yi/utilisateurs/modifier.php?id=<?= $u['id'] ?>" class="btn btn-secondary">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Modifier
                    </a>
                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <a href="/xibaar_yi/utilisateurs/supprimer.php?id=<?= $u['id'] ?>" class="btn btn-danger"
                       onclick="return confirm('Supprimer cet utilisateur ?')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                        Supprimer
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php require_once '../pied.php'; ?>