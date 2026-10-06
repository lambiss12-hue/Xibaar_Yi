<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/upload.php';

exiger_role(['editeur', 'administrateur']);

if (!isset($_GET['id'])) {
    header('Location: ' . url('admin/articles/liste.php'));
    exit;
}

$id = (int) $_GET['id'];

// Récupérer l'article à modifier
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Location: ' . url('admin/articles/liste.php'));
    exit;
}

// Récupérer les catégories pour le formulaire
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

$erreur = '';
$succes = '';

// Envoi trop lourd pour le serveur : le formulaire arrive vide (voir includes/upload.php)
if (envoi_trop_lourd()) {
    $erreur = "L'image est trop lourde (5 Mo maximum).";
// Jeton CSRF : le formulaire doit venir de notre site
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valide()) {
    $erreur = "Le formulaire a expiré. Veuillez réessayer.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description_courte'] ?? '');
    $contenu      = trim($_POST['contenu'] ?? '');
    $id_categorie = (int)($_POST['id_categorie'] ?? 0);
    $ancienne_image = $article['image'];
    $image          = $ancienne_image;

    if (empty($titre) || empty($description) || empty($contenu) || $id_categorie === 0) {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!in_array($id_categorie, array_map('intval', array_column($categories, 'id')), true)) {
        $erreur = "Cette catégorie n'existe pas.";
    } elseif (mb_strlen($titre) > 255) {
        $erreur = "Le titre ne doit pas dépasser 255 caractères.";
    } else {
        // Nouvelle image envoyée ? Sinon on garde l'ancienne
        $nouvelle_image = enregistrer_image($_FILES['image'] ?? null, $erreur);
        if ($nouvelle_image !== null) {
            $image = $nouvelle_image;
        }

        if (empty($erreur)) {
            $stmt = $pdo->prepare("
                UPDATE articles
                SET titre = ?, description_courte = ?, contenu = ?, id_categorie = ?, image = ?
                WHERE id = ?
            ");
            $stmt->execute([$titre, $description, $contenu, $id_categorie, $image, $id]);
            $succes = "Article modifié avec succès !";

            // Image remplacée : l'ancien fichier ne sert plus à rien
            if ($image !== $ancienne_image) {
                supprimer_image_inutilisee($pdo, $ancienne_image);
            }

            // Recharger l'article
            $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // En cas d'erreur, on réaffiche ce que l'utilisateur a tapé (pas l'ancienne version)
    if ($erreur) {
        $article['titre']              = $titre;
        $article['description_courte'] = $description;
        $article['contenu']            = $contenu;
        $article['id_categorie']       = $id_categorie;
    }
}

require_once __DIR__ . '/../../../includes/entete.php';
?>

<div style="max-width:760px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Modifier l'article</div>
        <a href="<?= url('admin/articles/liste.php') ?>" class="btn btn-secondary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Retour aux articles
        </a>
    </div>

    <?php if ($erreur): ?>
    <div class="alert alert-danger">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        <?= htmlspecialchars($erreur) ?>
    </div>
    <?php endif; ?>

    <?php if ($succes): ?>
    <div class="alert alert-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 15.01 9 12.01"/></svg>
        <?= htmlspecialchars($succes) ?>
    </div>
    <?php endif; ?>

    <div style="background:#fff; border-radius:8px; border:0.5px solid #e0e0e0; padding:28px;">
        <form method="POST" action="modifier.php?id=<?= $id ?>" enctype="multipart/form-data" id="formModifier">
            <?= csrf_champ() ?>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Titre *</label>
                <input type="text" name="titre" maxlength="255" value="<?= htmlspecialchars($article['titre']) ?>"
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-family:inherit;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Description courte *</label>
                <input type="text" name="description_courte" value="<?= htmlspecialchars($article['description_courte']) ?>"
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-family:inherit;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Contenu complet *</label>
                <textarea name="contenu" rows="10"
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-family:inherit; resize:vertical;"><?= htmlspecialchars($article['contenu']) ?></textarea>
            </div>

            <div class="form-grille" style="margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Catégorie *</label>
                    <select name="id_categorie"
                        style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-family:inherit;">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $article['id_categorie'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Image (optionnelle)</label>
                    <?php if ($article['image']): ?>
                        <img src="<?= url('uploads/') ?><?= htmlspecialchars($article['image']) ?>" 
                             style="width:100%; height:80px; object-fit:cover; border-radius:4px; margin-bottom:8px;">
                    <?php endif; ?>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                        style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:13px;">
                </div>
            </div>

            <button type="submit"
                style="width:100%; background:#111; color:#fff; padding:13px; border:none; border-radius:4px; font-size:13px; font-weight:600; cursor:pointer; letter-spacing:.5px;">
                Enregistrer les modifications
            </button>

        </form>
    </div>
</div>

<script>
document.getElementById('formModifier').addEventListener('submit', function(e) {
    const titre      = document.querySelector('[name="titre"]').value.trim();
    const description = document.querySelector('[name="description_courte"]').value.trim();
    const contenu    = document.querySelector('[name="contenu"]').value.trim();
    const categorie  = document.querySelector('[name="id_categorie"]').value;

    if (!titre || !description || !contenu || !categorie) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires.');
    }
});
</script>

<?php require_once __DIR__ . '/../../../includes/pied.php'; ?>