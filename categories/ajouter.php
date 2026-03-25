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

    $stmt = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once '../entete.php';
    require_once '../menu.php';
?>
<div style="max-width:600px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Nouvel catégorie</div>
        <a href="/xibaar_yi/utilisateurs/liste.php" class="btn btn-secondary">
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

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" placeholder="Agriculture">
                </div>
            </div>


            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Créer la catégorie
            </button>

        </form>
    </div>
</div>

<!-- VALIDATION JAVASCRIPT -->
<script>
document.getElementById('formAjouter').addEventListener('submit', function(e) {
    const nom    = document.querySelector('[name="nom"]').value.trim();
    if (!nom || !prenom || !login || !mdp || !role) {
        e.preventDefault();
        alert('Veuillez remplir le nom de la catégories.');
        return;
    }
});
</script>

<?php require_once '../pied.php'; ?>