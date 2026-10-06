# Xibaar Yi

**English** · [Français](README.fr.md)

**Xibaar Yi** ("the news" in Wolof) is a Senegalese news website built from scratch in **PHP and MySQL**,
without a framework: a public site where readers browse, search and comment on articles, and a
back-office where the editorial team publishes and moderates content.

**Live demo: [https://xibaaryi.infinityfreeapp.com](https://xibaaryi.infinityfreeapp.com)**: to explore the back-office, log in with
`demo` / `demo1234` (read-only account: everything is visible, nothing can be changed).

![Home page](docs/captures/accueil.png)

## Highlights

- **No framework**: routing, sessions, access control, CSRF protection, uploads and pagination are written by hand,
  to understand what frameworks do under the hood.
- **Security first**: prepared statements, bcrypt passwords, CSRF tokens, login throttling, upload checks
  on the real file content, XSS escaping everywhere (details below).
- **Deployed on shared hosting** (InfinityFree): `.htaccess` rules expose only `public/`, credentials live in
  a git-ignored config file, and PHP errors are logged, never shown to visitors.
- **Read-only demo mode**: a dedicated `demo` role can open every back-office page, but every form submission is
  rejected centrally in `exiger_role()`, and visitors' emails and phone numbers are masked.

## Features

**Public site**
- Latest articles with pagination, category filter, and full-text search (title, summary, content)
- Article page with view counter ("Most read") and "Read also" suggestions
- Comments, published only after moderation (anti-spam honeypot + rate limit)
- Newsletter sign-up and contact form, both stored in the database
- Responsive layout (phone, tablet, desktop)

**Back-office**

| Role | Articles | Categories | Messages & comments | Users |
|------|:--------:|:----------:|:-------------------:|:-----:|
| Editor | ✅ | ✅ | ✅ | ❌ |
| Administrator | ✅ | ✅ | ✅ | ✅ |
| Demo | 👁 read-only | 👁 | 👁 (contact details masked) | 👁 |

- Article management with image upload, search and category filter
- Comment moderation (pending / approved), contact inbox with unread counter
- User management for administrators; "My account" page to update one's own profile and password

<p>
  <img src="docs/captures/admin-commentaires.png" alt="Comment moderation in the back-office" width="66%">
  <img src="docs/captures/mobile-accueil.png" alt="Home page on a phone" width="24%">
</p>

## Tech stack

- **PHP 8** (PDO, sessions), no framework, no Composer dependency
- **MySQL 8**: 7 tables with foreign keys, schema in [`database/database.sql`](database/database.sql),
  versioned migrations in [`database/migrations/`](database/migrations/)
- **HTML / CSS / vanilla JavaScript** (client-side form checks; the server always re-validates)
- Hosted on **InfinityFree** (Apache), deployed over FTP

## Security

| Threat | Protection |
|---|---|
| SQL injection | PDO prepared statements everywhere; `LIKE` wildcards escaped in search |
| Stolen passwords | `password_hash()` / `password_verify()` (bcrypt); legacy SHA-256 hashes upgraded at login |
| Brute force | login blocked for 15 minutes after 5 failures from the same IP |
| CSRF | secret token on every back-office form, deletions and logout only via POST |
| XSS | every user-provided text is printed with `htmlspecialchars()` |
| Session hijacking | `session_regenerate_id()` on login and password change; `HttpOnly` + `SameSite` cookie |
| Malicious upload | image type checked from the file content (`getimagesize`), random file name, no script execution in `uploads/` |
| Stale permissions | the role is re-read from the database on every request: a demoted or deleted user loses access immediately |
| Information leak | database errors go to the PHP log; visitors only see a neutral message |

More in [docs/architecture.md](docs/architecture.md) (in French).

## Run it locally

Requirements: PHP 8 with `pdo_mysql`, MySQL 8 (Laragon, XAMPP, WAMP or a plain install).

```bash
git clone https://github.com/lambiss12-hue/Xibaar_Yi.git
cd Xibaar_Yi

mysql -u root -e "CREATE DATABASE xibaar_yi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root xibaar_yi < database/database.sql
mysql -u root xibaar_yi < database/demo.sql      # optional: 15 more articles, comments, messages

php -S localhost:8000 -t public
```

Then open <http://localhost:8000>. Accounts: `admin` / `admin123` (administrator), `demo` / `demo1234` (read-only),
and with `demo.sql`, `fatou` or `moussa` / `redac123` (editors). Change these passwords on any public server.

The database settings are in [`includes/config.php`](includes/config.php) (`root` without password on port 3306;
set the `DB_PORT` environment variable for another port). For production, copy
[`includes/config.local.exemple.php`](includes/config.local.exemple.php) to `includes/config.local.php`.

## Project structure

```
public/          web root: the only folder exposed to the browser
  admin/         back-office (articles, categories, comments, messages, users, account)
  assets/        CSS, favicon, preview image
includes/        config, access control + CSRF, upload, shared header/footer
database/        schema, demo data, migrations
docs/            architecture, database schema, screenshots (in French)
```

## Credits

The first version was built as a team project at the **École Supérieure Polytechnique (ESP) de Dakar**
by **Salamba Diène** and **Fatoumata Mandioula Diallo**. Since then, the project has been maintained by
Salamba Diène: deployment, security hardening, bug fixes and the read-only demo mode.

## License

[MIT](LICENSE)
