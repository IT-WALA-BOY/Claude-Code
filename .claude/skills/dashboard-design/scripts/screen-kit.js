/* =============================================================================
 * screen-kit.js — builder prelude for /dashboard-design
 *
 * Paste into a use_figma call: section 1 (KIT, filled from the Step 0 discovery
 * call), section 2 (boot + primitives), and whichever builders the screen needs.
 * Then:
 *
 *   const page = await boot();
 *   const s = await shell({ name: 'Vendors · 1440', active: 'Vendors',
 *     title: 'Vendors', sub: 'Who we buy from, and what we owe them · Lahore Office' });
 *   ... compose cards / tables into s.main ...
 *   placeRightOf(s.frame, 'PREVIOUS_SCREEN_ID');
 *   await s.frame.screenshot({ scale: 0.5 });
 *   return { createdNodeIds: [s.frame.id] };
 *
 * Property keys are looked up by their label (the part before "#"), so the kit
 * works in any file whose components use the /dashboard-design-system names.
 * ========================================================================== */

/* ---------------------------------------------------------------------------
 * 1. KIT — fill from the discovery call (values below are the Al Qafla file)
 * ------------------------------------------------------------------------- */
const KIT = {
  pageId: '5:13',          // page that holds the flagship dashboard
  sidebar: '13:150',       // Sidebar component
  searchClone: '31:1046',  // search field inside the flagship header (cloned)
  bellClone: '31:1054',    // notification button inside the flagship header (cloned)
  sets: {
    button: '7:136', iconButton: '7:180', field: '8:118', checkbox: '8:154', chip: '8:163',
    segment: '8:175', badge: '9:20', tag: '9:80', th: '11:45', td: '11:71', tf: '11:76',
    alert: '12:22', emptyState: '12:83', docHeader: '20:24', docFooter: '20:74'
  },
  icons: {
    search: '5:84', plus: '5:89', down: '5:94', right: '5:99', more: '5:106', printer: '5:113', x: '5:125',
    check: '5:130', alert: '5:136', key: '5:150', receipt: '5:156', chart: '5:162', download: '5:169',
    left: '5:174', bank: '5:182', history: '5:189', refund: '5:195', wallet: '5:71', building: '5:64',
    bell: '28:646', calendar: '28:651', trendUp: '28:656', trendDown: '28:661', funnel: '28:686',
    gear: '28:691', command: '28:701', caretLeft: '28:711'
  },
  buttonIconColor: { Primary: 'icon/on-accent', Secondary: 'icon/primary', Quiet: 'icon/secondary', Danger: 'icon/negative' }
};

/* ---------------------------------------------------------------------------
 * 2. Boot and primitives
 * ------------------------------------------------------------------------- */
