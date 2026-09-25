# Figma Plugin API gotchas (learned building the Al Qafla design system)

Read before writing `use_figma` scripts. Each item cost at least one failed or wrong call in production.

## Calls and pages
- Load `figma:figma-use` first; pass `skillNames` on every call.
- **Never run `use_figma` calls in parallel.** Mutations must be sequential.
- Page context resets every call: `await figma.setCurrentPageAsync(page)` once at the top. For read-only audits across pages use `await page.loadAsync()` before `findAll`.
- Keep scripts under ~40k characters. Batch SVG icons ≤ 14 per call.
- Always `return` created/mutated node IDs and record them in the ledger; never guess IDs.
- Cross-file work is two calls: read the source file (its fileKey), then write the target file.
- Unsupported in this environment: `figma.notify`, `figma.setFileThumbnailNodeAsync`, `loadAllPagesAsync`, `setPluginData`, `createImageAsync`.
- `node.screenshot({ scale })` inside a script returns an inline image; use it after every build step.
- Community kits can have locked pages. Koala's Pro pages show a "Restricted" frame, so use its free pages (Welcome, Color, Typography, Effects, Icons, Button, Input).

## Text and fonts
- Check `figma.listAvailableFontsAsync()`. Style strings differ: **Inter `Semi Bold` / `Extra Bold`**, **DM Sans / Manrope / Plus Jakarta Sans `SemiBold`**.
- Load every font (family + style) before setting `characters`, `fontName` or `setTextStyleIdAsync`.
- Text styles with bound variables report the default-mode `fontName`; load that too.
- Fixed-width wrapping text: append to parent → `layoutSizingHorizontal = 'FIXED'` → `resize(w, h)` → `textAutoResize = 'HEIGHT'`.
- Fonts like DM Sans have no emoji, ❖ or ✨ glyphs. Keep them out of text layers (page names are fine).

## Auto layout
- `layoutSizingHorizontal/Vertical = 'FILL'` only works **after** the node is appended to an auto-layout parent.
- `layoutPositioning = 'ABSOLUTE'` also only after appending; then set x/y.
- Per-side strokes: `strokeTopWeight`, `strokeBottomWeight`, `strokeLeftWeight`, `strokeRightWeight` + `strokeAlign = 'INSIDE'`.
- Truly centred middle group in a header: equal fixed widths on the left and right groups plus `primaryAxisAlignItems = 'SPACE_BETWEEN'`.
- Bound height: `counterAxisSizingMode = 'FIXED'` (horizontal frame), then `node.setBoundVariable('height', V['size/button-medium'])`.

## Variables
- `figma.variables.createVariable(name, collectionNode, type)`; alias via `figma.variables.createVariableAlias(variable)`.
- Set `scopes` explicitly (primitives `[]`); set `setVariableCodeSyntax('WEB', 'var(--x)')`.
- **Mode limits depend on the plan.** Starter plans allow one mode, so `collection.addMode()` throws. Detect it and use the fallback.
- Frame mode toggle: `frame.setExplicitVariableModeForCollection(collection, modeId)`.
- Text style variables: `style.setBoundVariable('fontSize' | 'lineHeight' | 'letterSpacing' | 'fontFamily' | 'fontStyle', variable)`. Wrap each call in try/catch and report failures. Line height and letter spacing bind as **pixels**.
- Bound paint with transparency: `figma.variables.setBoundVariableForPaint({ type: 'SOLID', color: {r:0,g:0,b:0}, opacity: 0.08 }, 'color', v)`; the opacity is kept.
- Renaming a variable keeps its ID and every binding, so it's the safe way to evolve names.

## Components
- `figma.combineAsVariants(components, parent)` stacks variants at 0,0. Grid them, `resize` the set, set `fills = []`.
- Property keys carry a suffix (`Label#7:0`). Look them up by the part before `#`.
- `instance.setProperties({ [iconKey]: componentId })` for INSTANCE_SWAP takes the **component ID string**.
- **Replacing the children of a main component** (e.g. swapping icon geometry) leaves instances that had per-layer overrides **empty**. After a swap, scan instances with zero vectors, call `instance.resetOverrides()`, then re-apply colours.
- Hidden layers don't render in screenshots. Toggle them visible to verify, then hide again.
- Screenshot after every component; check it visually, not just the returned JSON.

## SVG icons
- Source export: `component.exportAsync({ format: 'SVG_STRING' })`. Strip the `<svg>` wrapper and `fill="black"`, and round numbers to 1 decimal to shrink the payload.
- Target: `figma.createNodeFromSvg(svg)` → `rescale(size / 32)` → `figma.flatten(vectors, node)` → move the glyph into the component keeping its x/y → bind fill → remove the temporary frame → constraints `SCALE`.

## Sections
- `figma.createSection()`, `section.appendChild(frame)` (child coords become section-relative), `section.resizeWithoutConstraints(w, h)`.
- Stack sections by reading `max(y + height)` of the page's existing children.
