<?php
// Enregistrement sécurisé d'une image envoyée depuis le back-office.

// Types d'images acceptés => extension utilisée pour le fichier enregistré
const IMAGES_AUTORISEES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

const TAILLE_MAX_IMAGE = 5 * 1024 * 1024; // 5 Mo

/*
 * true si le formulaire envoyé dépassait la limite du serveur (post_max_size) :
 * PHP jette alors TOUT le formulaire ($_POST et $_FILES vides, jeton CSRF compris).
 * Sans ce test, on afficherait "Le formulaire a expiré" au lieu de "image trop lourde".
 */
function envoi_trop_lourd()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
        && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
}

/*
 * Enregistre $fichier (une entrée de $_FILES) dans public/uploads/.
 * Retourne :
 *   - le nom du fichier enregistré si tout va bien
 *   - null si aucun fichier n'a été envoyé (champ laissé vide)
 * En cas de problème, retourne null et remplit $erreur.
 */
function enregistrer_image(?array $fichier, &$erreur)
{
    if ($fichier === null || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($fichier['error'] === UPLOAD_ERR_INI_SIZE || $fichier['error'] === UPLOAD_ERR_FORM_SIZE
        || $fichier['size'] > TAILLE_MAX_IMAGE) {
        $erreur = "L'image est trop lourde (5 Mo maximum).";
        return null;
    }

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        $erreur = "L'envoi de l'image a échoué. Veuillez réessayer.";
        return null;
    }

    // On vérifie le CONTENU du fichier, pas seulement son extension :
    // un fichier "virus.php" renommé en "photo.jpg" sera refusé ici.
    $infos = @getimagesize($fichier['tmp_name']);
    $type  = $infos['mime'] ?? '';

    if (!isset(IMAGES_AUTORISEES[$type])) {
        $erreur = "Ce fichier n'est pas une image valide. Utilisez jpg, png ou webp.";
        return null;
    }

    // Nom aléatoire + extension déduite du vrai type (on ignore le nom d'origine)
    $nom_image = bin2hex(random_bytes(8)) . '.' . IMAGES_AUTORISEES[$type];
    $dossier   = __DIR__ . '/../public/uploads/';

    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }

    if (!move_uploaded_file($fichier['tmp_name'], $dossier . $nom_image)) {
        $erreur = "Impossible d'enregistrer l'image sur le serveur.";
        return null;
    }

    return $nom_image;
}

/*
 * Supprime le fichier $image de public/uploads/ s'il n'est plus utilisé par aucun article
 * (après la suppression d'un article, ou quand on remplace son image).
 * Seulement les images envoyées depuis le back-office (nom aléatoire en hexadécimal) :
 * les images d'exemple du projet (sport.png...) sont suivies par Git, on n'y touche pas.
 */
function supprimer_image_inutilisee(PDO $pdo, $image)
{
    if (!preg_match('/^[a-f0-9]{13,16}\.(jpg|jpeg|png|webp)$/', (string) $image)) {
        return;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE image = ?");
    $stmt->execute([$image]);

    $fichier = __DIR__ . '/../public/uploads/' . basename($image);
    if ($stmt->fetchColumn() == 0 && is_file($fichier)) {
        unlink($fichier);
    }
}
