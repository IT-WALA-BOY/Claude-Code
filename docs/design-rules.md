# Design rules: Workflow Dashboard

These rules apply to the Figma screens and to the PHP build. Give this file to any tool before it writes UI code.

Figma: https://www.figma.com/design/MpJESdIHTiE4uCqCdcnbi6/Task-Management-Dashboard (page `🎨 Design System`)

## Foundations

**Icon set:** Lucide only (lucide-static). Never mix in another set. No emoji anywhere in the UI.
**Icon stroke:** icons are live 24px strokes with round caps. The weight comes from the `Icon` variable collection (`icon/stroke`): **Regular 1.25 (default)**, Light 1, Thin 0.85. Switch a frame's mode and every icon inside follows. In CSS: `stroke-width: var(--icon-stroke)`.
**Icon sizes:**
- 16px in buttons, pills and meta lines
- 20px in navigation, 18px in card headers
- 24px only when an icon stands alone

**Icon color follows its label:**
- `text/secondary` label uses `icon/secondary`
- the active nav item uses `icon/accent`
- icons on black tiles use `icon/on-inverse`

**Font:** Inter Display in the build. The Figma variable `Typography/font/family` is set to "Inter" because the cloud environment can't load Inter Display. Change that variable to "Inter Display" (and the semibold weight to "SemiBold") on a machine that has the font.

| Role | Style | Size / line / weight |
|---|---|---|
| Hero figure (one per screen) | Title/Display | 40/48 medium |
| KPI value | Title/Stat | 32/40 medium |
| Page title | Title/Page | 24/32 medium |
| Welcome line | Title/Welcome | 22/28 medium |
| Card title | Title/Section | 16/24 medium |
| Body | Body/Regular, Medium, Semibold | 14/20 |
| Meta, labels | Meta/*, Label/Small | 12/16 |

- Labels are sentence case, except sidebar group labels, which use `Label/Caps` (12px medium, uppercase, +4% tracking), as in Flowza.
- Titles are medium weight, not semibold. Hierarchy comes from size and contrast.
- Money uses tabular figures.

**Spacing:** 4 / 8 / 12 / 16 / 24 / 32 / 48. **Radius:** 6 (pills), 8 (buttons, inputs, nav items), 16 (cards), full (avatars, dots, bars).
**Elevation:**
- Every card has a 1px `border/default` hairline plus `Shadow/Card` (0 1 2, 4%).
- `Shadow/Menu` is for popovers.
- `Shadow/Drag` is only for a card being dragged.

## Color

| Token | Value | Used for |
|---|---|---|
| bg/canvas | #F5F5F4 | app background |
| bg/surface | #FFFFFF | cards, sidebar, inputs |
| bg/muted | #EFEFED | tracks, counts, segmented control |
| border/default | #E4E3E0 | every hairline |
| text/primary | #111111 | headings, body |
| text/secondary | #3D3D3D | nav labels, links |
| text/muted | #6F6F6F | meta (4.6:1 on canvas) |
| bg/accent | #3D4EF5 | pointing only: active nav icon, brand mark, one urgent count, unread dot, one highlighted datum |
| bg/shell | #F5F5F4 | outer shell of every card |
| bg/inverse | #111111 | the Primary button, black stat tiles, tooltips |
| status soft/700 | green, yellow, red | status only |
| category/* | pastel blue, green, purple, pink, orange, yellow | data: task categories, Gantt bars, calendar blocks |
| chart/* | ink, periwinkle, steel, mint, rose | chart series |

**Category colors:** LinkedIn = blue, Upwork = green, Self study = purple, Figma = pink, Personal = orange, Admin = yellow.

**Card anatomy (Flowza):**
- Every card is a tinted shell (`bg/shell`, 1px border, radius 16, padding 4).
- The shell holds a title row (18px icon + Title/Section, actions on the right) and a white inner panel (radius 12, hairline, whisper shadow).
- KPI cards: number and delta chip in the panel, then a divider and one footnote whose lead figure carries the color.

**Delta chips:**
- The icon shows direction (trending up or down).
- The color shows whether it's good or bad: fewer pending tasks is green with a down arrow, higher spend is red with an up arrow.
- Radius 6.

**Accent budget per screen:**
- The shell uses the filled blue active nav item, one urgent count, the avatar and the unread dot. The brand mark is black.
- The content gets at most 2 accent touches, e.g. today's bar in a chart.
- The Primary button is black, not blue.

## Buttons

| Role | Style | Rule |
|---|---|---|
| Primary | black fill, white label | exactly one per view |
| Secondary | white + `border/strong` | header actions, row verbs |
| Quiet | text only, `text/secondary` | links and escape hatches |

Height 36 (Medium) or 32 (Small), horizontal padding 14 / 10, sentence-case labels.

## Action vocabulary

| Concept | The word | Never |
|---|---|---|
| Create a task | Add task | New task, Create |
| Remove permanently | Delete | Remove, Trash |
| Persist changes | Save | Update, Submit |
| Leave without saving | Cancel | Discard, Close |
| Follow up with a lead | Follow up | Ping, Nudge |
| Open detail | View details | See more, Details |

## Data formatting

- **No em dashes anywhere.** Empty values show `0` or "Not set".
- Time ranges read "9:30 to 11:00 PM".
- Truncate titles on one line with an ellipsis (the full text goes in a tooltip in the build).
- Money: USD like `$6,240`, local currency like `Rs 72,800`.
- Dates: `24 Sep`, and add the weekday in headers.
- The app clock is **US Pacific**. The header also shows the local time in Depalpur (PKT).

## Required states

Every data surface needs 4 states: empty (one sentence + one action), loading (a skeleton in the shape of the content), error (plain words + retry), and populated with realistic data.

## Motion

- **Allowed:** skeleton fade-in, completion check, transitions of 200ms or less, the tilt and dashed placeholder while dragging a kanban card.
- **Banned:** parallax, entry animations on cards.

## Per-page intent

| Page | Ahmad comes here to... |
|---|---|
| Overview | see what's due today, what's late, and whether September income is on pace for $10K |

Figma: page `🗒️ Dashboard screens`, frame `01 Overview · 1440` (v2). v1 is kept beside it for comparison.

## Visual references

`docs/design-references.md` holds all 10 Figma notes with their takeaways. Tasklify (icons, stat cards), Flowza (layout), FacilityFlow (cleanliness), the Analytics screen (rounded charts), and "image 3" (kanban cards).
