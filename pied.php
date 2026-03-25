<?php
/*
 * ============================================================
 *  XIBAAR YI — pied.php
 *  Rôle : Pied de page commun à TOUTES les pages du site.
 *         Ferme aussi le <div class="site"> ouvert dans entete.php
 *         et les balises </body> et </html>.
 *
 *  Inclus dans chaque page avec :
 *    - include 'pied.php';       (depuis la racine)
 *    - include '../pied.php';    (depuis un sous-dossier)
 * ============================================================
 */

// Même logique que dans entete.php pour calculer le préfixe de chemin.
$dossier_racine = dirname(__FILE__);
$dossier_script = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
$prefix = ($dossier_racine === $dossier_script) ? '' : '../';
?>

    <!-- ============================================================
         PIED DE PAGE (.footer)
         Bande noire en bas : logo + liens + copyright
         ============================================================ -->
    <div class="footer">

        <!-- Bloc gauche : logo + copyright -->
        <div>
            <a href="<?php echo $prefix; ?>accueil.php" style="text-decoration: none;">
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
            <a href="<?php echo $prefix; ?>accueil.php">Accueil</a>
            <a href="<?php echo $prefix; ?>accueil.php?categorie=Technologie">Technologie</a>
            <a href="<?php echo $prefix; ?>accueil.php?categorie=Sport">Sport</a>
             <a href="<?php echo $prefix; ?>accueil.php?categorie=Politique">Politique</a>
              <a href="<?php echo $prefix; ?>accueil.php?categorie=Education">Education</a>
               <a href="<?php echo $prefix; ?>accueil.php?categorie=Culture">Culture</a>
            <a href="<?php echo $prefix; ?>connexion.php">Connexion</a>
            <a href="#">Contact</a>
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