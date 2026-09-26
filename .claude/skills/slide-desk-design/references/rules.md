# Rules — locked, derived, and how to build

The rulebook the `slide-desk-design` skill applies. Tokens named here are the ones in this system.

### Content fidelity — the first rule

These came out of the live rebuild, where the styling was right and the content was quietly wrong. They rank above every visual rule.

1. **Never reword a takeaway, recommendation, bullet, heading, subheading or note.** Carry the source text verbatim, including its `KEY TAKEAWAY` / `RECOMMENDATION` labels. The design changes how it looks, never what it says.
2. **Never drop supporting content.** Bullets, table headings and subheadings, methodology notes, footnotes, highlight cards. Before building a slide, **inventory every text frame, table and image on the source slide and tick each one off** in the rebuilt version. Extracting the tables and working outward is how 26 bullets and five slides' worth of cards went missing.
3. **Image-based source slides** (a pasted chart or table) need a visual read, not a text extraction — that is where columns and rows disappear.
4. **Verify every number against the source** with a script before showing anything. Wrong numbers are worse than old styling.
5. The one place trimming is allowed: a highlight card's context line may be shortened so it fits on one line — and only if the user asks or the wrap is visible.
6. **Never author evidence.** If a source note is a one-liner and a commentary card would be half-empty, carry the one line and flag it rather than writing a supporting sentence. Anything you write that the source did not say gets flagged explicitly.

### Locked

| Locked | Value |
| --- | --- |
| Typeface | Plus Jakarta Sans, weights 500 / 600 / 700 / 800 (Google-hosted) |
| Brand blue | `#1767FF` |
| Logo blue | `#175280` — cover, transition and dark-block backgrounds only, never text or table structure |
| Deep blue | `#0E3A70` (pale-ground text, line series) · Heading navy `#0E2A44` (card and table headings) |
| Text | Primary `#030A17` · Secondary `#555555` · Tertiary `#999999` · Muted `#6B7178` · Faint `#9AA1AA` |
| Lines | Container `#E9E9E9` / `#E2E6EB` · Interior grid `#EDEFF2` |
| Fills | Total `#F1F6FF` · Sub-header band `#F8F8F8` · Label column `#FCFCFD` · Pale takeaway `#E8F0FF` |
| Accents | Red `#C1291C` (negatives, errors only) · Green `#1E7A2A` · Yellow `#FFBD60` (promo tag, never data) |
| Grid | 12 columns at 78px, 24px gutter, 1200px content in 1440px canvas |
| Radius | 8px containers, 100px pills |
| Elevation | `0 3px 10px rgba(0,26,74,0.04)` |
| Numbers | `$` on money · $K/$M · pp · bps · costs favourable-positive · **negatives in parentheses in plain cells, minus sign `−` in chips** |

Load weight 800 — all headline tiers use it. Loading 400–700 leaves the browser to synthesise bold, which is visibly worse.

The parentheses/minus split is deliberate, not an inconsistency: a chip is a small coloured shape where brackets fight the rounded edge, and the colour already carries the sign. `−4.2%` in a pill, `(136,144)` in a cell.

### Two conflict rules

These exist because the same collisions recurred repeatedly until stated generally. Any conditional treatment added later inherits them.

**A row owns its font weight.** Totals 800, member rows 500 with a 600 label, subtotals 600, percentage rows 500 in tertiary grey at 11px. No cell treatment overrides this.

**A cell treatment owns its colour.** A row fill paints everything including group gaps, *except* a cell that already has its own colour — a conditional gradient, a filled variance cell, a chip. Those survive a parent fill rather than being overwritten.

### Slide anatomy

Four zones, in order, all flex items — nothing absolutely positioned, so nothing can overlap by construction.