const V = {}, TS = {}, ES = {}, SETS = {};
async function boot() {
  for (const v of await figma.variables.getLocalVariablesAsync()) V[v.name] = v;
  for (const s of await figma.getLocalTextStylesAsync()) TS[s.name] = s;
  for (const s of await figma.getLocalEffectStylesAsync()) ES[s.name] = s;
  const loaded = new Set();
  for (const s of Object.values(TS)) {
    const k = s.fontName.family + '|' + s.fontName.style;
    if (!loaded.has(k)) { loaded.add(k); await figma.loadFontAsync(s.fontName); }
  }
  const page = await figma.getNodeByIdAsync(KIT.pageId);
  await figma.setCurrentPageAsync(page);
  return page;
}
const G = (id) => figma.getNodeByIdAsync(id);
const P = (name, opacity = 1) => {
  if (!V[name]) throw new Error('Missing colour variable ' + name);
  return figma.variables.setBoundVariableForPaint({ type: 'SOLID', color: { r: 0, g: 0, b: 0 }, opacity }, 'color', V[name]);
};
async function T(chars, style, color = 'text/primary') {
  const t = figma.createText();
  await t.setTextStyleIdAsync(TS[style].id);
  t.characters = chars;
  t.fills = [P(color)];
  return t;
}
const AL = (dir, name, gap = 0) => { const f = figma.createAutoLayout(dir, { name, itemSpacing: gap }); f.fills = []; return f; };
const RADII = ['topLeftRadius', 'topRightRadius', 'bottomLeftRadius', 'bottomRightRadius'];
const rad = (node, token) => RADII.forEach(k => node.setBoundVariable(k, V[token]));
const pad = (n, t, r = t, b = t, l = r) => { n.paddingTop = t; n.paddingRight = r; n.paddingBottom = b; n.paddingLeft = l; };
const add = (parent, child, fillW = false, fillH = false) => { parent.appendChild(child); if (fillW) child.layoutSizingHorizontal = 'FILL'; if (fillH) child.layoutSizingVertical = 'FILL'; return child; };
const hairline = (n, side, color = 'border/default') => {
  n.strokes = [P(color)];
  n.strokeTopWeight = side === 't' ? 1 : 0; n.strokeBottomWeight = side === 'b' ? 1 : 0;
  n.strokeLeftWeight = side === 'l' ? 1 : 0; n.strokeRightWeight = side === 'r' ? 1 : 0;
  n.strokeAlign = 'INSIDE';
};
const recolor = (node, color) => { for (const v of node.findAll(x => x.type === 'VECTOR')) v.fills = [P(color)]; };
const wrapText = (t) => { t.layoutSizingHorizontal = 'FILL'; t.textAutoResize = 'HEIGHT'; }; // after appending
function divider(parent) { const d = figma.createRectangle(); d.name = 'Divider'; d.resize(10, 1); d.fills = [P('border/default')]; add(parent, d, true); return d; }
async function card(name, padding = 24, gap = 16, dir = 'VERTICAL') {
  const f = AL(dir, name, gap);
  f.fills = [P('bg/surface')]; f.strokes = [P('border/default')]; f.strokeWeight = 1; f.strokeAlign = 'INSIDE';
  rad(f, 'radius/lg'); pad(f, padding);
  await f.setEffectStyleIdAsync(ES['Shadow/Card'].id);
  return f;
}
async function icon(key, size = 16, color = 'icon/secondary') {
  const i = (await G(KIT.icons[key])).createInstance();
  if (size !== 16) i.resize(size, size);
  recolor(i, color);
  return i;
}

/* ---------------------------------------------------------------------------
 * 3. Component instances (keys looked up by label)
 * ------------------------------------------------------------------------- */
