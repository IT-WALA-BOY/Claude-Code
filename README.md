# Workflow Dashboard

A private, single-admin dashboard for tasks, schedule, goals, LinkedIn outreach, Figma work, income, expenses and a buy list.
Plain PHP 8.1+ and MySQL/MariaDB with no build step. Deploys as a zip + `.sql` to Hostinger or XAMPP, and can sit next to a WordPress site in a subfolder.

- Plan: [docs/plan.md](docs/plan.md)
- Design rules: [docs/design-rules.md](docs/design-rules.md) (Figma: *Task Management Dashboard*, page "Dashboard screens")
- Code standards: [docs/code-standards.md](docs/code-standards.md)
- Deploy: [docs/deploy.md](docs/deploy.md)

## Run locally

```bash
php -S 127.0.0.1:8080 tools/dev-router.php
```

Then open `http://127.0.0.1:8080/install.php` (needs a MySQL database). Tick "Load sample data" to try it with the data from the Figma screens.

## How it is built

- `index.php` routes pages and the JSON API. `install.php` runs once.
- `app/` holds the PHP: `core/` (database, auth, CSRF, routing), `lib/` (formatting, time, charts, UI helpers, optional AI), `repo/` (queries per module), `api/` (JSON handlers per module).
- `pages/` holds one template per screen, `partials/` the shell and dialogs.
- `assets/js/app.js` is the shared runtime; `assets/js/pages/*.js` add behaviour per screen. No JS libraries: drag and drop, charts, the Gantt timeline and calendars are in-house.
- Icons come from Lucide, built into one sprite by `tools/build-icons.mjs`.

## Status
- [x] Plan
- [x] Design system
- [x] Figma pilot (Overview screen)
- [x] Figma screens (all 14)
- [x] Build (all screens, tested in the browser)
- [x] Package for deploy (`sh tools/package.sh`)
- [x] Run on XAMPP 8.2.12 following `docs/deploy.md` (install, all screens, phpMyAdmin export and import)
- [x] Update the three design skills with what this project learned
