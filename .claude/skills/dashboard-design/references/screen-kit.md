# Screen kit: discovery, prelude, per-screen call template

## 1. Discovery call (Step 0, read-only)

Run once on the target file. Replace `FLAGSHIP_NAME_PART` with a distinctive part of the flagship frame name (e.g. `Dashboard ·`).

```js
const pages = figma.root.children.map(p => ({ id: p.id, name: p.name }));
// find the page holding the flagship: try the product-pages page names first
const candidate = figma.root.children.find(p => /dashboard|screens|product/i.test(p.name) && !/^-/.test(p.name));
await figma.setCurrentPageAsync(candidate);
const flagship = candidate.findOne(n => n.type === 'FRAME' && n.name.includes('FLAGSHIP_NAME_PART') && n.width === 1440);
const brief = (n, d) => ({ id: n.id, name: n.name, type: n.type, w: Math.round(n.width), h: Math.round(n.height),
  children: d > 0 && 'children' in n && n.type !== 'INSTANCE' ? n.children.slice(0, 12).map(c => brief(c, d - 1)) : undefined });
const sets = candidate.parent.children; // all pages
const inventory = {};
for (const p of figma.root.children) {
  // NOTE: component pages must be loaded; split into one call per page if the file is large
  await p.loadAsync();
  for (const s of p.findAllWithCriteria({ types: ['COMPONENT_SET', 'COMPONENT'] })) {
    if (s.type === 'COMPONENT' && s.parent && s.parent.type === 'COMPONENT_SET') continue;
    if (s.name.startsWith('Icon/')) { (inventory.icons ||= {})[s.name.slice(5)] = s.id; continue; }
    inventory[s.name] = { id: s.id, props: Object.entries(s.componentPropertyDefinitions).map(([k, d]) => `${k}:${d.type}${d.variantOptions ? '[' + d.variantOptions.join('|') + ']' : ''}`) };
  }
}
return {
  pages, flagshipPage: candidate.id, flagship: flagship ? brief(flagship, 3) : null,
  inventory,
  variables: (await figma.variables.getLocalVariablesAsync()).map(v => v.name),
  textStyles: (await figma.getLocalTextStylesAsync()).map(s => s.name),
  effects: (await figma.getLocalEffectStylesAsync()).map(s => s.name)
};
```

From the result, fill in `KIT` in `scripts/screen-kit.js`:
- `pageId`: the page with the flagship.
- `sidebar`: the Sidebar component.
- `searchClone` and `bellClone`: IDs of the flagship header's search frame and notification button (look under `Header › Actions`).
- `sets`: IDs of Button, Icon Button, Field, Checkbox, Choice Chip, Segment, Badge, Tag, Table Header Cell, Table Cell, Table Footer Cell, Alert, Empty State, Documentation Header/Footer.
- `icons`: the `Icon/*` IDs.

If a component the archetypes need is missing (Segment, Table Cell…), build it with `/dashboard-design-system`'s components-spec first.

## 2. Prelude rules

- **Every `use_figma` call is a fresh script.** Paste the KIT plus only the builders that call needs. Keep the script under ~40k characters.
- `boot()` loads variables, styles and fonts and switches to the page, so call it first.
- Builders use **token names** (`bg/surface`, `text/muted`), never hex.
- `btn()` re-colours the swapped icon to match the button style, because swapping an INSTANCE_SWAP icon drops the variant's colour override.
- `tabs()` hides segment icons, because selected segment icons render in the accent colour.
- `table()` resizes every cell to the row height and applies `bg/muted` to a selected row.
- `shell()` sets the active nav item through nested `setProperties({ State: 'Active' })` and resets Dashboard to Default.

## 3. Per-screen call template

```js
// (paste KIT + boot + primitives + needed builders here)
const page = await boot();
const s = await shell({ name: 'Expenses · 1440', active: 'Expenses', title: 'Expenses',
  sub: 'What the branch spends, and which bills are due · Lahore Office' });
await placeRightOf(s.frame, 'PREVIOUS_SCREEN_ID');
s.actions.appendChild(await bellButton());
s.actions.appendChild(await btn('September 2026', 'Secondary', 'Medium', 'calendar'));
s.actions.appendChild(await btn('Add expense', 'Primary', 'Medium', 'plus'));

const row = AL('HORIZONTAL', 'Row/Month', 24); row.counterAxisAlignItems = 'MIN'; add(s.main, row, true);
const hero = await card('Spent this month'); add(row, hero, true);
// ... hero content ...

const { card: tc, tools } = await tableCard(s.main, 'All expenses', 'September 2026 · newest first');
tools.appendChild(await tabs(['All 23', 'Paid 17', 'Unpaid 4', 'Overdue 1']));
const cols = [{ label: 'Date', w: 150, type: 'Two-line' }, { label: 'Expense', type: 'Two-line' }, { label: 'Amount', w: 120, align: 'Right', type: 'Amount' }, { label: 'Status', w: 120, type: 'Badge' }, { label: '', w: 56, type: 'Actions' }];
await table(tc, cols, [[{ t: '12 Sep 2026', s: 'by Bilal' }, { t: 'Tea and water', s: 'Paid cash' }, '2,350', { t: 'Paid', tone: 'Positive' }, '']]);
await footerRow(tc, cols, ['Totals', '1 of 23 shown', '2,350', '', '']);
await pagination(tc, 'Showing 1 of 23 expenses', ['1', '2', '3']);

await s.frame.screenshot({ scale: 0.5 });
return { createdNodeIds: [s.frame.id], accent: accentTouches(s.frame), overflow: textOverflow(s.frame) };
```

Split a screen into two calls when it passes ~250 created nodes. Build shell, header and row 1 in the first call and return the `Main` id. Look it up in the second call with `await G(mainId)`.
