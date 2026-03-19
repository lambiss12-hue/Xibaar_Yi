<nav style="background:#2C2C2E; padding:12px 40px; display:flex; align-items:center; gap:24px;">
    <a href="/xibaar_yi/accueil.php" style="color:white; text-decoration:none; font-size:14px;">Accueil</a>

    <?php if (isset($_SESSION['user_role'])): 
        
        //a barre de navigation. Elle est intelligente
        //elle affiche des liens différents selon si vous êtes visiteur
        //éditeur ou administrateur
        //Là aussi écrit une seule fois, inclus partout.\
        
        ?>

        <a href="/xibaar_yi/articles/ajouter.php" style="color:white; text-decoration:none; font-size:14px;">+ Article</a>
        <a href="/xibaar_yi/categories/liste.php" style="color:white; text-decoration:none; font-size:14px;">Catégories</a>

        <?php if ($_SESSION['user_role'] === 'administrateur'): ?>
            <a href="/xibaar_yi/utilisateurs/liste.php" style="color:white; text-decoration:none; font-size:14px;">Utilisateurs</a>
        <?php endif; ?>

        <a href="/xibaar_yi/deconnexion.php" style="color:#ff6b6b; text-decoration:none; font-size:14px; margin-left:auto;">
            Déconnexion (<?= htmlspecialchars($_SESSION['user_login']) ?>)
        </a>

    <?php else: ?>

        <a href="/xibaar_yi/connexion.php" style="color:#4CAF50; text-decoration:none; font-size:14px; margin-left:auto;">
            Connexion
        </a>

    <?php endif; ?>
</nav>