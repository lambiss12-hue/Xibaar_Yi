<?php
require_once __DIR__ . '/../../../includes/auth.php';

exiger_role(['administrateur']);

$stmt = $pdo->query("
    SELECT u.*, COUNT(a.id) AS nb_articles
    FROM utilisateurs u
    LEFT JOIN articles a ON a.id_auteur = u.id
    GROUP BY u.id
    ORDER BY u.nom ASC
");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../../includes/entete.php';
?>

<div style="max-width:1100px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
    <h1 class="page-title">Gestion des utilisateurs</h1>
    
    <a href="<?= url('admin/utilisateurs/ajouter.php') ?>" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        Nouvel utilisateur
    </a>
    </div>

    <?php if (($_GET['erreur'] ?? '') === 'articles') : ?>
    <div class="alert alert-danger">
        Impossible de supprimer un utilisateur qui est l'auteur d'articles.
        Supprimez d'abord ses articles.
    </div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>Nom complet</th>
                <th>Login</th>
                <th>Rôle</th>
                <th>Articles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= htmlspecialchars(masquer_si_demo($u['login'], 'login')) ?></td>
                <td>
                    <span class="badge"><?= htmlspecialchars($u['role']) ?></span>
                </td>
                <td><?= (int) $u['nb_articles'] ?></td>
                <td style="display:flex; gap:8px;">
                    <a href="<?= url('admin/utilisateurs/modifier.php') ?>?id=<?= $u['id'] ?>" class="btn btn-secondary">
                        Modifier
                    </a>
                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <form method="POST" action="<?= url('admin/utilisateurs/supprimer.php') ?>" class="form-inline"
                          onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        <?= csrf_champ() ?>
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../../includes/pied.php'; ?>
