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

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');

    if (empty($nom)) {
        $erreur = "Le nom de la catégorie est obligatoire.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE nom = ?");
        $stmt->execute([$nom]);
        if ($stmt->fetch()) {
            $erreur = "Cette catégorie existe déjà.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (?)");
            $stmt->execute([$nom]);
            $succes = "Catégorie créée avec succès !";
        }
    }
}

require_once '../entete.php';

?>
<link rel="stylesheet" href="/Projet%20back-end/Xibaar_Yi/style.css">
<div style="max-width:600px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Nouvelle catégorie</div>
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
        <form method="POST" action="ajouter.php" id="formAjouter">
            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input type="text" name="nom" class="form-control" placeholder="Agriculture" style="width:100%; margin-bottom:16px;">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Créer la catégorie
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('formAjouter').addEventListener('submit', function(e) {
    const nom = document.querySelector('[name="nom"]').value.trim();
    if (!nom) {
        e.preventDefault();
        alert('Veuillez remplir le nom de la catégorie.');
    }
});
</script>

<?php require_once '../pied.php'; ?>