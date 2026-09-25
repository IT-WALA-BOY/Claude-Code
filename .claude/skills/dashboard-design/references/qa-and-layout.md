# QA, defects log and page layout

## Per-screen checklist (run on every screenshot before building the next screen)

- [ ] The header states the page's question in the context line; nothing on screen answers a different question.
- [ ] Exactly one Primary button is live (overlay screens: the drawer or modal holds it).
- [ ] `accentTouches()`: beyond the fixed shell touches (brand mark, active nav icon, urgent count, avatar initials, unread dot), the content has at most 2 — the primary CTA and one highlighted datum. Screens that embed a scaled product preview (sign-in showcase) are exempt inside the preview.
- [ ] Nav item for this screen is Active; Dashboard is Default.
- [ ] No clipped or overflowing text (`textOverflow()` empty); long meta lines wrap.
- [ ] Cards in the same row have equal heights; columns align to the 24px gap.
- [ ] Tables: amounts right-aligned; totals footer present; pagination explains the count; `⋯` actions column.
- [ ] Colour only for state: owed red, credit green, overdue text red, running balances neutral, share bars neutral.
- [ ] Numbers match the data bible and the other screens.
- [ ] Empty / zero values shown as `0` or `—`, never blank.
- [ ] Search placeholders fit on one line.

## Defects already found (and the fix that worked)

