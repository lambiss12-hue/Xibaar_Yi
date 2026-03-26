<?php
/*
 * ============================================================
 *  XIBAAR YI — articles/detail.php
 *  Rôle : Affiche le contenu COMPLET d'un article
 *         quand l'utilisateur clique sur son titre
 *  Exemple d'URL : articles/detail.php?id=7
 *  Accessible : par tout le monde, sans connexion
 *  Auteur : Personne 1
 * ============================================================
 */

// --- INCLUSION DE CONFIG.PHP ---
// On remonte d'un niveau avec "../" car ce fichier est dans le sous-dossier articles/
// et config.php est à la racine du projet.
// Sans le "../", PHP chercherait config.php dans le dossier articles/ → erreur.
require_once '../config.php';


// ============================================================
//  ÉTAPE 1 : RÉCUPÉRER ET VALIDER L'ID DE L'ARTICLE
// ============================================================

// L'URL contient le paramètre "id" : articles/detail.php?id=7
// On récupère cette valeur depuis le tableau $_GET.
// isset() vérifie que le paramètre "id" existe bien dans l'URL.
// intval() convertit la valeur en entier : si quelqu'un met "abc" ou du code
// malveillant dans l'URL, intval() retourne 0, ce qui est géré juste en-dessous.
$id_article = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérification de sécurité : un ID valide est forcément un entier positif (> 0).
// Si l'ID est 0 ou négatif (URL invalide ou tentative de manipulation),
// on redirige l'utilisateur vers la page d'accueil.
// header('Location: ...') envoie une instruction au navigateur pour qu'il aille
// à une autre URL. Le "../" remonte d'un niveau pour atteindre accueil.php.
// exit() est OBLIGATOIRE après header() : il arrête l'exécution du reste du script.
// Sans exit(), PHP continuerait à exécuter le code même après la redirection.
if ($id_article <= 0) {
    header('Location: ../accueil.php');
    exit();
}


// ============================================================
//  ÉTAPE 2 : RÉCUPÉRER L'ARTICLE DEPUIS LA BASE DE DONNÉES
// ============================================================

// On prépare la requête SQL pour récupérer les informations complètes de l'article.
//
// Détail des colonnes sélectionnées :
//   articles.id              → l'identifiant unique
//   articles.titre           → le titre complet
//   articles.contenu         → le corps entier de l'article (texte long)
//   articles.description_courte     → le résumé court
//   articles.date_publication → la date de publication
//   categories.nom AS categorie_nom → le nom de la catégorie (alias pour clarté)
//   CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
//     → CONCAT() assemble plusieurs chaînes : prénom + espace + nom
//     → AS auteur_nom lui donne un alias pour y accéder facilement
//
// JOIN categories ON articles.id_categorie = categories.id
//   → relie la table articles à la table categories.
//   → Grâce à cette jointure, on obtient le nom de la catégorie au lieu de son ID.
//
// JOIN utilisateurs u ON articles.id_auteur = u.id
//   → relie la table articles à la table utilisateurs.
//   → "u" est un alias court pour "utilisateurs" (pratique dans la requête).
//
// WHERE articles.id = :id_article
//   → filtre : on ne veut QUE l'article dont l'id correspond au paramètre de l'URL.
//   → ":id_article" est un paramètre préparé (sécurité anti-injection SQL).
//
// LIMIT 1
//   → on s'attend à recevoir au maximum 1 résultat (l'id est unique).
//   → Bonne pratique : limiter explicitement quand on sait qu'on cherche un seul enregistrement.
$sql = "SELECT articles.id,
               articles.titre,
               articles.contenu,
               articles.description_courte,
               articles.date_publication,
               categories.nom AS categorie_nom,
               CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
        FROM articles
        JOIN categories ON articles.id_categorie = categories.id
        JOIN utilisateurs u ON articles.id_auteur = u.id
        WHERE articles.id = :id_article
        LIMIT 1";

