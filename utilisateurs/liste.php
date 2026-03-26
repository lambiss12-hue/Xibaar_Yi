<?php
require_once '../config.php';

if (!isset($_SESSION['user_role'])) {
    header('Location: /xibaar_yi/connexion.php');
    exit;
}

if ($_SESSION['user_role'] !== 'administrateur') {
    header('Location: /xibaar_yi/accueil.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY nom ASC");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../entete.php';
?>

<div style="max-width:1100px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
    <h1 class="page-title">Gestion des utilisateurs</h1>
    
    <!-- Utilise le prefixe absolu pour que le lien fonctionne -->
    <a href="/Projet back-end/Xibaar_Yi/utilisateurs/ajouter.php" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14")/>>
        </svg>
        Nouvel utilisateur
    </a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nom complet</th>
                <th>Login</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= htmlspecialchars($u['login']) ?></td>
                <td>
                    <span class="badge"><?= htmlspecialchars($u['role']) ?></span>
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="/Projet back-end/Xibaar_Yi/utilisateurs/modifier.php?id=<?= $u['id'] ?>" class="btn btn-secondary">
                        Modifier
                    </a>
                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <a href="/Projet back-end/Xibaar_Yi/utilisateurs/supprimer.php?id=<?= $u['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer ?')">
                        Supprimer
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../pied.php'; ?>