| Defect | Cause | Fix |
|---|---|---|
| Selected tab showed an orange icon | Segment "Selected=True" variant colours its icon with `icon/accent` | `setProperties({ 'Show icon': false })` on every segment (the kit's `tabs()` does this) |
| Search placeholder wrapped to 2 lines | Cloned field 260 wide with a long placeholder | Width ≥ 280–300 and a shorter placeholder |
| Whole running-balance column was red | Used `Balance Owed` for every statement line | Running balances use `Amount`; only the summary balance is red |
| `strokeDashes` threw | Wrong property name | Use `node.dashPattern = [4, 4]` |
| Script threw mid-build | Any exception inside a `use_figma` script | Failed scripts are **rolled back atomically** (observed twice: no partial frames, no orphan instances). Still run a quick read-only check, then retry the fixed script. Removing leftovers by exact name is a harmless safety net |
| Promoted component collapsed to a 1px line in its documentation frame | It was cloned from a screen child set to FILL; inside a hugging doc container, FILL on that axis collapses | After moving the component into the doc container, set `layoutSizingHorizontal/Vertical = 'FIXED'` (or `HUG`) and resize to the original size. Screen instances keep their own sizing, but check them with a quick size audit |
| A property lookup hit a frame, not the text (`Cannot attach 'TEXT' component property reference`) | Container frames share names with text layers ("Balance", "Name", "Amount") | Look up text layers with `n.type === 'TEXT' && n.name === X` |
| `findOne(n => n.name === 'Icon')` returned null after an icon swap | Setting an INSTANCE_SWAP property renames the nested instance to the swapped component's name (e.g. `Icon/plane`) | Find swapped icons by position (`top.children[0]`) or by type, not by the layer name you gave in the component |
| Service tile meta text cut off | Fixed-width tile, text on auto width | `text.layoutSizingHorizontal = 'FILL'; text.textAutoResize = 'HEIGHT'` |
| Detail panel sub-lines ran past the card | Same as above in a 352 panel | Same; also set the title group to FILL |
| Account cards had uneven heights | Content length differs | After building: `maxH` of the row → each card `layoutSizingVertical='FIXED'`, `resize(width, maxH)`, then `layoutSizingHorizontal='FILL'` again |
| Footer label "Totals · 6 of 48 mover…" truncated | 150px first column | Put "Totals" in column 1 and the count in column 2 |
| Seven orange checkboxes in a permission list | Checkbox checked fill bound to `bg/accent` | Checkbox **component** checked box → `bg/inverse` (neutral). Selection controls stay neutral in dense lists |
| Swapped button icon rendered grey on an orange button | INSTANCE_SWAP resets the nested glyph colour | Re-colour the Icon glyphs after `setProperties` (`btn()` does it) |
| Field in Error state kept the component's sample helper ("Enter an amount greater than zero.") | The Error variant's helper text layer isn't linked to the `Helper` property | Set the helper TEXT node's `characters` directly on the instance (load its font first) |
| Checkbox label overlapped the next column | 2-column permission rows in a 368 panel with a long label | Keep labels ≤ 22 characters in 2-column lists ("See cost and profit"), or use one column |

## Layout on the flagship page — flow sections (preferred)

Screens go into one Figma Section per flow, with no documentation wrappers. `layoutFlowSections()`:

```js
// FLOWS: [[sectionName, [[screenId, 'NN.n Screen name'], ...]], ...]
const PAD_X = 160, PAD_TOP = 200, PAD_BOTTOM = 160, GAP = 160, SECTION_GAP = 240;
const resolved = [];
for (const [name, list] of FLOWS) {
  const screens = [];
  for (const [id, label] of list) screens.push({ s: await figma.getNodeByIdAsync(id), label });
  resolved.push({ name, screens });
}
const widths = resolved.map(r => r.screens.reduce((a, x) => a + x.s.width, 0) + GAP * (r.screens.length - 1));
const W = Math.max(...widths) + PAD_X * 2;
let y = 0;
resolved.forEach((r, i) => {
  const sec = figma.createSection(); sec.name = r.name; figma.currentPage.appendChild(sec);
  const maxH = Math.max(...r.screens.map(x => x.s.height));
  let x = Math.round((W - widths[i]) / 2);
  for (const { s, label } of r.screens) { sec.appendChild(s); s.x = x; s.y = PAD_TOP; s.name = label; x += s.width + GAP; }
  sec.resizeWithoutConstraints(W, PAD_TOP + maxH + PAD_BOTTOM);
  sec.x = 0; sec.y = y; y += sec.height + SECTION_GAP;
});
```

If screens were previously wrapped, move each screen into its section first, then delete the now-empty wrapper frames (only frames that contain a `Documentation container` child).

## Legacy — documentation-wrapped layout (do not use for product screens)

Kept for reference only: the flagship documentation frame first, then every screen wrapped, in brief order, rows of 4, 160px gaps.

```js
// paste KIT + boot + primitives + wrapScreen from screen-kit.js
const page = await boot();
const ORDER = [ // [screenFrameId, title, description]
  ['SCREEN_ID', 'Ledgers — Clients owe us', 'Who owes money, and how much? Balance overview with aging, balances table with totals.'],
];
const flagshipWrapper = await G('FLAGSHIP_WRAPPER_ID');
const wrappers = [flagshipWrapper];
for (const [id, title, desc] of ORDER) {
  const screen = await G(id);
  const already = screen.parent && screen.parent.name === 'Documentation container';
  wrappers.push(already ? screen.parent.parent : await wrapScreen(screen, title, desc));
}
const PER_ROW = 4, GAP = 160;
let x = 0, y = 0, rowH = 0;
wrappers.forEach((w, i) => {
  if (i > 0 && i % PER_ROW === 0) { x = 0; y += rowH + GAP; rowH = 0; }
  w.x = x; w.y = y; x += w.width + GAP; rowH = Math.max(rowH, w.height);
});
return { wrapperIds: wrappers.map(w => w.id) };
```

Do the wrapping in batches of 3–4 screens per call (each wrap moves a large subtree), then one final positioning call.

## Final audit (read-only)

```js
// paste KIT + boot + accentTouches + textOverflow
const page = await boot();
const report = {};
for (const w of page.children.filter(n => n.type === 'FRAME')) {
  const screen = w.findOne(n => n.type === 'FRAME' && n.width === 1440 && / · 1440$/.test(n.name)) || w;
  let unbound = 0;
  for (const n of screen.findAll(() => true)) {
    if (n.type === 'INSTANCE' || (n.parent && n.parent.type === 'INSTANCE')) continue;
    for (const prop of ['fills', 'strokes']) {
      const arr = n[prop];
      if (Array.isArray(arr)) for (const p of arr) if (p.type === 'SOLID' && p.visible !== false && !(p.boundVariables && p.boundVariables.color)) unbound++;
    }
  }
  report[w.name] = { accent: accentTouches(screen), overflow: textOverflow(screen).slice(0, 5), unbound };
}
return report;
```
