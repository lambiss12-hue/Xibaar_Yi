<?php
/*
 * ============================================================
 *  XIBAAR YI — pied.php
 *  Rôle : Pied de page commun à TOUTES les pages du site.
 *         Ferme aussi le <div class="site"> ouvert dans entete.php
 *         et les balises </body> et </html>.
 *
 *  Inclus dans chaque page avec :
 *    include __DIR__ . '/../includes/pied.php';
 *  Les liens utilisent url() (défini dans config.php).
 * ============================================================
 */
require_once __DIR__ . '/config.php';
?>

    <!-- ============================================================
         PIED DE PAGE (.footer)
         Bande noire en bas : logo + liens + copyright
         ============================================================ -->
    <div class="footer">

        <!-- Bloc gauche : logo + copyright -->
        <div>
            <a href="<?= url('index.php') ?>" style="text-decoration: none;">
                <div class="footer-logo">Xibaar Yi</div>
            </a>
            <!-- date('Y') affiche l'année courante dynamiquement.
                 Ainsi le copyright se met à jour automatiquement chaque année. -->
            <div class="footer-text">
                &copy; <?php echo date('Y'); ?> — École Supérieure Polytechnique
            </div>
        </div>

        <!-- Bloc droit : liens de navigation -->
        <div class="footer-links">
            <a href="<?= url('index.php') ?>">Accueil</a>
            <a href="<?= url('index.php') ?>?categorie=Technologie">Technologie</a>
            <a href="<?= url('index.php') ?>?categorie=Sport">Sport</a>
             <a href="<?= url('index.php') ?>?categorie=Politique">Politique</a>
              <a href="<?= url('index.php') ?>?categorie=Education">Education</a>
               <a href="<?= url('index.php') ?>?categorie=Culture">Culture</a>
            <a href="<?= url('connexion.php') ?>">Connexion</a>
            <a href="<?= url('contact.php') ?>">Contact</a>
        </div>

    </div>
    <!-- Fin .footer -->

</div>
<!-- Fin .site — ce div a été OUVERT dans entete.php.
     On le ferme ici car pied.php est toujours inclus en dernier. -->

</body>
</html>
<!-- Fin du document HTML.
     Chaque page PHP du projet se termine donc avec include 'pied.php'
     qui ferme proprement toutes les balises ouvertes dans entete.php. -->