1. **Header** — eyebrow (12px 700 brand blue), title (30/38 800), subtitle (13/19 500 secondary) left; full-colour logo right at 104 × 65.
2. **Content** — centred vertically in the space between header and takeaway. Leftover space splits above and below, never pooling underneath. Items in the content zone sit 9px apart; a card row followed by anything else takes 19px, because cards are a different register from the tables beneath them.
3. **Bullets** — if the source has supporting bullets they sit between the content and the takeaway: 12/17 500 secondary, brand-blue 4px dot markers, two columns on standard and dense slides, one column on open slides.
4. **Takeaway** — anchored at the foot on every slide, with 14px of air beneath it before the footer. Same position across a deck so it never jumps.
5. **Footer** — source line bottom-right (10px faint, one line, ellipsis), page number.

#### Takeaway rules

- **K3 solid on every load** — brand blue fill, white text, 8px radius. The dense-slide exception (pale K2) was tried and overruled: in practice the pale band disappears under a wall of numbers. Sizes: open 16.5/23 · standard 15/22 · dense 15/22 with 11px vertical padding.
- **Takeaway and recommendation are two components, side by side.** Where the source has both a `KEY TAKEAWAY` and a `RECOMMENDATION`, they split the width equally: takeaway in K3 solid on the left, recommendation in **K2** on the right — `#E8F0FF` fill, 4px `#1767FF` left bar, `#0E3A70` text. Labels take the contrast of their ground: `#BBD4FF` on the solid band, `#1767FF` on the pale. Any commentary line beginning `RECOMMENDATION` is promoted out of the bullet list into this slot automatically. A recommendation is the second half of the argument, not a bullet.
- **A slide whose commentary already does the takeaway's job carries no band.** Never render an empty band.

#### Layout rules

**Nothing stretches to fill — except a dense table.** Cards, commentary columns and charts shrink-wrap. A commentary column is ~320px beside tables (520px alone) because that is a comfortable measure, not what was left over. The one exception: on a dense-load slide the table takes the full content width so its columns breathe instead of huddling with 400px of slide empty beside them. Inside a paired column the same applies.

**Comparable columns share a width.** A run of months is one visual set. **Two tables with identical columns on one slide share their column geometry exactly** — fixed layout, same widths, so the eye reads straight down between them.

**Notes that are commentary become commentary cards** beside the tables, not italic lines beneath them. Shrink the tables to make room; it usually buys vertical space too.

### Derived rules

Compute these. Do not ask the user unless the result looks wrong.

#### Density — step down until it fits, both ways

Start at the most generous step and step down until the content zone fits with **6% of the height still clear** *and* no table is wider than its container. The fit check measures width as well as height — a 16-column statement fails on width first.

| Step | Body type / line | Header type | Column padding |
| --- | --- | --- | --- |
| Roomy | 14.5 / 38 | 11.5 | 18px |
| Open | 13.5 / 30 | 11 | 16px |
| Standard | 12.5 / 23 | 10.5 | 10px |
| Compact | 11.5 / 20 | 10 | 14px |
| Tight | 10.4 / 18 | 9.4 | 13px |
| Micro | 9.4 / 15 | 8.6 | 11px |

Compact fills the 22% gap that used to throw statements from Standard straight to Tight. Micro exists for the case the source keeps together — two full collections tables on one slide — where splitting would misrepresent the deck. **When a slide steps to Micro, every component on it steps too**: cards, commentary, impact panels, notes, chips. A card at 38px beside a 9px table is a bug the checker catches.

**Group headers do not step.** They are the table's signposts — "Actual", "Year on year", "Full year" — and hold at 10.5px 800 brand blue regardless of density.

Only if Micro still fails: split across two slides, at a parent boundary.

#### Statement header — two tiers, corner split

Row one: column-group labels, **white ground**, 800 brand blue, centred over their group. Row two: column labels, **one continuous `#F8F8F8` band** across every group, `#6B7178` 700, smaller than the group labels. The corner over the row-label column is **two cells, not one spanning both rows** — top white with the groups, bottom grey with the labels. Column-group tints paint the body only; a tint reaching the header is the bug that erased the grey band.

#### Column-group separation

