<?php
// Mon compte : chaque membre de la rédaction modifie ses propres informations
// et son mot de passe (le login et le rôle restent gérés par l'administrateur).
require_once __DIR__ . '/../../includes/auth.php';

exiger_role(['editeur', 'administrateur']);

$id = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Compte supprimé entre-temps : on déconnecte
if (!$user) {
    header('Location: ' . url('deconnexion.php'));
    exit;
}

$erreur_infos = '';
$succes_infos = '';
$erreur_mdp   = '';
$succes_mdp   = '';

$formulaire = $_POST['formulaire'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valide()) {
    // Jeton CSRF : le formulaire doit venir de notre site
    if ($formulaire === 'mot_de_passe') {
        $erreur_mdp = "Le formulaire a expiré. Veuillez réessayer.";
    } else {
        $erreur_infos = "Le formulaire a expiré. Veuillez réessayer.";
    }

} elseif ($formulaire === 'infos') {
    // ---------- Formulaire 1 : informations personnelles ----------
    $nom       = trim($_POST['nom'] ?? '');
    $prenom    = trim($_POST['prenom'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($nom === '' || $prenom === '') {
        $erreur_infos = "Le nom et le prénom sont obligatoires.";
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur_infos = "L'adresse email n'est pas valide.";
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, telephone = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $email, $telephone, $id]);

        // Le nom affiché ailleurs sur le site vient de la session
        $_SESSION['user_nom'] = $prenom . ' ' . $nom;

        $user = array_merge($user, compact('nom', 'prenom', 'email', 'telephone'));
        $succes_infos = "Vos informations ont été mises à jour.";
    }

} elseif ($formulaire === 'mot_de_passe') {
    // ---------- Formulaire 2 : changement de mot de passe ----------
    $ancien       = trim($_POST['ancien_mdp'] ?? '');
    $nouveau      = trim($_POST['nouveau_mdp'] ?? '');
    $confirmation = trim($_POST['confirmation_mdp'] ?? '');

    if ($ancien === '' || $nouveau === '' || $confirmation === '') {
        $erreur_mdp = "Tous les champs sont obligatoires.";
    } elseif (!password_verify($ancien, $user['mot_de_passe'])) {
        // On redemande l'ancien mot de passe : quelqu'un qui trouverait
        // la session ouverte ne peut pas changer le mot de passe à votre place.
        $erreur_mdp = "Le mot de passe actuel est incorrect.";
    } elseif (mb_strlen($nouveau) < 6) {
        $erreur_mdp = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    } elseif ($nouveau !== $confirmation) {
        $erreur_mdp = "La confirmation ne correspond pas au nouveau mot de passe.";
    } elseif ($nouveau === $ancien) {
        $erreur_mdp = "Le nouveau mot de passe doit être différent de l'ancien.";
    } else {
        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);

        $user['mot_de_passe'] = $hash;
        session_regenerate_id(true);
        $succes_mdp = "Votre mot de passe a été modifié.";
    }
}

$titre_page = 'Mon compte';
require_once __DIR__ . '/../../includes/entete.php';
?>

<div style="max-width:600px; margin:32px auto; padding:0 24px;">

    <div class="page-header">
        <h1 class="page-title">Mon compte</h1>
        <span class="badge"><?= htmlspecialchars($user['role']) ?></span>
    </div>

    <!-- ===== Informations personnelles ===== -->
    <h2 class="compte-titre">Mes informations</h2>

    <?php if ($erreur_infos): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur_infos) ?></div>
    <?php endif; ?>
    <?php if ($succes_infos): ?>
        <div class="alert alert-success"><?= htmlspecialchars($succes_infos) ?></div>
    <?php endif; ?>

    <div class="compte-carte">
        <form method="POST" action="<?= url('admin/compte.php') ?>">
            <?= csrf_champ() ?>
            <input type="hidden" name="formulaire" value="infos">

            <div class="form-grille">
                <div class="form-group">
                    <label class="form-label" for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" class="form-control" required
                           value="<?= htmlspecialchars($user['nom']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" required
                           value="<?= htmlspecialchars($user['prenom']) ?>">
                </div>
            </div>

            <div class="form-grille">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" class="form-control"
                           value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Login</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['login']) ?>" disabled>
                <p class="compte-aide">Le login et le rôle ne peuvent être modifiés que par un administrateur.</p>
            </div>

            <button type="submit" class="btn btn-primary compte-bouton">Enregistrer mes informations</button>
        </form>
    </div>

    <!-- ===== Mot de passe ===== -->
    <h2 class="compte-titre">Changer mon mot de passe</h2>

    <?php if ($erreur_mdp): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur_mdp) ?></div>
    <?php endif; ?>
    <?php if ($succes_mdp): ?>
        <div class="alert alert-success"><?= htmlspecialchars($succes_mdp) ?></div>
    <?php endif; ?>

    <div class="compte-carte">
        <form method="POST" action="<?= url('admin/compte.php') ?>" id="formMdp">
            <?= csrf_champ() ?>
            <input type="hidden" name="formulaire" value="mot_de_passe">

            <div class="form-group">
                <label class="form-label" for="ancien_mdp">Mot de passe actuel *</label>
                <input type="password" id="ancien_mdp" name="ancien_mdp" class="form-control"
                       autocomplete="current-password" required>
            </div>

            <div class="form-grille">
                <div class="form-group">
                    <label class="form-label" for="nouveau_mdp">Nouveau (6 caractères min.) *</label>
                    <input type="password" id="nouveau_mdp" name="nouveau_mdp" class="form-control"
                           autocomplete="new-password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirmation_mdp">Confirmation *</label>
                    <input type="password" id="confirmation_mdp" name="confirmation_mdp" class="form-control"
                           autocomplete="new-password" minlength="6" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary compte-bouton">Changer le mot de passe</button>
        </form>
    </div>

</div>

<script>
document.getElementById('formMdp').addEventListener('submit', function (e) {
    const nouveau      = document.getElementById('nouveau_mdp').value;
    const confirmation = document.getElementById('confirmation_mdp').value;
    if (nouveau !== confirmation) {
        e.preventDefault();
        alert('La confirmation ne correspond pas au nouveau mot de passe.');
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/pied.php'; ?>
