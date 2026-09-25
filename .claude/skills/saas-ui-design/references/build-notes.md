# Building the design: fast, smooth, clean code

Learned by building the Workflow Dashboard (14 screens, plain PHP + MySQL, no build step, no JS libraries) from its Figma file. The rules apply to any stack.

## 1. The prompt for clean AI code is a file, not a sentence
Current guidance on AI-assisted coding agrees on three things: keep the rules in the repo, work in small chunks, and test and review each chunk before the next. So before the first line of code:
- Write `docs/code-standards.md`: goals in order (correct, fast, smooth, readable), the stack, the folder structure (one file per screen, one query file per module, no SQL in templates, no HTML in queries), language rules (strict types, prepared statements only, escape every printed value, CSRF on every write, GET never changes data), the motion rules below, and the per-chunk workflow.
- Point `CLAUDE.md` (or `.cursorrules`) at it and at `design-rules.md`.
- Per chunk: build one screen, lint every changed file, click through it in a real browser, compare with the Figma screenshot, re-read the diff for dead code and missing escapes, then commit.
- At the end, sweep for unused CSS classes and unused functions with a script (build dynamic class names like `'c-' . $color` into your allow list), em dashes in strings, and inline styles that are not CSS variables.

## 2. Tokens and icons
- Copy tokens from Figma with a read-only script that resolves every variable to hex. Never retype them. One class per category colour sets three custom properties (`--c-bg`, `--c-text`, `--c-bar`) so pills, bars and calendar blocks share one source.
- Icons: one SVG sprite built from the names the code actually uses (scan for any quoted kebab token that is an icon name, since names travel through arrays and helpers). Add `vector-effect="non-scaling-stroke"` to each shape and set `stroke-width: var(--icon-stroke)` so the Figma stroke modes carry over exactly.
- Self-host a variable font with the optical size axis (Inter's `opsz`) and `font-optical-sizing: auto`: large figures get the Display cut for free. Preload it.

## 3. Speed
- Render on the server. Charts can be HTML/SVG strings from the server (flex columns with rounded segments, SVG donuts with `stroke-linecap: round` and dash gaps); JS only adds tooltips. Nothing waits for a chart library.
- No libraries unless one earns its weight. Custom drag and drop, Gantt and calendars were about 2 to 3 KB gzipped each.
- Load each screen's module only on that screen. Pass the shared runtime into page modules (`init(root, app)`) instead of importing it, or a cache-busted URL (`app.js?v=123`) and a plain import (`app.js`) load it twice.
- Version asset URLs with the file's modified time and cache them for a year; send `no-cache` for unversioned URLs.
- Instant navigation: Speculation Rules prerender on hover (`eagerness: "moderate"`), exclude downloads and logout (`data-no-prerender`), and **drop and re-add the rules after every write** so a prerendered page never shows stale data.
- Measure: server time per page (aim under 50 ms) and first-visit weight (the Workflow build was about 100 KB gzipped including the font).

## 4. Smoothness (motion rules)
- Animate only `transform` and `opacity`. Durations: 120 ms hovers and presses, 180 ms menus and dialogs, 200 ms maximum otherwise. Enter with `cubic-bezier(.2,.8,.2,1)`, leave with `cubic-bezier(.4,0,1,1)`.
- **Optimistic UI:** change the DOM first, send the request, roll back and show a toast on failure. Ticks, counters and drags never wait for the network.
- **Drag and drop:** pointer events (mouse, pen, touch with a 220 ms long press so scrolling still works), the item moves to `position: fixed` with `translate3d(...) rotate(2deg) scale(1.02)`, a dashed placeholder marks the drop spot, neighbours slide with FLIP (record rects, move the placeholder, animate the difference with the Web Animations API), the item animates into the placeholder on drop, Esc cancels. Keep a keyboard alternative ("Move to" menu).
- Dialogs: `<dialog>` with `showModal()`, animate in and out (intercept `cancel` so Esc animates too), close on backdrop click.
- Page changes: cross-document View Transitions (`@view-transition { navigation: auto; }`) with a short fade of the main panel and the sidebar excluded.
- Respect `prefers-reduced-motion`: set the duration tokens to 0.

## 5. Structure that keeps growing screens clean
- One generic record dialog pattern: `[data-open="id"]` opens a form empty (with `data-fill` defaults), `[data-edit="id"]` fills it from the closest `[data-record]` JSON, repeating rows come from a `<template>`, delete comes from `data-delete-api`. Every add/edit form in the product reuses it. Check `data-edit` before `data-open`, or an item inside a clickable day cell opens "new" instead of itself.
- After a save, a soft refresh fetches the current URL and swaps the page and sidebar regions. The server stays the single source of truth.
- Store times as the app clock's wall time and compute "today" on the server; send today's date to the client in a meta tag instead of trusting the browser's time zone.
- For work that crosses midnight (night shifts), group by "work night", not by calendar date.

## 6. Test on the real stack
- Drive every screen and interaction with Playwright and fail on any console error or 4xx/5xx response. Include security checks: private folders return 403, API without a session returns 401, API without the CSRF token is refused, GET logout does nothing, the lockout triggers.
- **Test behind the production web server, not only the dev server.** Apache turned an unknown status code (419) into a 500; PHP's built-in server passed it through. Use standard codes (403 for CSRF).
- Test the deploy guide itself (for XAMPP: create the database in phpMyAdmin, run the installer, export `.sql`, import into a fresh database, hand-write `config.php`, sign in). XAMPP already has `htdocs/dashboard`, so do not tell users to install there.
- Phone width: find overflow by hiding each element in turn and watching `document.documentElement.scrollWidth`. A classic culprit is a screen-reader-only label (`position: absolute`) inside a table header, positioned against the page instead of the scroll container: give the scroll container `position: relative`.
- Hide secondary chrome (the second clock) below 520 px rather than squeezing the page title.
