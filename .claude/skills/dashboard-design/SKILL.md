---
name: dashboard-design
description: Design every screen of a dashboard, back-office, admin or SaaS product in Figma once its foundations exist. It covers the sitemap from the brief, a shared app shell (side panel and header), and proven page archetypes (overview, balances list, drawer over list, form with sticky summary, multi-section builder, master-detail, accounts, bills, reports, admin with permissions, sign-in). It also covers numbers that reconcile across screens, an accent budget, per-screen QA, and documentation frames laid out on one page. Builds on /dashboard-design-system (tokens, components, flagship dashboard). Use when the user runs /dashboard-design or asks to design the remaining pages, CRUD flows, list/detail/form screens, reports or settings of a dashboard in Figma.
---

# /dashboard-design — the rest of the product, screen by screen

The Al Qafla Travel back-office proved this workflow. After the flagship dashboard (from `/dashboard-design-system`), ten more screens were built on the same page, from the same shell and components, with numbers that reconcile from screen to screen. This skill is how to repeat that for any product.

## Relationship to /dashboard-design-system (read it, don't repeat it)

`/dashboard-design-system` owns the **foundations and the flagship screen**. This skill owns **everything after**. Before building, read these files from the sibling skill:

| File | Why you need it here |
|---|---|
| `../dashboard-design-system/SKILL.md` | Non-negotiables (neutral chrome, one accent, sentence-case labels, radius 6/8/16) |
| `../dashboard-design-system/references/intake-and-tokens.md` | Token names (`bg/*`, `text/*`, `icon/*`, `border/*`), text styles, effects, contrast rules |
| `../dashboard-design-system/references/components-spec.md` | Component inventory and variant names you will instance |
| `../dashboard-design-system/references/dashboard-recipe.md` | Sidebar, header row, card and chart measurements. Every screen here inherits them |
| `../dashboard-design-system/references/presentation.md` | Koala documentation frame, used to wrap each screen |
| `../dashboard-design-system/references/figma-gotchas.md` | Plugin API traps |
| `../dashboard-design-system/scripts/helpers.js` | Colour maths, chart paths, icon import, audits |

If the file has **no** design system yet, stop and run `/dashboard-design-system` first, or ask the user whether to.

Load `figma:figma-use` before any `use_figma` call (pass `skillNames: "figma-use,dashboard-design"`), and `saas-ui-design` for the per-screen audit.

This skill's own references:
- `references/page-archetypes.md` — 11 screen recipes with measurements, content slots and pitfalls
- `references/screen-kit.md` — the builder prelude (shell, card, table, pager, tabs, buttons) and the discovery script
- `references/qa-and-layout.md` — per-screen checklist, defects already found and their fixes, the page layout and audit scripts
- `scripts/screen-kit.js` — the prelude as a file to copy into `use_figma` calls

## Workflow

### Step 0 — Inventory (read-only, one call)
Run the discovery script from `screen-kit.md` on the target file. It returns the page holding the flagship dashboard, the flagship frame's structure (sidebar instance, header parts to clone), every component set with its **property keys** (`Label#7:0`, …) and variant options, plus token and style names. Record it in `<scratchpad>/dashboard-design-<product>.json`. Never guess IDs or keys.

### Step 1 — Sitemap and one sentence per page
From the brief's information architecture, list the screens still missing. Write one line per screen: **"<Viewer> comes here to <question> and to <action>."** Map each screen to an archetype in `page-archetypes.md`, then print the table to chat:

| Screen | Archetype | The question | Primary action | Nav item |
|---|---|---|---|---|

Default order: list screens first (they reuse the table kit), then forms and builders, then reports, then admin, then sign-in.

### Step 2 — Data bible (before drawing anything)
Write a small data sheet in the ledger file: clients, vendors, accounts, staff, and the key totals (receivable, payable, bank balances, month spend, gross and net profit). **Every screen reads from it**, so the same client owes the same amount on the dashboard, the ledger and the statement. Make the arithmetic reconcile (charges − received = balance; category rows sum to the total; bank cards sum to the total balance). Use real-world ugly data: long company names, refunds as minus figures, one settled row, one overdue row.

