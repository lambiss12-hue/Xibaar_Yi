# Xibaar Yi

**Xibaar Yi** (« les nouvelles » en wolof) est un site d'actualité sénégalaise développé en PHP / MySQL
dans le cadre du projet back-end de l'École Supérieure Polytechnique.

Les visiteurs lisent les articles par catégorie ; les membres de la rédaction se connectent pour
publier et gérer le contenu.

## Fonctionnalités

**Partie publique**
- Page d'accueil avec les derniers articles, pagination (5 par page) et filtre par catégorie
- Page de détail d'un article avec suggestions « À lire aussi »
- Inscription à la newsletter
- Page de contact

**Back-office** (après connexion)

| Rôle | Articles | Catégories | Utilisateurs |
|------|:--------:|:----------:|:------------:|
| Éditeur | ✅ | ✅ | ❌ |
| Administrateur | ✅ | ✅ | ✅ |

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
│   ├── contact.php
│   ├── traitement_newsletter.php
│   ├── admin/
│   │   ├── articles/        ajouter, modifier, supprimer
│   │   ├── categories/      liste, ajouter, modifier, supprimer
│   │   └── utilisateurs/    liste, ajouter, modifier, supprimer
│   ├── assets/css/style.css
│   └── uploads/             images des articles
├── includes/
│   ├── config.php           connexion BDD, session, fonction url()
│   ├── auth.php             exiger_role(), jeton CSRF (csrf_champ, exiger_post_csrf)
│   ├── entete.php           en-tête commun (menu, catégories)
│   └── pied.php             pied de page commun
└── database/
    ├── database.sql         création des tables + données de test
    └── migrations/          mises à jour pour les bases déjà créées
```

## Installation

### 1. Récupérer le projet

```bash
git clone https://github.com/lambiss12-hue/Xibaar_Yi.git
cd Xibaar_Yi
```

### 2. Créer la base de données

Démarrer MySQL (Laragon, XAMPP, WAMP…) puis importer le fichier SQL :

```bash
mysql -u root < database/database.sql
```

Ou via phpMyAdmin / HeidiSQL : **Importer** → `database/database.sql`.

Le script crée la base `xibaar_yi`, les tables `categories`, `utilisateurs`, `articles`, `newsletter`,
ainsi que 5 catégories, 5 articles d'exemple et un compte administrateur.

> **Base déjà existante ?** Exécuter aussi les scripts de `database/migrations/` (une seule fois).
> Les anciens mots de passe SHA-256 restent valides : ils sont convertis en `password_hash()`
> automatiquement à la prochaine connexion.

### 3. Configurer la connexion

Les paramètres sont dans [`includes/config.php`](includes/config.php) (par défaut : `root` sans mot de passe
sur `localhost:3306`). Si MySQL tourne sur un autre port, définir la variable d'environnement `DB_PORT`.

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

## Améliorations prévues

- [ ] Traiter le formulaire de contact (`traitement_contact.php` n'existe pas encore)
- [x] Hacher les mots de passe avec `password_hash()` / `password_verify()` au lieu de SHA-256
- [x] Supprimer via un formulaire POST avec jeton CSRF plutôt qu'un simple lien
- [ ] Gérer l'inscription en double à la newsletter

## Équipe

Projet réalisé par les étudiants de l'École Supérieure Polytechnique (ESP) de Dakar.
