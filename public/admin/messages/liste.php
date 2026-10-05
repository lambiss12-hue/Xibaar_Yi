<?php
require_once __DIR__ . '/../../../includes/auth.php';

exiger_role(['editeur', 'administrateur']);

// Non lus en premier, puis du plus récent au plus ancien
$stmt = $pdo->query("SELECT * FROM messages_contact ORDER BY lu ASC, date_envoi DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nb_non_lus = count(array_filter($messages, fn($m) => !$m['lu']));

// Libellés des sujets (mêmes valeurs que la liste déroulante de contact.php)
$libelles_sujets = [
    'info'      => 'Information',
    'technique' => 'Problème technique',
    'publicite' => 'Publicité / Partenariat',
    'autre'     => 'Autre',
];

include __DIR__ . '/../../../includes/entete.php';
?>

<div style="max-width:1100px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <h1 class="page-title">Messages de contact</h1>
        <span style="font-size:12px; color:#999;">
            <?= count($messages) ?> message(s) · <strong style="color:#cc0000;"><?= $nb_non_lus ?> non lu(s)</strong>
        </span>
    </div>

    <?php if (empty($messages)) : ?>
        <p style="font-size:13px; color:#999;">Aucun message pour le moment.</p>
    <?php else : ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Expéditeur</th>
                    <th>Sujet</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                <tr style="<?= $m['lu'] ? 'color:#888;' : 'background:#fffaf5;' ?>">
                    <td style="white-space:nowrap;">
                        <?php if (!$m['lu']): ?>
                            <span class="badge" style="background:#cc0000; color:#fff;">Nouveau</span><br>
                        <?php endif; ?>
                        <?= date('d/m/Y H:i', strtotime($m['date_envoi'])) ?>
                    </td>
                    <td style="<?= $m['lu'] ? '' : 'font-weight:700;' ?>">
                        <?= htmlspecialchars($m['nom']) ?><br>
                        <a href="mailto:<?= htmlspecialchars(masquer_si_demo($m['email'])) ?>" style="font-size:12px; color:#cc0000; font-weight:400;">
                            <?= htmlspecialchars(masquer_si_demo($m['email'])) ?>
                        </a>
                    </td>
                    <td><span class="badge"><?= htmlspecialchars($libelles_sujets[$m['sujet']] ?? $m['sujet']) ?></span></td>
                    <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                    <td>
                        <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">
                            <form method="POST" action="<?= url('admin/messages/action.php') ?>" class="form-inline">
                                <?= csrf_champ() ?>
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="hidden" name="action" value="<?= $m['lu'] ? 'non_lu' : 'lu' ?>">
                                <button type="submit" class="btn btn-secondary" style="cursor:pointer; font-family:inherit; white-space:nowrap;">
                                    <?= $m['lu'] ? 'Marquer non lu' : 'Marquer lu' ?>
                                </button>
                            </form>
                            <form method="POST" action="<?= url('admin/messages/action.php') ?>" class="form-inline"
                                  onsubmit="return confirm('Supprimer ce message ?')">
                                <?= csrf_champ() ?>
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="hidden" name="action" value="supprimer">
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
