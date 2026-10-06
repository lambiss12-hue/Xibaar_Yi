<?php
// ------------------------------------------------------------
// Import des titres d'actualité depuis les flux RSS de médias sénégalais.
//
// On garde seulement le titre, une courte accroche, la date et le lien
// vers l'article original : le texte complet reste chez le média.
//
// Lancé automatiquement au plus toutes les 3 heures (voir pied.php),
// ou à la demande depuis le back-office (admin/articles/importer.php).
// ------------------------------------------------------------

// Flux lus à chaque import. "categorie" force la rubrique (flux thématique) ;
// sinon la rubrique est devinée d'après les catégories du flux et le titre.
const SOURCES_RSS = [
    ['nom' => 'APS',          'url' => 'https://aps.sn/category/politique/feed/', 'categorie' => 'Politique'],
    ['nom' => 'APS',          'url' => 'https://aps.sn/category/sport/feed/',     'categorie' => 'Sport'],
    ['nom' => 'APS',          'url' => 'https://aps.sn/category/culture/feed/',   'categorie' => 'Culture'],
    ['nom' => 'APS',          'url' => 'https://aps.sn/category/education/feed/', 'categorie' => 'Education'],
    ['nom' => 'APS',          'url' => 'https://aps.sn/feed/'],
    ['nom' => 'Socialnetlink','url' => 'https://www.socialnetlink.org/feed/',     'categorie' => 'Technologie'],
    ['nom' => 'Digital Business Africa', 'url' => 'https://www.digitalbusiness.africa/feed/', 'categorie' => 'Technologie'],
    ['nom' => 'wiwsport',     'url' => 'https://www.wiwsport.com/feed',           'categorie' => 'Sport'],
    ['nom' => 'Le Soleil',    'url' => 'https://lesoleil.sn/feed/'],
    ['nom' => 'Dakaractu',    'url' => 'https://www.dakaractu.com/xml/syndication.rss'],
    ['nom' => 'PressAfrik',   'url' => 'https://www.pressafrik.com/xml/syndication.rss'],
];

// Mots-clés (sans accents, en minuscules) qui rangent un titre dans une rubrique.
// L'ordre compte : la première rubrique trouvée l'emporte.
const MOTS_CLES_RUBRIQUES = [
    // "lutte" seul est exclu : "programme de lutte contre les inondations" n'est pas du sport
    'Sport'       => ['sport', 'football', 'basket', 'basketball', 'lutteur', 'lutteurs', 'lutte senegalaise',
                      'arene nationale', 'athletisme', 'handball',
                      'judo', 'tennis', 'cyclisme', 'lions', 'lionnes', 'can', 'mondial', 'coupe du monde',
                      'ligue 1', 'joj', 'olympiques', 'match', 'navetanes'],
    'Culture'     => ['culture', 'musique', 'cinema', 'festival', 'livre', 'litterature', 'ecrivain', 'theatre',
                      'artiste', 'artistes', 'biennale', 'fildak', 'exposition', 'patrimoine', 'mbalax', 'film',
                      'concert', 'roman', 'poete'],
    'Education'   => ['education', 'ecole', 'ecoles', 'enseignant', 'enseignants', 'eleves', 'etudiants',
                      'universite', 'universites', 'ucad', 'baccalaureat', 'bac', 'examen', 'examens',
                      'scolaire', 'bfem', 'cfee', 'formation professionnelle', 'rentree des classes'],
    'Technologie' => ['technologie', 'technologies', 'numerique', 'digital', 'internet', 'intelligence artificielle',
                      'telecoms', 'telecommunications', 'startup', 'startups', 'fibre optique', 'cybersecurite',
                      'informatique', 'artp', 'sonatel', 'innovation'],
    'Politique'   => ['politique', 'gouvernement', 'assemblee nationale', 'depute', 'deputes', 'ministre',
                      'premier ministre', 'president de la republique', 'election', 'elections', 'parti',
                      'opposition', 'conseil des ministres', 'conseil constitutionnel', 'diplomatie', 'pastef'],
];

const TITRES_PAR_RUBRIQUE   = 30; // les plus anciens titres importés sont supprimés au-delà
const AGE_MAX_JOURS         = 15; // titres plus vieux : ignorés
const INTERVALLE_IMPORT_MIN = 180; // import automatique au plus toutes les 3 heures

