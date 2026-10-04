# Xibaar Yi

**Xibaar Yi** (« les nouvelles » en wolof) est un site d'actualité sénégalaise développé en PHP / MySQL
dans le cadre du projet back-end de l'École Supérieure Polytechnique.

Les visiteurs lisent les articles par catégorie ; les membres de la rédaction se connectent pour
publier et gérer le contenu.

## Fonctionnalités

**Partie publique**
- Page d'accueil avec les derniers articles, pagination (5 par page) et filtre par catégorie
- Recherche d'articles (titre, résumé, contenu) depuis l'en-tête
- « Les plus lus » : compteur de vues par article (une vue par visiteur et par session)
- Page de détail d'un article (image, nombre de vues) avec suggestions « À lire aussi »
- Commentaires sous chaque article, publiés après validation par la rédaction
  (anti-spam : champ piège invisible + un commentaire toutes les 30 secondes)
- Site adapté aux téléphones et tablettes
- Inscription à la newsletter
- Page de contact (messages enregistrés en base, consultables dans le back-office)

**Back-office** (après connexion)

| Rôle | Articles | Catégories | Messages | Utilisateurs |
|------|:--------:|:----------:|:--------:|:------------:|
| Éditeur | ✅ | ✅ | ✅ | ❌ |
| Administrateur | ✅ | ✅ | ✅ | ✅ |

