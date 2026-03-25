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
<<<<<<< HEAD
    <title>Xibaar Yi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            color: #1C1C1E;
        }

        /* HEADER */
        header {
            background: #111111;
            padding: 14px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
        }

        .logo-text {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .logo-sub {
            color: #888888;
            font-size: 12px;
            font-weight: 400;
        }

        .header-right {
            color: #555555;
            font-size: 12px;
        }

        /* NAVIGATION */
        nav {
            background: #1a1a1a;
            padding: 0 40px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #2a2a2a;
        }

        nav a {
            color: #aaaaaa;
            font-size: 13px;
            padding: 14px 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-bottom: 2px solid transparent;
            transition: color 0.2s, border-color 0.2s;
        }

        nav a:hover {
            color: #ffffff;
        }

        nav a.active {
            color: #ffffff;
            border-bottom: 2px solid #ffffff;
        }

        nav a svg {
            width: 14px;
            height: 14px;
        }

        .nav-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-connexion {
            background: #ffffff;
            color: #111111;
            font-size: 12px;
            font-weight: 600;
            padding: 7px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-connexion:hover {
            background: #e8e8e8;
        }

        .btn-deconnexion {
            color: #ff6b6b;
            font-size: 12px;
            font-weight: 500;
            padding: 7px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #ff6b6b33;
            transition: background 0.2s;
        }

        .btn-deconnexion:hover {
            background: #ff6b6b11;
        }

        .user-info {
            color: #888;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            background: #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
        }

        /* MAIN CONTENT */
        main {
            max-width: 1100px;
            margin: 32px auto;
            padding: 0 24px;
        }

        /* CARDS */
        .article-card {
            background: #ffffff;
            border-radius: 8px;
            border: 0.5px solid #e0e0e0;
            overflow: hidden;
            margin-bottom: 16px;
            display: flex;
            transition: box-shadow 0.2s, transform 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .article-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }

        .article-card-img {
            width: 110px;
            min-height: 100px;
            background: #111;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .article-card-img svg {
            width: 32px;
            height: 32px;
            color: #444;
        }

        .article-card-body {
            padding: 16px 18px;
            flex: 1;
        }

        .badge {
            display: inline-block;
            background: #f0f0f0;
            color: #444444;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .article-title {
            font-size: 15px;
            font-weight: 600;
            color: #111111;
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .article-desc {
            font-size: 13px;
            color: #888888;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .article-meta {
            font-size: 11px;
            color: #bbbbbb;
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .article-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .article-meta svg {
            width: 11px;
            height: 11px;
        }

        /* SIDEBAR */
        .sidebar-box {
            background: #ffffff;
            border-radius: 8px;
            border: 0.5px solid #e0e0e0;
            padding: 18px;
            margin-bottom: 16px;
        }

        .sidebar-title {
            font-size: 11px;
            font-weight: 700;
            color: #111111;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .cat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 0.5px solid #f5f5f5;
            font-size: 13px;
            color: #444444;
            text-decoration: none;
            transition: color 0.2s;
        }

        .cat-item:last-child {
            border-bottom: none;
        }

        .cat-item:hover {
            color: #111111;
        }

        .cat-count {
            background: #f0f0f0;
            color: #666666;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #eeeeee;
        }

        .pagination a {
            background: #ffffff;
            border: 0.5px solid #dddddd;
            color: #444444;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: #111111;
            color: #ffffff;
            border-color: #111111;
        }

        .pagination span {
            font-size: 13px;
            color: #aaaaaa;
        }

        /* BOUTONS ACTION */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn svg {
            width: 14px;
            height: 14px;
        }

        .btn-primary {
            background: #111111;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #333333;
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #444444;
            border: 0.5px solid #dddddd;
        }

        .btn-secondary:hover {
            background: #eeeeee;
        }

        .btn-danger {
            background: #fff0f0;
            color: #cc0000;
            border: 0.5px solid #ffcccc;
        }

        .btn-danger:hover {
            background: #ffe0e0;
        }

        /* FORMULAIRES */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 0.5px solid #dddddd;
            border-radius: 4px;
            font-size: 14px;
            color: #111111;
            background: #ffffff;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #111111;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .form-error {
            color: #cc0000;
            font-size: 12px;
            margin-top: 4px;
        }

        /* ALERTS */
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .alert-success {
            background: #f0faf4;
            color: #1a7a3c;
            border: 0.5px solid #b8e6cb;
        }

        .alert-danger {
            background: #fff0f0;
            color: #cc0000;
            border: 0.5px solid #ffcccc;
        }

        /* PAGE TITLE */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #eeeeee;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #111111;
        }

        /* TABLE */
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table th {
            text-align: left;
            padding: 10px 14px;
            background: #f5f5f5;
            color: #666666;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #eeeeee;
        }

        .table td {
            padding: 12px 14px;
            border-bottom: 0.5px solid #f5f5f5;
            color: #333333;
        }

        .table tr:hover td {
            background: #fafafa;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            main {
                grid-template-columns: 1fr;
            }
            header {
                padding: 14px 20px;
            }
            nav {
                padding: 0 20px;
                overflow-x: auto;
            }
        }
    </style>
=======
    <title><?php echo htmlspecialchars($titre_page, ENT_QUOTES, 'UTF-8'); ?> — Xibaar Yi</title>
    <meta name="description" content="Xibaar Yi — L'actualité du Sénégal.">
    <link rel="stylesheet" href="<?php echo $prefix; ?>style.css">
>>>>>>> ab47af5aed85abe0d983b0e91efcfef81bb7c13d
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
