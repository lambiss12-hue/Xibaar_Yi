<?php
require_once 'config.php';

$page_courante = isset($_GET['page']) ? intval($_GET['page']) : 1;

if ($page_courante < 1) {
    $page_courante = 1;
}

$filtre_categorie = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';

$articles_par_page = 5;

$offset = ($page_courante - 1) * $articles_par_page;

if ($filtre_categorie !== '') {
    
    $sql_count = "SELECT COUNT(*) 
                  FROM articles 
                  JOIN categories ON articles.id_categorie = categories.id 
                  WHERE categories.nom = :nom_categorie";

    $stmt_count = $pdo->prepare($sql_count);

    $stmt_count->bindValue(':nom_categorie', $filtre_categorie, PDO::PARAM_STR);

} else {
    $sql_count = "SELECT COUNT(*) FROM articles";
    $stmt_count = $pdo->prepare($sql_count);
}

$stmt_count->execute();

$total_articles = intval($stmt_count->fetchColumn());

$total_pages = ceil($total_articles / $articles_par_page);

if ($total_pages > 0 && $page_courante > $total_pages) {
    $page_courante = $total_pages;
    $offset = ($page_courante - 1) * $articles_par_page;
}

if ($filtre_categorie !== '') {
  
    $sql = "SELECT articles.id,
                   articles.titre,
                   articles.description_courte,
                   articles.date_publication,
                   articles.image,
                   categories.nom AS categorie_nom,
                   CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
            FROM articles
            JOIN categories ON articles.id_categorie = categories.id
            JOIN utilisateurs u ON articles.id_auteur = u.id
            WHERE categories.nom = :nom_categorie
            ORDER BY articles.date_publication DESC
            LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':nom_categorie', $filtre_categorie, PDO::PARAM_STR);
    $stmt->bindValue(':limite', $articles_par_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

} else {
    $sql = "SELECT articles.id,
                   articles.titre,
                   articles.description_courte,
                   articles.date_publication,
                   articles.image,
                   categories.nom AS categorie_nom,
                   CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
            FROM articles
            JOIN categories ON articles.id_categorie = categories.id
            JOIN utilisateurs u ON articles.id_auteur = u.id
            ORDER BY articles.date_publication DESC
            LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $articles_par_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}


$stmt->execute();

$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_cats = "SELECT id, nom FROM categories ORDER BY nom ASC";

$stmt_cats = $pdo->query($sql_cats);

$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

$ids_affiches = array_column($articles, 'id');

if (!empty($ids_affiches)) {
  
    $ids_str = implode(',', array_map('intval', $ids_affiches));

    $sql_sidebar = "SELECT articles.id, articles.titre,
                           categories.nom AS categorie_nom
                    FROM articles
                    JOIN categories ON articles.id_categorie = categories.id
                    WHERE articles.id NOT IN ($ids_str)
                    ORDER BY RAND()
                    LIMIT 3";
} else {
    
    $sql_sidebar = "SELECT articles.id, articles.titre,
                           categories.nom AS categorie_nom
                    FROM articles
                    JOIN categories ON articles.id_categorie = categories.id
                    ORDER BY RAND()
                    LIMIT 3";
}

$stmt_sidebar = $pdo->query($sql_sidebar);
$articles_sidebar = $stmt_sidebar->fetchAll(PDO::FETCH_ASSOC);


?>
<?php
include 'entete.php';
?>

