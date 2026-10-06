<?php
// Balises communes du <head> : description, icône, aperçu quand on partage un lien
// (LinkedIn, WhatsApp, Facebook...). Inclus par entete.php et connexion.php.
// Une page peut définir avant l'inclusion (sinon, valeurs du site) :
//   $titre_page       titre de l'onglet et de l'aperçu
//   $meta_description résumé affiché sous le titre
//   $meta_image       image de l'aperçu (chemin dans public/)
$description_site = "Xibaar Yi — site d'actualité sénégalaise : articles par catégorie, recherche, "
                  . "commentaires modérés et back-office de rédaction. Projet PHP / MySQL.";

$meta_titre       = isset($titre_page) ? $titre_page . ' — Xibaar Yi' : "Xibaar Yi — L'actualité du Sénégal";
$meta_description = $meta_description ?? $description_site;
$meta_image       = $meta_image ?? 'assets/img/apercu.png';

// Les réseaux sociaux veulent des adresses complètes (https://domaine/...)
$https     = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$url_hote  = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
    <meta name="description" content="<?= htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="Salamba Diène">
    <link rel="icon" type="image/svg+xml" href="<?= url('assets/img/favicon.svg') ?>">

    <meta property="og:type" content="<?= isset($meta_type) ? $meta_type : 'website' ?>">
    <meta property="og:site_name" content="Xibaar Yi">
    <meta property="og:title" content="<?= htmlspecialchars($meta_titre, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($url_hote . url($meta_image), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
