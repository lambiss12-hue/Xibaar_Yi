<?php
require_once __DIR__ . '/../includes/config.php';

// Saisie gardée après une erreur (voir traitement_contact.php), affichée une seule fois
$saisie = $_SESSION['contact_saisie'] ?? [];
unset($_SESSION['contact_saisie']);
$saisie += ['nom' => '', 'email' => '', 'sujet' => '', 'message' => ''];

$sujets = [
    'info'      => 'Partager une information',
    'technique' => 'Problème technique',
    'publicite' => 'Publicité / Partenariat',
    'autre'     => 'Autre demande',
];

$titre_page = 'Contact';
include __DIR__ . '/../includes/entete.php';
?>

<main class="main">
    <div class="main-left">
        
        <div style="margin-bottom: 30px;">
            <h2 style="font-family: Georgia, serif; font-size: 28px; border-bottom: 2px solid #111; padding-bottom: 10px;">
                Contactez la rédaction
            </h2>
            <p style="font-size: 14px; color: #666; margin-top: 10px;">
                Une information à nous partager ? Une question sur nos articles ? Utilisez le formulaire ci-dessous.
            </p>
        </div>

        <?php if (isset($_GET['envoi'])) : ?>
            <?php if ($_GET['envoi'] === 'ok') : ?>
                <p style="max-width: 600px; background: #f0fff4; color: #1a7f37; border: 1px solid #b7ebc6; padding: 12px 16px; font-size: 13px; margin-bottom: 20px;">
                    ✓ Merci, votre message a bien été envoyé à la rédaction.
                </p>
            <?php else : ?>
                <p style="max-width: 600px; background: #fff0f0; color: #cc0000; border: 1px solid #ffcccc; padding: 12px 16px; font-size: 13px; margin-bottom: 20px;">
                    <?php if ($_GET['envoi'] === 'trop_rapide') : ?>
                        ✗ Message non envoyé : merci de patienter une minute avant d'envoyer un nouveau message.
                    <?php else : ?>
                        ✗ Message non envoyé : vérifiez que tous les champs sont remplis et que l'email est valide.
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <form action="<?= url('traitement_contact.php') ?>" method="POST" style="max-width: 600px;">

            <!-- Champ piège anti-robots : invisible pour les humains, ne pas remplir -->
            <div class="champ-piege" aria-hidden="true">
                <label for="site_web">Ne pas remplir ce champ</label>
                <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Nom complet</label>
                <input type="text" name="nom" required maxlength="150" placeholder="Ex: Moussa Diop"
                       value="<?= htmlspecialchars($saisie['nom'], ENT_QUOTES, 'UTF-8') ?>"
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; font-family: inherit; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Adresse Email</label>
                <input type="email" name="email" required maxlength="150" placeholder="votre@email.com"
                       value="<?= htmlspecialchars($saisie['email'], ENT_QUOTES, 'UTF-8') ?>"
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; font-family: inherit; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Sujet</label>
                <select name="sujet" style="width: 100%; padding: 12px; border: 1px solid #ddd; background: #fff; font-family: inherit;">
                    <?php foreach ($sujets as $valeur => $libelle) : ?>
                        <option value="<?= $valeur ?>" <?= $saisie['sujet'] === $valeur ? 'selected' : '' ?>><?= $libelle ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Votre message</label>
                <textarea name="message" required rows="6" maxlength="5000" placeholder="Écrivez votre message ici..."
                          style="width: 100%; padding: 12px; border: 1px solid #ddd; font-family: inherit; font-size: 14px; resize: vertical;"><?= htmlspecialchars($saisie['message'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <button type="submit" 
                    style="background: #111; color: #fff; border: none; padding: 15px 40px; font-size: 13px; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;">
                Envoyer le message
            </button>
        </form>

    </div>

    <aside class="main-sidebar">
        <div class="sidebar-section">
            <div class="sb-title">Nos bureaux</div>
            <p style="font-size: 12px; color: #444; line-height: 1.6;">
                <strong>Xibaar Yi Média</strong><br>
                Avenue Cheikh Anta Diop<br>
                Dakar, Sénégal<br><br>
                <strong>Téléphone :</strong> +221 33 000 00 00<br>
                <strong>Email :</strong> redaction@xibaaryi.sn
            </p>
        </div>

        <div class="sidebar-section" style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin-top: 20px; border: 1px solid #eee;">
            <div class="sb-title">Newsletter</div>
            <?php include __DIR__ . '/../includes/message_newsletter.php'; ?>
            <form action="<?= url('traitement_newsletter.php') ?>" method="POST">
                <input type="hidden" name="retour" value="contact.php">
                <!-- Champ piège anti-robots (caché) -->
                <input type="text" name="site_web" class="champ-piege" tabindex="-1" autocomplete="off" aria-hidden="true">
                <input type="email" name="email" placeholder="Votre email" required style="width: 100%; padding: 8px; font-size: 12px; border: 1px solid #ddd; margin-bottom: 8px;">
                <button type="submit" style="width: 100%; background: #111; color: #fff; border: none; padding: 8px; font-size: 11px; font-weight: bold; cursor: pointer; width: 100%;">S'ABONNER</button>
            </form>
        </div>
    </aside>
</main>

<?php 
include __DIR__ . '/../includes/pied.php'; 
?>
