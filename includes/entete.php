<?php
require_once __DIR__ . '/config.php';

$titre_page = isset($titre_page) ? $titre_page : 'Xibaar Yi';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titre_page, ENT_QUOTES, 'UTF-8'); ?> — Xibaar Yi</title>
    <meta name="description" content="Xibaar Yi — L'actualité du Sénégal.">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">

</head>
<body>

<div class="site">
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
    </div>

    <div class="header">
        <div class="header-main">
            <a href="<?= url('index.php') ?>" style="text-decoration:none;">
                <div class="logo-wordmark">Xibaar Yi</div>
                <div class="logo-tagline">L'actualité du Sénégal</div>
            </a>

            <div class="header-actions">
                <?php if (isset($_SESSION['user_role'])) : ?>
                    <span style="font-size:11px; color:#555;">
                        <?php echo htmlspecialchars($_SESSION['user_login'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <a href="<?= url('deconnexion.php') ?>" class="btn-cnx">Se déconnecter</a>
                <?php else : ?>
                    <a href="<?= url('connexion.php') ?>" class="btn-cnx">Se connecter</a>
                <?php endif; ?>

                <div class="header-actions">
                    <a href="<?= url('contact.php') ?>" class="btn-cnx">Contact</a>
                </div>
            </div>
        </div>

        <nav class="nav-cats">
            <?php $active_all = (!isset($_GET['categorie']) || empty($_GET['categorie'])) ? 'active' : ''; ?>
            <a href="<?= url('index.php') ?>" class="<?php echo $active_all; ?>">Accueil</a>

            <?php
            if (isset($pdo)) {
                $stmt_nav = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
                while ($cat = $stmt_nav->fetch(PDO::FETCH_ASSOC)) {
                    $nom = $cat['nom'];
                    $is_active = (isset($_GET['categorie']) && $_GET['categorie'] === $nom) ? 'active' : '';
                    
                    echo '<span class="sep">|</span>';
                    echo '<a href="' . url('index.php') . '?categorie=' . urlencode($nom) . '" class="' . $is_active . '">';
                    echo htmlspecialchars($nom);
                    echo '</a>';
                }
            }
            ?>
            
            <?php if (isset($_SESSION['user_role'])) : ?>
                <span class="sep">|</span>
                <?php if ($_SESSION['user_role'] === 'editeur' || $_SESSION['user_role'] === 'administrateur') : ?>
                    <a href="<?= url('admin/articles/ajouter.php') ?>" style="color:#cc0000;font-weight:700;">+ Article</a>
                <?php endif; ?>
                <?php if ($_SESSION['user_role'] === 'administrateur') : ?>
                    <span class="sep">|</span>
                    <a href="<?= url('admin/utilisateurs/liste.php') ?>" style="color:#cc0000;font-weight:700;">Admin</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>

    <div class="ticker">
        <div class="ticker-label">FLASH</div>
        <div class="ticker-text">Bienvenue sur Xibaar Yi — L'actualité du Sénégal en temps réel</div>
    </div>