<main class="main">
    
    <div class="main-left">
        
        <div style="margin-bottom: 24px;">
            <h2 style="font-family: Georgia, serif; font-size: 22px; color: #111;">
                <?php echo ($filtre_categorie !== '') ? 'Catégorie : ' . htmlspecialchars($filtre_categorie) : 'Derniers articles'; ?>
            </h2>
            <p style="font-size: 11px; color: #999; margin-top: 5px;">
                <?php echo $total_articles; ?> article(s) au total
            </p>
            <hr style="border: 0; border-top: 1px solid #eee; margin-top: 10px;">
        </div>

        <?php if (count($articles) === 0) : ?>
            <p style="color: #888; font-size: 14px;">Aucun article trouvé.</p>
        <?php else : ?>
            <?php foreach ($articles as $index => $article) : ?>
                
                <a href="articles/detail.php?id=<?php echo intval($article['id']); ?>" class="hero">
                    <div class="hero-img">
                        <?php if (!empty($article['image'])) : ?>
                            <img src="/Projet back-end/Xibaar_Yi/uploads/<?php echo htmlspecialchars($article['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8'); ?>"
                                 style="width:100%; height:100%; object-fit:cover; display:block;">
                        <?php else : ?>
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                 stroke="#444" stroke-width="1">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        <?php endif; ?>
                        <div class="hero-img-overlay">
                            <span class="hero-img-cat"><?php echo htmlspecialchars($article['categorie_nom']); ?></span>
                        </div>
                    </div>
                    <h2 class="hero-title"><?php echo htmlspecialchars($article['titre']); ?></h2>
                    <p class="hero-desc"><?php echo htmlspecialchars($article['description_courte']); ?></p>
                    <div class="hero-meta">Par <b><?php echo htmlspecialchars($article['auteur_nom']); ?></b></div>
                </a>

            <?php endforeach; ?>
        <?php endif; ?>

    </div> 
    <div class="main-mid">
        <div class="mid-section-title">En bref</div>

        <?php
        $articles_bref = array_slice($articles, 0, 3);

        if (empty($articles_bref)) : ?>
            <p style="font-size: 11px; color: #999; padding-top: 10px;">
                Aucun article disponible.
            </p>
        <?php else : ?>
            <?php foreach ($articles_bref as $bref) : ?>
                <div class="art-list-item">

                    <div class="ali-cat">
                        <?php echo htmlspecialchars($bref['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>

                    <a href="articles/detail.php?id=<?php echo intval($bref['id']); ?>"
                       style="text-decoration:none;">
                        <div class="ali-title">
                            <?php echo htmlspecialchars($bref['titre'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </a>

                    <div class="ali-meta">
                        <?php echo htmlspecialchars($bref['auteur_nom'], ENT_QUOTES, 'UTF-8'); ?>
                        &nbsp;·&nbsp;
                        <?php echo date('d/m/Y', strtotime($bref['date_publication'])); ?>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

<div class="main-sidebar">
    <div class="sidebar-section">
        <div class="sb-title">À ne pas manquer</div>

        <?php if (empty($articles_sidebar)) : ?>
            <p style="font-size: 11px; color: #999; margin-top: 8px;">
                Aucun article disponible.
            </p>
        <?php else : ?>
            <?php
    
            $num = 1;
            foreach ($articles_sidebar as $sb) :
            ?>
                <a href="articles/detail.php?id=<?php echo intval($sb['id']); ?>"
                   style="text-decoration:none; color:inherit;">
                    <div class="sb-item">

                        <div class="sb-num"><?php echo sprintf('%02d', $num); ?></div>

                        <div class="sb-item-title">
                            <?php echo htmlspecialchars($sb['titre'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <div class="sb-item-cat">
                            <?php echo htmlspecialchars($sb['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                    </div>
                </a>
            <?php $num++; endforeach; ?>
        <?php endif; ?>

    </div>

    <div class="sidebar-section" style="background: #f9f9f9; padding: 20px; border-radius: 4px; margin-top: 30px; border: 1px solid #eee;">
        <div class="sb-title" style="border-bottom: 1px solid #cc0000; color: #cc0000;">Newsletter</div>
        <p style="font-size: 11px; color: #666; margin-bottom: 15px; line-height: 1.4;">
            Recevez l'essentiel de l'actualité sénégalaise directement dans votre boîte mail.
        </p>
        
        <form action="traitement_newsletter.php" method="POST">
            <input type="email" name="email" placeholder="Votre email..." required 
                   style="width: 100%; padding: 10px; font-size: 12px; border: 1px solid #ddd; margin-bottom: 10px; display: block;">
            
            <button type="submit" 
                    style="width: 100%; background: #111; color: #fff; border: none; padding: 10px; font-size: 11px; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;">
                S'ABONNER
            </button>
        </form>
    </div>

</div>


    </div>

</main>

<div style="display: flex; justify-content: center; gap: 16px;
            padding: 20px 32px 32px; border-top: 1px solid #e8e8e8;">

    <?php
    if ($page_courante > 1) :

        $page_precedente = $page_courante - 1;

        if ($filtre_categorie !== '') {
            $url_precedente = "accueil.php?page={$page_precedente}&categorie=" . urlencode($filtre_categorie);
        } else {
            $url_precedente = "accueil.php?page={$page_precedente}";
        }
    ?>
        <a href="<?php echo $url_precedente; ?>"
           style="background: #fff; color: #111; font-size: 13px; font-weight: 600;
                  padding: 10px 24px; border: 1px solid #ccc; border-radius: 2px;
                  text-decoration: none; letter-spacing: .3px;">
            ← Précédent
        </a>

    <?php endif; ?>

    <span style="font-size: 13px; color: #888; padding: 10px 0; align-self: center;">
        Page <?php echo $page_courante; ?> / <?php echo max(1, $total_pages); ?>
    </span>


    <?php
    if ($page_courante < $total_pages) :

        $page_suivante = $page_courante + 1;

        if ($filtre_categorie !== '') {
            $url_suivante = "accueil.php?page={$page_suivante}&categorie=" . urlencode($filtre_categorie);
        } else {
            $url_suivante = "accueil.php?page={$page_suivante}";
        }
    ?>
        <a href="<?php echo $url_suivante; ?>"
           style="background: #111; color: #fff; font-size: 13px; font-weight: 600;
                  padding: 10px 24px; border-radius: 2px;
                  text-decoration: none; letter-spacing: .3px;">
            Suivant →
        </a>

    <?php endif; ?>

</div>

<?php if (isset($_GET['newsletter'])) : ?>
    <?php if ($_GET['newsletter'] === 'ok') : ?>
        <p style="color:green; font-size:11px; margin-bottom:8px;">
            ✓ Inscription réussie !
        </p>
    <?php else : ?>
        <p style="color:red; font-size:11px; margin-bottom:8px;">
            ✗ Email invalide.
        </p>
    <?php endif; ?>
<?php endif; ?>

<?php
include 'pied.php';
?>