### Step 3 — Build each screen (1–2 `use_figma` calls per screen, strictly sequential)
1. Paste the prelude from `scripts/screen-kit.js` (fill in the IDs and keys from Step 0).
2. `shell()` creates a 1440 frame on `bg/canvas`, a Sidebar instance with **this screen's nav item set to Active** (nested `setProperties({State:'Active'})` works), and a header row (title, one-line context, actions).
3. Compose the archetype from cards, tables, tabs and buttons. Bind every colour to tokens.
4. Place the frame 160px to the right of the previous screen.
5. Screenshot at 0.45–0.5 and check it against the per-screen checklist in `qa-and-layout.md`. Fix defects in the **next** call before starting the next screen.

### Step 4 — Group screens into flow sections (no documentation wrappers)
Product screens are **not** wrapped in Documentation Header/Footer frames. The user explicitly asked to remove them. Documentation frames belong only on foundation and component pages.
- Put every screen on the **same page as the flagship dashboard**, inside Figma **Sections**, one per user flow, the way website projects present flows. For example:
  - `01 · Sign in flow`
  - `02 · Dashboard`
  - `03 · Booking flow`
  - `04 · Receiving payments`
  - `05 · Paying out and bank`
  - `06 · Reports and administration`
- Name screens with flow numbers: `04.1 Ledgers — Clients owe us`, `04.2 Client statement — Record receipt`.
- Screens sit side by side in flow order: 160px gap, top padding 200 (room for the section label), side padding 160, bottom 160.
- All sections share one width (the widest flow) and their screens are **centred**. Sections are stacked vertically 240px apart.
- Use `layoutFlowSections()` from `qa-and-layout.md`.

### Step 4b — Promote repeated patterns to components
Any pattern built directly on a screen that appears more than once, or that another product would reuse, becomes a real component on its ❖ page. Examples: search field, notification button, pagination, section step, service tile, summary line, account card, bill due item, bill tile.
1. Clone one occurrence and run `figma.createComponentFromNode(clone)`.
2. Name the text layers semantically, add TEXT/BOOLEAN properties, and combine state variants.
3. Replace every on-screen occurrence with an instance (copy its texts into the properties, keep the index and FILL sizing).
4. Document it on the component page with an Overview row and its own frame.

### Step 5 — QA and handoff
Run the audits (unbound paints, empty icon instances, accent-touch count per screen, text overflow). Take a final screenshot of every wrapper. Update `design-rules.md` in the project folder with the per-page intent table. Reply with the file link, the screen list with one sentence each, what reconciles across screens, and follow-ups (mobile views, empty/loading states, modals not yet drawn).

## Non-negotiables

- **Same shell everywhere.** Sidebar instance plus a header row: `Title/Page`, a `Meta/Regular` muted context line (period, branch, freshness), actions on the right.
- **Accent budget.** The shell's accents are fixed and tiny: brand mark, active nav icon, one urgent nav count, avatar initials tint, unread dot. The **content** of a screen gets at most **two**: one primary CTA and one highlighted datum (the current chart bar, the top breakdown bar). Selected tabs, filter chips, checkboxes and step markers stay neutral. Use a neutral checkbox instead of an accent chip for filters like "Outstanding only".
- **One primary per view.** When a drawer or modal is open, the overlay holds the primary and the page underneath is dimmed by the scrim.
- **No component repeated just to fill space.** Each page answers its own question with its own composition: attention items with icon tiles on the dashboard, date tiles for bills, account cards for banks, a formula strip for profit.
- **Tables for anything scanned.** Use 44–60px rows, amounts right-aligned, status as soft badges, and actions behind `⋯`. End with a totals footer and pagination ("Showing 1–7 of 9 …").
- **Colour means state, never decoration.** Owed is red, credit is green, running balances are neutral, overdue dates are red text. Neutral share bars, with accent only on the single highlight.
- **Text never clips.** Long meta lines get `layoutSizingHorizontal='FILL'` and `textAutoResize='HEIGHT'`. Equalise card heights in a row. Leave search placeholders room (fields ≥ 280px wide).
- **Numbers reconcile across screens** (Step 2). If a figure changes, change it everywhere.
