<?php
require_once __DIR__ . '/../../../includes/auth.php';

exiger_role(['editeur', 'administrateur']);

// Filtre : en attente (par défaut), approuvés, ou tous
$filtres = [
    'en_attente' => 'En attente',
    'approuve'   => 'Approuvés',
    'tous'       => 'Tous',
];
$filtre = isset($filtres[$_GET['statut'] ?? '']) ? $_GET['statut'] : 'en_attente';

$where  = $filtre === 'tous' ? '' : 'WHERE c.statut = ?';
$params = $filtre === 'tous' ? [] : [$filtre];

$stmt = $pdo->prepare("
    SELECT c.*, a.titre AS article_titre
    FROM commentaires c
    JOIN articles a ON c.id_article = a.id
    $where
    ORDER BY c.date_envoi DESC
");
$stmt->execute($params);
$commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre par statut, pour les onglets
$compteurs = ['en_attente' => 0, 'approuve' => 0];
foreach ($pdo->query("SELECT statut, COUNT(*) AS n FROM commentaires GROUP BY statut") as $ligne) {
    $compteurs[$ligne['statut']] = (int) $ligne['n'];
}
$compteurs['tous'] = $compteurs['en_attente'] + $compteurs['approuve'];

$titre_page = 'Commentaires';
include __DIR__ . '/../../../includes/entete.php';
?>

<div style="max-width:1100px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <h1 class="page-title">Commentaires</h1>
    </div>

    <nav class="onglets">
        <?php foreach ($filtres as $cle => $libelle) : ?>
            <a href="<?= url('admin/commentaires/liste.php') ?>?statut=<?= $cle ?>"
               class="<?= $cle === $filtre ? 'actif' : '' ?>">
                <?= $libelle ?> <span class="onglet-compteur"><?= $compteurs[$cle] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if (empty($commentaires)) : ?>
        <p style="font-size:13px; color:#999;">Aucun commentaire ici.</p>
    <?php else : ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Auteur</th>
                    <th>Commentaire</th>
                    <th>Article</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commentaires as $c) : ?>
                <tr style="<?= $c['statut'] === 'en_attente' ? 'background:#fffaf5;' : '' ?>">
                    <td style="white-space:nowrap;">
                        <?php if ($c['statut'] === 'en_attente') : ?>
                            <span class="badge" style="background:#cc0000; color:#fff;">En attente</span><br>
                        <?php endif; ?>
                        <?= date('d/m/Y H:i', strtotime($c['date_envoi'])) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($c['nom']) ?><br>
                        <a href="mailto:<?= htmlspecialchars($c['email']) ?>" style="font-size:12px; color:#cc0000;">
                            <?= htmlspecialchars($c['email']) ?>
                        </a>
                    </td>
                    <td><?= nl2br(htmlspecialchars($c['contenu'])) ?></td>
                    <td>
                        <a href="<?= url('article.php') ?>?id=<?= $c['id_article'] ?>#commentaires" style="color:#111; font-size:13px;">
                            <?= htmlspecialchars($c['article_titre']) ?>
                        </a>
                    </td>
                    <td>
                        <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">
                            <?php if ($c['statut'] === 'en_attente') : ?>
                            <form method="POST" action="<?= url('admin/commentaires/action.php') ?>" class="form-inline">
                                <?= csrf_champ() ?>
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="action" value="approuver">
                                <input type="hidden" name="retour" value="<?= $filtre ?>">
                                <button type="submit" class="btn btn-secondary" style="cursor:pointer; font-family:inherit;">Approuver</button>
                            </form>
                            <?php else : ?>
                            <form method="POST" action="<?= url('admin/commentaires/action.php') ?>" class="form-inline">
                                <?= csrf_champ() ?>
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="action" value="masquer">
                                <input type="hidden" name="retour" value="<?= $filtre ?>">
                                <button type="submit" class="btn btn-secondary" style="cursor:pointer; font-family:inherit; white-space:nowrap;">Masquer</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('admin/commentaires/action.php') ?>" class="form-inline"
                                  onsubmit="return confirm('Supprimer ce commentaire ?')">
                                <?= csrf_champ() ?>
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="retour" value="<?= $filtre ?>">
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../../includes/pied.php'; ?>
