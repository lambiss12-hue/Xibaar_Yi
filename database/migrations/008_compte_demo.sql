-- À exécuter une seule fois sur une base créée avant le compte de démonstration.
-- Nouveau rôle "demo" : voit tout le back-office, mais ne peut rien modifier
-- (voir exiger_role() dans includes/auth.php), et les coordonnées des visiteurs
-- lui sont masquées. Compte public : login "demo", mot de passe "demo1234".

ALTER TABLE utilisateurs
    MODIFY role ENUM('editeur', 'administrateur', 'demo') NOT NULL DEFAULT 'editeur';

INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, telephone, login, mot_de_passe, role) VALUES
    (900, 'Démo', 'Visiteur', '', '', 'demo', '$2y$10$puCMpfcJWdeSJM.3EIOQeO4uN0WiB7J4U8CcJ0AhfGv3FIoo2WCMu', 'demo');
