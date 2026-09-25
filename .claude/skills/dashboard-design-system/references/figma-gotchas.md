# Figma Plugin API gotchas (learned building the Al Qafla and Workflow design systems)

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
- **Paint opacity is dropped when a variable is bound** (observed repeatedly on the Workflow build: nav count tints, Gantt overlays). For translucency use layer `opacity`, or bind a variable whose value has alpha (`bg/scrim`).
- **Stale raw colours on bound paints.** A bound paint keeps whatever raw `color` you passed; some renders and exports show that instead of the variable. Always pass the resolved value: `const r = v.resolveForConsumer(node).value; setBoundVariableForPaint({ type: 'SOLID', color: { r: r.r, g: r.g, b: r.b } }, 'color', v)`. Audit for paints whose raw colour differs from the resolved variable and re-set them.
- **Same variable name in two collections** (`chart/ink` in Primitives and Color) makes a name map pick the wrong one and prints `#NANNANNAN`. Build the map preferring the semantic collection: `if (!V[v.name] || v.variableCollectionId === colorCollectionId) V[v.name] = v`.
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

## More traps (Workflow Dashboard build)
- **Appending into an instance throws.** To add a badge or unread dot to an instance, wrap the instance in a small frame and add the dot to the wrapper.
- **Empty auto-layout frames default to 100×100.** A forgotten "Spacer" or empty "Head actions" frame silently adds 100px. Remove it, or resize its height to 1.
- **Resize after changing sizing mode.** `resize()` after `primaryAxisSizingMode = 'AUTO'` freezes the size. Resize first, then set `'AUTO'`. A doc frame stuck at 114px tall was this.
- **Cloned variants lose their text property references.** After cloning a variant (e.g. a Trend Chip tone), re-link the text: `textNode.componentPropertyReferences = { characters: 'Value#15:36' }`.
- **`findOne` searches the whole subtree.** `body.findOne(n => n.name === 'Board')` matched a view-switch button called "Board". Use `body.children.find(...)` for direct children.
- **A FILL child inside a HUG parent can explode.** A goals panel grew to 8758px wide. Set the wrapper `layoutSizingHorizontal = 'FILL'` inside a fixed-width parent.
- **Tall content clips inside fixed panels.** Set the content wrapper to HUG and give the panel a `minHeight` instead of a fixed height.
- **`maxLines` truncation is unreliable** on text with auto width. Shorten the copy instead, or set a fixed width plus `textTruncation = 'ENDING'`.
- **Inline screenshots can look dim** (icons appear lighter). Trust `get_screenshot` (the real render) before "fixing" colours.
- **Figma asset URLs may be blocked by the network proxy.** Use `get_screenshot` with the base64 response instead of downloading the PNG URL.
- **Reorder before you delete.** Removing a parent before moving its children out destroys the children.
