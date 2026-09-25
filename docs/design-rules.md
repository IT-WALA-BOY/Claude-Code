# Design rules: Workflow Dashboard

These rules apply to the Figma screens and to the PHP build. Give this file to any tool before it writes UI code.

Figma: https://www.figma.com/design/MpJESdIHTiE4uCqCdcnbi6/Task-Management-Dashboard (page `🎨 Design System`)

## Foundations

**Icon set:** Lucide only (lucide-static). Never mix in another set. No emoji anywhere in the UI.
**Icon sizes:** drawn at 24px with a 2px round stroke, outlined in Figma so they scale.
- 16px in buttons, pills and meta lines
- 20px in navigation and in the black stat tiles
- 24px only when an icon stands alone

**Icon color follows its label:**
- `text/secondary` label uses `icon/secondary`
- the active nav item uses `icon/accent`
- icons on black tiles use `icon/on-inverse`

**Font:** Inter Display in the build. The Figma variable `Typography/font/family` is set to "Inter" because the cloud environment can't load Inter Display. Change that variable to "Inter Display" (and the semibold weight to "SemiBold") on a machine that has the font.

| Role | Style | Size / line / weight |
|---|---|---|
| Hero figure (one per screen) | Title/Display | 40/48 semibold |
| Stat value | Title/Stat | 28/36 semibold |
| Page title | Title/Page | 24/32 semibold |
| Card title | Title/Section | 16/24 semibold |
| Body | Body/Regular, Medium, Semibold | 14/20 |
| Meta, labels | Meta/*, Label/Small | 12/16 |

- All labels are sentence case. No uppercase group labels.
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
| bg/inverse | #111111 | the Primary button, black stat tiles, tooltips |
| status soft/700 | green, yellow, red | status only |
| category/* | pastel blue, green, purple, pink, orange, yellow | data: task categories, Gantt bars, calendar blocks |
| chart/* | ink, periwinkle, steel, mint, rose | chart series |

**Category colors:** LinkedIn = blue, Upwork = green, Self study = purple, Figma = pink, Personal = orange, Admin = yellow.

**Accent budget per screen:**
- The shell uses the brand mark, the active nav icon, one urgent count, the avatar and the unread dot.
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

## Visual references

`docs/design-references.md` holds all 10 Figma notes with their takeaways. Tasklify (icons, stat cards), Flowza (layout), FacilityFlow (cleanliness), the Analytics screen (rounded charts), and "image 3" (kanban cards).
