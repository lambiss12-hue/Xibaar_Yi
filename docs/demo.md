# Démonstration — soutenance

Scénario de démo d'environ **10 minutes**, puis les questions probables du jury.

## Avant la soutenance (la veille)

- [ ] Base à jour : `database/database.sql` (base neuve) ou les scripts de `database/migrations/` (base existante)
- [ ] Données de démo importées : `mysql -u root xibaar_yi < database/demo.sql`
- [ ] Le site s'ouvre sur l'ordinateur de la présentation (accueil, un article, connexion)
- [ ] Deux onglets prêts : un **visiteur** (navigation privée) et un **membre de la rédaction**
- [ ] Comptes : `admin` / `admin123` (administrateur), `fatou` / `redac123` (éditrice)
- [ ] Un téléphone (ou le mode mobile du navigateur : F12 puis Ctrl+Shift+M) pour montrer l'affichage mobile

## Scénario

### 1. Le site vu par un visiteur (3 min) — onglet navigation privée

1. **Accueil** : les derniers articles, la pagination (20 articles, 4 pages), le menu des catégories.
2. Cliquer sur **Sport** : le filtre par catégorie.
3. **Recherche** en haut : taper `mobile` → trouve l'article sur le paiement mobile
   (la recherche porte sur le titre, le résumé **et** le contenu).
4. Barre de droite **« Les plus lus »** : classement par nombre de vues.
5. Ouvrir un article : image, auteur, date, **nombre de vues**, suggestions « À lire aussi ».
6. **Commentaires** : en laisser un → message « publié après validation ».
   Il n'apparaît pas encore : c'est voulu (modération).
7. **Newsletter** : s'inscrire, puis réessayer avec le même email en MAJUSCULES → « déjà inscrit ».
8. **Contact** : envoyer un message.
9. Montrer l'affichage **mobile** (menu, articles sur une colonne).

### 2. Le back-office (4 min) — onglet rédaction

1. Se connecter avec `fatou` (éditrice). La barre **Rédaction** apparaît, avec des compteurs :
   messages non lus et commentaires à valider.
2. **Commentaires** : approuver celui laissé à l'étape 1.6 → retourner sur l'article : il est visible.
   Supprimer le commentaire « Robot » (spam).
3. **Messages** : le message de l'étape 1.8 est en tête, marqué « Nouveau ». Le marquer comme lu.
4. **Articles** : rechercher, filtrer par catégorie. **+ Nouvel article** avec une image → il apparaît sur l'accueil.
5. **Catégories** : essayer de supprimer une catégorie qui contient des articles → **refusé**, avec un message
   (sinon tous ses articles seraient effacés).
6. **Mon compte** : modifier son téléphone ; montrer que changer de mot de passe demande l'ancien.
7. Se déconnecter, se connecter en **admin** : le lien **Utilisateurs** apparaît (réservé à l'administrateur).
   Montrer qu'une éditrice n'y a pas accès en tapant l'adresse `admin/utilisateurs/liste.php` avec le compte `fatou` → redirection.

### 3. Le code (3 min)

Ouvrir dans l'éditeur :
1. **L'arborescence** : `public/` (seul dossier accessible), `includes/`, `database/`.
2. **`includes/auth.php`** : `exiger_role()` et le jeton CSRF.
3. **`public/connexion.php`** : `password_verify()` et la conversion automatique des anciens mots de passe.
4. **`includes/upload.php`** : pourquoi on vérifie le contenu de l'image et pas seulement l'extension.
5. **`docs/base-de-donnees.md`** : le schéma de la base.

## Questions probables du jury

**Pourquoi PDO et des requêtes préparées ?**
Les valeurs sont envoyées séparément de la requête SQL : même si quelqu'un tape du SQL dans un
formulaire, il est traité comme du texte, pas comme une commande. C'est la protection contre
l'**injection SQL**.

**Comment sont stockés les mots de passe ?**
Avec `password_hash()` (bcrypt) : un hash à sens unique avec un **sel** aléatoire, donc deux
mots de passe identiques donnent des hash différents. À la connexion, `password_verify()` compare.
Au début du projet c'était du SHA-256 ; les anciens comptes sont convertis automatiquement
à leur prochaine connexion, sans que personne ait à changer de mot de passe.

**C'est quoi le CSRF ?**
Un autre site pourrait contenir un formulaire caché qui envoie « supprimer l'article 3 » à notre
site : si un rédacteur connecté visite cette page, son navigateur envoie la requête avec sa session.
Chaque formulaire du back-office contient donc un **jeton secret** stocké en session ; l'autre site
ne le connaît pas, la requête est refusée.

**Et le XSS ?**
Tout texte saisi par quelqu'un (titre, commentaire, recherche...) est affiché avec
`htmlspecialchars()` : `<script>` devient du texte affiché, pas du code exécuté.

**Pourquoi les commentaires ne sont-ils pas publiés tout de suite ?**
Pour éviter le spam et les messages injurieux sur un site d'information. En plus : un champ piège
invisible (les robots le remplissent et sont ignorés) et un commentaire toutes les 30 secondes au maximum.

**Pourquoi un dossier `public/` ?**
Seul ce dossier est accessible par le navigateur. `includes/config.php` (identifiants de la base)
ne peut donc pas être téléchargé.

**Que se passe-t-il si on supprime une catégorie ?**
La base est configurée en cascade (supprimer une catégorie supprimerait ses articles), mais
l'application **refuse** tant que la catégorie contient des articles. Même règle pour un auteur.

**Pourquoi pas de framework (Laravel, Symfony) ?**
Le but du projet était de maîtriser les bases du back-end : sessions, SQL, sécurité, formulaires.
Sans framework, chaque mécanisme est visible dans notre code.

**Comment avez-vous travaillé en équipe ?**
Git et GitHub : une branche par fonctionnalité, une Pull Request relue avant la fusion dans `main`,
et un script de **migration** à chaque changement de la base pour que chacun mette la sienne à jour.

**Quelles sont les limites / améliorations possibles ?**
- Pas d'envoi d'emails (newsletter et réponses aux messages) : il faudrait un serveur SMTP.
- Pas de réinitialisation de mot de passe oublié (c'est l'administrateur qui le change).
- Éditeur de texte simple (pas de gras, liens, images dans le contenu).
- Les listes du back-office ne sont pas paginées (suffisant pour quelques centaines d'articles).
- Le site tourne en local ; la mise en ligne demanderait un hébergeur PHP/MySQL et HTTPS.
