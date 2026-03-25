<?php
    // 1. DÉMARRER LA SESSION EN PREMIER
    session_start();

    require_once 'config.php';

    $erreur = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = trim($_POST['login'] ?? '');
        $mdp   = trim($_POST['mot_de_passe'] ?? '');

        if (empty($login) || empty($mdp)) {
            $erreur = "Tous les champs sont obligatoires.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // On compare le mot de passe haché
            if ($user && $user['mot_de_passe'] === hash('sha256', $mdp)) {
                // On remplit les informations de session
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_nom']   = $user['prenom'] . ' ' . $user['nom'];
                
                // Redirection propre vers l'accueil
                header('Location: accueil.php');
                exit;
            } else {
                $erreur = "Login ou mot de passe incorrect.";
            }
        }
    }

    // RÉCUPÉRER LE DERNIER ARTICLE (reste inchangé)
    $dernierArticle = $pdo->query("
        SELECT a.titre, u.nom, a.date_publication
        FROM articles a
        LEFT JOIN utilisateurs u ON a.id_auteur = u.id
        ORDER BY a.date_publication DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
?>


<div style="display:grid; grid-template-columns:1fr 1fr; min-height:100vh;">

    <!-- GAUCHE -->
    <div style="background:#0a0a0a; padding:48px 40px; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <a href="/xibaar_yi/accueil.php" style="text-decoration:none;">
                <div style="font-family:Georgia,serif; font-size:28px; font-weight:700; color:#fff;">Xibaar Yi</div>
                <div style="font-size:11px; color:#555; letter-spacing:2px; text-transform:uppercase; margin-top:4px;">L'actualité du Sénégal</div>
            </a>
        </div>
        <div>
            <div style="font-family:Georgia,serif; font-size:26px; color:#fff; line-height:1.4; font-weight:700; margin-bottom:16px;">
                Restez informé,<br>à tout moment.
            </div>
            <div style="font-size:13px; color:#666; line-height:1.7;">
                Accédez à l'espace rédaction pour gérer les articles, les catégories et les utilisateurs du site.
            </div>
        </div>
        <div style="border-left:2px solid #333; padding-left:16px;">
            <div style="font-family:Georgia,serif; font-size:14px; color:#555; font-style:italic; line-height:1.6;">
                "L'information est le premier droit du citoyen."
            </div>
            <div style="font-size:11px; color:#444; margin-top:8px; letter-spacing:.5px;">
                — École Supérieure Polytechnique, 2026
            </div>
        </div>
    </div>

    <!-- DROITE -->
    <div style="background:#fff; padding:48px 40px; display:flex; flex-direction:column; justify-content:center;">

        <div style="font-family:Georgia,serif; font-size:24px; font-weight:700; color:#111; margin-bottom:6px;">Connexion</div>
        <div style="font-size:13px; color:#999; margin-bottom:32px;">Accès réservé aux membres de la rédaction</div>

        <?php if ($erreur): ?>
            <div style="background:#fff0f0; color:#cc0000; border:0.5px solid #ffcccc; padding:12px 16px; border-radius:3px; font-size:13px; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="connexion.php" id="formConnexion">
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:11px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Login</label>
                <input type="text" name="login" id="login" class="form-control" placeholder="Votre identifiant">
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:11px; font-weight:700; color:#444; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Mot de passe</label>
                <input type="password" name="mot_de_passe" id="mdp" class="form-control" placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:13px; font-size:13px; letter-spacing:.5px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                Se connecter
            </button>
        </form>

        <div style="display:flex; align-items:center; gap:12px; margin:28px 0;">
            <div style="flex:1; height:1px; background:#eee;"></div>
            <div style="font-size:11px; color:#ccc;">Dernière actualité</div>
            <div style="flex:1; height:1px; background:#eee;"></div>
        </div>

        <?php if ($dernierArticle): ?>
        <div style="background:#f8f8f8; border-radius:4px; padding:14px 16px; border-left:3px solid #111;">
            <div style="font-size:9px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">À la une</div>
            <div style="font-family:Georgia,serif; font-size:13px; color:#111; font-weight:600; line-height:1.4;">
                <?= htmlspecialchars($dernierArticle['titre']) ?>
            </div>
            <div style="font-size:10px; color:#bbb; margin-top:6px;">
                <?= htmlspecialchars($dernierArticle['nom']) ?> · <?= date('d M Y', strtotime($dernierArticle['date_publication'])) ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.getElementById('formConnexion').addEventListener('submit', function(e) {
    const login = document.getElementById('login').value.trim();
    const mdp   = document.getElementById('mdp').value.trim();
    if (login === '' || mdp === '') {
        e.preventDefault();
        alert('Veuillez remplir tous les champs.');
        return;
    }
    if (mdp.length < 6) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 6 caractères.');
    }
});
</script>

<?php require_once 'pied.php'; ?>