async function setNode(kitName) { if (!SETS[kitName]) SETS[kitName] = await G(KIT.sets[kitName]); return SETS[kitName]; }
const keyOf = (set, label) => Object.keys(set.componentPropertyDefinitions).find(k => k.split('#')[0] === label);
async function variant(kitName, props = {}) {
  const set = await setNode(kitName);
  if (set.type === 'COMPONENT') return set.createInstance();
  const c = set.children.find(ch => {
    const kv = Object.fromEntries(ch.name.split(', ').map(p => p.split('=')));
    return Object.entries(props).every(([k, v]) => kv[k] === v);
  });
  if (!c) throw new Error(`No variant ${JSON.stringify(props)} in ${set.name}`);
  return c.createInstance();
}
/* Set component properties by label: setByLabel(instance, 'button', { Label: 'Save', 'Show icon': true }) */
async function setByLabel(instance, kitName, values) {
  const set = await setNode(kitName);
  const mapped = {};
  for (const [label, value] of Object.entries(values)) { const k = keyOf(set, label); if (k) mapped[k] = value; }
  if (Object.keys(mapped).length) instance.setProperties(mapped);
  return instance;
}
async function btn(label, style = 'Secondary', size = 'Medium', iconKey = null) {
  const b = await variant('button', { Style: style, Size: size, State: 'Default' });
  const vals = { Label: label, 'Show icon': !!iconKey };
  if (iconKey) vals.Icon = KIT.icons[iconKey];
  await setByLabel(b, 'button', vals);
  if (iconKey) { const i = b.findOne(n => n.name === 'Icon'); if (i) recolor(i, KIT.buttonIconColor[style]); } // swapped icons lose variant colours
  return b;
}
async function iconBtn(iconKey, style = 'Secondary') {
  const b = await variant('iconButton', { Style: style, State: 'Default' });
  await setByLabel(b, 'iconButton', { Icon: KIT.icons[iconKey] });
  recolor(b, style === 'Quiet' ? 'icon/secondary' : 'icon/primary');
  return b;
}
async function badge(label, tone = 'Neutral') { const b = await variant('badge', { Tone: tone }); return setByLabel(b, 'badge', { Label: label }); }
async function tag(label) { const t = await variant('tag'); return setByLabel(t, 'tag', { Label: label }); }
/* Tabs: selected segment stays neutral; icons hidden (selected icons turn accent). */
async function tabs(labels, selected = 0) {
  const f = AL('HORIZONTAL', 'Segmented', 2);
  f.fills = [P('bg/subtle')]; f.strokes = [P('border/default')]; f.strokeWeight = 1; f.strokeAlign = 'INSIDE';
  rad(f, 'radius/md'); pad(f, 2);
  for (let i = 0; i < labels.length; i++) {
    const s = await variant('segment', { Selected: i === selected ? 'True' : 'False' });
    await setByLabel(s, 'segment', { Label: labels[i], 'Show icon': false });
    f.appendChild(s);
  }
  return f;
}
async function field(label, value, { type = 'Text', state = 'Default', iconKey = null, required = false, helper = null } = {}) {
  const f = await variant('field', { Type: type, State: state });
  const vals = { Label: label, Value: value, Required: required, 'Show helper': !!helper, 'Show icon': !!iconKey };
  if (helper) vals.Helper = helper;
  if (iconKey) vals.Icon = KIT.icons[iconKey];
  await setByLabel(f, 'field', vals);
  if (iconKey) { const i = f.findOne(n => n.name === 'Leading icon' || n.name === 'Icon'); if (i) recolor(i, 'icon/muted'); }
  return f;
}
async function checkbox(label, checked) { const c = await variant('checkbox', { Checked: checked ? 'True' : 'False', State: 'Default' }); return setByLabel(c, 'checkbox', { Label: label }); }
async function searchField(placeholder, width = 300) {
  const s = (await G(KIT.searchClone)).clone();
  const t = s.children.find(n => n.type === 'TEXT'); t.characters = placeholder;
  s.resize(width, s.height);
  return s;
}
async function bellButton() { return (await G(KIT.bellClone)).clone(); }

/* ---------------------------------------------------------------------------
 * 4. Tables
 * cols: [{ label, w?, align?: 'Right', type?: 'Text'|'Two-line'|'Amount'|'Balance Owed'|'Balance Credit'|'Badge'|'Actions' }]
 * rows: arrays; each value is a string or { t, s?, type?, tone?, color? }
 * ------------------------------------------------------------------------- */
