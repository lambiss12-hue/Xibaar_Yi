<?php
require_once '../config.php';

if (!isset($_SESSION['user_role'])) {
    header('Location: /xibaar_yi/connexion.php');
    exit;
}

if ($_SESSION['user_role'] !== 'editeur' && $_SESSION['user_role'] !== 'administrateur') {
    header('Location: /xibaar_yi/accueil.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: /xibaar_yi/accueil.php');
    exit;
}

$id = (int) $_GET['id'];

// Récupérer l'article à modifier
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Location: /xibaar_yi/accueil.php');
    exit;
}

// Récupérer les catégories pour le formulaire
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description_courte'] ?? '');
    $contenu      = trim($_POST['contenu'] ?? '');
    $id_categorie = (int)($_POST['id_categorie'] ?? 0);
    $image        = $article['image'];

    if (empty($titre) || empty($description) || empty($contenu) || $id_categorie === 0) {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    } else {
        // Gestion image si nouvelle image uploadée
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $extensions_autorisees)) {
                $erreur = "Format d'image non autorisé.";
            } else {
                $nom_image = uniqid() . '.' . $extension;
                $dossier   = '../uploads/';
                if (!is_dir($dossier)) {
                    mkdir($dossier, 0755, true);
                }
                move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $nom_image);
                $image = $nom_image;
            }
        }

        if (empty($erreur)) {
            $stmt = $pdo->prepare("
                UPDATE articles 
                SET titre = ?, description_courte = ?, contenu = ?, id_categorie = ?, image = ?
                WHERE id = ?
            ");
            $stmt->execute([$titre, $description, $contenu, $id_categorie, $image, $id]);
            $succes = "Article modifié avec succès !";
            
            // Recharger l'article
            $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

require_once '../entete.php';
?>

<div style="max-width:760px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Modifier l'article</div>
        <a href="/xibaar_yi/accueil.php" class="btn btn-secondary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Retour à l'accueil
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

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Titre *</label>
                <input type="text" name="titre" value="<?= htmlspecialchars($article['titre']) ?>"
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

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
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
                        <img src="/xibaar_yi/uploads/<?= htmlspecialchars($article['image']) ?>" 
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

<?php require_once '../pied.php'; ?>