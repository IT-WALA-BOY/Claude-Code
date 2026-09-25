# Workflow Dashboard

Private single-admin dashboard. Plain PHP 8 + MySQL, no build step, deployed as a zip + .sql to Hostinger.

Before writing code, read:
- `docs/code-standards.md` (structure, PHP/JS rules, motion rules, workflow)
- `docs/design-rules.md` (tokens, copy, states)

Run locally: `php -S 127.0.0.1:8080 tools/dev-router.php` (needs MySQL; see `docs/deploy.md`).
Never use em dashes in UI copy, docs or commit messages.