async function table(parent, cols, rows, { rowHeight = 60, selected = -1 } = {}) {
  const hr = AL('HORIZONTAL', 'Header row', 0); add(parent, hr, true);
  for (const c of cols) {
    const h = await variant('th', { Align: c.align || 'Left' });
    await setByLabel(h, 'th', { Label: c.label });
    add(hr, h);
    if (c.w) { h.layoutSizingHorizontal = 'FIXED'; h.resize(c.w, h.height); } else h.layoutSizingHorizontal = 'FILL';
  }
  for (let r = 0; r < rows.length; r++) {
    const br = AL('HORIZONTAL', r === selected ? 'Body row · selected' : 'Body row', 0); add(parent, br, true);
    for (let i = 0; i < cols.length; i++) {
      const c = cols[i];
      const v = typeof rows[r][i] === 'string' ? { t: rows[r][i] } : rows[r][i];
      const type = v.type || c.type || 'Text';
      const cell = await variant('td', { Type: type });
      if (type === 'Two-line') await setByLabel(cell, 'td', { Text: v.t, Sub: v.s || '' });
      else if (['Amount', 'Balance Owed', 'Balance Credit'].includes(type)) await setByLabel(cell, 'td', { Amount: v.t });
      else if (type === 'Text') await setByLabel(cell, 'td', { Text: v.t });
      if (type === 'Badge') {
        const b = cell.findOne(n => n.type === 'INSTANCE' && n.name === 'Badge');
        if (b) { await setByLabel(b, 'badge', { Label: v.t }); b.setProperties({ Tone: v.tone || 'Neutral' }); }
      }
      if (r === selected) cell.fills = [P('bg/muted')];
      add(br, cell);
      if (c.w) { cell.layoutSizingHorizontal = 'FIXED'; cell.resize(c.w, rowHeight); }
      else { cell.layoutSizingHorizontal = 'FILL'; cell.layoutSizingVertical = 'FIXED'; cell.resize(cell.width, rowHeight); }
      if (v.color) { const tx = cell.findOne(n => n.type === 'TEXT'); if (tx) tx.fills = [P(v.color)]; }
    }
  }
}
async function footerRow(parent, cols, values, name = 'Footer row') {
  const fr = AL('HORIZONTAL', name, 0); add(parent, fr, true);
  for (let i = 0; i < cols.length; i++) {
    const right = cols[i].align === 'Right';
    const cell = await variant('tf', { Align: right ? 'Right' : 'Left' });
    await setByLabel(cell, 'tf', right ? { Amount: values[i] || '' } : { Text: values[i] || '' });
    add(fr, cell);
    if (cols[i].w) { cell.layoutSizingHorizontal = 'FIXED'; cell.resize(cols[i].w, cell.height); } else cell.layoutSizingHorizontal = 'FILL';
  }
  return fr;
}
async function pagination(parent, label, pages = ['1']) {
  const r = AL('HORIZONTAL', 'Pagination', 8);
  r.primaryAxisAlignItems = 'SPACE_BETWEEN'; r.counterAxisAlignItems = 'CENTER'; pad(r, 12, 24, 12, 24); hairline(r, 't');
  add(parent, r, true);
  r.appendChild(await T(label, 'Meta/Regular', 'text/muted'));
  const g = AL('HORIZONTAL', 'Pages', 4); g.counterAxisAlignItems = 'CENTER'; r.appendChild(g);
  g.appendChild(await iconBtn('caretLeft', 'Quiet'));
  for (let i = 0; i < pages.length; i++) {
    const b = AL('HORIZONTAL', 'Page ' + pages[i], 0);
    b.primaryAxisAlignItems = 'CENTER'; b.counterAxisAlignItems = 'CENTER';
    b.resize(32, 32); b.primaryAxisSizingMode = 'FIXED'; b.counterAxisSizingMode = 'FIXED'; rad(b, 'radius/md');
    if (i === 0) b.fills = [P('bg/muted')];
    b.appendChild(await T(pages[i], i === 0 ? 'Meta/Semibold' : 'Meta/Regular', i === 0 ? 'text/primary' : 'text/secondary'));
    g.appendChild(b);
  }
  g.appendChild(await iconBtn('right', 'Quiet'));
  return r;
}
/* Table card with the standard head: title, sub-line, optional tools row. */
async function tableCard(parent, title, subline) {
  const tc = await card(title, 0, 0); add(parent, tc, true); tc.clipsContent = true;
  const head = AL('HORIZONTAL', 'Head', 16); head.primaryAxisAlignItems = 'SPACE_BETWEEN'; head.counterAxisAlignItems = 'CENTER';
  pad(head, 20, 24, 20, 24); add(tc, head, true);
  const tt = AL('VERTICAL', 'Title', 2); head.appendChild(tt);
  tt.appendChild(await T(title, 'Title/Section')); tt.appendChild(await T(subline, 'Meta/Regular', 'text/muted'));
  const tools = AL('HORIZONTAL', 'Tools', 8); tools.counterAxisAlignItems = 'CENTER'; head.appendChild(tools);
  return { card: tc, head, tools };
}

/* ---------------------------------------------------------------------------
 * 5. Shell, placement, wrapping
 * ------------------------------------------------------------------------- */
