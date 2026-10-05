<?php
// auth.php charge config.php et fournit csrf_champ() (bouton de déconnexion)
require_once __DIR__ . '/auth.php';

$titre_page = isset($titre_page) ? $titre_page : 'Xibaar Yi';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titre_page, ENT_QUOTES, 'UTF-8'); ?> — Xibaar Yi</title>
    <?php include __DIR__ . '/meta.php'; ?>
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
                    <a href="<?= url('admin/compte.php') ?>" class="lien-compte" title="Mon compte">
                        <?php echo htmlspecialchars($_SESSION['user_login'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <!-- Formulaire POST + jeton CSRF (et pas un simple lien) :
                         une autre page ne peut pas déconnecter l'utilisateur à son insu -->
                    <form method="POST" action="<?= url('deconnexion.php') ?>" class="form-inline">
                        <?= csrf_champ() ?>
                        <button type="submit" class="btn-cnx">Se déconnecter</button>
                    </form>
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

        <?php if (voit_back_office()) : ?>
            <?php
            // Compteurs affichés à côté des liens : messages non lus, commentaires à valider
            $requetes_compteurs = [
                'admin/messages/liste.php'     => "SELECT COUNT(*) FROM messages_contact WHERE lu = 0",
                'admin/commentaires/liste.php' => "SELECT COUNT(*) FROM commentaires WHERE statut = 'en_attente'",
            ];
            $compteurs_admin = [];
            foreach ($requetes_compteurs as $chemin => $sql) {
                try {
                    $compteurs_admin[$chemin] = (int) $pdo->query($sql)->fetchColumn();
                } catch (PDOException $e) {
                    // Migration pas encore exécutée : on n'affiche simplement pas ce compteur
                }
            }

            // Lien actif = section admin de la page courante
            $page_admin = $_SERVER['SCRIPT_NAME'] ?? '';
            $liens_admin = [
                'admin/articles/liste.php'     => 'Articles',
                'admin/articles/ajouter.php'   => '+ Nouvel article',
                'admin/categories/liste.php'   => 'Catégories',
                'admin/messages/liste.php'     => 'Messages',
                'admin/commentaires/liste.php' => 'Commentaires',
            ];
            // Le compte démo voit aussi la page Utilisateurs (en lecture seule)
            if (in_array($_SESSION['user_role'], ['administrateur', 'demo'], true)) {
                $liens_admin['admin/utilisateurs/liste.php'] = 'Utilisateurs';
            }
            $liens_admin['admin/compte.php'] = 'Mon compte';

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
                        <?php if (($compteurs_admin[$chemin] ?? 0) > 0) : ?>
                            <span class="nav-admin-compteur"><?= $compteurs_admin[$chemin] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if (est_demo()) : ?>
                <div class="bandeau-demo">
                    <strong>Mode démo</strong> — explorez librement le back-office :
                    les ajouts, modifications et suppressions sont désactivés,
                    et les coordonnées des visiteurs sont masquées.
                </div>
                <?php if (!empty($_SESSION['message_demo'])) : ?>
                    <?php unset($_SESSION['message_demo']); ?>
                    <div class="alert alert-danger bandeau-demo-refus">
                        Action non enregistrée : le compte démo est en lecture seule.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php
    // Bandeau FLASH : les 3 derniers articles publiés (mis à jour à chaque nouvel article)
    $articles_flash = $pdo->query("SELECT id, titre FROM articles
                                   ORDER BY date_publication DESC
                                   LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="ticker">
        <div class="ticker-label">FLASH</div>
        <div class="ticker-text">
            <?php if (empty($articles_flash)) : ?>
                Bienvenue sur Xibaar Yi — L'actualité du Sénégal
            <?php else : ?>
                <?php foreach ($articles_flash as $i => $flash) : ?>
                    <?= $i > 0 ? '<span class="ticker-sep">·</span>' : '' ?>
                    <a href="<?= url('article.php') ?>?id=<?= (int) $flash['id'] ?>"><?= htmlspecialchars($flash['titre'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