// prepare() envoie le "modèle" de requête à MySQL.
// MySQL le compile et l'attend avec les paramètres à remplir.
$stmt = $pdo->prepare($sql);

// bindValue() remplace ":id_article" par la vraie valeur de $id_article.
// PDO::PARAM_INT précise que c'est un entier → MySQL traite ça comme un nombre,
// pas comme une chaîne entre guillemets, ce qui est correct pour un id numérique.
$stmt->bindValue(':id_article', $id_article, PDO::PARAM_INT);

// execute() déclenche l'exécution de la requête avec le paramètre lié.
$stmt->execute();

// fetch() récupère UNE SEULE ligne du résultat (contrairement à fetchAll() qui récupère tout).
// PDO::FETCH_ASSOC retourne un tableau associatif :
//   $article['titre'], $article['contenu'], $article['auteur_nom'], etc.
// Si aucun article n'est trouvé avec cet id, fetch() retourne false.
$article = $stmt->fetch(PDO::FETCH_ASSOC);


// ============================================================
//  ÉTAPE 3 : GÉRER LE CAS OÙ L'ARTICLE N'EXISTE PAS
// ============================================================

// Si $article vaut false, cela signifie qu'aucun article n'a cet id dans la base.
// Cela peut arriver si :
//   - L'id dans l'URL a été inventé (ex: id=99999 alors qu'il n'existe pas)
//   - L'article a été supprimé entre temps
// Dans ce cas, on redirige vers la page d'accueil.
if (!$article) {
    header('Location: ../accueil.php');
    exit();
}


// ============================================================
//  ÉTAPE 4 : RÉCUPÉRER LES ARTICLES RÉCENTS POUR LA SIDEBAR
//  (suggérés à droite de la page pour inciter à lire d'autres articles)
// ============================================================

// On récupère 4 articles récents DIFFÉRENTS de l'article actuellement affiché.
// WHERE articles.id != :id_actuel → le "!=" exclut l'article qu'on est en train de lire.
// ORDER BY date_publication DESC → du plus récent au plus ancien.
// LIMIT 4 → on veut seulement 4 suggestions.
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

?>
<?php
// ============================================================
//  ÉTAPE 5 : DÉBUT DE L'AFFICHAGE HTML
// ============================================================

// On inclut l'en-tête avec "../" car on est dans le sous-dossier articles/.
include '../entete.php';
// menu.php supprimé : navigation intégrée dans entete.php
?>

<!-- ============================================================
     CONTENU PRINCIPAL : DÉTAIL DE L'ARTICLE
     ============================================================ -->

