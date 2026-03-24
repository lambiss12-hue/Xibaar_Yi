<?php
/*
 * ============================================================
 *  XIBAAR YI — entete.php (version finale)
 *  Rôle : En-tête HTML commun à TOUTES les pages du site.
 *
 *  Inclus dans chaque page avec :
 *    - include 'entete.php';        (depuis la racine)
 *    - include '../entete.php';     (depuis un sous-dossier)
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$titre_page = isset($titre_page) ? $titre_page : 'Xibaar Yi';

// Gestion du préfixe pour les sous-dossiers
$dossier_racine = dirname(__FILE__);
$dossier_script = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
$prefix = ($dossier_racine === $dossier_script) ? '' : '../';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titre_page, ENT_QUOTES, 'UTF-8'); ?> — Xibaar Yi</title>
    <meta name="description" content="Xibaar Yi — L'actualité du Sénégal.">
    <link rel="stylesheet" href="<?php echo $prefix; ?>style.css">
</head>
<body>

<div class="site">
    <!-- BARRE SUPÉRIEURE -->
    <div class="topbar">
        <div class="topbar-date">
            <?php
            $jours_fr = ['Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi','Sunday'=>'Dimanche'];
            $mois_fr = ['January'=>'janvier','February'=>'février','March'=>'mars','April'=>'avril','May'=>'mai','June'=>'juin','July'=>'juillet','August'=>'août','September'=>'septembre','October'=>'octobre','November'=>'novembre','December'=>'décembre'];
            $nom_jour = $jours_fr[date('l')] ?? date('l');
            $nom_mois = $mois_fr[date('F')]  ?? date('F');
            echo $nom_jour . ' ' . date('j') . ' ' . $nom_mois . ' ' . date('Y') . ' · Dakar, Sénégal';
            ?>
        </div>
        <div class="topbar-links">
            <a href="#">Newsletter</a>
            <a href="#">Contact</a>
        </div>
    </div>

    <!-- EN-TÊTE PRINCIPAL -->
    <div class="header">
        <div class="header-main">
            <a href="<?php echo $prefix; ?>accueil.php" style="text-decoration:none;">
                <div class="logo-wordmark">Xibaar Yi</div>
                <div class="logo-tagline">L'actualité du Sénégal</div>
            </a>

            <div class="header-actions">
                <?php if (isset($_SESSION['user_role'])) : ?>
                    <span style="font-size:11px; color:#555;">
                        <?php echo htmlspecialchars($_SESSION['user_login'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <a href="<?php echo $prefix; ?>deconnexion.php" class="btn-cnx">Se déconnecter</a>
                <?php else : ?>
                    <a href="<?php echo $prefix; ?>connexion.php" class="btn-cnx">Se connecter</a>
                    <div class="btn-abonne">S'abonner</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- NAVIGATION DYNAMIQUE (CORRECTION DES DOUBLONS ICI) -->
        <nav class="nav-cats">
            <!-- 1. Lien Accueil (Tout) -->
            <?php $active_all = (!isset($_GET['categorie']) || empty($_GET['categorie'])) ? 'active' : ''; ?>
            <a href="<?php echo $prefix; ?>accueil.php" class="<?php echo $active_all; ?>">Accueil</a>

            <?php
            // 2. Boucle unique sur les catégories de la BDD
            if (isset($pdo)) {
                // On utilise DISTINCT pour être sûr de ne pas avoir de doublons de noms
                $stmt_nav = $pdo->query("SELECT DISTINCT nom FROM categories ORDER BY id ASC");
                while ($cat = $stmt_nav->fetch(PDO::FETCH_ASSOC)) {
                    $nom = $cat['nom'];
                    $is_active = (isset($_GET['categorie']) && $_GET['categorie'] === $nom) ? 'active' : '';
                    
                    echo '<span class="sep">|</span>';
                    echo '<a href="' . $prefix . 'accueil.php?categorie=' . urlencode($nom) . '" class="' . $is_active . '">';
                    echo htmlspecialchars($nom);
                    echo '</a>';
                }
            }
            ?>

            <!-- 3. Liens d'administration -->
            <?php if (isset($_SESSION['user_role'])) : ?>
                <span class="sep">|</span>
                <?php if ($_SESSION['user_role'] === 'editeur' || $_SESSION['user_role'] === 'administrateur') : ?>
                    <a href="<?php echo $prefix; ?>articles/ajouter.php" style="color:#cc0000;font-weight:700;">+ Article</a>
                <?php endif; ?>
                <?php if ($_SESSION['user_role'] === 'administrateur') : ?>
                    <span class="sep">|</span>
                    <a href="<?php echo $prefix; ?>utilisateurs/liste.php" style="color:#cc0000;font-weight:700;">Admin</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>

    <div class="ticker">
        <div class="ticker-label">FLASH</div>
        <div class="ticker-text">Bienvenue sur Xibaar Yi — L'actualité du Sénégal en temps réel</div>
    </div>
