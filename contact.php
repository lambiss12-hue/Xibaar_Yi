<?php
// Inclusion de la configuration (si besoin de la base de données plus tard)
require_once 'config.php';

// Inclusion de l'entête commune (Logo, Menu, Ticker)
include 'entete.php'; 
?>

<main class="main">
    <!-- On utilise .main-left pour que le formulaire occupe la partie large à gauche -->
    <div class="main-left">
        
        <div style="margin-bottom: 30px;">
            <h2 style="font-family: Georgia, serif; font-size: 28px; border-bottom: 2px solid #111; padding-bottom: 10px;">
                Contactez la rédaction
            </h2>
            <p style="font-size: 14px; color: #666; margin-top: 10px;">
                Une information à nous partager ? Une question sur nos articles ? Utilisez le formulaire ci-dessous.
            </p>
        </div>

        <!-- Formulaire de contact stylisé "Journal" -->
        <form action="traitement_contact.php" method="POST" style="max-width: 600px;">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Nom complet</label>
                <input type="text" name="nom" required placeholder="Ex: Moussa Diop" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; font-family: inherit; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Adresse Email</label>
                <input type="email" name="email" required placeholder="votre@email.com" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; font-family: inherit; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Sujet</label>
                <select name="sujet" style="width: 100%; padding: 12px; border: 1px solid #ddd; background: #fff; font-family: inherit;">
                    <option value="info">Partager une information</option>
                    <option value="technique">Problème technique</option>
                    <option value="publicite">Publicité / Partenariat</option>
                    <option value="autre">Autre demande</option>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Votre message</label>
                <textarea name="message" required rows="6" placeholder="Ecrivez votre message ici..." 
                          style="width: 100%; padding: 12px; border: 1px solid #ddd; font-family: inherit; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <button type="submit" 
                    style="background: #111; color: #fff; border: none; padding: 15px 40px; font-size: 13px; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;">
                Envoyer le message
            </button>
        </form>

    </div>

    <!-- On garde la barre latérale pour la cohérence visuelle -->
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

        <!-- Rappel Newsletter dans la page contact -->
        <div class="sidebar-section" style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin-top: 20px; border: 1px solid #eee;">
            <div class="sb-title">Newsletter</div>
            <form action="traitement_newsletter.php" method="POST">
                <input type="email" name="email" placeholder="Votre email" required style="width: 100%; padding: 8px; font-size: 12px; border: 1px solid #ddd; margin-bottom: 8px;">
                <button type="submit" style="width: 100%; background: #111; color: #fff; border: none; padding: 8px; font-size: 11px; font-weight: bold; cursor: pointer; width: 100%;">S'ABONNER</button>
            </form>
        </div>
    </aside>
</main>

<?php 
// fichier pied.php (footer), inclure ici
include 'pied.php'; 
?>
