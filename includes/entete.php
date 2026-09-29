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
                <form method="GET" action="<?= url('index.php') ?>" class="recherche" role="search">
                    <input type="search" name="q" placeholder="Rechercher un article..." aria-label="Rechercher un article"
                           value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" aria-label="Lancer la recherche">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    </button>
                </form>

                <?php if (isset($_SESSION['user_role'])) : ?>
                    <span style="font-size:11px; color:#555;">
                        <?php echo htmlspecialchars($_SESSION['user_login'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <a href="<?= url('deconnexion.php') ?>" class="btn-cnx">Se déconnecter</a>
                <?php else : ?>
                    <a href="<?= url('connexion.php') ?>" class="btn-cnx">Se connecter</a>
                <?php endif; ?>

                <a href="<?= url('contact.php') ?>" class="btn-cnx">Contact</a>
            </div>
        </div>

        <nav class="nav-cats">
            <?php $active_all = (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php' && empty($_GET['categorie'])) ? 'active' : ''; ?>
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
            
        </nav>

        <?php if (in_array($_SESSION['user_role'] ?? '', ['editeur', 'administrateur'], true)) : ?>
            <?php
            // Nombre de messages non lus (affiché à côté de "Messages")
            $nb_messages_non_lus = 0;
            try {
                $nb_messages_non_lus = (int) $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn();
            } catch (PDOException $e) {
                // Migration 004 pas encore exécutée : on n'affiche simplement pas le compteur
            }

            // Lien actif = section admin de la page courante
            $page_admin = $_SERVER['SCRIPT_NAME'] ?? '';
            $liens_admin = [
                'admin/articles/liste.php'     => 'Articles',
                'admin/articles/ajouter.php'   => '+ Nouvel article',
                'admin/categories/liste.php'   => 'Catégories',
                'admin/messages/liste.php'     => 'Messages',
            ];
            if ($_SESSION['user_role'] === 'administrateur') {
                $liens_admin['admin/utilisateurs/liste.php'] = 'Utilisateurs';
            }

            // Lien à mettre en surbrillance : la page elle-même si elle est dans le menu,
            // sinon la liste de sa section (ex. categories/modifier.php -> Catégories)
            $lien_actif = null;
            foreach ($liens_admin as $chemin => $libelle) {
                if (substr($page_admin, -strlen($chemin)) === $chemin) {
                    $lien_actif = $chemin;
                }
            }
            if ($lien_actif === null) {
                foreach ($liens_admin as $chemin => $libelle) {
                    if (basename($chemin) === 'liste.php' && strpos($page_admin, '/' . dirname($chemin) . '/') !== false) {
                        $lien_actif = $chemin;
                    }
                }
            }
            ?>
            <nav class="nav-admin">
                <span class="nav-admin-titre">Rédaction</span>
                <?php foreach ($liens_admin as $chemin => $libelle) : ?>
                    <a href="<?= url($chemin) ?>" class="<?= $chemin === $lien_actif ? 'active' : '' ?>">
                        <?= $libelle ?>
                        <?php if ($chemin === 'admin/messages/liste.php' && $nb_messages_non_lus > 0) : ?>
                            <span class="nav-admin-compteur"><?= $nb_messages_non_lus ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>

    <div class="ticker">
        <div class="ticker-label">FLASH</div>
        <div class="ticker-text">Bienvenue sur Xibaar Yi — L'actualité du Sénégal en temps réel</div>
    </div>
