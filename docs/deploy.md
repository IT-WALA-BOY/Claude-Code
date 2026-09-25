# Deploy guide

The dashboard is plain PHP 8.1+ and MySQL (or MariaDB). There is no build step. It can live at the domain root or in a subfolder next to WordPress, for example `yoursite.com/dashboard`.

## 1. Make the package

```bash
sh tools/package.sh
```

This writes `dist/workflow-dashboard.zip` with only the files the server needs (no docs, tools or Git history).

## 2. Try it locally with XAMPP (optional)

1. Unzip into `C:\xampp\htdocs\dashboard`.
2. In phpMyAdmin, create an empty database, for example `dashboard` (collation `utf8mb4_unicode_ci`).
3. Open `http://localhost/dashboard/install.php`.
4. Fill in the database details (XAMPP default: user `root`, empty password), your name, username and password. Tick "Load sample data" if you want to try it with example data.
5. Sign in at `http://localhost/dashboard/login`.

## 3. Put it on Hostinger

1. **hPanel > Databases > MySQL Databases:** create a database and a user, and give the user all privileges on it. Note the host (usually `localhost`), database name, user and password.
2. **hPanel > Files > File Manager:** open `public_html`, create a folder `dashboard`, upload the zip into it and extract it. Next to WordPress is fine: WordPress keeps working, and this folder has its own `.htaccess`.
3. Open `https://yoursite.com/dashboard/install.php` and fill in the form. The installer creates the tables and your admin account, writes `config.php`, and then locks itself.
4. Sign in at `https://yoursite.com/dashboard/login`.

If the installer says it could not write `config.php`, it shows the file contents. Create `config.php` next to `index.php` in the File Manager and paste them in.

## 4. Moving data from XAMPP to Hostinger

This is the same flow your friend uses:

1. On XAMPP, phpMyAdmin > your database > **Export** > Quick > SQL. This gives a `.sql` file with all your data.
2. Zip the `htdocs/dashboard` folder **without** `config.php` (the database details differ on Hostinger).
3. On Hostinger, create the database (step 3.1), then phpMyAdmin > **Import** the `.sql` file.
4. Upload and extract the zip, then copy `config.sample.php` to `config.php` and fill in the Hostinger database details and a long random `secret`.
5. Do not run `install.php`: the imported data already has your admin account.

For a fresh, empty install, `database/schema.sql` also imports cleanly in phpMyAdmin (then run `install.php` to create the admin).

## Security checklist

- HTTPS on (Hostinger gives free SSL). Session cookies are marked secure automatically on HTTPS.
- `.htaccess` blocks `app/`, `pages/`, `partials/`, `database/`, `storage/`, `tools/`, `docs/` and `config.php`. Check that `https://yoursite.com/dashboard/config.php` returns 403.
- One admin account only. After 5 wrong passwords, sign-in locks for 15 minutes.
- The optional Anthropic API key is stored in the database and only used by the server.
- Back up from Settings > Data, or with phpMyAdmin Export.

## Requirements

- PHP 8.1 or newer with `pdo_mysql`, `mbstring`, `json` (and `curl` only for the optional AI helper).
- MySQL 5.7+ or MariaDB 10.3+.
- Apache with `mod_rewrite` (Hostinger and XAMPP both have it).

## Updating

Upload the new files over the old ones, keeping `config.php` and `storage/`. Asset URLs carry a version stamp, so browsers pick up new CSS and JS straight away.
