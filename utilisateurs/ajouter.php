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

    $erreur = '';
    $succes = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom       = trim($_POST['nom'] ?? '');
        $prenom    = trim($_POST['prenom'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $login     = trim($_POST['login'] ?? '');
        $mdp       = trim($_POST['mot_de_passe'] ?? '');
        $role      = trim($_POST['role'] ?? '');

        // Vérification 1 : champs obligatoires
        if (empty($nom) || empty($prenom) || empty($login) || empty($mdp) || empty($role)) {
            $erreur = "Les champs nom, prénom, login, mot de passe et rôle sont obligatoires.";
        
        // Vérification 2 : longueur mot de passe
        } elseif (strlen($mdp) < 6) {
            $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
        
        // Vérification 3 : login déjà pris
        } else {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ?");
            $stmt->execute([$login]);
            if ($stmt->fetch()) {
                $erreur = "Ce login est déjà utilisé.";
            } else {
                // Tout est bon — on insère
                $stmt = $pdo->prepare("
                    INSERT INTO utilisateurs (nom, prenom, email, telephone, login, mot_de_passe, role)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $nom, $prenom, $email, $telephone,
                    $login, hash('sha256', $mdp), $role
                ]);
                $succes = "Utilisateur créé avec succès !";
            }
        }
    }
require_once '../entete.php';
require_once '../menu.php';
?>

<div style="max-width:600px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Nouvel utilisateur</div>
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
                    <input type="text" name="nom" class="form-control" placeholder="Diallo">
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" placeholder="Amadou">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="amadou@esp.sn">
                </div>
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" placeholder="77 000 00 00">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Login *</label>
                <input type="text" name="login" class="form-control" placeholder="amadou123">
            </div>

            <div class="form-group">
                <label class="form-label">Mot de passe * (minimum 6 caractères)</label>
                <input type="password" name="mot_de_passe" id="mdp" class="form-control" placeholder="••••••••">
            </div>

            <div class="form-group">
                <label class="form-label">Rôle *</label>
                <select name="role" class="form-control">
                    <option value="">-- Choisir un rôle --</option>
                    <option value="editeur">Éditeur</option>
                    <option value="administrateur">Administrateur</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Créer l'utilisateur
            </button>

        </form>
    </div>
</div>

<!-- VALIDATION JAVASCRIPT -->
<script>
document.getElementById('formAjouter').addEventListener('submit', function(e) {
    const nom    = document.querySelector('[name="nom"]').value.trim();
    const prenom = document.querySelector('[name="prenom"]').value.trim();
    const login  = document.querySelector('[name="login"]').value.trim();
    const mdp    = document.getElementById('mdp').value.trim();
    const role   = document.querySelector('[name="role"]').value;

    if (!nom || !prenom || !login || !mdp || !role) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires.');
        return;
    }

    if (mdp.length < 6) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 6 caractères.');
    }
});
</script>

<?php require_once '../pied.php'; ?>