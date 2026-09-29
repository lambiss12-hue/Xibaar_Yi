<?php
require_once __DIR__ . '/../../../includes/auth.php';

exiger_role(['editeur', 'administrateur']);

$stmt = $pdo->query("SELECT * FROM messages_contact ORDER BY date_envoi DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <span style="font-size:12px; color:#999;"><?= count($messages) ?> message(s)</span>
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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                <tr>
                    <td style="white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($m['date_envoi'])) ?></td>
                    <td>
                        <?= htmlspecialchars($m['nom']) ?><br>
                        <a href="mailto:<?= htmlspecialchars($m['email']) ?>" style="font-size:12px; color:#cc0000;">
                            <?= htmlspecialchars($m['email']) ?>
                        </a>
                    </td>
                    <td><span class="badge"><?= htmlspecialchars($libelles_sujets[$m['sujet']] ?? $m['sujet']) ?></span></td>
                    <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../../includes/pied.php'; ?>
