<?php
    require_once '../config.php';

    if (!isset($_SESSION['user_role'])) {
        header('Location: /Projet back-end/Xibaar_Yi/connexion.php');
        exit;
    }

    if ($_SESSION['user_role'] !== 'editeur' && $_SESSION['user_role'] !== 'administrateur') {
        header('Location: /Projet back-end/Xibaar_Yi/accueil.php');
        exit;
    }

    $stmt = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once '../entete.php';
   
?>

<div style="max-width:1100px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Gestion des catégories</div>
        <a href="/Projet back-end/Xibaar_Yi/categories/ajouter.php" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouvelle catégorie
        </a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['nom']) ?></td>
                <td style="display:flex; gap:8px;">
                    <a href="/Projet back-end/Xibaar_Yi/categories/modifier.php?id=<?= $cat['id'] ?>" class="btn btn-secondary">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Modifier
                    </a>
                    <a href="/Projet back-end/Xibaar_Xi/categories/supprimer.php?id=<?= $cat['id'] ?>" class="btn btn-danger"
                       onclick="return confirm('Supprimer cette catégorie ?')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                        Supprimer
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php require_once '../pied.php'; ?>