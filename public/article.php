<?php

require_once __DIR__ . '/../includes/auth.php';



$id_article = isset($_GET['id']) ? intval($_GET['id']) : 0;


if ($id_article <= 0) {
    header('Location: ' . url('index.php'));
    exit();
}



$sql = "SELECT articles.id,
               articles.titre,
               articles.contenu,
               articles.description_courte,
               articles.date_publication,
               articles.image,
               articles.vues,
               categories.nom AS categorie_nom,
               CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
        FROM articles
        JOIN categories ON articles.id_categorie = categories.id
        JOIN utilisateurs u ON articles.id_auteur = u.id
        WHERE articles.id = :id_article
        LIMIT 1";


$stmt = $pdo->prepare($sql);


$stmt->bindValue(':id_article', $id_article, PDO::PARAM_INT);


$stmt->execute();


$article = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$article) {
    header('Location: ' . url('index.php'));
    exit();
}

// Compteur de vues : une seule vue par article et par visiteur (session),
// pour qu'actualiser la page ne gonfle pas le compteur
$_SESSION['articles_vus'] = $_SESSION['articles_vus'] ?? [];

if (!in_array($id_article, $_SESSION['articles_vus'], true)) {
    $stmt_vue = $pdo->prepare("UPDATE articles SET vues = vues + 1 WHERE id = ?");
    $stmt_vue->execute([$id_article]);

    $_SESSION['articles_vus'][] = $id_article;
    $article['vues']++;
}


$sql_recents = "SELECT articles.id,
                       articles.titre,
                       articles.date_publication,
                       categories.nom AS categorie_nom
                FROM articles
                JOIN categories ON articles.id_categorie = categories.id
                WHERE articles.id != :id_actuel
                ORDER BY articles.date_publication DESC
                LIMIT 4";

$stmt_recents = $pdo->prepare($sql_recents);
$stmt_recents->bindValue(':id_actuel', $id_article, PDO::PARAM_INT);
$stmt_recents->execute();

// fetchAll() récupère les 4 articles récents dans un tableau.
$articles_recents = $stmt_recents->fetchAll(PDO::FETCH_ASSOC);

