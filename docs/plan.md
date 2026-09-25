# Personal Workflow Dashboard: Plan

## Context
Muhammad Ahmad is a freelance UI/UX designer who works on Upwork, does LinkedIn outreach, designs in Figma, and is self-studying (e.g. the CXL mini degree). He needs one private dashboard to track tasks, expenses, goals, outreach and income.

**Deployment:** his friend deploys it the way he deployed an earlier dashboard. He builds it locally in XAMPP (`htdocs` + phpMyAdmin), zips the folder, exports the database as `.sql`, and uploads both to Hostinger. So this is a **plain PHP + MySQL app with no build step**. It can live in a subfolder next to the WordPress site (e.g. `yoursite.com/dashboard`).

**Process (the user's order):**
1. Plan.
2. Design system.
3. One small Figma pilot screen, refined until he's satisfied.
4. Key screens in Figma.
5. Build from Figma.

**Inputs:**
- `docs/context.md`: user context, dashboard-relevant parts only.
- `docs/design-references.md`: all 10 Figma notes, quoted exactly, with what each one means.
- Two `.md` skill files the user will provide next (one covers icons). These will be updated to match this plan and will then guide the design and build.

## Decisions so far
- **Clock:** US Pacific (`America/Los_Angeles`). "Today", due dates, the schedule and monthly totals all follow Pacific days. PKT is shown as a second clock and timezone column.
- **Money:** USD is primary. PKR is used for daily expenses, with an exchange rate set in Settings.
- **Light mode only** (Figma note 9).
- **Icons:** Lucide only, styled like the Tasklify reference (note 1). The stat-card icons sit in black rounded tiles.
- **Visual base:** Tasklify cards and icons + the Flowza layout and screens + FacilityFlow cleanliness. The kanban cards follow the "image 3" style (note 10).
- **UI copy:** short and direct. No em dashes. The design must not look generic or AI-made.
- **Declined extras:** Upwork proposal tracker, habits, savings goal, fee/net income.
- **AI:** the built-in wizards work with no API key. An "Ask Claude" button appears only if a key is added later.

---

## Tech stack
- **Backend:** PHP 8.x (no framework), PDO + MySQL/MariaDB, prepared statements everywhere.
- **Frontend:** server-rendered app shell with vanilla JS modules. Libraries are vendored in `assets/vendor/` (no CDN, no npm build):
  - **Alpine.js** for reactivity
  - **SortableJS** for all drag-and-drop: kanban cards, reordering lists, the tilted drag ghost, the dashed drop placeholder
  - **Chart.js** for charts: rounded bars (`borderRadius`), donuts with gaps (`spacing` + `borderRadius`), stacked horizontal bars
  - **Lucide** icons (the UMD build, or an SVG sprite generated from it)
- **Custom vanilla components**, so they match the Figma design exactly:
  - **Gantt timeline:** drag bars to move dates, drag edges to resize, today marker, Day / Week / Month / Quarter / Year zoom, rows grouped with a sidebar. The behavior is modelled on the Kibo UI Gantt.
  - **Calendars:** week grid with the current-time line and a second timezone column, month grid with dot pills, mini calendar.
- **Font:** Inter Display, self-hosted, with Inter as a fallback.
- **Optional AI:** a PHP cURL call to the Anthropic Messages API, used only if a key is saved in Settings.
- **Works on:** XAMPP and Hostinger shared hosting. `.htaccess` protects `app/`, `config.php` and `database/`.

## Folder structure
```
index.php            app shell (redirects to login if not authed)
login.php  logout.php
install.php          first run: DB creds + create the single admin, then locks itself
config.sample.php    copied to config.php (DB creds, app secret)
api/index.php        JSON router: ?r=tasks|expenses|goals|linkedin|figma|schedule|income|analytics|settings
app/                 db.php, auth.php, csrf.php, helpers.php, modules/*.php (one per resource)
assets/css/          tokens.css (from the Figma design system), app.css
assets/js/           app.js, components/ (gantt.js, calendar.js, kanban.js, charts.js), modules/*.js (one per screen)
assets/vendor/       alpine, sortable, chart.js, lucide
assets/fonts/        Inter Display
database/schema.sql  tables for a phpMyAdmin import; seed.sql has default categories
docs/                plan.md, context.md, design-references.md, deploy.md
.htaccess
```

## Login (single admin)
- `install.php` creates exactly one admin (`password_hash`) and then refuses to run again. There's no registration page.
- Session hardening: session id regenerated on login, httponly/secure/samesite cookies, idle timeout.
- A CSRF token is required on every API write.
- The login locks for 15 minutes after 5 failed attempts (`login_attempts` table).
- "Change password" in Settings.

## Shared UI patterns (from the Figma references)
- **Layout:**
  - Sidebar with MENU / WORK / GENERAL sections, 20 px Lucide icons, and the profile card at the bottom.
  - Top bar with the page title, search, bell, and both clocks (Pacific + PKT).
  - Soft gray canvas with white rounded cards and 1 px warm borders.
- **Stat card (Tasklify):** black icon tile, "View details >" link, value, label. **KPI card (Flowza):** title + `...` menu, big number, delta chip, footnote.
- **View switch** on the work screens: Board / List / Timeline / Calendar.
- **Board (kanban):**
  - Columns have a status circle, count badge, `+` and `...`, with "+ Add task" at the top or bottom.
  - Card anatomy:
    - a colored priority header band (Urgent red / Moderate orange / Low green)
    - a category tag
    - a title and short description, or a **checklist inside the card**
    - a segmented progress bar with %
    - due date, and comment/checklist counts
  - Drag between columns and reorder within them. The card tilts while dragging, and a dashed placeholder shows the drop spot.
- **List:**
  - Status groups with tinted header bars (blue / yellow / pink / green), each with a count and a `+`, collapsible.
  - Table columns have header icons. Rows can be reordered by drag handle.
- **Timeline (Gantt):**
  - Task list on the left, pastel bars on the right. Bars use the category pill colors (blue / green / pink / purple).
  - Controls: `< This Month >` and the Day…Year segmented control.
- **Pills:** one pastel set shared by status, priority, category, Gantt bars, calendar blocks and chart series.

## Screens and features
1. **Overview (Dashboard):** "Welcome back, Ahmad" + a black "Add task" button.
   - 4 KPI cards: pending tasks, $ earned this month vs the $10K target, spend this month, LinkedIn follow-ups due.
   - A rounded stacked bar chart of completed / new / overdue tasks.
   - An activity heatmap.
   - Today's agenda from the schedule, active goals as progress bars, and Figma tasks in progress.
2. **Tasks** (replaces "Tasks & Notes"). Every task is a card with an optional **checklist**, which covers the "note blocks with checkboxes" request.
   - Fields: category (LinkedIn, Upwork, Self Study, Figma, custom), status (To do / In progress / Review / Done / Later), priority, importance and urgency flags, start date, due date, estimated minutes, progress.
   - Views: **Board** (drag between columns and reorder), **List** (status groups), **Timeline** (Gantt, drag to reschedule), **Calendar** (month grid with dot pills).
   - Filter chips: All / To do / In progress / Completed / Overdue, plus category filters.
3. **Expenses:** date, category (Upwork Connects, Daily, custom), amount, currency (USD / PKR), quantity (e.g. number of connects), note.
   - Monthly totals per category in USD and PKR.
   - Rounded charts: month-over-month and a category donut.
4. **Priority buy-list (Eisenhower):** a 2×2 grid (Important+Urgent, Important+Not urgent, Not important+Urgent, Neither).
   - Drag items between the quadrants. Each item has an estimated cost.
   - "Mark bought" turns the item into an expense.
5. **Goals & Progress:** long-term goals with nested sub-goals and a progress log.
   - A **Timeline (Gantt)** view of the sub-goals.
   - **Breakdown wizard** (no AI) asks:
     - the goal type
     - the number of courses and each one's duration, or the total hours (e.g. CXL: 9 courses, 64 h)
     - a deadline *or* the hours per day
     - rest days per week, and a buffer %
   - It then calculates the daily hours or the finish date, and generates weekly milestones and per-course targets. It can push the daily study block into the Schedule.
   - Optional "Ask Claude" when a key is set.
6. **Income goal ($10K/month):** income entries (date, amount, currency, source, client).
   - A progress ring, a required-pace line vs actual, and "on track / behind by $X".
7. **LinkedIn:**
   - (a) **Post planner:** date, topic, status draft → scheduled → posted, and the URL. It also shows on the calendar.
   - (b) **Prospect pipeline:** Shortlisted → Connected → Teardown sent → Replied → Paid audit → Redesign pitch → Won / Lost.
     - Compact cards you can drag and reorder (pipeline reference, light mode): name, company, stage fraction (3/7), a warning icon when the follow-up is overdue, market (US / UK), industry, and a "why neglected" note.
     - Prospects due for follow-up appear on the Overview.
   - (c) **Daily outreach counters** vs targets. The default time budget is 1 to 1.5 h/day.
8. **Figma:** projects (client, due date, Figma link) with tasks. It uses the same Board / List / Timeline views, and the project % is computed from its tasks.
9. **Schedule (calendar + schedule maker):** follows the Schedule and Flowza references.
   - **Left panel:** a mini month calendar with today circled, then an **agenda** (Today / Tomorrow / date) listing each block's icon, time, title and its task link.
   - **Main area:** a **week grid** (and Day / Month) with pastel blocks that have a colored left border and time chip, the **current-time line**, today's column highlighted, and a **Pacific + PKT timezone column**.
   - Drag blocks to move them and drag the edges to resize. Category filter pills and a "+ Add block" button.
   - **Maker:**
     - Inputs: availability windows (which can cross midnight PKT), fixed blocks (study from the goal plan, LinkedIn 1 to 1.5 h) and open tasks.
     - It fills the slots greedily: Important+Urgent first, then the earlier deadline, then Important+Not urgent. Pomodoro is optional, and tasks that don't fit spill to the next day.
     - "Regenerate" and mark-done.
   - Optional "Ask Claude to optimise my day".
10. **Analytics** (new, from the Analytics reference). The charts must match that style **exactly**.
    - Tabs: Tasks / Schedule / Money / Outreach, plus the Day…Year segmented control.
    - A KPI table (ahead / behind schedule, % complete, tasks to do, overdue).
    - A **donut with rounded, gapped segments** and callouts + legend counts (tasks by status).
    - **Rounded horizontal progress bars** per goal or project.
    - A **Time chart:** diverging rounded bars, behind vs on time.
    - A **stacked rounded bars chart:** completed / remaining / overdue per category.
    - Money tab: spend by category and income vs target. Outreach tab: the pipeline funnel and daily counters.
11. **Settings:** timezone (default Pacific) + a second clock (PKT), the exchange rate, the monthly income target, categories and their colors, daily LinkedIn targets, availability windows, the optional API key (server-side only), JSON export/import, and change password.

## Database tables (`database/schema.sql`)
- **Setup and auth:** `settings`(key, value), `admin`(single row), `login_attempts`
- **Tasks:**
  - `categories`(name, color, scope)
  - `tasks`(title, description, category_id, status, priority, important, urgent, start_date, due_date, est_minutes, progress, position, project_id null)
  - `task_checklist`(task_id, text, done, position)
- **Money:** `expenses`, `buy_items`(quadrant, est_cost, position, bought_expense_id), `income`
- **Goals:** `goals`(parent_id, type, target, unit, start, deadline, status, position), `goal_logs`
- **LinkedIn:** `li_posts`, `li_prospects`(stage, position, next_followup, market, industry, notes), `li_daily`
- **Figma:** `figma_projects` (its tasks live in `tasks.project_id`)
- **Schedule:** `availability`, `schedule_blocks`(date, start, end, title, color, source_type, source_id, done)

---

## Roadmap
1. **Plan:** done. Context and design references are captured.
2. **Skill files:** the user sends 2 `.md` files (one covers icons). Update them to match this plan and the references, and save them in the repo under `.claude/skills/`.
3. **Design system in Figma**, in the user's file or a new page:
   - tokens: light-mode colors, the pastel pill set, the Inter Display type scale, 4/8 spacing, radius, borders, shadows
   - Lucide icon rules
   - core components: sidebar, top bar, stat card, KPI card, pills, buttons, segmented control, view switch, kanban column + card (all states, including dragging), list group, table row, Gantt bar, calendar block, mini calendar, agenda item, chart styles, inputs, modal
4. **Figma pilot:** the Overview screen. Screenshot, iterate until approved.
5. **Key Figma screens:** Login, Tasks (Board + Timeline), Schedule, Analytics, LinkedIn pipeline, Goals + wizard, Expenses. The other screens reuse these patterns.
6. **Build**, following the Figma designs:
   - (a) skeleton, install, login, layout, tokens.css, icons
   - (b) Tasks (all 4 views) and Overview
   - (c) Schedule + maker
   - (d) Expenses, buy-list, income goal
   - (e) Goals + wizard, LinkedIn, Figma
   - (f) Analytics, Settings, optional AI
7. **Package:** zip + `schema.sql` + `docs/deploy.md` for the friend.

## Verification (build phase)
- PHP built-in server + MariaDB in the container. `php -l` on every file.
- Playwright click-through:
  - install → login
  - create a task with a checklist → drag it across columns → reschedule it on the Timeline
  - add an expense
  - run the goal wizard (64 h / 30 days)
  - generate a schedule → check that the time line and PKT column are right
  - Analytics charts render with rounded shapes
- Compare Playwright screenshots against the Figma screens.
- Security: 5 wrong passwords trigger the lockout, a write without CSRF is rejected, `/app/` `/config.php` `/database/` return 403, and `install.php` refuses to run twice.
- A fresh phpMyAdmin import of `schema.sql` works.
- Commit and push each phase to `claude/focused-bardeen-ls5r7i`.
