# Tables — the nine types

Nine types. Pick by what the table is doing, not what it looks like. The defining properties are not negotiable the way styling is — they are what makes the type correct rather than merely consistent.

## Contents

1. Statement · 2. Breakdown · 3. Series · 4. Experiment · 5. Reference · 6. Rollup · 7. Narrative matrix · 8. Ranked list · 9. The overrides

## Chrome shared by every type

Container: 8px radius, `#E9E9E9` border, white, elevation `0 3px 10px rgba(0,26,74,.04)`, `overflow:hidden` so the header band clips to the corners. Full grid: `#EDEFF2` interior rules on rows *and* columns; last column and last row carry no trailing rule. Tabular figures. Nothing wraps except in a Reference table. Label column `#FCFCFD`, 600.

Header rows live inside a real `<thead>`. This is not pedantry: orphan `<tr>`s fall into an implicit body and every header style in the sheet then matches nothing.

Heading trio above a table, when the source has one: **heading** 11.5px 800 `#0E2A44`; **subheading** 10.5px italic `#8A929C`; **note** beneath the table 9.6px italic `#9AA1AA`. All three are part of the table component and are carried verbatim.

---

## 1 · Statement

Line items down, periods and variances across. The monthly P&L, YoY portfolio, YoY same store, full-year forecast.

- **Row order matches the source system and is never re-sorted.** A statement is read by people who know where each line sits.
- **Two-tier header, corner split.** Group labels on white in brand blue 800, centred over their group; column labels beneath on one continuous `#F8F8F8` band in `#6B7178`. The corner is two cells — top white, bottom grey.
- **Gutter separation.** 10px spacer columns between groups, at header and body alike, with faint per-group body washes. Never a spanning rule, never tint on the header.
- **Indent row labels.** No group rows. Members indent 30px in `#555`; totals proud at 14px, 800, on `#F1F6FF`. Percentage-of-revenue rows in `#999` at 11px.
- Three total rows is normal; each carries the fill and 800, and its group cells deepen the wash.
- **Variance chips on percentage columns only**, never on absolute dollars. Chips take the minus sign; plain cells keep parentheses.
- On a dense-load slide the statement fills the content width. Group headers hold at 10.5px through every density step.

## 2 · Breakdown

One row per entity — metro, site, campaign — with the same metric block repeated across.

- **Sort by the column that carries the argument**, not alphabetically. If the story is Texas turning, DFW and Houston go top.
- **The total must foot.** A breakdown that does not sum invites the question you do not want.
- No semantic row grouping — every row is the same kind of thing, so all structure is columnar. Same header and gutter treatment as the statement; the entity label ("Metro") sits in the lower corner cell.
- Cap around 16 rows on a slide. Past that, split by region or show top and bottom with a remaining row.

## 3 · Series

Periods down, metrics across. Read for trend and for where the current period sits.

- **Oldest at top, newest at bottom.** The one table where putting the newest row first is wrong, because it breaks the trend.
- The current period is a row of interest, not a total — `#F1F6FF` fill, 800, label cell `#E4EEFF`. Do not give it total weight in the reader's mind by adding a rule.
- Conditional colour belongs on exactly one column, the one the slide is about. **Declare its direction** (lower-better for a cost or loss measure) and **scale it to the data**, fifths of the actual range. Where an annual series and a monthly breakout share a slide they share the scale and the column geometry.
- **Never combine conditional colour with variance chips.** Two colour systems in one table cancel each other out.
- Twelve to thirteen rows maximum. Past that it is a chart.

## 4 · Experiment

Arms down, outcomes across. One row is a control.

- **The control goes last, in grey (`#8A929C`, 600), above a 2px `#D7DBE0` rule, and never takes the total fill.** It is a reference line, not a sum.
- Sort best-first — order is an argument here.
- **Sample-size column is mandatory.** A lift without an N is a claim without evidence.
- One winner highlighted (`#F1F6FF`, 700, label `#E4EEFF`), or none. If two arms are within noise of each other, marking one is a lie the design is telling. A `BEST` pill (9px 800 uppercase, brand blue) may sit after the winner's label.
- **A caveat line appears whenever arms are under 100.** Amber note — `#FDF6E3` ground, 3px `#E8A33D` bar, `#8A6A1F` text. Part of the component, not an optional extra.
- Chips on the lift column only. Cost and value columns stay plain so the eye lands on the outcome first.
- A figure below breakeven is a genuine negative and takes red. A methodology label is not, and takes grey.

## 5 · Reference

Lookup material. Text in most cells, no arithmetic.

- Left-align everything; cells wrap; vertical-align top; 1.35 line height. The only type where rows are not single-line.
- No tabular figures, no chips, no totals, no sorting by value. Label column transparent, 700.
- Monospace chips for literal values — codes, field names, IDs. They are strings, not words.
- Past about ten rows it belongs in an appendix.

## 6 · Rollup

Two levels — children rolling into parents, parents into a grand total.

- **Split at a parent boundary, never inside a parent's children.**
- **Totals sit in their own block**, visually separate from the table, repeated on every page so no page reads as the whole.
- Child rows carry a left bar; parent rows are bold.
- Conditional colour on the current-period column only — the prior-period equivalent stays plain, or the eye reads two coloured columns as a comparison.
- Parent totals sit **above** their children: headline, then detail.

## 7 · Narrative matrix

Qualitative rows and columns. Each row is a claim with its evidence and current state.

- **Read top to bottom as an argument. Never sorted** — the order is the case being made.
- Cells hold sentences. Nothing foots.
- The left column is the spine of the argument and carries more weight than the others.
- Separated card rows suit it better than a grid, because each row is an independent case rather than a comparable record.

## 8 · Ranked list

One metric, many rows, no total. The order is the content.

- **Membership marking** — highlight the rows that are ours, so the reader sees our position in the field. This is set membership, not conditional formatting.
- **A subject row** — the one entry the slide exists for, boxed.
- Rank numbers stay on when the list runs into a second column, or it reads as two separate lists rather than one continued.
- Top-and-bottom is the compressed form: six from each end, middle dropped. Use it rather than squeezing forty rows onto a slide.

## 9 · The overrides

Everything else follows the derived rules. These do not, and the reason matters more than the rule — it is what stops another being added casually.

**Statement and breakdown fill the width on a dense slide.** The general rule is that nothing stretches; a 14-column table shrink-wrapped to its content leaves a third of the slide empty and the columns huddled. Width here is legibility, not decoration.

**Rollup paginates and floats its totals.** Because 41 rows do not fit a slide and the totals must survive the split.

**Experiment carries evidence rules** — control row treatment, mandatory N, caveat line. Because this table makes a claim rather than reporting a figure, and these are what stop it reading as stronger than the evidence.

**Micro exists.** The density rule says split when Tight fails; two collections tables the source keeps together get one more rung instead, because splitting them would change what the deck says.

*Retired:* the statement no longer takes a vertical rule in place of the gutter. The rebuilt deck uses gutters throughout and the team signed it off.