Une barre « Rédaction » apparaît sous le menu une fois connecté : liste des articles
(recherche par titre, filtre par catégorie), catégories, messages de contact
(avec le nombre de messages non lus), les commentaires à valider, les utilisateurs pour l'administrateur, et « Mon compte »
où chacun modifie ses informations et son mot de passe (l'ancien mot de passe est demandé).

## Technologies

- PHP 8 (PDO, sessions) — sans framework
- MySQL 8
- HTML / CSS / JavaScript (validation des formulaires)

## Structure du projet

```
Xibaar_Yi/
├── public/                  ← racine web (seul dossier exposé au navigateur)
│   ├── index.php            accueil
│   ├── article.php          détail d'un article
│   ├── connexion.php / deconnexion.php
│   ├── contact.php / traitement_contact.php
│   ├── traitement_commentaire.php
│   ├── traitement_newsletter.php
│   ├── admin/
│   │   ├── articles/        liste (recherche, filtre), ajouter, modifier, supprimer
│   │   ├── categories/      liste, ajouter, modifier, supprimer
│   │   ├── messages/        liste, action (lu / non lu, supprimer)
│   │   ├── commentaires/    liste (en attente / approuvés), action (approuver, masquer, supprimer)
│   │   ├── utilisateurs/    liste, ajouter, modifier, supprimer
│   │   └── compte.php       mon compte (infos + mot de passe)
│   ├── assets/css/style.css
│   └── uploads/             images des articles
├── includes/
│   ├── config.php           connexion BDD, session, fonction url()
│   ├── auth.php             exiger_role(), jeton CSRF (csrf_champ, csrf_valide, exiger_post_csrf)
│   ├── upload.php           enregistrer_image() : vérifie le contenu et la taille des images
│   ├── entete.php           en-tête commun (menu, catégories)
│   ├── pied.php             pied de page commun
│   └── message_newsletter.php  message après inscription à la newsletter
├── database/
│   ├── database.sql         création des tables + données de test
│   ├── demo.sql             données de démonstration (optionnel)
│   └── migrations/          mises à jour pour les bases déjà créées
└── docs/                    architecture, base de données, démo de soutenance
```

## Installation

### 1. Récupérer le projet

```bash
git clone https://github.com/lambiss12-hue/Xibaar_Yi.git
cd Xibaar_Yi
```

### 2. Créer la base de données

Démarrer MySQL (Laragon, XAMPP, WAMP…), créer la base `xibaar_yi` puis y importer le fichier SQL :

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS xibaar_yi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root xibaar_yi < database/database.sql
```

Ou via phpMyAdmin / HeidiSQL : créer la base `xibaar_yi`, la sélectionner, puis **Importer** → `database/database.sql`.

Le script ne crée pas la base lui-même (chez un hébergeur, son nom est imposé) : il crée les tables `categories`, `utilisateurs`, `articles`, `newsletter`, `messages_contact`, `commentaires`,
ainsi que 5 catégories, 5 articles d'exemple et un compte administrateur.

> **Base déjà existante ?** Exécuter aussi les scripts de `database/migrations/` (une seule fois).
> Les anciens mots de passe SHA-256 restent valides : ils sont convertis en `password_hash()`
> automatiquement à la prochaine connexion.

### 3. Configurer la connexion

Les paramètres sont dans [`includes/config.php`](includes/config.php) (par défaut : `root` sans mot de passe
sur `localhost:3306`). Si MySQL tourne sur un autre port, définir la variable d'environnement `DB_PORT`.

**En ligne** : copier [`includes/config.local.exemple.php`](includes/config.local.exemple.php) en
`includes/config.local.php` et y mettre les identifiants donnés par l'hébergeur. Ce fichier est ignoré par Git :
le mot de passe de la base ne part jamais sur GitHub. Les fichiers `.htaccess` interdisent l'accès direct
à `includes/` et `database/`, et l'exécution de scripts dans `public/uploads/`.

### 4. Lancer le site

**Option A — Laragon / XAMPP / WAMP** : placer le dossier dans `www/` (ou `htdocs/`) puis ouvrir
<http://localhost/Xibaar_Yi/public/>

**Option B — serveur intégré de PHP** :

```bash
php -S localhost:8000 -t public
```

puis ouvrir <http://localhost:8000>

Les liens sont générés automatiquement par la fonction `url()` : le site fonctionne quel que soit
le nom ou l'emplacement du dossier.

### Compte de test

| Login | Mot de passe | Rôle |
|-------|--------------|------|
| `admin` | `admin123` | administrateur |

> ⚠️ Changer ce mot de passe avant toute mise en ligne.

### Données de démonstration (optionnel)

Pour une démo plus vivante (15 articles de plus, commentaires, messages, 2 éditeurs) :

```bash
mysql -u root xibaar_yi < database/demo.sql
```

Comptes éditeurs ajoutés : `fatou` et `moussa`, mot de passe `redac123`.
Le fichier explique aussi comment retirer ces données.

## Documentation

| Document | Contenu |
|---|---|
| [docs/architecture.md](docs/architecture.md) | organisation du code, construction d'une page, rôles, mesures de sécurité |
| [docs/base-de-donnees.md](docs/base-de-donnees.md) | schéma de la base, tables, relations, migrations |
| [docs/demo.md](docs/demo.md) | scénario de démonstration et questions probables pour la soutenance |

## Travailler à plusieurs (Git)

1. Mettre `main` à jour : `git switch main && git pull`
2. Créer une branche par tâche : `git switch -c nom-de-la-tache`
3. Commiter régulièrement, puis pousser : `git push -u origin nom-de-la-tache`
4. Ouvrir une *Pull Request* sur GitHub pour que l'équipe relise avant la fusion dans `main`

### Ajouter une page d'administration

```php
<?php
require_once __DIR__ . '/../../../includes/auth.php';
exiger_role(['editeur', 'administrateur']);   // ou ['administrateur']

// ... traitement ...

require_once __DIR__ . '/../../../includes/entete.php';
?>
<a href="<?= url('admin/categories/liste.php') ?>">Catégories</a>

<!-- Action sensible (suppression...) : formulaire POST + jeton CSRF -->
<form method="POST" action="<?= url('admin/categories/supprimer.php') ?>">
    <?= csrf_champ() ?>
    <input type="hidden" name="id" value="3">
    <button type="submit">Supprimer</button>
</form>
<?php require_once __DIR__ . '/../../../includes/pied.php'; ?>
```

## Sécurité

- Mots de passe hachés avec `password_hash()` (bcrypt)
- Requêtes SQL préparées (PDO) contre les injections SQL
- Jeton CSRF sur tous les formulaires du back-office
- Suppressions uniquement en POST ; impossible de supprimer une catégorie ou un
  utilisateur qui a encore des articles (évite d'effacer des articles par erreur)
- Images vérifiées par leur contenu réel (jpg, png, webp, 5 Mo max), renommées aléatoirement
- Textes affichés avec `htmlspecialchars()` contre le XSS
- Erreurs de base de données écrites dans le journal PHP, jamais affichées aux visiteurs

## Améliorations prévues

- [x] Traiter le formulaire de contact
- [x] Hacher les mots de passe avec `password_hash()` / `password_verify()` au lieu de SHA-256
- [x] Supprimer via un formulaire POST avec jeton CSRF plutôt qu'un simple lien
- [x] Gérer l'inscription en double à la newsletter

## Équipe

Projet réalisé par les étudiants de l'École Supérieure Polytechnique (ESP) de Dakar.
