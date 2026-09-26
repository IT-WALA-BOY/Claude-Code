---
name: slide-desk-design
description: RecNation's slide system for monthly performance decks, investor decks, board material and any report table, chart or scorecard. Use whenever building, restyling, reformatting or reviewing a RecNation slide or deck (including "make a slide", "build the monthly deck", "reformat this deck", "put this in a table", or a shared .pptx to update). Produces a native, editable PowerPoint through a bundled builder so every teammate gets the same result.
---

# RecNation slides

The look is already decided and signed off (the RecNation Slides design system in Claude Design, and the August 2026 reference deck). This skill does not re-decide it. It turns content into that look **through one script**, so the output is identical no matter who asks or how they phrase it.

**The rule that makes it work: never hand-place shapes or hand-style a table.** You write content into `deck.json`; `scripts/rn_build.py` does every measurement, colour, weight and position. If something looks wrong, fix the builder or the JSON, never the slide.

## Workflow (every time)

1. **Inventory the source.** `python scripts/rn_extract.py source.pptx inventory.json`. Every text frame, table cell, chart series, note and picture per slide. Pictures flagged there may be pasted tables or charts: look at them (render the slide) and transcribe the numbers.
2. **Write `deck.json`.** One entry per slide, content verbatim from the inventory (schema below). Pick the table type by what the table does (`references/tables.md`). Classify rows (`member`, `total`, `pct`, `win`, `control`, `now`). Do not reword anything.
3. **Build.** `python scripts/rn_build.py deck.json out.pptx`. It prints the load and density it chose per slide, and says if a slide cannot fit even at Micro (then split at a parent boundary).
4. **Check.** `python scripts/rn_check.py deck.json out.pptx inventory.json`. Fit, every number present, every source line present. Fix every issue it names.
5. **Look.** Render to images (`soffice --headless --convert-to pdf out.pptx`, then `pdftoppm -png -r 110`) and look at every slide before handing it over.
6. **Report** what you flagged: any label you had to author, any picture you transcribed, any slide you split, anything the system does not cover.

## Content fidelity (ranks above every visual rule)

- Takeaways, recommendations, bullets, headings, subheadings, notes and figures are the source's words, exactly, including `KEY TAKEAWAY` / `RECOMMENDATION` lead-ins.
- Never drop content. The checker lists anything missing.
- A line starting `RECOMMENDATION` goes to the `recommendation` field, never the bullets.
- A highlight card label comes from the source text split at its colon. If there is no colon, write the shortest label and set `"authored_label": true` so it is reported.
- Never write evidence the source did not state. A thin card stays thin; flag it.
- The only formatting change to a figure: chips turn `(5.1)%` into `−5.1%`. Plain cells keep parentheses.

## deck.json

```json
{"copyright": "© RecNation Storage 2026 All rights reserved",
 "slides": [
  {"type": "cover", "title": "August 2026 Financial Update"},
  {"eyebrow": "Monthly performance", "title": "...", "subtitle": "...", "source": "Source: ...",
   "blocks": [ <block>, ... ],
   "bullets": ["...", "..."],
   "takeaway": "KEY TAKEAWAY ...", "recommendation": "RECOMMENDATION ...",
   "notes": "speaker notes, verbatim"}]}
```

Blocks:

- `{"type":"table","kind":"statement|breakdown|series|experiment", "heading","subheading","note", "corner":"Metro", "groups":[{"label":"Actual","cols":["Jul-26","Aug-26"]}, ...], "chips":[flat column indexes], "ramp":{"col":i,"dir":"up|down"}, "fill_width":true, "rows":[{"label":"...","style":"member","cells":["..."]}], "caveat":"..."}`
- `{"type":"cards","items":[{"label":"Retention lift","value":"+13.4 pp","context":"...","accent":true}]}` (4 to 6 items; `accent` on the figure(s) the slide is about, never positional)
- `{"type":"row","items":[<block>,<block>]}` two blocks side by side.

Cells are strings exactly as the source prints them. Flat column index counts data columns across all groups from 0.

## What the builder decides (do not ask the user)

Everything below is computed. Details and reasons are in the design system; do not restate them to the user.

- **Load**: dense at 25+ rows or 14+ columns, standard at 10+ rows, otherwise open. Sets takeaway and bullet size.
- **Density**: Roomy → Open → Standard → Compact → Tight → Micro, first step where content fits with 6% clear **and** no table is wider than the slide. Group headers stay 10.5px.
- **Column groups**: 3+ groups or 9+ columns get 10px gutter columns with per-group washes; otherwise none.
- **Column widths**: measured from the actual font file, never guessed.
- **Statement**: two-tier header, corner split, members indented 30px, totals 800 on the tint, `%` rows grey 11px.
- **Experiment**: control last in grey over a 2px rule, one winner row (or none), caveat when any arm is under 100.
- **Series**: current period row tinted; ramp on one column, fifths of the actual range, direction declared in the JSON.
- **Chips** on `chips` columns only (percent or point columns, never dollars, never with a ramp).
- **Takeaway** solid brand band at the foot; recommendation pale K2 beside it as an equal half.

## PowerPoint translation (why v1 broke, do not undo)

These are the reasons the Claude Design output did not survive into PowerPoint. The builder handles each; keep it that way.

1. **Weights are face names.** PowerPoint cannot reach weight 800 with a bold flag. 500 = `Plus Jakarta Sans Medium`, 600 = `Plus Jakarta Sans SemiBold`, 700 = `Plus Jakarta Sans` + bold, 800 = `Plus Jakarta Sans ExtraBold`.
2. **Tall font metrics.** Plus Jakarta Sans reserves 1.65 em per line. On default spacing every table row grows ~30% and the table runs into the bullets. Table paragraphs get exact line spacing.
3. **Empty cells.** An empty cell falls back to 18pt and forces its whole row to ~29px. Every cell, including spacers, gets a sized run.
4. **No flexbox.** PowerPoint positions absolutely, so "cannot overlap by construction" has to be computed: the builder stacks header, content, bullets and takeaway from measured heights.
5. **Native tables only.** Tables stay real PowerPoint tables so anyone can edit a number later. Rounded corners and elevation come from a container shape behind the table.
6. **Chips** are a coloured cell (fill + text colour from the chip tokens), the closest native equivalent of the pill.

## Tokens

`scripts/tokens.json` is generated from the design system's `tokens.css`. The design system is the source of truth. When a token changes there, re-export and replace this file; do not type colours into the builder.

## Fonts

Every machine that opens the decks needs Plus Jakarta Sans installed (all four files in `assets/fonts`, free from Google Fonts). Without it PowerPoint substitutes a different width and tables no longer fit.

## Not covered yet: flag and ask

Charts in the builder (combo, stacked column, simple column are specified in `references/charts.md`; build them natively with the palette there until the builder gains them), Reference, Rollup, Narrative matrix and Ranked list table kinds, commentary cards, impact panel, maps, section dividers, statements at 14+ columns (panels vs gutters is undecided).
