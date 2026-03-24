<?php
/*
 * ============================================================
 *  XIBAAR YI — accueil.php
 *  Rôle : Page d'accueil publique — liste des articles
 *         avec pagination et filtre par catégorie
 *  Auteur : Personne 1
 *  Accessible : par tout le monde, sans connexion
 * ============================================================
 */

// --- INCLUSION DE CONFIG.PHP ---
// "require_once" inclut le fichier config.php UNE seule fois.
// Si le fichier est introuvable, PHP arrête tout et affiche une erreur.
// config.php contient la variable $pdo qui est notre connexion à la base de données.
// Sans cette ligne, on ne peut pas faire de requête SQL.
require_once 'config.php';


// ============================================================
//  ÉTAPE 1 : RÉCUPÉRER LES PARAMÈTRES DE L'URL
// ============================================================

// $_GET est un tableau PHP qui contient les paramètres passés dans l'URL.
// Exemple d'URL : accueil.php?page=2&categorie=Sport
// $_GET['page'] vaudrait "2" et $_GET['categorie'] vaudrait "Sport".

// On récupère le numéro de page demandé.
// isset() vérifie si la clé 'page' existe dans $_GET (pour éviter une erreur si elle est absente).
// intval() convertit la valeur en nombre entier (sécurité : empêche d'injecter du texte à la place d'un chiffre).
// Si 'page' n'existe pas dans l'URL, on utilise 1 par défaut (première page).
$page_courante = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Sécurité supplémentaire : si quelqu'un met page=0 ou page=-5 dans l'URL,
// on force la valeur à 1 pour éviter des comportements inattendus.
if ($page_courante < 1) {
    $page_courante = 1;
}

// On récupère le filtre de catégorie depuis l'URL (ex: ?categorie=Sport).
// trim() supprime les espaces inutiles en début et fin de la valeur.
// Si 'categorie' n'existe pas dans l'URL, on met une chaîne vide '' (= pas de filtre).
$filtre_categorie = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';


// ============================================================
//  ÉTAPE 2 : PARAMÈTRES DE PAGINATION
// ============================================================

// Nombre d'articles à afficher par page.
// On définit une constante ici : si on veut changer ce nombre plus tard,
// on n'a qu'un seul endroit à modifier.
$articles_par_page = 5;

// On calcule à partir de quel article on doit commencer à lire dans la base de données.
// Exemple : page 1 → on commence à l'article 0 (les 5 premiers : 0,1,2,3,4)
//           page 2 → on commence à l'article 5 (les 5 suivants : 5,6,7,8,9)
// Formule : OFFSET = (numéro_de_page - 1) × articles_par_page
$offset = ($page_courante - 1) * $articles_par_page;


// ============================================================
//  ÉTAPE 3 : COMPTER LE NOMBRE TOTAL D'ARTICLES
//  (pour calculer le nombre de pages et activer/désactiver
//   les boutons Précédent / Suivant)
// ============================================================

// On construit la requête SQL pour compter les articles.
// COUNT(*) compte le nombre de lignes retournées par la requête.
// On prépare deux cas : avec ou sans filtre de catégorie.
if ($filtre_categorie !== '') {
    // CAS 1 : l'utilisateur a choisi une catégorie dans le menu.
    // On joint la table 'categories' à la table 'articles' via la clé étrangère
    // articles.id_categorie = categories.id
    // Puis on filtre avec WHERE categories.nom = :nom_categorie
    // Le ":nom_categorie" est un paramètre préparé (protection contre l'injection SQL).
    $sql_count = "SELECT COUNT(*) 
                  FROM articles 
                  JOIN categories ON articles.id_categorie = categories.id 
                  WHERE categories.nom = :nom_categorie";

    // $pdo->prepare() prépare la requête SQL sans l'exécuter.
    // Cela crée un "modèle" de requête sécurisé côté serveur MySQL.
    $stmt_count = $pdo->prepare($sql_count);

    // bindValue() remplace le paramètre ":nom_categorie" par la vraie valeur
    // de manière SÉCURISÉE. PDO::PARAM_STR indique que c'est une chaîne de caractères.
    // C'est ça qui empêche les injections SQL : la valeur est traitée comme donnée,
    // jamais comme du code SQL.
    $stmt_count->bindValue(':nom_categorie', $filtre_categorie, PDO::PARAM_STR);

} else {
    // CAS 2 : pas de filtre, on compte TOUS les articles.
    $sql_count = "SELECT COUNT(*) FROM articles";
    $stmt_count = $pdo->prepare($sql_count);
}

// execute() envoie la requête préparée à MySQL pour qu'il l'exécute.
$stmt_count->execute();