// "Éducation" -> "education" : pour comparer sans se soucier des accents ni des majuscules
function sans_accents($texte)
{
    $texte = mb_strtolower($texte, 'UTF-8');
    return strtr($texte, [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e',
        'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ù' => 'u',
        'û' => 'u', 'ü' => 'u', 'ú' => 'u', 'ÿ' => 'y', 'œ' => 'oe', 'æ' => 'ae', '’' => "'",
    ]);
}

// Rubrique d'un titre : d'après le titre lui-même, sinon d'après les catégories données par le média
// (moins fiables : Dakaractu range un salon du livre dans "People & Sports")
function deviner_rubrique($titre, array $categories_flux)
{
    foreach ([$titre, implode(' | ', $categories_flux)] as $texte) {
        $texte = sans_accents($texte);
        foreach (MOTS_CLES_RUBRIQUES as $rubrique => $mots) {
            foreach ($mots as $mot) {
                if (preg_match('/\b' . preg_quote($mot, '/') . '\b/u', $texte)) {
                    return $rubrique;
                }
            }
        }
    }
    return null;
}

// Texte brut à partir du HTML d'un flux : sans balises, sans entités (&#8217;...), espaces nettoyés
function texte_brut($html)
{
    $texte = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $texte));
}

// Courte accroche (environ 2 lignes) : on ne reprend pas l'article du média
function accroche($description)
{
    $texte = texte_brut($description);

    // Mentions ajoutées automatiquement par certains sites en fin de flux
    $texte = preg_replace('/(The post .*|Cet article a été initialement publié.*)$/u', '', $texte);
    $texte = preg_replace('/^\(\s*\w+\s*\)\s*-\s*/u', '', $texte);   // "(wiwsport) - "
    $texte = preg_replace('/^\[[^\]]+\]\s*[–-]?\s*/u', '', $texte);   // "[DIGITAL Business Africa] – "
    $texte = preg_replace('/\s*(www\.)?[a-z0-9-]+\.(com|sn|org|net|africa)$/u', '', $texte); // "... www.pressafrik.com"
    // Texte déjà coupé par le média ("[…]", "...") : on remettra un seul "…" propre
    $deja_coupe = (bool) preg_match('/(\[(…|\.\.\.)\]|…|\.\.\.)$/u', $texte);
    $texte      = trim(preg_replace('/\s*(\[(…|\.\.\.)\]|…|\.\.\.)$/u', '', $texte));

    if (mb_strlen($texte) <= 220) {
        return $texte . ($deja_coupe && $texte !== '' ? '…' : '');
    }
    $coupe = mb_substr($texte, 0, 220);
    return mb_substr($coupe, 0, (int) mb_strrpos($coupe, ' ')) . '…';
}

// Options communes à chaque téléchargement de flux
const OPTIONS_CURL = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 3,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (XibaarYi)',
];

// Télécharge les flux. Si possible tous en même temps (curl_multi) : l'import ne dure pas plus
// longtemps que le flux le plus lent. Sinon un par un : InfinityFree désactive curl_multi_exec().
function telecharger_flux(array $urls)
{
    $fonctions_multi = ['curl_multi_init', 'curl_multi_add_handle', 'curl_multi_exec', 'curl_multi_select',
                        'curl_multi_getcontent', 'curl_multi_remove_handle', 'curl_multi_close'];

    if (count(array_filter($fonctions_multi, 'function_exists')) === count($fonctions_multi)) {
        return telecharger_en_parallele($urls);
    }
    return telecharger_un_par_un($urls);
}

function telecharger_en_parallele(array $urls)
{
    $multi    = curl_multi_init();
    $requetes = [];
    foreach ($urls as $cle => $url) {
        $requetes[$cle] = curl_init($url);
        curl_setopt_array($requetes[$cle], OPTIONS_CURL);
        curl_multi_add_handle($multi, $requetes[$cle]);
    }

    do {
        curl_multi_exec($multi, $en_cours);
        curl_multi_select($multi, 1);
    } while ($en_cours > 0);

    $resultats = [];
    foreach ($requetes as $cle => $requete) {
        $code = curl_getinfo($requete, CURLINFO_HTTP_CODE);
        $resultats[$cle] = $code === 200 ? curl_multi_getcontent($requete) : null;
        curl_multi_remove_handle($multi, $requete);
        curl_close($requete);
    }
    curl_multi_close($multi);

    return $resultats;
}

