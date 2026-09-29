<?php
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['editeur', 'administrateur']);

    $stmt = $pdo->query("
        SELECT c.*, COUNT(a.id) AS nb_articles
        FROM categories c
        LEFT JOIN articles a ON a.id_categorie = c.id
        GROUP BY c.id
        ORDER BY c.nom ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once __DIR__ . '/../../../includes/entete.php';
   
?>

<div style="max-width:1100px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Gestion des catégories</div>
        <a href="<?= url('admin/categories/ajouter.php') ?>" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouvelle catégorie
        </a>
    </div>

    <?php if (($_GET['erreur'] ?? '') === 'articles') : ?>
    <div class="alert alert-danger">
        Impossible de supprimer une catégorie qui contient encore des articles.
        Déplacez ou supprimez d'abord ses articles.
    </div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Articles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['nom']) ?></td>
                <td><?= (int) $cat['nb_articles'] ?></td>
                <td style="display:flex; gap:8px;">
                    <a href="<?= url('admin/categories/modifier.php') ?>?id=<?= $cat['id'] ?>" class="btn btn-secondary">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Modifier
                    </a>
                    <form method="POST" action="<?= url('admin/categories/supprimer.php') ?>" class="form-inline"
                          onsubmit="return confirm('Supprimer cette catégorie ?')">
                        <?= csrf_champ() ?>
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                            Supprimer
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php require_once __DIR__ . '/../../../includes/pied.php'; ?>