// fetchColumn() récupère la valeur de la première colonne de la première ligne.
// Ici, notre requête retourne une seule valeur : le nombre total d'articles.
// intval() s'assure que c'est bien un entier.
$total_articles = intval($stmt_count->fetchColumn());

// On calcule le nombre total de pages nécessaires.
// ceil() arrondit à l'entier SUPÉRIEUR.
// Exemple : 11 articles ÷ 5 par page = 2.2 → ceil(2.2) = 3 pages
$total_pages = ceil($total_articles / $articles_par_page);

// Sécurité : si la page demandée dans l'URL dépasse le total de pages réel,
// on revient à la dernière page disponible.
// Exemple : il y a 3 pages mais l'URL contient page=99 → on affiche la page 3.
if ($total_pages > 0 && $page_courante > $total_pages) {
    $page_courante = $total_pages;
    $offset = ($page_courante - 1) * $articles_par_page;
}


// ============================================================
//  ÉTAPE 4 : RÉCUPÉRER LES ARTICLES À AFFICHER
// ============================================================

if ($filtre_categorie !== '') {
    // CAS 1 : avec filtre de catégorie.
    // On sélectionne plusieurs colonnes utiles pour l'affichage :
    //   - articles.id         → l'identifiant unique de l'article (pour le lien vers detail.php)
    //   - articles.titre      → le titre de l'article
    //   - articles.description_courte → le résumé court affiché sur la page d'accueil
    //   - articles.date_publication → la date de parution
    //   - categories.nom AS categorie_nom → le nom de la catégorie (on lui donne un alias)
    //   - CONCAT(u.prenom, ' ', u.nom) → on concatène prénom + nom pour afficher l'auteur
    //     AS auteur_nom → alias pour faciliter l'affichage côté HTML
    //
    // JOIN categories ON articles.id_categorie = categories.id
    //   → lie la table articles à la table categories pour récupérer le nom de la catégorie
    //
    // JOIN utilisateurs u ON articles.id_auteur = u.id
    //   → lie la table articles à la table utilisateurs pour récupérer le nom de l'auteur
    //   → "u" est un alias court pour "utilisateurs" (évite de répéter le nom long)
    //
    // WHERE categories.nom = :nom_categorie → filtre sur la catégorie choisie
    //
    // ORDER BY articles.date_publication DESC
    //   → trie du plus récent au plus ancien (DESC = ordre décroissant)
    //
    // LIMIT :limite → nombre maximum d'articles à retourner (= articles_par_page)
    // OFFSET :offset → à partir de quel enregistrement commencer (= décalage de pagination)
    $sql = "SELECT articles.id,
                   articles.titre,
                   articles.description_courte,
                   articles.date_publication,
                   categories.nom AS categorie_nom,
                   CONCAT(u.prenom, ' ', u.nom) AS auteur_nom
            FROM articles
            JOIN categories ON articles.id_categorie = categories.id
            JOIN utilisateurs u ON articles.id_auteur = u.id
            WHERE categories.nom = :nom_categorie
            ORDER BY articles.date_publication DESC
            LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    // On lie les trois paramètres de cette requête :
    $stmt->bindValue(':nom_categorie', $filtre_categorie, PDO::PARAM_STR);

    // PDO::PARAM_INT indique que ce sont des nombres entiers.
    // Important pour LIMIT et OFFSET : PDO doit savoir que ce sont des entiers
    // sinon il les met entre guillemets dans le SQL, ce qui provoque une erreur MySQL.
    $stmt->bindValue(':limite', $articles_par_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

} else {
    // CAS 2 : sans filtre, on récupère tous les articles.
    $sql = "SELECT articles.id,
                   articles.titre,
                   articles.description_courte,
                   articles.date_publication,
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

// On exécute la requête finale.
$stmt->execute();

// fetchAll() récupère TOUTES les lignes du résultat en un seul tableau PHP.
// PDO::FETCH_ASSOC indique qu'on veut un tableau associatif :
// chaque ligne est un tableau avec les noms des colonnes comme clés.
// Exemple : $articles[0]['titre'] → titre du premier article
//           $articles[0]['auteur_nom'] → "Fatou Sow"
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ============================================================
//  ÉTAPE 5 : RÉCUPÉRER LA LISTE DES CATÉGORIES
//  (pour les boutons de filtre dans la barre latérale)
// ============================================================

// Requête simple : on veut juste la liste de toutes les catégories disponibles.
// ORDER BY nom ASC → tri alphabétique (ASC = ordre croissant, de A à Z)
$sql_cats = "SELECT id, nom FROM categories ORDER BY nom ASC";

// query() est un raccourci pour les requêtes simples SANS paramètres variables.
// On peut l'utiliser ici car il n'y a aucune donnée venant de l'utilisateur.
// Pour toute requête avec une variable de l'utilisateur, on utilise TOUJOURS prepare().
$stmt_cats = $pdo->query($sql_cats);

// On récupère toutes les catégories dans un tableau PHP.
$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

?>
<?php
// ============================================================
//  ÉTAPE 6 : AFFICHAGE HTML — DÉBUT DE LA PAGE
// ============================================================

// include() inclut le fichier entete.php à cet endroit précis.
// entete.php contient tout le début du HTML : <!DOCTYPE html>, <head>,
// la balise ouvrante <body>, la barre de navigation du haut, etc.
// Tous les fichiers PHP du projet partagent le même entete.php
// → cohérence visuelle et pas de code dupliqué.
include 'entete.php';

// include() inclut le menu de navigation (liens Accueil, Tech, Sport, etc.)
// menu.php est partagé par toutes les pages.
// La Personne 3 s'occupe de rendre ce menu dynamique (options différentes selon le rôle).
// menu.php supprimé : navigation intégrée dans entete.php
?>

<!-- ============================================================
     CONTENU PRINCIPAL DE LA PAGE D'ACCUEIL
     ============================================================ -->

<!-- Barre de filtre par catégorie -->
<!-- <div class="sidebar-section" style="padding: 16px 32px; border-bottom: 1px solid #e8e8e8;">
    <div class="sb-title">Parcourir par catégorie</div>
    <div style="padding: 6px 0;">

        // Bouton "Tout" : réinitialise le filtre en allant vers accueil.php sans paramètre 
        // La classe CSS "active" est ajoutée dynamiquement si aucun filtre n'est actif 
        <a href="accueil.php"
           class="cat-pill <?php echo ($filtre_categorie === '') ? 'active' : ''; ?>">
            Tout
        </a>

        <?php
        // On boucle sur chaque catégorie récupérée depuis la base de données.
        // $cat est un tableau associatif à chaque itération :
        // $cat['id'] → l'identifiant, $cat['nom'] → le nom (ex: "Sport")
        foreach ($categories as $cat) :
        ?>
             Lien vers accueil.php?categorie=NomDeLaCategorie 
            htmlspecialchars() convertit les caractères spéciaux en entités HTML.
                 Exemple : "L'éducation" devient "L&#039;éducation"
                 Cela empêche les attaques XSS (Cross-Site Scripting) :
                 si un nom de catégorie contenait du JavaScript malveillant,
                 htmlspecialchars() le rend inoffensif en l'affichant comme du texte.
                 ENT_QUOTES convertit aussi bien les guillemets simples que doubles.
                 'UTF-8' précise l'encodage pour bien gérer les accents. 
            <a href="accueil.php?categorie=<?php echo urlencode($cat['nom']); ?>"
               class="cat-pill <?php echo ($filtre_categorie === $cat['nom']) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8'); ?>
            </a>

        <?php endforeach; // Fin de la boucle foreach ?>
    </div>
</div> -->

<!-- Zone principale : liste des articles -->
<div class="main" style="padding: 24px 32px;">

    <!-- Titre de la section avec indication du filtre actif si applicable -->
    <div style="margin-bottom: 20px;">
        <?php if ($filtre_categorie !== '') : ?>
            <!-- On affiche le nom de la catégorie filtrée dans le titre -->
            <h2 style="font-family: Georgia, serif; font-size: 18px; color: #111;">
                Catégorie :
                <!-- htmlspecialchars() ici aussi, car $filtre_categorie vient de l'URL (= utilisateur) -->
                <?php echo htmlspecialchars($filtre_categorie, ENT_QUOTES, 'UTF-8'); ?>
            </h2>
        <?php else : ?>
            <h2 style="font-family: Georgia, serif; font-size: 18px; color: #111;">
                Derniers articles
            </h2>
        <?php endif; ?>

        <!-- Affiche le nombre total d'articles trouvés -->
        <p style="font-size: 12px; color: #999; margin-top: 4px;">
            <?php echo $total_articles; ?> article(s) au total
        </p>
    </div>

    <?php
    // ============================================================
    //  ÉTAPE 7 : AFFICHER LES ARTICLES OU UN MESSAGE SI VIDE
    // ============================================================

    // count() retourne le nombre d'éléments dans le tableau $articles.
    // Si le tableau est vide (aucun article trouvé), on affiche un message.
    if (count($articles) === 0) :
    ?>
        <p style="color: #888; font-size: 14px;">Aucun article trouvé.</p>

    <?php else : ?>

        <?php
        // On boucle sur chaque article du tableau $articles.
        // À chaque itération, $article contient les données d'un article :
        // $article['id'], $article['titre'], $article['description_courte'],
        // $article['date_publication'], $article['categorie_nom'], $article['auteur_nom']
        foreach ($articles as $article) :
        ?>

            <!-- Carte article -->
            <!-- On entoure l'article d'un lien <a> vers sa page de détail.
                 L'URL pointe vers articles/detail.php avec l'id de l'article en paramètre.
                 Exemple : articles/detail.php?id=7 -->
            <a href="articles/detail.php?id=<?php echo intval($article['id']); ?>"
               style="text-decoration: none; color: inherit; display: block;">

                <div class="art-sm" style="padding: 18px 0; cursor: pointer;">

                    <!-- Image de l'article (placeholder SVG pour l'instant) -->
                    <div class="art-sm-img">
                        <!-- Icône SVG générique en attendant une vraie image -->
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                             stroke="#444" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>

                    <!-- Texte de l'article -->
                    <div class="art-sm-body">

                        <!-- Nom de la catégorie en rouge -->
                        <div class="art-sm-cat">
                            <!-- htmlspecialchars() protège contre le XSS à chaque affichage
                                 de données qui viennent de la base de données. -->
                            <?php echo htmlspecialchars($article['categorie_nom'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <!-- Titre de l'article -->
                        <div class="art-sm-title">
                            <?php echo htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <!-- Description courte de l'article -->
                        <div style="font-size: 12px; color: #666; margin: 4px 0 6px; line-height: 1.5;">
                            <?php echo htmlspecialchars($article['description_courte'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                        <!-- Métadonnées : auteur et date -->
                        <div class="art-sm-meta">

                            <!-- Nom de l'auteur -->
                            <?php echo htmlspecialchars($article['auteur_nom'], ENT_QUOTES, 'UTF-8'); ?>

                            &nbsp;·&nbsp;

                            <!-- Date de publication : on la formate pour l'affichage.
                                 date() formate une date PHP selon un format donné.
                                 strtotime() convertit la date MySQL (ex: "2026-03-19 14:30:00")
                                 en un timestamp Unix (nombre de secondes depuis le 1er janvier 1970)
                                 que date() peut ensuite formater.
                                 'd/m/Y' → affiche "19/03/2026" -->
                            <?php echo date('d/m/Y', strtotime($article['date_publication'])); ?>
                        </div>
                    </div>
                </div>

            </a><!-- Fin du lien cliquable -->

        <?php endforeach; // Fin de la boucle sur les articles ?>

    <?php endif; // Fin du if/else sur le nombre d'articles ?>

</div><!-- Fin de .main -->


<!-- ============================================================
     ÉTAPE 8 : BOUTONS DE PAGINATION (Précédent / Suivant)
     ============================================================ -->
<div style="display: flex; justify-content: center; gap: 16px;
            padding: 20px 32px 32px; border-top: 1px solid #e8e8e8;">

    <?php
    // ---- BOUTON PRÉCÉDENT ----
    // On affiche le bouton "Précédent" seulement si on n'est PAS sur la première page.
    // Si on est à la page 1, il n'y a pas de page précédente → on n'affiche pas le bouton.
    if ($page_courante > 1) :

        // On calcule le numéro de la page précédente.
        $page_precedente = $page_courante - 1;

        // On construit l'URL du bouton "Précédent".
        // Si un filtre de catégorie est actif, on le garde dans l'URL
        // pour ne pas perdre le filtre en changeant de page.
        if ($filtre_categorie !== '') {
            // urlencode() encode la catégorie pour qu'elle soit valide dans une URL.
            // Exemple : "Éducation" devient "%C3%89ducation" dans l'URL.
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

    <?php endif; // Fin du if page_courante > 1 ?>


    <!-- Indicateur de page courante -->
    <!-- On affiche toujours "Page X / Y" pour que l'utilisateur sache où il en est. -->
    <span style="font-size: 13px; color: #888; padding: 10px 0; align-self: center;">
        Page <?php echo $page_courante; ?> / <?php echo max(1, $total_pages); ?>
        <!-- max(1, $total_pages) évite d'afficher "Page 1 / 0" quand il n'y a aucun article -->
    </span>


    <?php
    // ---- BOUTON SUIVANT ----
    // On affiche le bouton "Suivant" seulement si on n'est PAS sur la dernière page.
    if ($page_courante < $total_pages) :

        $page_suivante = $page_courante + 1;

        // Même logique que pour "Précédent" : on conserve le filtre de catégorie dans l'URL.
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

    <?php endif; // Fin du if page_courante < total_pages ?>

</div><!-- Fin de la zone pagination -->


<?php
// ============================================================
//  ÉTAPE 9 : FIN DE LA PAGE
// ============================================================

// On inclut le pied de page partagé par toutes les pages.
// pied.php contient la fermeture du HTML : le footer, </body>, </html>.
include 'pied.php';
?>