// Un flux après l'autre, avec un délai plus court par flux et 20 secondes au total :
// l'hébergeur arrête un script PHP qui dure trop longtemps.
function telecharger_un_par_un(array $urls)
{
    $debut     = time();
    $avec_curl = function_exists('curl_init') && function_exists('curl_exec');
    $contexte  = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'Mozilla/5.0 (XibaarYi)']]);
    $resultats = [];

    foreach ($urls as $cle => $url) {
        if (time() - $debut > 20) {
            $resultats[$cle] = null; // plus le temps : ce flux sera lu au prochain import
            continue;
        }

        if ($avec_curl) {
            $requete = curl_init($url);
            curl_setopt_array($requete, [CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 8] + OPTIONS_CURL);
            $contenu = curl_exec($requete);
            $resultats[$cle] = curl_getinfo($requete, CURLINFO_HTTP_CODE) === 200 ? $contenu : null;
            curl_close($requete);
        } else {
            $resultats[$cle] = @file_get_contents($url, false, $contexte) ?: null;
        }
    }
    return $resultats;
}

// Adresse d'image acceptable : https (le site est en https), un vrai fichier image, pas une icône
function image_valide($url)
{
    $url = html_entity_decode(trim((string) $url), ENT_QUOTES, 'UTF-8');
    if (strlen($url) > 500 || !preg_match('#^https://[^\s"\'<>]+$#i', $url)) {
        return null;
    }
    $chemin = (string) parse_url($url, PHP_URL_PATH);
    if (!preg_match('/\.(jpe?g|png|webp|gif)$/i', $chemin) || preg_match('#(smilies|emoji|gravatar|logo|icon)#i', $chemin)) {
        return null;
    }
    return $url;
}

// Photo donnée directement par le flux : <media:content>, <enclosure>, ou <img> dans le texte
function image_du_flux(SimpleXMLElement $item)
{
    $candidates = [];

    $media = $item->children('http://search.yahoo.com/mrss/');
    foreach ([$media->content, $media->thumbnail] as $balises) {
        foreach ($balises as $balise) {
            $candidates[] = (string) $balise->attributes()->url;
        }
    }
    foreach ($item->enclosure as $enclosure) {
        if (strpos((string) $enclosure['type'], 'image/') === 0) {
            $candidates[] = (string) $enclosure['url'];
        }
    }
    $html = (string) $item->description . (string) $item->children('http://purl.org/rss/1.0/modules/content/')->encoded;
    if (preg_match_all('/<img[^>]+src=["\']([^"\']+)/i', $html, $images)) {
        array_push($candidates, ...$images[1]);
    }

    foreach ($candidates as $url) {
        if ($valide = image_valide($url)) {
            return $valide;
        }
    }
    return null;
}

// Image d'aperçu de la page de l'article (og:image) : celle que le média choisit lui-même
// pour les partages sur les réseaux sociaux (balise du <head>).
function image_de_la_page($url)
{
    $debut_page = '';

    if (function_exists('curl_init') && function_exists('curl_exec')) {
        // Page entière (moins d'une seconde) : couper le téléchargement en route s'est révélé plus lent
        $requete = curl_init($url);
        curl_setopt_array($requete, [CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 6] + OPTIONS_CURL);
        $debut_page = (string) curl_exec($requete);
        curl_close($requete);
    } else {
        $contexte   = stream_context_create(['http' => ['timeout' => 6, 'user_agent' => 'Mozilla/5.0 (XibaarYi)']]);
        $debut_page = (string) @file_get_contents($url, false, $contexte, 0, 300000);
    }

    // Les balises d'aperçu sont dans le <head> : inutile de chercher plus loin
    $fin_head = stripos($debut_page, '</head>');
    if ($fin_head !== false) {
        $debut_page = substr($debut_page, 0, $fin_head);
    }

    foreach (['og:image', 'twitter:image'] as $propriete) {
        // L'attribut content peut être avant ou après property/name
        $motifs = [
            '/<meta[^>]+(?:property|name)=["\']' . $propriete . '["\'][^>]*content=["\']([^"\']+)/i',
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]*(?:property|name)=["\']' . $propriete . '["\']/i',
        ];
        foreach ($motifs as $motif) {
            if (preg_match($motif, $debut_page, $trouve) && ($valide = image_valide($trouve[1]))) {
                return $valide;
            }
        }
    }
    return null;
}

