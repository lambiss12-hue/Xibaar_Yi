<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/upload.php';

exiger_role(['editeur', 'administrateur']);

// Récupérer les catégories pour le formulaire
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

$erreur = '';
$succes = '';

// Valeurs du formulaire : vides au départ, puis ce que l'utilisateur a tapé
// (en cas d'erreur, il ne perd pas son article)
$titre        = trim($_POST['titre'] ?? '');
$description  = trim($_POST['description_courte'] ?? '');
$contenu      = trim($_POST['contenu'] ?? '');
$id_categorie = (int) ($_POST['id_categorie'] ?? 0);

// Envoi trop lourd pour le serveur : le formulaire arrive vide (voir includes/upload.php)
if (envoi_trop_lourd()) {
    $erreur = "L'image est trop lourde (5 Mo maximum).";
// Jeton CSRF : le formulaire doit venir de notre site
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valide()) {
    $erreur = "Le formulaire a expiré. Veuillez réessayer.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_auteur   = $_SESSION['user_id'];
    $image       = '';

    // Validation
    if (empty($titre) || empty($description) || empty($contenu) || $id_categorie === 0) {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!in_array($id_categorie, array_map('intval', array_column($categories, 'id')), true)) {
        $erreur = "Cette catégorie n'existe pas.";
    } elseif (mb_strlen($titre) > 255) {
        $erreur = "Le titre ne doit pas dépasser 255 caractères.";
    } else {
        // Gestion de l'image (vérification du contenu, voir includes/upload.php)
        $image = enregistrer_image($_FILES['image'] ?? null, $erreur) ?? '';

        if (empty($erreur)) {
            $stmt = $pdo->prepare("
                INSERT INTO articles (titre, description_courte, contenu, id_categorie, id_auteur, image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$titre, $description, $contenu, $id_categorie, $id_auteur, $image]);
            $succes = "Article publié avec succès !";
            $id_nouvel_article = (int) $pdo->lastInsertId();

            // Article enregistré : formulaire vide pour le suivant
            $titre = $description = $contenu = '';
            $id_categorie = 0;
        }
    }
}

require_once __DIR__ . '/../../../includes/entete.php';
?>

<div style="max-width:760px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Nouvel article</div>
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
        <a href="<?= url('article.php') ?>?id=<?= $id_nouvel_article ?>" style="margin-left:auto; color:inherit; font-weight:600;">Voir l'article →</a>
    </div>
    <?php endif; ?>

    <div style="background:#fff; border-radius:8px; border:0.5px solid #e0e0e0; padding:28px;">
    <form method="POST" action="ajouter.php" enctype="multipart/form-data" id="formArticle">
        <?= csrf_champ() ?>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Titre *</label>
            <input type="text" name="titre" placeholder="Titre de l'article" maxlength="255"
                value="<?= htmlspecialchars($titre) ?>"
                style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-family:inherit;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Description courte *</label>
            <input type="text" name="description_courte" placeholder="Résumé en une phrase"
                value="<?= htmlspecialchars($description) ?>"
                style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-family:inherit;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Contenu complet *</label>
            <textarea name="contenu" rows="10" placeholder="Rédigez votre article ici..."
                style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-family:inherit; resize:vertical;"><?= htmlspecialchars($contenu) ?></textarea>
        </div>

        <div class="form-grille" style="margin-bottom:20px;">
            <div>
                <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Catégorie *</label>
                <select name="id_categorie"
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:14px; font-family:inherit;">
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (int) $cat['id'] === $id_categorie ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                    <option value="new_category" style="font-weight: bold; color: #007bff;">+ Ajouter une catégorie</option>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Image (optionnelle)</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:4px; font-size:13px; font-family:inherit;">
            </div>
        </div>

        <div style="background:#f8f8f8; border-radius:4px; padding:12px 16px; margin-bottom:20px; font-size:12px; color:#888;">
            Article publié par : <strong><?= htmlspecialchars($_SESSION['user_nom']) ?></strong>
            &nbsp;·&nbsp; Date : <strong><?= date('d/m/Y') ?></strong>
        </div>

        <button type="submit"
            style="width:100%; background:#111; color:#fff; padding:13px; border:none; border-radius:4px; font-size:13px; font-weight:600; cursor:pointer; letter-spacing:.5px;">
            + Publier l'article
        </button>

    </form>
</div>


<script>
// "+ Ajouter une catégorie" quitte la page : on prévient si un texte a déjà été tapé
document.querySelector('select[name="id_categorie"]').addEventListener('change', function() {
    if (this.value !== 'new_category') {
        return;
    }
    const dejaTape = ['titre', 'description_courte', 'contenu']
        .some(nom => document.querySelector('[name="' + nom + '"]').value.trim() !== '');

    if (!dejaTape || confirm('Quitter cette page ? Le texte de l\'article sera perdu.')) {
        window.location.href = '<?= url('admin/categories/ajouter.php') ?>';
    } else {
        this.value = '';
    }
});
</script>


<script>
document.getElementById('formArticle').addEventListener('submit', function(e) {
    const titre       = document.querySelector('[name="titre"]').value.trim();
    const description = document.querySelector('[name="description_courte"]').value.trim();
    const contenu     = document.querySelector('[name="contenu"]').value.trim();
    const categorie   = document.querySelector('[name="id_categorie"]').value;

    if (!titre || !description || !contenu || !categorie) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires.');
    }
});
</script>

<?php require_once __DIR__ . '/../../../includes/pied.php'; ?>