async function shell({ name, active, title, sub, back = false }) {
  const frame = AL('HORIZONTAL', name, 0);
  frame.fills = [P('bg/canvas')]; frame.counterAxisAlignItems = 'MIN';
  frame.resize(1440, 1024); frame.primaryAxisSizingMode = 'FIXED'; frame.counterAxisSizingMode = 'AUTO'; frame.minHeight = 1024;
  const sidebar = (await G(KIT.sidebar)).createInstance(); frame.appendChild(sidebar); sidebar.layoutSizingVertical = 'FILL';
  for (const n of sidebar.findAll(x => x.type === 'INSTANCE' && x.name === 'Nav Item')) {
    const label = (n.findOne(t => t.type === 'TEXT') || {}).characters;
    if (label === active) n.setProperties({ State: 'Active' });
    else if (label === 'Dashboard') n.setProperties({ State: 'Default' });
  }
  const main = AL('VERTICAL', 'Main', 24); pad(main, 28, 32, 32, 32); add(frame, main, true);
  const header = AL('HORIZONTAL', 'Header', 16); header.primaryAxisAlignItems = 'SPACE_BETWEEN'; header.counterAxisAlignItems = 'CENTER'; add(main, header, true);
  const titleGroup = AL('HORIZONTAL', 'Title group', 12); titleGroup.counterAxisAlignItems = 'CENTER'; header.appendChild(titleGroup);
  if (back) titleGroup.appendChild(await iconBtn('left', 'Secondary'));
  const tg = AL('VERTICAL', 'Title', 4); titleGroup.appendChild(tg);
  const titleRow = AL('HORIZONTAL', 'Title row', 10); titleRow.counterAxisAlignItems = 'CENTER'; tg.appendChild(titleRow);
  titleRow.appendChild(await T(title, 'Title/Page'));
  tg.appendChild(await T(sub, 'Meta/Regular', 'text/muted'));
  const actions = AL('HORIZONTAL', 'Actions', 12); actions.counterAxisAlignItems = 'CENTER'; header.appendChild(actions);
  return { frame, sidebar, main, header, titleRow, actions };
}
async function placeRightOf(frame, previousId, gap = 160) {
  const prev = await G(previousId);
  frame.x = prev.x + prev.width + gap; frame.y = prev.y;
}
/* Wrap a finished screen in the Koala documentation frame. */
async function wrapScreen(screen, title, description) {
  const w = AL('VERTICAL', title, 0);
  w.fills = [P('bg/surface')]; w.cornerRadius = 8; w.clipsContent = true;
  w.resize(screen.width + 64, 400); w.counterAxisSizingMode = 'FIXED'; w.primaryAxisSizingMode = 'AUTO';
  const header = (await G(KIT.sets.docHeader)).createInstance(); add(w, header, true);
  await setByLabel(header, 'docHeader', { Section: 'Product Pages', Title: title, Description: description });
  const container = AL('VERTICAL', 'Documentation container', 32); pad(container, 32); add(w, container, true);
  const x = screen.x, y = screen.y;
  container.appendChild(screen);
  const footer = (await G(KIT.sets.docFooter)).createInstance(); add(w, footer, true);
  w.x = x; w.y = y;
  return w;
}

/* ---------------------------------------------------------------------------
 * 6. Audits (read-only)
 * ------------------------------------------------------------------------- */
/* Every node whose visible paint is bound to an accent token. Count the touches. */
function accentTouches(root) {
  const ids = new Set(['bg/accent', 'bg/accent-hover', 'bg/accent-soft', 'text/accent', 'icon/accent', 'border/accent', 'border/accent-soft']
    .filter(n => V[n]).map(n => V[n].id));
  const hits = [];
  for (const n of root.findAll(() => true)) {
    if (!n.visible) continue;
    for (const prop of ['fills', 'strokes']) {
      const arr = n[prop];
      if (Array.isArray(arr) && arr.some(p => p.visible !== false && p.boundVariables && p.boundVariables.color && ids.has(p.boundVariables.color.id))) {
        let owner = n; while (owner.parent && owner.parent.type === 'INSTANCE') owner = owner.parent;
        hits.push(owner.name);
      }
    }
  }
  return [...new Set(hits)];
}
/* Text nodes that extend past the right edge of their parent frame. */
function textOverflow(root) {
  const out = [];
  for (const t of root.findAllWithCriteria({ types: ['TEXT'] })) {
    const p = t.parent;
    if (!p || !('absoluteBoundingBox' in p) || !p.absoluteBoundingBox || !t.absoluteBoundingBox) continue;
    const limit = p.absoluteBoundingBox.x + p.absoluteBoundingBox.width - (p.paddingRight || 0) + 1;
    if (t.absoluteBoundingBox.x + t.absoluteBoundingBox.width > limit) out.push(`${p.name} › "${t.characters.slice(0, 40)}"`);
  }
  return out;
}
