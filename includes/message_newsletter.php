<?php
// Message affiché dans l'encadré Newsletter après une inscription
// (traitement_newsletter.php redirige avec ?newsletter=ok|deja|erreur)
$messages_newsletter = [
    'ok'     => ['#1a7f37', '✓ Inscription réussie, merci !'],
    'deja'   => ['#8a6d00', 'ℹ Cet email est déjà inscrit à la newsletter.'],
    'erreur' => ['#cc0000', '✗ Email invalide.'],
];

$cle_newsletter = $_GET['newsletter'] ?? '';

if (isset($messages_newsletter[$cle_newsletter])) :
    [$couleur, $texte] = $messages_newsletter[$cle_newsletter];
?>
    <p style="color:<?= $couleur ?>; font-size:11px; font-weight:600; margin-bottom:10px;">
        <?= $texte ?>
    </p>
<?php endif; ?>
