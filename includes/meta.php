<?php
// Balises communes du <head> : description, icône, aperçu quand on partage un lien
// (LinkedIn, WhatsApp, Facebook...). Inclus par entete.php et connexion.php.
$description_site = "Xibaar Yi — site d'actualité sénégalaise : articles par catégorie, recherche, "
                  . "commentaires modérés et back-office de rédaction. Projet PHP / MySQL.";

// Les réseaux sociaux veulent des adresses complètes (https://domaine/...)
$https     = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$url_hote  = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
    <meta name="description" content="<?= htmlspecialchars($description_site, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="Salamba Diène">
    <link rel="icon" type="image/svg+xml" href="<?= url('assets/img/favicon.svg') ?>">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Xibaar Yi">
    <meta property="og:title" content="Xibaar Yi — L'actualité du Sénégal">
    <meta property="og:description" content="<?= htmlspecialchars($description_site, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= $url_hote . url('assets/img/apercu.png') ?>">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
