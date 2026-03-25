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
        header('Location: /xibaar_yi/categories/liste.php');
        exit;
    }

    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cat) {
        header('Location: /xibaar_yi/categories/liste.php');
        exit;
    }

    $erreur = '';
    $succes = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');

        if (empty($nom)) {
            $erreur = "Le nom est obligatoire.";
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET nom = ? WHERE id = ?");
            $stmt->execute([$nom, $id]);
            $succes = "Catégorie modifiée avec succès !";
        }
    }

    require_once '../entete.php';
?>

<div style="max-width:600px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Modifier la catégorie</div>
        <a href="/xibaar_yi/categories/liste.php" class="btn btn-secondary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Retour à la liste
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
        <form method="POST" action="modifier.php?id=<?= $id ?>" id="formModifier">
            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($cat['nom']) ?>" style="width:100%; margin-bottom:16px;">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('formModifier').addEventListener('submit', function(e) {
    const nom = document.querySelector('[name="nom"]').value.trim();
    if (!nom) {
        e.preventDefault();
        alert('Veuillez remplir le nom de la catégorie.');
    }
});
</script>

<?php require_once '../pied.php'; ?>