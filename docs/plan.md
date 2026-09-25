# Personal Workflow Dashboard — Plan

## Context
The user is a freelance UI/UX designer who works on Upwork, does LinkedIn outreach, designs in Figma, and is self-studying (e.g. the CXL mini degree). They need one private dashboard to track tasks, expenses, goals, outreach and income. At the moment this work is spread across notes and memory.

Deployment constraint: the user's friend deploys the same way as for an earlier dashboard. He builds it locally in XAMPP (`htdocs` folder + phpMyAdmin), zips the folder, exports the database as `.sql`, and uploads both to Hostinger. So the dashboard is a **plain PHP + MySQL app with no build step**. It can live in a subfolder next to their WordPress site on Hostinger (e.g. `yoursite.com/dashboard`).

Agreed process (the user's order):
1. Finish this plan.
2. Define the design system.
3. Do one small Figma pilot screen and refine it until the user is satisfied.
4. Design the key screens in Figma.
5. Build from the Figma designs.

The repo `IT-WALA-BOY/Claude-Code` is empty, so there's no existing code to reuse.

Memory: this session has **no access to the user's claude.ai memory**. The user will paste their personal context (role, niche, rates, routine, ICP), and it goes into `docs/context.md` so every later step uses it.

---

## Tech stack
- **Backend:** PHP 8.x (no framework), PDO + MySQL/MariaDB, prepared statements everywhere.
- **Frontend:** server-rendered app shell with vanilla JS modules. Vendored libraries go in `assets/vendor/` (no CDN, no npm build):
  - Alpine.js for reactivity
  - SortableJS for drag-and-drop
  - Chart.js for charts
- **Optional AI:** PHP cURL call to the Anthropic Messages API, used only if the user enters an API key in Settings. All AI buttons stay hidden until then, and the built-in wizards work without it.
- **Works on:** XAMPP locally, and Hostinger shared hosting (PHP + MySQL) with an `.htaccess` that protects `app/`, `config.php` and `database/`.

## Folder structure (to create)
```
index.php            app shell (redirects to login if not authed)
login.php  logout.php
install.php          first run: DB creds + create the single admin, then locks itself
config.sample.php    copied to config.php (DB creds, app secret)
api/index.php        JSON router: ?r=tasks|expenses|goals|linkedin|figma|schedule|income|settings
app/                 db.php, auth.php, csrf.php, helpers.php, modules/*.php (one per resource)
assets/css/          tokens.css (from the Figma design system), app.css
assets/js/           app.js, modules/*.js (one per screen)
assets/vendor/       alpine.min.js, sortable.min.js, chart.umd.min.js
database/schema.sql  tables for a phpMyAdmin import; seed.sql has default categories
docs/                context.md, deploy.md (step-by-step for the friend)
.htaccess
```

## Login (single admin)
- `install.php` creates exactly one admin (`password_hash`) and then refuses to run again. There's no registration page anywhere.
- Session hardening: session id regenerated on login, httponly/secure/samesite cookies, idle timeout.
- A CSRF token is required on every API write.
- Brute-force protection: a `login_attempts` table locks out the login for 15 minutes after 5 failed attempts.
- A "Change password" option in Settings.

## Screens and features
1. **Overview:** today's pending tasks grouped by category, today's schedule blocks, and the monthly income target (earned / target / days left / $ needed per day). It also shows:
   - LinkedIn follow-ups due today
   - active goal progress bars
   - Figma tasks in progress
   - this month's spend (Upwork connects vs daily expenses)
2. **Tasks & Notes:** a board of note blocks (like Google Keep / Trello) with checklist items.
   - Create, edit, delete and drag blocks (SortableJS), and drag items between blocks.
   - Categories with colors: LinkedIn, Upwork, Self Study, Figma, plus custom ones.
   - Per item: due date, estimated minutes, importance/urgency flags (these feed the schedule maker), and a done checkbox.
   - Filters for category, status and due date.
3. **Expenses:** entries with date, category (Upwork Connects, Daily, custom), amount, currency (USD or local), quantity (e.g. number of connects), and a note.
   - Monthly view with totals per category, in USD and local currency using an exchange rate set in Settings.
   - Charts: month-over-month and category split.
4. **Priority buy-list (Eisenhower):** items to buy go into a 2×2 grid: Important+Urgent, Important+Not urgent, Not important+Urgent, Neither.
   - Each item has an estimated cost, and you drag it between quadrants.
   - "Mark bought" turns the item into an expense entry.
5. **Goals & Progress:** long-term goals with nested sub-goals and a progress log.
   - **Breakdown wizard** (built in, no AI) asks:
     - the goal type (course/learning, hours, count, money)
     - the number of courses and each one's duration, or the total hours (e.g. CXL: 9 courses, 64 h)
     - a deadline *or* the hours per day you can give
     - rest days per week, and a buffer %
   - It then calculates the daily hours, or the finish date if you fixed the hours instead. Example: 64 h ÷ 26 study days ≈ 2.5 h/day.
   - It generates sub-goals: weekly milestones, and per-course targets proportional to each course's length.
   - It can push the daily study block into the schedule maker.
   - Optional: an "Ask Claude" button suggests a breakdown (only when an API key is set).
6. **Income goal ($10K/month):** income entries (date, amount, currency, source: Upwork / direct / other, client).
   - Monthly target set in Settings.
   - A progress ring, a required-pace line vs actual, and "on track / behind by $X".
7. **LinkedIn:**
   - (a) **Post planner:** planned date, topic, and status draft → scheduled → posted, plus the post URL.
   - (b) **Prospect pipeline (kanban):** Shortlisted → Connected → Messaged → Follow-up 1 → Follow-up 2 → Replied → Call → Won/Lost.
     - Each prospect has a profile URL, ICP notes, and a next follow-up date.
     - Prospects due for follow-up appear on the Overview.
   - (c) **Daily outreach counters:** new reach-outs, messages, follow-ups, posts. Tracked against daily targets.
8. **Figma:** projects (client, due date) with task lists. Each task has status To do / In progress / Review / Done, and project progress % is computed from its tasks. It can also hold a Figma file link.
9. **Schedule maker:**
   - Inputs:
     - weekly availability windows (e.g. Mon–Sat 10:00–14:00, 16:00–20:00)
     - fixed recurring blocks (study 2.5 h from the goal plan, LinkedIn 30 min)
     - open tasks with their estimated minutes, importance/urgency and deadline
   - The planner fills the free slots greedily:
     - order: Important+Urgent first, then earlier deadline, then Important+Not urgent
     - optional Pomodoro breaks
     - tasks that don't fit spill to the next day
   - Output is a day/week timeline where you can drag blocks and mark them done, with a "Regenerate" button.
   - Optional: "Ask Claude to optimise my day" when an API key is set.
10. **Settings:** currency and exchange rate, monthly income target, categories, daily LinkedIn targets, availability windows, the optional Anthropic API key (stored server-side, never sent to the browser), export/import of the data as JSON, and change password.

## Database tables (`database/schema.sql`)
- **Setup and auth:** `settings`(key, value), `admin`(single row), `login_attempts`
- **Tasks & Notes:** `categories`(name, color, scope), `note_blocks`, `note_items`
- **Money:** `expenses`, `buy_items`(quadrant, est_cost, bought_expense_id), `income`
- **Goals:** `goals`(parent_id, type, target, unit, start, deadline, status), `goal_logs`
- **LinkedIn:** `li_posts`, `li_prospects`, `li_daily`
- **Figma:** `figma_projects`, `figma_tasks`
- **Schedule:** `availability`, `schedule_blocks`(date, start, end, source_type, source_id, done)

---

## Roadmap (in the user's order)
1. **Plan:** this document. The user adds their personal context → `docs/context.md`.
2. **Design system:**
   - Check the user's Figma libraries (`get_libraries` / `search_design_system`). Reuse their existing kit if they have one.
   - Otherwise define: color tokens (light + dark), type scale, 4/8 px spacing, radius, elevation, and core components (sidebar nav, top bar, stat card, progress ring/bar, task card, checklist item, kanban column, table row, modal, form fields, buttons, tags).
3. **Figma pilot:** design one screen (**Overview**, desktop) with the design system, share a screenshot, and iterate until the user is happy.
4. **Key Figma screens:** Login, Tasks & Notes, Expenses, Goals + wizard, LinkedIn pipeline, Schedule. Add a mobile view of Overview. The other screens reuse these patterns.
5. **Build**, following the Figma designs:
   - (a) skeleton, install, login, layout, tokens.css
   - (b) Tasks & Notes and Overview
   - (c) Expenses, buy-list, income goal
   - (d) Goals + wizard, LinkedIn, Figma
   - (e) Schedule maker, Settings, optional AI
6. **Package:** the zip of the folder + the `schema.sql` export + `docs/deploy.md` for the friend. Deploy steps: upload to `public_html/dashboard`, create the MySQL DB in hPanel, import the SQL, edit `config.php` (or run `install.php`).

## Verification (build phase)
- Run locally with PHP's built-in server + MariaDB/MySQL in the container:
  - `php -S localhost:8000`
  - check syntax with `php -l` on every file
- Use Playwright (pre-installed Chromium) to click through: install → login → create a task block + drag → add an expense → run the goal wizard (64 h / 30 days) → generate a schedule → check the Overview figures.
- Security checks:
  - a wrong password 5× triggers the lockout
  - an API write without the CSRF token is rejected
  - `/app/`, `/config.php` and `/database/` return 403
  - `install.php` refuses to run a second time
- Test that a fresh phpMyAdmin import of `schema.sql` works, which is the path the friend will use.
- Commit and push each phase to `claude/focused-bardeen-ls5r7i`.
