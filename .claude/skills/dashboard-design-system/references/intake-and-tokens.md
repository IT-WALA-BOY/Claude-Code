# Intake and tokens — /dashboard-design-system

## 1. Sources to read before asking anything

| Source | How | What you take |
|---|---|---|
| Brief / PRD (.docx, .md, .pdf) | Extract text (python-docx or the docx/pdf skill) | §2 items |
| Live app | Browser pane: `getComputedStyle` over `:root` CSS variables, font stack, screenshots of key pages | Current tokens, naming, pain points |
| Codebase | Grep the main stylesheet for `:root`, component class names, the icon sprite | CSS variable names to keep as code syntax |
| Reference kits (optional) | Read-only `use_figma` on the community file | Accent hex, type scale, radii, card treatment |

Proven references: **Koala UI Free** `CfPwhOt0XCKiQLi2rQjcnN` (presentation, DM Sans scale, Phosphor Bold icons) · **Designo LMS Dashboards** `rU7JqyQw7cufujtXO9jCxt` (orange `#FF4B00`, sidebar CTA feel) · **SnowUI Dashboard Design System** `kaGQdYohgp9XkCfrSkaaKp` (layout density, neutral chrome).

## 2. Brief extraction (print a 5-line summary)

1. **Product and primary viewer** of the flagship dashboard.
2. **The one question** the dashboard answers (e.g. "What did we book, and what did it earn?").
3. **Top objects** by frequency (bookings, invoices, orders, tickets…) → the table.
4. **Money and status vocabulary** — exact labels (Client charge, Vendor cost, Receivable…), status words (Pending, Issued, Overdue…) → badges and copy.
5. **What needs action** (overdue bills, unpaid refunds, pending approvals) → the Needs-attention list.
Also note: roles and permissions (hide money columns without permission), multi-branch/workspace context (switcher), currency and date formats, required states.

## 3. Token set

### 3.1 `Primitives` (mode `Value`, scopes `[]`)
| Group | Tokens (example = orange) |
|---|---|
| Accent | `orange/50 #FFF4EE` · `orange/100 #FFE4D6` · `orange/500 #FF4B00` · `orange/600 #E64400` · `orange/700 #C23A00` — generate from the chosen hex with `makeShades()` and keep 50/100/500/600/700. Name the group after the hue (`blue/…`), not "accent" |
| Neutral (Koala) | `white #FFFFFF` · `gray/25 #FAFAFA` · `gray/50 #F5F5F5` · `gray/100 #F0F0F0` · `gray/200 #E5E5E5` · `gray/300 #D4D4D4` · `gray/500 #767676` · `gray/700 #575757` · `gray/900 #1A1A1A` |
| Status | `green/50 #F0FDF4` `green/700 #15803D` · `yellow/50 #FEFCE8` `yellow/700 #A16207` · `red/50 #FEF2F2` `red/700 #B91C1C` · `blue/50 #EFF6FF` `blue/700 #1D4ED8` |
| Overlay | `overlay/scrim` `#1A1A1A` @ 40% |
When the accent is itself yellow/amber, change warning to a distinct hue family, so warning never reads as brand.

