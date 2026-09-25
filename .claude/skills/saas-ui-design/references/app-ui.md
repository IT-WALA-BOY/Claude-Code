# In-app UI: dashboards, tables, forms, panels

Everything here assumes the core rule from SKILL.md: color and visual weight are information, not decoration.

## 1. Start from intent, not from components

Most people open a blank page and start thinking about cards, icons, and where the sidebar goes. That is starting at the end.

For each screen, write one sentence: *"The user came here to ____."* Then:

- Everything that serves that sentence stays.
- Everything that does not, moves to the page where it belongs or is deleted.
- A page called "Guests" is a list of guests. It is not a place for total-guests-this-year, revenue charts, or team analytics: those live on the pages named after them.

A second intent justifies more functionality. Someone who wants a holiday but does not know where is *browsing*, not searching: that is what filters are for. Intent expanding is the only valid reason for the surface to expand.

## 2. Color system

- **Base:** near-white surfaces (`#FCFCFC`–`#FFFFFF`), hairline borders (`1px`, ~8% black), text in two or three greys. Dark mode: near-black surfaces, same discipline.
- **Accent:** exactly one. Used for the primary action and nothing else.
- **Semantic:** green / amber / red reserved for status. Keep them muted: a pale tint background with a thin border of the same hue, not a saturated block.
- **Data carries the color.** Charts, status pills, category dots. Chrome stays grey.

Action hierarchy inside a single view: this is how a well-designed app steers the user without instructions:

| Role | Treatment | Example |
|---|---|---|
| Primary (the job) | Solid accent fill | Send email |
| Secondary (useful, not the job) | White/neutral with border | Save draft |
| Tertiary / escape | Text only, muted | Discard |

One primary per view. If two things look equally primary, neither is.

## 3. Density and repeated components

AI is worst at dense repeated components: a list of cards is where it dumps every button, chip, and timestamp it can think of. Compress with these moves:

- Collapse a row of action buttons into a single `⋯` overflow menu. Keep at most one inline action if it is used constantly.
- Replace text chips with icons, or with one small colored dot plus plain text ("● In progress").
- Push the number that actually matters to the right edge where the eye lands.
- Do not double-encode. If status is a colored dot, priority should be the colored thing *or* the dot: not both, or the row turns into confetti again.
- Same information, roughly a third of the noise. That is the target.

Metadata pattern: replace field labels with icons. A building icon before the company name, a calendar icon before the date, a currency symbol before the amount: no "Company:", "Date:", "Value:" prefixes.

## 4. Layout and containers

- **Choose the container by content volume.** A four-field form does not need a slide-out panel with acres of dead space: a centered modal fits it. A long editable record does not belong in a modal: use a full page or a wide drawer.
- **Tables beat card grids** for anything the user scans, compares, or filters. Cards are for genuinely visual items.
- **One spacing scale** (4 / 8 / 12 / 16 / 24 / 32 / 48). Every gap is a step on it.
- **Sidebar:** navigation and account state only. Persistent, useful context (remaining credits, current plan, workspace) belongs at the bottom of it where it is always visible without competing.
- **Filters** sit above the data they filter, with a legend if any filter is non-obvious.

## 5. Typography

Define once, then never deviate:

- Page title: one size, bold.
- Section label: one size, medium weight, muted; pick uppercase-with-tracking *or* sentence case and use it everywhere.
- Body: one size.
- Secondary/meta: one size, muted grey.

Four roles is usually enough for an entire product. Hierarchy comes from size, weight, color, and space: in that order of reliability.

## 6. Design for ugly data

Your app looks great because you filled it with tidy example data. Decide these rules before shipping, not after a user hits them:

- **Long strings:** truncate with an ellipsis at a fixed character count, full value on hover/tooltip. Test with a 60-character name and no spaces.
- **Missing values:** a defined placeholder (an em dash), never a blank cell or "undefined".
- **Numbers:** fixed decimal places, thousands separators, defined behavior at 0 and at 7 digits.
- **Avatars and icons over unknown backgrounds:** put them on a solid circle so they survive any image behind them.
- **Volume:** design for 0 rows, 1 row, and 400 rows. All three are real.
- **Dates:** one format across the whole product; relative time ("2 minutes ago") only where recency is the point.

## 7. Per-screen checklist

- [ ] One sentence states why the user is here; nothing on screen contradicts it
- [ ] Exactly one primary action, visually dominant
- [ ] No component repeated from another page without a reason
- [ ] Icons from one set, no emoji
- [ ] Color only on status, data, and the primary action
- [ ] Radii, spacing, and type all from the defined scales
- [ ] Empty, loading, and error states exist (see `states-and-flow.md`)
- [ ] Tested with realistic ugly data
