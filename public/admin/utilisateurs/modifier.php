<?php
    require_once __DIR__ . '/../../../includes/auth.php';

    exiger_role(['administrateur']);

    if (!isset($_GET['id'])) {
        header('Location: ' . url('admin/utilisateurs/liste.php'));
        exit;
    }

    $id = (int) $_GET['id'];
    //ici on a mis mis int pour des moyens de protection car par exmple si 
    //un hacker met un url malveillant ou something else 
    //php vas transformer cela en int ainsi en 0 si c est du teste et ainsi causer aucun degat

    // Récupérer l'utilisateur à modifier
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si l'utilisateur n'existe pas on redirige
    if (!$user) {
        header('Location: ' . url('admin/utilisateurs/liste.php'));
        exit;
    }

    $erreur = '';
    $succes = '';

    // Jeton CSRF : le formulaire doit venir de notre site
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valide()) {
        $erreur = "Le formulaire a expiré. Veuillez réessayer.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom       = trim($_POST['nom'] ?? '');
        $prenom    = trim($_POST['prenom'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $login     = trim($_POST['login'] ?? '');
        $role      = trim($_POST['role'] ?? '');
        $mdp       = trim($_POST['mot_de_passe'] ?? '');

        if (empty($nom) || empty($prenom) || empty($login) || empty($role)) {
            $erreur = "Les champs nom, prénom, login et rôle sont obligatoires.";
        } elseif (!in_array($role, ['editeur', 'administrateur', 'demo'], true)) {
            $erreur = "Rôle invalide.";
        } elseif ($id === (int) $_SESSION['user_id'] && $role !== 'administrateur') {
            // Sinon l'administrateur perdrait lui-même l'accès à cette page
            // (et le site pourrait se retrouver sans aucun administrateur)
            $erreur = "Vous ne pouvez pas retirer votre propre rôle d'administrateur.";
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = "L'adresse email n'est pas valide.";
        } elseif (mb_strlen($nom) > 100 || mb_strlen($prenom) > 100 || mb_strlen($login) > 50
                  || mb_strlen($email) > 150 || mb_strlen($telephone) > 30) {
            $erreur = "Un des champs est trop long.";
        } else {
            // Vérifier si le login est pris par un AUTRE utilisateur
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
            $stmt->execute([$login, $id]);
            if ($stmt->fetch()) {
                $erreur = "Ce login est déjà utilisé par un autre utilisateur.";
            } else {
                // Modifier sans changer le mot de passe si vide
                if (!empty($mdp)) {
                    if (mb_strlen($mdp) < 6) {
                        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
                    } else {
                        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=?, telephone=?, login=?, mot_de_passe=?, role=? WHERE id=?");
                        $stmt->execute([$nom, $prenom, $email, $telephone, $login, password_hash($mdp, PASSWORD_DEFAULT), $role, $id]);
                        $succes = "Utilisateur modifié avec succès !";
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=?, telephone=?, login=?, role=? WHERE id=?");
                    $stmt->execute([$nom, $prenom, $email, $telephone, $login, $role, $id]);
                    $succes = "Utilisateur modifié avec succès !";
                }
            }
        }

        // On réaffiche ce qui a été tapé (après une erreur comme après un succès)
        $user = array_merge($user, compact('nom', 'prenom', 'email', 'telephone', 'login', 'role'));

        // Son propre compte : le nom affiché dans l'en-tête doit suivre
        if ($succes && $id === (int) $_SESSION['user_id']) {
            $_SESSION['user_login'] = $login;
            $_SESSION['user_nom']   = $prenom . ' ' . $nom;
        }
    }

    require_once __DIR__ . '/../../../includes/entete.php';

?>

<div style="max-width:600px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <div class="page-title">Modifier l'utilisateur</div>
        <a href="<?= url('admin/utilisateurs/liste.php') ?>" class="btn btn-secondary">
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
            <?= csrf_champ() ?>

            <div class="form-grille">
                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" maxlength="100" value="<?= htmlspecialchars($user['nom']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" maxlength="100" value="<?= htmlspecialchars($user['prenom']) ?>">
                </div>
            </div>

            <div class="form-grille">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" maxlength="150" value="<?= htmlspecialchars(masquer_si_demo($user['email'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" maxlength="30" value="<?= htmlspecialchars(masquer_si_demo($user['telephone'] ?? '', 'telephone')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Login *</label>
                <input type="text" name="login" class="form-control" maxlength="50" value="<?= htmlspecialchars(masquer_si_demo($user['login'], 'login')) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" name="mot_de_passe" id="mdp" class="form-control" placeholder="••••••••">
            </div>

            <div class="form-group">
                <label class="form-label">Rôle *</label>
                <select name="role" class="form-control">
                    <option value="editeur" <?= $user['role'] === 'editeur' ? 'selected' : '' ?>>Éditeur</option>
                    <option value="administrateur" <?= $user['role'] === 'administrateur' ? 'selected' : '' ?>>Administrateur</option>
                    <option value="demo" <?= $user['role'] === 'demo' ? 'selected' : '' ?>>Démo (lecture seule)</option>
                </select>
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
    const nom    = document.querySelector('[name="nom"]').value.trim();
    const prenom = document.querySelector('[name="prenom"]').value.trim();
    const login  = document.querySelector('[name="login"]').value.trim();
    const mdp    = document.getElementById('mdp').value.trim();

    if (!nom || !prenom || !login) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires.');
        return;
    }

    if (mdp && mdp.length < 6) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 6 caractères.');
    }
});
</script>

<?php require_once __DIR__ . '/../../../includes/pied.php'; ?>