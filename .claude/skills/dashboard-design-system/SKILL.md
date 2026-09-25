---
name: dashboard-design-system
description: Design a high-end, token-driven dashboard design system and flagship dashboard screen in Figma for a back-office, SaaS, admin or internal-tool product. It reads the product brief and existing code tokens, uses one accent colour with restraint (asked every run), Koala-UI-style documentation pages, one rounded icon set (Phosphor Bold, or Lucide with three stroke-weight modes), about 30 dashboard components (buttons, fields, badges, stats, tables, alerts, empty states, nav with counts, sidebar, menus, modals, drawers), and composes a non-generic dashboard with a side panel, hero metric and chart, position card, data table, needs-attention list and breakdown. Use when the user runs /dashboard-design-system or asks to design, redesign or restyle an admin dashboard, back-office UI kit, or a dashboard design system in Figma.
---

# /dashboard-design-system: premium dashboard kit + flagship screen

This is the recipe behind the Al Qafla Travel v2 dashboard: orange `#FF4B00` on Koala UI neutrals, DM Sans, Phosphor Bold icons, a 264px side panel, and one hero number. The owner called it "extremely high end". The recipe works for any data-heavy product because it follows three rules. **The dashboard answers one question. Colour carries meaning only. One accent does all the pointing.**

It was reused for the Workflow Dashboard (a freelancer's personal work tracker): Flowza-style nested cards, Lucide icons with stroke modes, Inter, a blue `#3D4EF5` accent used only for pointing, and a black primary button. That build added the options marked "Variant" below, the icon-stroke collection, and most of the new gotchas.

## Load first (every run)

1. `figma:figma-use`: **mandatory before any `use_figma` call.** Pass `skillNames: "figma-use,figma-generate-library,dashboard-design-system"`.
2. `figma:figma-generate-library`: phases, scopes, code syntax, variant grids, state ledger.
3. `saas-ui-design`: the eleven-tells audit and app-UI rules. The dashboard must pass them.
4. `figma:figma-create-new-file`: only when creating a new file.
5. Read these references when their phase starts:
   - `references/intake-and-tokens.md`: questions, brief extraction, token set, text/effect styles
   - `references/presentation.md`: Koala-style pages, Documentation Header/Footer, Docs styles
   - `references/components-spec.md`: the component list with variants, sizes and token bindings
   - `references/dashboard-recipe.md`: the flagship screen, measurement by measurement, and how to adapt it to a new domain
   - `references/figma-gotchas.md` and `scripts/helpers.js`: proven Plugin API patterns (chart paths, icon import, audits)

## Step 0: Intake

Read `user-config.json` (Figma plan and "Templates & Design Systems" project). Gather sources silently first: brief or PRD files the user attached, the live app URL, the codebase's CSS `:root` tokens and icon sprite.

**Notes in the user's Figma file are requirements.** If the file holds reference screenshots with sticky notes or comments, read every note (`findAll` on STICKY, TEXT near images, and comments), quote each one word for word into `docs/design-references.md` with what it implies, and change the plan when a note asks for a feature you had not planned (a Gantt view, an analytics page, light mode only). Identify the icon set of any reference the user loves from its layer names (Lucide names are kebab-case: `circle-check-big`, `file-clock`).

Then ask **one** `AskUserQuestion` call:

| # | Header | Question | Options |
|---|---|---|---|
| 1 | Accent | Which accent colour should drive CTAs and highlights? (type a hex via Other) | Orange `#FF4B00` (Recommended) · Blue `#2563EB` · Violet `#7C3AED` · Emerald `#059669` |
| 2 | Font | Which font? | DM Sans (Recommended) · Inter · Geist · Manrope |
| 3 | Target | Where should it be built? | New file in "Templates & Design Systems" · New file in Drafts · Existing file (paste URL via Other) |
| 4 | Viewer | Whose dashboard is the flagship screen? | Branch/team admin · Owner / executive · Front-line staff · Other |
| 5 | Icons | Which icon set? (ask only when no reference decides it) | Phosphor Bold (Recommended) · Lucide with stroke modes 1.25 / 1 / 0.85 |

When the user reacts to a screen with "this looks terrible" or "change X", build an **options board**: three labelled options per decision, side by side on one frame, and let them pick (A / B / C). It settles taste questions in one round.

Skip any question the user already answered. When a brief exists, extract the items in intake-and-tokens.md §2 before designing and print a five-line summary: product, primary user, the dashboard's one question, top 3 objects, money/status vocabulary.

## Phases (sequential `use_figma`, one focused unit per call)

State ledger: `<scratchpad>/dashboard-ds-<product>.json`. Post a one-line update as each phase starts and a short summary when it ends.

**P1 · Tokens**, `Primitives` (accent 50/100/500/600/700, Koala neutrals, status 50/700, scrim), `Color` semantic (bg / text / icon / border / focus), `Dimensions` (spacing 4…48, radius 6/8/16/full). If the product has CSS variables, keep their names in code syntax so developers only change values. Text styles (DM Sans product scale) and effects (`Shadow/Card`, `Shadow/Menu`, `Focus/Ring`). Print the contrast table. Put the font family and weights in a `Typography` collection (`font/family`, `font/weight-*`) and bind the text styles to them, so an unavailable font (Inter Display in the cloud) can be swapped by changing one variable. For Lucide, add an `Icon` collection with `icon/stroke` modes Regular 1.25 · Light 1 · Thin 0.85 (intake-and-tokens.md §3.6).

**P2 · File structure**: Koala page list, `Docs/*` styles, Documentation Header/Footer on `📋 Documentation`, then the foundation pages (`🎨 Color`, `🆎 Typography`, `🧱 Spacing & Radius`, `🪄 Effects`, `🫰 Icons`).

**P3 · Icons**, Phosphor Bold from the Koala kit (two-call import in helpers.js), about 40 dashboard icons. Or Lucide from the `lucide-static` npm package: keep strokes live (do not outline), bind stroke weight to `icon/stroke` and stroke colour to the icon token on the `Glyph` vector. Brand glyphs (figma, linkedin) were removed from lucide 1.x; take them from `lucide-static@0.460.0`.

**P4 · Components**: atoms to organisms, one per call: Button → Icon Button → Field → Checkbox / Choice Chip / Segmented Control → Badge / Tag → Stat → Table cells → Alert / Toast / Empty State / Skeleton → Attention Item → Nav Item → Sidebar → Top Bar (optional) → Menu → Modal → Drawer. Each component gets an Overview row plus its own Koala documentation frame, and a screenshot check.

**P5 · Flagship dashboard**: build it on `🗒️ Dashboard` exactly as dashboard-recipe.md describes, adapted to the domain. Use only library instances and tokens, plus the chart vectors.

**P6 · States** (brief or time permitting): the same dashboard in loading (skeletons), empty (new branch / first week) and restricted-permission variants.

**P7 · QA + handoff**, audits (unbound paints, stale raw colours on bound paints, empty icon instances, variables without scopes or code syntax, 4px grid, contrast, em dashes in any text layer). Rebuild the `👋 Welcome` cover with the dashboard collage, and write or refresh `design-rules.md` in the project folder from the saas-ui-design template. Reply with the file link, what the dashboard answers, how the accent is used, and any open contrast decision (white on a bright accent is often under 4.5:1; offer the darker CTA shade). When the product will be coded next, dump every variable with its resolved hex (one read-only script) so `tokens.css` is copied, not retyped (saas-ui-design `references/build-notes.md`).

## Non-negotiables (what made it look expensive)

- **Neutral chrome, one accent.** Canvas `#F5F5F5`, white cards, hairline `#E5E5E5` borders and a whisper shadow (0 1 2 / 4%). The accent budget per screen is at most 5 touches: primary CTA, brand mark, active nav icon, one urgent count, and the hero chart line or the top bar in a breakdown.
- **No green (or any second brand colour) in chrome.** Status colours are soft tints (50 background, 700 text) and appear only on status.
- **One hero, not four equal KPI tiles.** A 40px figure with a trend chip and a comparison line beats a row of identical stat cards. *Variant:* a row of four KPI cards is fine when each has its own footnote whose lead figure carries the meaning ("**1** overdue · 4 due today") and the screen still has one larger hero figure below.
- **Delta chips: the arrow shows direction, the colour shows good or bad.** Fewer pending tasks is green with a down arrow; higher spend is red with an up arrow.
- **Sentence-case 12px muted labels**, not uppercase caps. 14px body. Tabular figures, right-aligned amounts. *Variant:* sidebar group labels may be 12px medium caps with +4% tracking when the user's reference does it (Flowza).
- **Variant, pointing-only accent:** the accent fills the active nav item, one urgent count, the unread dot and at most two data points; the primary button is black (`bg/inverse`). Use it when the reference's CTAs are neutral.
- **Variant, nested card shell (Flowza):** a tinted shell (`bg/shell`, hairline, radius 16, padding 4) holds a title row (18px icon + Title/Section, tools right) and a white inner panel (radius 12, hairline, whisper shadow). It reads more premium than flat cards on a white main panel.
- **A side panel, not a top bar.** Grouped navigation with counts, a workspace/branch switcher, and the user at the bottom.
- **Actions live where the problem is.** The "Needs attention" list has a verb button on each row (Pay, Enter, Review).
- **Radius scale 6 / 8 / 16**, spacing on 4px steps, rounded Phosphor icons only, no emoji, no gradients, no glows.
- **Real, ugly data.** Long names, 7-digit amounts, minus figures for refunds. Empty values read `0` or "Not set", never an em dash; many owners see em dashes as a sign of AI-written copy, so keep them out of every text layer.
- **Everything bound.** No raw hex on components or the dashboard. Paint opacity is dropped when a variable is bound, so translucency comes from layer opacity or a variable that has alpha (`bg/scrim`), never from paint opacity on a bound colour.