// Commentaires validés par la rédaction (les plus anciens en premier, comme une conversation)
$stmt_com = $pdo->prepare("SELECT nom, contenu, date_envoi
                           FROM commentaires
                           WHERE id_article = ? AND statut = 'approuve'
                           ORDER BY date_envoi ASC");
$stmt_com->execute([$id_article]);
$commentaires = $stmt_com->fetchAll(PDO::FETCH_ASSOC);

// Message après l'envoi d'un commentaire (traitement_commentaire.php)
$messages_commentaire = [
    'attente'     => ['alert-success', "Merci ! Votre commentaire sera publié après validation par la rédaction."],
    'erreur'      => ['alert-danger',  "Commentaire non envoyé : vérifiez votre nom, votre email et votre message (2 à 2000 caractères)."],
    'trop_rapide' => ['alert-danger',  "Merci de patienter quelques secondes avant d'envoyer un autre commentaire."],
];
$message_commentaire = $messages_commentaire[$_GET['commentaire'] ?? ''] ?? null;

// Onglet et aperçu du lien partagé (voir includes/meta.php) : ceux de l'article
$titre_page       = $article['titre'];
$meta_description = $article['description_courte'];
$meta_type        = 'article';
if (!empty($article['image'])) {
    $meta_image = 'uploads/' . rawurlencode($article['image']);
}

?>
<?php


include __DIR__ . '/../includes/entete.php';

?>



<div class="article-page">

    <div>

       
        <div style="font-size: 11px; color: #999; margin-bottom: 20px;">

            <!-- Lien retour vers la page d'accueil -->
            <a href="<?= url('index.php') ?>" style="color: #999; text-decoration: none;">
                Accueil
            </a>

            <!-- Séparateur visuel -->
            &nbsp;›&nbsp;

            
            <a href="<?= url('index.php') ?>?categorie=<?php echo urlencode($article['categorie_nom']); ?>"
               style="color: #999; text-decoration: none;">
                <?php echo htmlspecialchars($article['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
            </a>

            &nbsp;›&nbsp;

            <span>
                <?php
                // mb_ : compte les caractères (é = 1), pas les octets ; on coupe AVANT d'échapper
                $titre_court = (mb_strlen($article['titre']) > 50) ? mb_substr($article['titre'], 0, 50) . '...' : $article['titre'];
                echo htmlspecialchars($titre_court, ENT_QUOTES, 'UTF-8');
                ?>
            </span>
        </div>

        <div class="hero-kicker">
            <?php echo htmlspecialchars($article['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <h1 style="font-family: Georgia, serif; font-size: 28px; font-weight: 700;
                   color: #111; line-height: 1.3; margin: 10px 0 14px;">
            <?php echo htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8'); ?>
        </h1>

       
        <p style="font-size: 16px; color: #444; font-style: italic;
                  line-height: 1.6; margin-bottom: 16px; border-left: 3px solid #e00;
                  padding-left: 14px;">
            <?php echo htmlspecialchars($article['description_courte'], ENT_QUOTES, 'UTF-8'); ?>
        </p>

       
        <div class="hero-meta" style="margin-bottom: 24px; padding-bottom: 20px;
                                      border-bottom: 1px solid #e8e8e8;">

            
            <span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <?php echo htmlspecialchars($article['auteur_nom'], ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            
                <?= date_fr($article['date_publication']) ?>
            </span>

            <span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <?= (int) $article['vues'] ?> vue(s)
            </span>
        </div>

        <?php if (!empty($article['image'])) : ?>
            <img src="<?= url('uploads/' . rawurlencode($article['image'])) ?>"
                 alt="<?= htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8') ?>"
                 class="article-image">
        <?php endif; ?>

        <div style="font-size: 15px; color: #333; line-height: 1.8;">
            <?php
            echo nl2br(htmlspecialchars($article['contenu'], ENT_QUOTES, 'UTF-8'));
            ?>
        </div>

        
        <div class="article-actions">
            <a href="<?= url('index.php') ?>"
               style="background: #fff; color: #111; font-size: 12px; font-weight: 600;
                      padding: 10px 20px; border: 1px solid #ccc; border-radius: 2px;
                      text-decoration: none;">
                ← Retour aux articles
            </a>

                <?php if (voit_back_office()): ?>
    <a href="<?= url('admin/articles/modifier.php') ?>?id=<?= $article['id'] ?>"
       style="margin-left:10px; background:#333; color:#fff; font-size:12px;
              font-weight:600; padding:10px 20px; border-radius:2px; text-decoration:none;
              display:inline-flex; align-items:center; gap:6px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
        Modifier
    </a>
    <form method="POST" action="<?= url('admin/articles/supprimer.php') ?>" class="form-inline"
          onsubmit="return confirm('Supprimer cet article ?')">
        <?= csrf_champ() ?>
        <input type="hidden" name="id" value="<?= $article['id'] ?>">
        <button type="submit"
           style="margin-left:10px; background:#cc0000; color:#fff; font-size:12px;
                  font-weight:600; padding:10px 20px; border:none; border-radius:2px; cursor:pointer;
                  font-family:inherit; display:inline-flex; align-items:center; gap:6px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14H6L5 6"/>
                <path d="M10 11v6M14 11v6"/>
            </svg>
            Supprimer
        </button>
    </form>
<?php endif; ?>
            <a href="<?= url('index.php') ?>?categorie=<?php echo urlencode($article['categorie_nom']); ?>"
               style="margin-left: 10px; background: #111; color: #fff; font-size: 12px;
                      font-weight: 600; padding: 10px 20px; border-radius: 2px;
                      text-decoration: none;">
                Voir tous les articles :
                <?php echo htmlspecialchars($article['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </div>

        <!-- ===== Commentaires ===== -->
        <section class="commentaires" id="commentaires">
            <h2 class="commentaires-titre">Commentaires (<?= count($commentaires) ?>)</h2>

            <?php if (empty($commentaires)) : ?>
                <p class="commentaires-vide">Aucun commentaire pour le moment. Soyez le premier à réagir !</p>
            <?php else : ?>
                <?php foreach ($commentaires as $com) : ?>
                    <div class="commentaire">
                        <div class="commentaire-auteur">
                            <?= htmlspecialchars($com['nom'], ENT_QUOTES, 'UTF-8') ?>
                            <span class="commentaire-date">· <?= date('d/m/Y à H:i', strtotime($com['date_envoi'])) ?></span>
                        </div>
                        <div class="commentaire-texte"><?= nl2br(htmlspecialchars($com['contenu'], ENT_QUOTES, 'UTF-8')) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h3 class="commentaires-sous-titre">Laisser un commentaire</h3>

            <?php if ($message_commentaire) : ?>
                <div class="alert <?= $message_commentaire[0] ?>"><?= htmlspecialchars($message_commentaire[1]) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= url('traitement_commentaire.php') ?>" class="commentaire-form">
                <input type="hidden" name="id_article" value="<?= (int) $article['id'] ?>">

                <!-- Champ piège anti-robots : invisible pour les humains, ne pas remplir -->
                <div class="champ-piege" aria-hidden="true">
                    <label for="site_web">Ne pas remplir ce champ</label>
                    <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-grille">
                    <div class="form-group">
                        <label class="form-label" for="com_nom">Nom *</label>
                        <input type="text" id="com_nom" name="nom" class="form-control" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="com_email">Email * <span class="commentaire-note">(non publié)</span></label>
                        <input type="email" id="com_email" name="email" class="form-control" maxlength="150" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="com_contenu">Commentaire *</label>
                    <textarea id="com_contenu" name="contenu" class="form-control" rows="4"
                              minlength="2" maxlength="2000" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary compte-bouton">Publier le commentaire</button>
                <p class="commentaire-note">Les commentaires sont vérifiés par la rédaction avant publication.</p>
            </form>
        </section>

    </div>


   
    <div>

        
        <div class="sidebar-section">
            <div class="sb-title">À lire aussi</div>

            <?php
            
            if (count($articles_recents) > 0) :
               
                $compteur = 1;

                foreach ($articles_recents as $recent) :
            ?>
               
                <a href="<?= url('article.php') ?>?id=<?php echo intval($recent['id']); ?>"
                   style="text-decoration: none; color: inherit;">

                    <div class="sb-item">
                        
                        <div class="sb-num"><?php echo sprintf('%02d', $compteur); ?></div>

                        <div class="sb-item-title">
                            <?php echo htmlspecialchars($recent['titre'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <div class="sb-item-cat">
                            <?php echo htmlspecialchars($recent['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
                            &nbsp;·&nbsp;
                            <?php echo date('d/m/Y', strtotime($recent['date_publication'])); ?>
                        </div>
                    </div>

                </a>

                <?php
                
                $compteur++;
                endforeach;

            else :
            ?>
                <p style="font-size: 12px; color: #999;">Aucun autre article disponible.</p>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php
include __DIR__ . '/../includes/pied.php';
?>