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
                &copy; <?php echo date('Y'); ?> Salamba Diène — projet né à l'École Supérieure Polytechnique de Dakar
            </div>
            <div class="footer-text">
                <a href="https://github.com/lambiss12-hue/Xibaar_Yi" target="_blank" rel="noopener" class="footer-source">
                    Code source sur GitHub
                </a>
            </div>
        </div>

        <!-- Bloc droit : liens de navigation -->
        <div class="footer-links">
            <a href="<?= url('index.php') ?>">Accueil</a>
            <?php
            // Mêmes catégories que le menu du haut (lues dans la base, jamais écrites en dur)
            foreach ($pdo->query("SELECT nom FROM categories ORDER BY nom ASC") as $cat_pied) : ?>
                <a href="<?= url('index.php') ?>?categorie=<?= urlencode($cat_pied['nom']) ?>">
                    <?= htmlspecialchars($cat_pied['nom'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
            <?php if (!isset($_SESSION['user_role'])) : ?>
                <a href="<?= url('connexion.php') ?>">Connexion</a>
            <?php endif; ?>
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