<div style="max-width: 960px; margin: 0 auto; padding: 32px;
            display: grid; grid-template-columns: 1fr 260px; gap: 40px;">


    <!-- ======================================================
         COLONNE GAUCHE : L'ARTICLE COMPLET
         ====================================================== -->
    <div>

        <!-- Fil d'Ariane (breadcrumb) : aide l'utilisateur à savoir où il est
             et à revenir facilement à la page précédente -->
        <div style="font-size: 11px; color: #999; margin-bottom: 20px;">

            <!-- Lien retour vers la page d'accueil -->
            <a href="../accueil.php" style="color: #999; text-decoration: none;">
                Accueil
            </a>

            <!-- Séparateur visuel -->
            &nbsp;›&nbsp;

            <!-- Lien vers la catégorie de cet article.
                 urlencode() encode le nom de la catégorie pour l'URL.
                 Ex : "Éducation" → "Education" (caractères spéciaux en code URL). -->
            <a href="../accueil.php?categorie=<?php echo urlencode($article['categorie_nom']); ?>"
               style="color: #999; text-decoration: none;">
                <?php echo htmlspecialchars($article['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
            </a>

            &nbsp;›&nbsp;

            <!-- Titre tronqué de l'article actuel dans le fil d'Ariane.
                 substr() extrait une portion de chaîne : ici les 50 premiers caractères.
                 strlen() retourne la longueur d'une chaîne.
                 Si le titre dépasse 50 caractères, on ajoute "..." à la fin. -->
            <span>
                <?php
                $titre_court = htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8');
                echo (strlen($titre_court) > 50) ? substr($titre_court, 0, 50) . '...' : $titre_court;
                ?>
            </span>
        </div>

        <!-- Étiquette de catégorie (en rouge, comme sur l'accueil) -->
        <div class="hero-kicker">
            <?php echo htmlspecialchars($article['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <!-- Titre principal de l'article -->
        <!-- On utilise <h1> car c'est LE titre le plus important de cette page.
             Important pour l'accessibilité et le référencement (SEO). -->
        <h1 style="font-family: Georgia, serif; font-size: 28px; font-weight: 700;
                   color: #111; line-height: 1.3; margin: 10px 0 14px;">
            <?php echo htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8'); ?>
        </h1>

        <!-- Description courte (chapeau) : le résumé introductif de l'article -->
        <p style="font-size: 16px; color: #444; font-style: italic;
                  line-height: 1.6; margin-bottom: 16px; border-left: 3px solid #e00;
                  padding-left: 14px;">
            <?php echo htmlspecialchars($article['description_courte'], ENT_QUOTES, 'UTF-8'); ?>
        </p>

        <!-- Métadonnées : auteur et date de publication -->
        <div class="hero-meta" style="margin-bottom: 24px; padding-bottom: 20px;
                                      border-bottom: 1px solid #e8e8e8;">

            <!-- Icône auteur + nom -->
            <span>
                <!-- Petit SVG représentant une silhouette de personne -->
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <?php echo htmlspecialchars($article['auteur_nom'], ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <!-- Icône calendrier + date formatée -->
            <span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <!-- date() formate la date.
                     'j' → jour sans zéro devant (ex: 5)
                     'F' → nom du mois en toutes lettres (ex: mars)
                     Mais attention : date() donne les mois en anglais par défaut.
                     Pour avoir le mois en français, on utilise setlocale() ou un tableau. -->
                <?php
                // Tableau de correspondance numéro de mois → nom en français.
                $mois_fr = [
                    1 => 'janvier', 2 => 'février',   3 => 'mars',
                    4 => 'avril',   5 => 'mai',        6 => 'juin',
                    7 => 'juillet', 8 => 'août',       9 => 'septembre',
                    10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
                ];

                // strtotime() convertit la date MySQL en timestamp Unix.
                $timestamp = strtotime($article['date_publication']);

                // date('n', ...) extrait le numéro du mois (1 à 12) sans zéro devant.
                // date('j', ...) extrait le jour.
                // date('Y', ...) extrait l'année sur 4 chiffres.
                $jour  = date('j', $timestamp);
                $mois  = intval(date('n', $timestamp));
                $annee = date('Y', $timestamp);

                // On affiche la date complète en français : "19 mars 2026"
                echo $jour . ' ' . $mois_fr[$mois] . ' ' . $annee;
                ?>
            </span>
        </div>

        <!-- Contenu complet de l'article -->
        <!-- nl2br() convertit les retours à la ligne (\n) du texte en balises <br> HTML.
             Sans nl2br(), tout le texte s'afficherait sur une seule ligne.
             ATTENTION : on n'utilise PAS htmlspecialchars() sur le contenu si on veut
             pouvoir y mettre du HTML basique. Mais si le contenu est du texte brut
             (sans HTML), il FAUT utiliser htmlspecialchars() pour la sécurité. -->
        <div style="font-size: 15px; color: #333; line-height: 1.8;">
            <?php
            // Ici on suppose que le contenu est du texte brut (sans HTML).
            // On applique donc htmlspecialchars() pour la sécurité XSS,
            // puis nl2br() pour convertir les sauts de ligne en <br>.
            echo nl2br(htmlspecialchars($article['contenu'], ENT_QUOTES, 'UTF-8'));
            ?>
        </div>

        <!-- Bouton retour vers la liste des articles -->
        <div style="margin-top: 36px; padding-top: 20px; border-top: 1px solid #e8e8e8;">
            <a href="../accueil.php"
               style="background: #fff; color: #111; font-size: 12px; font-weight: 600;
                      padding: 10px 20px; border: 1px solid #ccc; border-radius: 2px;
                      text-decoration: none;">
                ← Retour aux articles

                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'editeur' || $_SESSION['user_role'] === 'administrateur')): ?>
    <a href="modifier.php?id=<?= $article['id'] ?>"
       style="margin-left:10px; background:#333; color:#fff; font-size:12px;
              font-weight:600; padding:10px 20px; border-radius:2px; text-decoration:none;
              display:inline-flex; align-items:center; gap:6px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
        Modifier
    </a>
    <a href="supprimer.php?id=<?= $article['id'] ?>"
       onclick="return confirm('Supprimer cet article ?')"
       style="margin-left:10px; background:#cc0000; color:#fff; font-size:12px;
              font-weight:600; padding:10px 20px; border-radius:2px; text-decoration:none;
              display:inline-flex; align-items:center; gap:6px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6l-1 14H6L5 6"/>
            <path d="M10 11v6M14 11v6"/>
        </svg>
        Supprimer
    </a>
<?php endif; ?>
            </a>

            <!-- Lien pour filtrer sur la même catégorie -->
            <a href="../accueil.php?categorie=<?php echo urlencode($article['categorie_nom']); ?>"
               style="margin-left: 10px; background: #111; color: #fff; font-size: 12px;
                      font-weight: 600; padding: 10px 20px; border-radius: 2px;
                      text-decoration: none;">
                Voir tous les articles :
                <?php echo htmlspecialchars($article['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </div>

    </div><!-- Fin colonne gauche -->


    <!-- ======================================================
         COLONNE DROITE : SIDEBAR (articles récents)
         ====================================================== -->
    <div>

        <!-- Section "À lire aussi" -->
        <div class="sidebar-section">
            <div class="sb-title">À lire aussi</div>

            <?php
            // On vérifie qu'il y a bien des articles récents à afficher.
            if (count($articles_recents) > 0) :
                // On numérote les articles pour l'affichage (1, 2, 3, 4).
                // On utilise une variable compteur qu'on incrémente à chaque tour.
                $compteur = 1;

                foreach ($articles_recents as $recent) :
            ?>
                <!-- Chaque article suggéré est cliquable vers sa page de détail -->
                <a href="detail.php?id=<?php echo intval($recent['id']); ?>"
                   style="text-decoration: none; color: inherit;">

                    <div class="sb-item">
                        <!-- Numéro décoratif en gris clair (01, 02, 03...) -->
                        <!-- sprintf() formate une valeur selon un masque.
                             '%02d' → entier sur 2 chiffres avec zéro devant si nécessaire.
                             Exemple : 1 → "01", 2 → "02", 10 → "10" -->
                        <div class="sb-num"><?php echo sprintf('%02d', $compteur); ?></div>

                        <!-- Titre de l'article suggéré -->
                        <div class="sb-item-title">
                            <?php echo htmlspecialchars($recent['titre'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <!-- Catégorie + date de l'article suggéré -->
                        <div class="sb-item-cat">
                            <?php echo htmlspecialchars($recent['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
                            &nbsp;·&nbsp;
                            <?php echo date('d/m/Y', strtotime($recent['date_publication'])); ?>
                        </div>
                    </div>

                </a>

                <?php
                // On incrémente le compteur à chaque article affiché.
                // $compteur++ est équivalent à $compteur = $compteur + 1
                $compteur++;
                endforeach;

            else :
            ?>
                <p style="font-size: 12px; color: #999;">Aucun autre article disponible.</p>
            <?php endif; ?>

        </div><!-- Fin sidebar-section -->

    </div><!-- Fin colonne droite -->

</div><!-- Fin de la grille principale -->


<?php
// ============================================================
//  ÉTAPE 6 : FIN DE LA PAGE
// ============================================================

// On inclut le pied de page avec "../" car on est dans le dossier articles/.
include '../pied.php';
?>