Statements and breakdowns: **10px gutter spacers** between groups at every row (header rows too, or header and body drift out of alignment), with faint per-group body washes — `#F5F8FE`, `#FAFBFD`, `#F7F9FC`, deepening to `#E9F0FF` / `#E2ECFF` / `#EAF0F9` on total rows. Two groups and 8 columns or fewer: tint alone.

14+ columns is past what gutters were designed for. Three statements in the reference deck run at 16 columns and land on Tight because of it; separate panels would buy the width back. **Unresolved — flag it when you hit it**, and offer the panel version alongside.

#### Row labels — indent, not label rows

Financial statements take the **indent** treatment: no group-label rows at all. Member rows indent 30px in `#555`; totals sit proud at 14px, 800, on the `#F1F6FF` fill. Century-old financial-statement convention, and it costs no vertical space — dropping the label rows is what lifted the statements from Compact to Standard. Breakdowns are flat. Two-level rollups keep the child bar with parent bold.

#### Conditional colour — direction declared, scale from the data

A colour ramp needs two things the data cannot supply:

**Direction.** 68.8% occupancy is good; 2.09% bad-debt impact is bad; both are just percentages. Default: **lower is better for any cost or loss measure, higher is better for any revenue or utilisation measure.** State it per table where the default is wrong.

**Scale.** Thresholds span the actual range of the data being coloured, split into fifths — never fixed cut-offs, which leave every cell in the middle bands and the ramp unreadable. **Two tables sharing a measure on one slide share one scale**, so 1.11% is the same green in both.

Ramp (bad → good): `#F6C3BC`/`#8E2115` · `#FBDCC2`/`#8A5A12` · `#FDF0D2`/`#7A621F` · `#D4EED9`/`#256B2C` · `#AEE0B7`/`#0F5218`. Weight 700. Exactly one coloured column per table. Never alongside chips.

#### Highlight-card accent — stated, not positional

Every card has the same anatomy: uppercase grey label (10.5px 800 `#8A94A3`, .08em), figure (38/44 800 `#030A17`, −.03em), context line (11.5/16 500 `#6B7178`). Padding 16/20/18. **No small variant.**

The accent — figure in `#1767FF` — marks **the figure(s) the slide is about**, and that is not always the first card. It is a flag on the card, never `:first-child`. Card figures are **never** coloured green or red by verdict: one accent per card, and red means error or a negative number, nowhere else. A methodology label such as *(Illustrative)* is grey, not red.

#### Chart sizing, subject, caption

Step width is the larger of: room for the category's own label, and a cap that stops bars drifting apart. Bars take 62% of the step, to a 56px ceiling. **The plot shrinks to the bars.** The highlight follows **the caption, not the maximum** — on a cost chart the lowest figure wins. Caption note shown only when the chart does not own the slide's takeaway.

#### Commentary rail colour

Cards carrying a **verdict** take semantic colour — green improved, red worsened. Cards carrying **observations** take brand blue. Colouring observations implies wins that are not there. The reference deck is almost entirely observations, so it is almost entirely blue.

### Build discipline

The reason every rule above got broken at least once during the rebuild was the same: each slide's markup was hand-written, so ten slides meant ten implementations of four components, and every one re-decided a settled question. Do not build that way.

1. **Build each component once and reuse it.** Content is data; markup comes from one template per component.
2. **Render in a headless browser and look before showing.** Reasoning about the CSS cascade is how four rounds were spent editing `thead th` rules that matched nothing because the header rows had no `<thead>`. One screenshot costs less than one guess.
3. **Run `scripts/check_components.py`** before every review: it compares the computed style of every instance of every component, within the same density step, and names the odd one out. Declared variants (`.lead > .cc`, `.card.hl`) are their own component; undeclared drift is a failure.
4. **Run the fit check**: zero vertical overflow, zero tables wider than their container, on every slide.
5. **Run the number check** against the source file.
6. **Run the text inventory** (rule 2 above). The three checks before it see numbers and styling, not omissions.

Only after all four pass does the user see it. Their job is judgment, not quality control.

