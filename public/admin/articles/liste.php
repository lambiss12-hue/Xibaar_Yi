<?php
require_once __DIR__ . '/../../../includes/auth.php';

exiger_role(['editeur', 'administrateur']);

// Filtres : recherche dans le titre + catégorie
$recherche    = trim($_GET['q'] ?? '');
$id_categorie = (int) ($_GET['categorie'] ?? 0);

$conditions = [];
$params     = [];

if ($recherche !== '') {
    $conditions[] = "a.titre LIKE ?";
    $params[]     = '%' . $recherche . '%';
}
if ($id_categorie > 0) {
    $conditions[] = "a.id_categorie = ?";
    $params[]     = $id_categorie;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$stmt = $pdo->prepare("
    SELECT a.id, a.titre, a.date_publication,
           c.nom AS categorie_nom,
           CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
    FROM articles a
    JOIN categories c   ON a.id_categorie = c.id
    JOIN utilisateurs u ON a.id_auteur = u.id
    $where
    ORDER BY a.date_publication DESC
");
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../../includes/entete.php';
?>

<div style="max-width:1100px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <h1 class="page-title">Gestion des articles</h1>
        <a href="<?= url('admin/articles/ajouter.php') ?>" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouvel article
        </a>
    </div>

    <form method="GET" action="<?= url('admin/articles/liste.php') ?>"
          style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
        <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>" placeholder="Rechercher un titre..."
               style="flex:1; min-width:200px; padding:10px 14px; border:1px solid #ddd; font-size:13px; font-family:inherit;">
        <select name="categorie" style="padding:10px 14px; border:1px solid #ddd; font-size:13px; font-family:inherit; background:#fff;">
            <option value="0">Toutes les catégories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $id_categorie ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nom']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary" style="cursor:pointer; font-family:inherit;">Filtrer</button>
        <?php if ($recherche !== '' || $id_categorie > 0): ?>
            <a href="<?= url('admin/articles/liste.php') ?>" class="btn btn-secondary">Effacer</a>
        <?php endif; ?>
    </form>

    <p style="font-size:12px; color:#999; margin-bottom:10px;"><?= count($articles) ?> article(s)</p>

    <?php if (empty($articles)) : ?>
        <p style="font-size:13px; color:#999;">Aucun article trouvé.</p>
    <?php else : ?>
    <table class="table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Auteur</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $a): ?>
            <tr>
                <td>
                    <a href="<?= url('article.php') ?>?id=<?= $a['id'] ?>" style="color:#111; font-weight:600; text-decoration:none;">
                        <?= htmlspecialchars($a['titre']) ?>
                    </a>
                </td>
                <td><span class="badge"><?= htmlspecialchars($a['categorie_nom']) ?></span></td>
                <td><?= htmlspecialchars($a['auteur_nom']) ?></td>
                <td style="white-space:nowrap;"><?= date('d/m/Y', strtotime($a['date_publication'])) ?></td>
                <td style="display:flex; gap:8px;">
                    <a href="<?= url('admin/articles/modifier.php') ?>?id=<?= $a['id'] ?>" class="btn btn-secondary">Modifier</a>
                    <form method="POST" action="<?= url('admin/articles/supprimer.php') ?>" class="form-inline"
                          onsubmit="return confirm('Supprimer cet article ?')">
                        <?= csrf_champ() ?>
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <input type="hidden" name="retour" value="admin/articles/liste.php">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../../includes/pied.php'; ?>