/*
 * Cherche la photo des titres importés qui n'en ont pas encore (image_url NULL), les plus récents d'abord,
 * jusqu'à $fin (timestamp) : le reste sera fait au prochain import.
 */
function completer_images(PDO $pdo, $fin)
{
    $a_chercher = $pdo->query("SELECT id, source_url FROM articles
                               WHERE source_url IS NOT NULL AND image_url IS NULL
                               ORDER BY date_publication DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    $mise_a_jour = $pdo->prepare("UPDATE articles SET image_url = ? WHERE id = ?");

    foreach ($a_chercher as $article) {
        if (time() >= $fin) {
            break;
        }
        // '' = cherchée mais aucune trouvée : on ne recommencera pas
        $mise_a_jour->execute([image_de_la_page($article['source_url']) ?? '', $article['id']]);
    }
}

// Image affichée pour un titre importé : celle de sa rubrique (uploads/sport.png...), s'il y en a une
function image_rubrique($nom_rubrique)
{
    $fichier = preg_replace('/[^a-z0-9]+/', '-', sans_accents($nom_rubrique)) . '.png';
    return is_file(__DIR__ . '/../public/uploads/' . $fichier) ? $fichier : '';
}

/*
 * Importe les nouveaux titres. Retourne ['ajoutes' => n, 'erreurs' => [...]],
 * ou null si un autre import est déjà en cours (deux visiteurs en même temps).
 */
function importer_actualites(PDO $pdo)
{
    // Verrou MySQL : un seul import à la fois.
    // Certains hébergeurs interdisent GET_LOCK : on importe alors sans verrou
    // (sans risque de doublon : source_url est unique et l'insertion utilise INSERT IGNORE).
    $verrou = false;
    try {
        if ((int) $pdo->query("SELECT GET_LOCK('xibaar_import_rss', 0)")->fetchColumn() !== 1) {
            return null;
        }
        $verrou = true;
    } catch (PDOException $e) {
        error_log('Xibaar Yi - GET_LOCK indisponible : ' . $e->getMessage());
    }

    try {
        // Rubriques du site : "education" => ['id' => 4, 'image' => 'education.png']
        $rubriques = [];
        foreach ($pdo->query("SELECT id, nom FROM categories") as $cat) {
            $rubriques[sans_accents($cat['nom'])] = ['id' => (int) $cat['id'], 'image' => image_rubrique($cat['nom'])];
        }

        $debut    = time();
        $contenus = telecharger_flux(array_column(SOURCES_RSS, 'url'));
        $ajoutes  = 0;
        $erreurs  = [];
        $limite   = time() - AGE_MAX_JOURS * 86400;

        // Colonne image_url absente tant que la migration 010 n'a pas été exécutée
        try {
            $pdo->query("SELECT image_url FROM articles LIMIT 0");
            $avec_photos = true;
        } catch (PDOException $e) {
            $avec_photos = false;
        }

        $insertion = $pdo->prepare("
            INSERT IGNORE INTO articles
                (titre, description_courte, contenu, image, date_publication, id_categorie, id_auteur, source_nom, source_url"
                . ($avec_photos ? ", image_url" : "") . ")
            VALUES (?, ?, '', ?, ?, ?, NULL, ?, ?" . ($avec_photos ? ", ?" : "") . ")
        ");
        $meme_titre = $pdo->prepare("SELECT 1 FROM articles WHERE titre = ?");

        // Rubrique déjà pleine (30 titres) : un titre plus ancien que le 30e serait supprimé
        // aussitôt après l'import, puis réimporté la fois suivante. On ne le prend pas.
        $date_plancher = $pdo->prepare("
            SELECT date_publication FROM articles
            WHERE id_categorie = ? AND source_url IS NOT NULL
            ORDER BY date_publication DESC
            LIMIT 1 OFFSET " . (TITRES_PAR_RUBRIQUE - 1)
        );
        foreach ($rubriques as $cle => $rubrique) {
            $date_plancher->execute([$rubrique['id']]);
            $plancher = $date_plancher->fetchColumn();
            $rubriques[$cle]['plancher'] = $plancher ? strtotime($plancher) : 0;
        }

        foreach (SOURCES_RSS as $i => $source) {
            $xml = $contenus[$i] ? @simplexml_load_string($contenus[$i], 'SimpleXMLElement', LIBXML_NOCDATA) : false;
            if (!$xml || !isset($xml->channel->item)) {
                $erreurs[] = $source['nom'] . ' (' . parse_url($source['url'], PHP_URL_PATH) . ') : flux indisponible';
                continue;
            }

            foreach ($xml->channel->item as $item) {
                $titre = mb_substr(texte_brut($item->title), 0, 255);
                $lien  = trim((string) $item->link);
                $date  = strtotime((string) $item->pubDate);

                if ($titre === '' || !preg_match('#^https?://#', $lien) || strlen($lien) > 500 || !$date || $date < $limite) {
                    continue;
                }

                // Catégories données par le média (<category> ou <dc:subject>)
                $categories_flux = [];
                foreach ($item->category as $c) {
                    $categories_flux[] = (string) $c;
                }
                foreach ($item->children('http://purl.org/dc/elements/1.1/')->subject as $c) {
                    $categories_flux[] = (string) $c;
                }

                $nom_rubrique = $source['categorie'] ?? deviner_rubrique($titre, $categories_flux);
                $rubrique     = $nom_rubrique ? ($rubriques[sans_accents($nom_rubrique)] ?? null) : null;
                if (!$rubrique) {
                    continue; // sujet hors de nos rubriques (faits divers, international...)
                }
                if (min($date, time()) <= $rubrique['plancher']) {
                    continue; // trop ancien pour une rubrique déjà pleine
                }

                // Même dépêche reprise par plusieurs médias : on ne la garde qu'une fois
                $meme_titre->execute([$titre]);
                if ($meme_titre->fetchColumn()) {
                    continue;
                }

                $valeurs = [
                    $titre,
                    accroche($item->description),
                    $rubrique['image'],
                    date('Y-m-d H:i:s', min($date, time())),
                    $rubrique['id'],
                    $source['nom'],
                    $lien,
                ];
                if ($avec_photos) {
                    $valeurs[] = image_du_flux($item); // NULL si le flux n'en donne pas : cherchée ensuite sur la page
                }
                $insertion->execute($valeurs);
                $ajoutes += $insertion->rowCount();
            }
        }

        // On ne garde que les 30 titres importés les plus récents de chaque rubrique
        // (les articles écrits par la rédaction ne sont jamais supprimés)
        $anciens = $pdo->prepare("
            SELECT id FROM articles
            WHERE id_categorie = ? AND source_url IS NOT NULL
            ORDER BY date_publication DESC
            LIMIT 1000 OFFSET " . TITRES_PAR_RUBRIQUE
        );
        $suppression = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        foreach ($rubriques as $rubrique) {
            $anciens->execute([$rubrique['id']]);
            foreach ($anciens->fetchAll(PDO::FETCH_COLUMN) as $id) {
                $suppression->execute([$id]);
            }
        }

        // Photos manquantes : lues sur les pages des articles, dans la limite de 25 secondes d'import en tout
        if ($avec_photos) {
            completer_images($pdo, $debut + 25);
        }

        $pdo->prepare("INSERT INTO imports_rss (nb_ajoutes, erreurs) VALUES (?, ?)")
            ->execute([$ajoutes, implode("\n", $erreurs)]);
    } finally {
        // Libéré même si l'import plante en route
        if ($verrou) {
            $pdo->query("SELECT RELEASE_LOCK('xibaar_import_rss')");
        }
    }

    return ['ajoutes' => $ajoutes, 'erreurs' => $erreurs];
}

// Dernier import (ou null) : affiché dans le back-office
function dernier_import(PDO $pdo)
{
    try {
        return $pdo->query("SELECT * FROM imports_rss ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        return null; // Migration 009 pas encore exécutée
    }
}

// Import automatique si le dernier date de plus de 3 heures (appelé en fin de page, voir pied.php)
function actualiser_si_necessaire(PDO $pdo)
{
    $dernier = dernier_import($pdo);
    if ($dernier === null) {
        try {
            $pdo->query("SELECT 1 FROM imports_rss LIMIT 1");
        } catch (PDOException $e) {
            return; // Migration 009 pas encore exécutée : pas d'import
        }
    } elseif (strtotime($dernier['date_import']) > time() - INTERVALLE_IMPORT_MIN * 60) {
        return;
    }

    try {
        importer_actualites($pdo);
    } catch (Throwable $e) {
        // Un flux mal formé ne doit jamais casser la page du visiteur
        error_log('Xibaar Yi - import RSS : ' . $e->getMessage());
    }
}