### 3.2 `Color` (semantic, mode `Light`)
| Token | → Primitive | Scope | Use |
|---|---|---|---|
| `bg/canvas` | gray/50 | FRAME_FILL, SHAPE_FILL | app background behind cards |
| `bg/surface` | white | 〃 | cards, sidebar, dialogs, inputs |
| `bg/subtle` | gray/25 | 〃 | table heads, read-only fields |
| `bg/muted` | gray/100 | 〃 | hover rows, nav hover, chart tracks, count pills |
| `bg/accent` | accent/500 | 〃 | primary CTA, brand mark, one highlighted datum |
| `bg/accent-hover` | accent/600 | 〃 | CTA hover |
| `bg/accent-soft` | accent/50 | 〃 | urgent count, avatar, selected chip |
| `bg/positive-soft` · `bg/warning-soft` · `bg/negative-soft` · `bg/info-soft` | status/50 | 〃 | badges, alert tiles |
| `bg/negative` | red/700 | 〃 | destructive toast |
| `bg/inverse` | gray/900 | 〃 | tooltip, default toast |
| `bg/scrim` | overlay/scrim | 〃 | modal/drawer backdrop |
| `bg/skeleton` | gray/200 | 〃 | loading bars |
| `text/primary` · `text/secondary` · `text/muted` | gray/900 · gray/700 · gray/500 | TEXT_FILL | ≥ 4.5:1 on white and canvas (muted #767676 = 4.54) |
| `text/accent` | accent/700 | TEXT_FILL | orange links/text on white (≥ 4.5:1) |
| `text/on-accent` | white | TEXT_FILL | CTA label |
| `text/positive` · `text/warning` · `text/negative` · `text/info` | status/700 | TEXT_FILL | credit, pending, owed/overdue, info |
| `icon/primary` · `icon/secondary` · `icon/muted` · `icon/accent` · `icon/on-accent` · `icon/positive` · `icon/warning` · `icon/negative` | mirror the text tokens (`icon/accent` → accent/500) | SHAPE_FILL, STROKE_COLOR | |
| `border/default` · `border/strong` · `border/hover` | gray/200 · gray/300 · gray/500 | STROKE_COLOR | |
| `border/accent` · `border/accent-soft` · `border/negative` · `border/warning` | accent/500 · accent/100 · red/700 · yellow/700 | STROKE_COLOR | |
| `focus/ring` | accent/100 | STROKE_COLOR, EFFECT_COLOR | |
Code syntax: reuse the product's CSS variable names when they exist (e.g. `bg/accent` → `var(--a)`); otherwise `var(--color-bg-accent)`.

### 3.3 `Dimensions`
`spacing/xs 4 · sm 8 · md 12 · lg 16 · xl 24 · 2xl 32 · 3xl 48` (GAP) · `radius/sm 6 · md 8 · lg 16 · full 999` (CORNER_RADIUS)

### 3.4 Text styles (DM Sans; DM Mono for account/ticket numbers)
| Style | Weight | Size/line | Tracking | Use |
|---|---|---|---|---|
| `Title/Display` | SemiBold | 40/48 | −3% | the one hero figure |
| `Title/Stat` | SemiBold | 28/36 | −2% | position figures |
| `Title/Page` | SemiBold | 24/32 | −2% | page title / greeting |
| `Title/Section` | SemiBold | 16/24 | −3% | card and dialog titles |
| `Title/Brand` | Bold | 16/24 | −3% | sidebar brand |
| `Body/Regular` · `/Medium` · `/Semibold` | 400/500/600 | 14/20 | −3% | UI text, buttons, strong cells |
| `Meta/Regular` · `/Medium` · `/Semibold` | 400/500/600 | 12/16 | −3% | sub-lines, labels, badges |
| `Label/Small` | Medium | 12/16 | −2% | KPI labels, table heads, nav group labels — sentence case |
| `Mono/Regular` | DM Mono | 12/16 | 0 | IBAN, PNR, IDs |

### 3.5 Effects
`Shadow/Card` 0 1 2 #1A1A1A 4% (cards, active nav pill, switcher) · `Shadow/Menu` 0 8 24 −4 8% + 0 1 2 4% (menus, popovers, floating reminders) · `Focus/Ring` 0 0 0 3 `focus/ring`.

## 4. Contrast rules to report
- Text tokens ≥ 4.5:1 on `bg/surface` and `bg/canvas`; status text ≥ 4.5:1 on its soft tint.
- `text/on-accent` on `bg/accent`: bright accents (orange `#FF4B00` = 3.4:1) fail AA for 14px. Ship semibold labels and **offer** the 700 shade for CTA fills. Record the user's decision.
- Icons and graphics ≥ 3:1 (`icon/accent` can sit at 500).
