/* =============================================================================
 * helpers.js: paste only the functions a use_figma call needs.
 * Every use_figma call is a fresh script: re-declare what you use each time.
 * Shared by /design-system and /dashboard-design-system.
 * ========================================================================== */

/* ---------------------------------------------------------------------------
 * 1. Colour maths (pure JS, no Figma API)
 * ------------------------------------------------------------------------- */
const hexToRgb = (hex) => {
  let h = hex.trim().replace('#', '');
  if (h.length === 3) h = h.split('').map(c => c + c).join('');
  return { r: parseInt(h.slice(0, 2), 16) / 255, g: parseInt(h.slice(2, 4), 16) / 255, b: parseInt(h.slice(4, 6), 16) / 255 };
};
const rgbToHex = (c) => '#' + [c.r, c.g, c.b].map(x => Math.round(Math.min(1, Math.max(0, x)) * 255).toString(16).padStart(2, '0')).join('').toUpperCase();
const luminance = (c) => { const f = x => (x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4)); return 0.2126 * f(c.r) + 0.7152 * f(c.g) + 0.0722 * f(c.b); };
const contrast = (a, b) => {
  const A = typeof a === 'string' ? hexToRgb(a) : a, B = typeof b === 'string' ? hexToRgb(b) : b;
  const [l1, l2] = [luminance(A), luminance(B)].sort((x, y) => y - x);
  return Math.round(((l1 + 0.05) / (l2 + 0.05)) * 100) / 100;
};
const mix = (a, b, t) => ({ r: a.r + (b.r - a.r) * t, g: a.g + (b.g - a.g) * t, b: a.b + (b.b - a.b) * t });

/* 10-shade scale (50–900). The user's exact hex is kept at the step whose
 * lightness matches it; other steps are tinted toward white or shaded toward
 * black until they hit a target luminance, so every brand gets an even ramp. */
const SHADE_STEPS = ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900'];
const SHADE_TARGET_LUMINANCE = [0.93, 0.84, 0.70, 0.53, 0.37, 0.25, 0.16, 0.10, 0.06, 0.035];
function makeShades(hex) {
  const base = hexToRgb(hex), L = luminance(base);
  const WHITE = { r: 1, g: 1, b: 1 }, BLACK = { r: 0, g: 0, b: 0 };
  let anchor = 0, best = Infinity;
  SHADE_TARGET_LUMINANCE.forEach((t, i) => {
    const d = Math.abs(Math.log(L + 0.02) - Math.log(t + 0.02));
    if (d < best) { best = d; anchor = i; }
  });
  const solve = (target, end, lighter) => {
    let lo = 0, hi = 1;
    for (let k = 0; k < 30; k++) {
      const mid = (lo + hi) / 2, l = luminance(mix(base, end, mid));
      if (lighter ? l < target : l > target) lo = mid; else hi = mid;
    }
    return mix(base, end, (lo + hi) / 2);
  };
  const shades = {};
  SHADE_STEPS.forEach((step, i) => {
    if (i === anchor) shades[step] = rgbToHex(base);
    else if (i < anchor) shades[step] = rgbToHex(solve(SHADE_TARGET_LUMINANCE[i], WHITE, true));
    else shades[step] = rgbToHex(solve(SHADE_TARGET_LUMINANCE[i], BLACK, false));
  });
  return { shades, anchorStep: SHADE_STEPS[anchor] };
}

/* First candidate that reaches `min` contrast against EVERY background.
 * candidates: [{ name: 'gray/700', hex: '#374151' }, ...] in preference order. */
function resolveByContrast(candidates, backgroundHexes, min) {
  let bestCand = null, bestWorst = 0;
  for (const c of candidates) {
    const worst = Math.min(...backgroundHexes.map(bg => contrast(c.hex, bg)));
    if (worst >= min) return { ...c, worst, pass: true };
    if (worst > bestWorst) { bestWorst = worst; bestCand = c; }
  }
  return { ...bestCand, worst: bestWorst, pass: false };
}
const rating = (r) => (r >= 7 ? 'AAA' : r >= 4.5 ? 'AA' : r >= 3 ? 'AA large' : 'FAIL');

/* ---------------------------------------------------------------------------
 * 2. Figma context loaders (use inside use_figma)
 * ------------------------------------------------------------------------- */
// const { V, C, TS, ES } = await loadContext();
async function loadContext() {
  const V = {}; for (const v of await figma.variables.getLocalVariablesAsync()) V[v.name] = v;
  const C = {}; for (const c of await figma.variables.getLocalVariableCollectionsAsync()) C[c.name] = c;
  const TS = {}; for (const s of await figma.getLocalTextStylesAsync()) TS[s.name] = s;
  const ES = {}; for (const s of await figma.getLocalEffectStylesAsync()) ES[s.name] = s;
  const loaded = new Set();
  for (const s of Object.values(TS)) {
    const k = s.fontName.family + '|' + s.fontName.style;
    if (!loaded.has(k)) { loaded.add(k); await figma.loadFontAsync(s.fontName); }
  }
  return { V, C, TS, ES };
}

// Requires V, TS in scope.
const paint = (name, opacity = 1) => {
  if (!V[name]) throw new Error('Missing colour variable: ' + name);
  return figma.variables.setBoundVariableForPaint({ type: 'SOLID', color: { r: 0, g: 0, b: 0 }, opacity }, 'color', V[name]);
};
async function text(chars, styleName, colorName) {
  if (!TS[styleName]) throw new Error('Missing text style: ' + styleName);
  const t = figma.createText();
  await t.setTextStyleIdAsync(TS[styleName].id);
  t.characters = chars;
  t.fills = [paint(colorName)];
  return t;
}
const al = (dir, name, gap = 0) => { const f = figma.createAutoLayout(dir, { name, itemSpacing: gap }); f.fills = []; return f; };
const PAD = ['paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft'];
const RADII = ['topLeftRadius', 'topRightRadius', 'bottomLeftRadius', 'bottomRightRadius'];
const bind = (node, fields, varName) => { if (!V[varName]) throw new Error('Missing variable: ' + varName); for (const f of fields) node.setBoundVariable(f, V[varName]); };
const wrapText = (t, width) => { t.layoutSizingHorizontal = 'FIXED'; t.resize(width, t.height); t.textAutoResize = 'HEIGHT'; };
const recolorGlyphs = (node, colorName) => { for (const v of node.findAll(n => n.type === 'VECTOR')) v.fills = [paint(colorName)]; };
const propKey = (componentOrSet, label) => Object.keys(componentOrSet.componentPropertyDefinitions).find(k => k.split('#')[0] === label);

/* ---------------------------------------------------------------------------
 * 3. Variables
 * ------------------------------------------------------------------------- */
const cssVar = (name) => 'var(--' + name.toLowerCase().replace(/\s+/g, '-').replace(/\//g, '-') + ')';

async function ensureCollection(name, modeNames) {
  const all = await figma.variables.getLocalVariableCollectionsAsync();
  const c = all.find(x => x.name === name) || figma.variables.createVariableCollection(name);
  modeNames.forEach((m, i) => {
    if (c.modes[i]) { if (c.modes[i].name !== m) c.renameMode(c.modes[i].modeId, m); }
    else c.addMode(m); // throws on 1-mode plans → caller must catch and use the fallback
  });
  return c;
}

/* values: { Desktop: 56, Mobile: 40 } or { default: value }: value may be a
 * number, string, boolean, {r,g,b,a} or a Variable to alias. */
async function upsertVariable(collection, name, type, values, { scopes = [], description } = {}) {
  const existing = (await figma.variables.getLocalVariablesAsync()).find(v => v.name === name && v.variableCollectionId === collection.id);
  const v = existing || figma.variables.createVariable(name, collection, type);
  for (const mode of collection.modes) {
    let val = values[mode.name] !== undefined ? values[mode.name] : values.default;
    if (val === undefined) continue;
    if (val && typeof val === 'object' && 'resolvedType' in val) val = figma.variables.createVariableAlias(val);
    v.setValueForMode(mode.modeId, val);
  }
  v.scopes = scopes;
  v.setVariableCodeSyntax('WEB', cssVar(name));
  if (description) v.description = description;
  return v;
}
const colorValue = (hex, alpha) => { const c = hexToRgb(hex); return { ...c, a: alpha === undefined ? 1 : alpha }; };

/* Responsive text style bound to `4. Responsive` variables. */
async function responsiveTextStyle(V, styleName, scale, familyVar, weightVar, textCase = 'ORIGINAL') {
  const style = (await figma.getLocalTextStylesAsync()).find(s => s.name === styleName) || figma.createTextStyle();
  style.name = styleName;
  const first = (v) => Object.values(v.valuesByMode)[0];
  const family = first(V[familyVar]), weight = first(V[weightVar]);
  await figma.loadFontAsync({ family, style: weight });
  style.fontName = { family, style: weight };
  style.textCase = textCase;
  const failed = [];
  for (const [field, varName] of [['fontFamily', familyVar], ['fontStyle', weightVar], ['fontSize', `${scale}/font-size`], ['lineHeight', `${scale}/line-height`], ['letterSpacing', `${scale}/letter-spacing`]]) {
    try { style.setBoundVariable(field, V[varName]); } catch (e) { failed.push(`${field}: ${e.message || e}`); }
  }
  return { id: style.id, failed };
}

/* ---------------------------------------------------------------------------
 * 4. Documentation frames and sections
 * ------------------------------------------------------------------------- */
/* Requires DOC_HEADER / DOC_FOOTER component nodes in scope. */
function docFrame({ name, section, title, description, width = 1600, pad = 80, gap = 64, dir = 'VERTICAL', surface = 'background/white' }) {
  const f = al('VERTICAL', name, 0);
  f.fills = [paint(surface)]; f.cornerRadius = 16; f.clipsContent = true;
  f.counterAxisSizingMode = 'FIXED'; f.resize(width, 400); f.primaryAxisSizingMode = 'AUTO';
  const h = DOC_HEADER.createInstance(); f.appendChild(h); h.layoutSizingHorizontal = 'FILL';
  h.setProperties({ [propKey(DOC_HEADER, 'Section')]: section, [propKey(DOC_HEADER, 'Title')]: title, [propKey(DOC_HEADER, 'Description')]: description });
  const content = al(dir, 'Content', gap); PAD.forEach(k => (content[k] = pad));
  f.appendChild(content); content.layoutSizingHorizontal = 'FILL';
  const ft = DOC_FOOTER.createInstance(); f.appendChild(ft); ft.layoutSizingHorizontal = 'FILL';
  return { frame: f, content };
}

function nextSectionY(page, gap = 200) {
  const bottoms = page.children.map(n => n.y + n.height);
  return bottoms.length ? Math.max(...bottoms) + gap : 0;
}
function placeInSection(page, frame, sectionName, margin = 80) {
  const y = nextSectionY(page);
  const s = figma.createSection(); s.name = sectionName;
  s.appendChild(frame); frame.x = margin; frame.y = margin;
  s.resizeWithoutConstraints(frame.width + margin * 2, frame.height + margin * 2);
  s.x = 0; s.y = y;
  return s;
}
/* After content inside a section grows, refit it. */
function refitSection(section, margin = 80) {
  const f = section.children[0];
  section.resizeWithoutConstraints(f.width + margin * 2, f.height + margin * 2);
}

/* Variant grid + Koala-style purple dashed outline. */
function gridVariants(set, columns, gapX = 32, gapY = 32, pad = 40) {
  const kids = set.children;
  const colW = Math.max(...kids.map(k => k.width)), rowH = Math.max(...kids.map(k => k.height));
  kids.forEach((k, i) => { k.x = pad + (i % columns) * (colW + gapX); k.y = pad + Math.floor(i / columns) * (rowH + gapY); });
  const rows = Math.ceil(kids.length / columns);
  set.resize(pad * 2 + columns * colW + (columns - 1) * gapX, pad * 2 + rows * rowH + (rows - 1) * gapY);
  set.fills = [];
  set.strokes = [{ type: 'SOLID', color: { r: 151 / 255, g: 71 / 255, b: 1 } }];
  set.strokeWeight = 1; set.dashPattern = [10, 5]; set.cornerRadius = 16;
}

/* ---------------------------------------------------------------------------
 * 5. Icons (Phosphor Bold from the Koala UI Free kit)
 * ------------------------------------------------------------------------- */
/* CALL A: run with fileKey "CfPwhOt0XCKiQLi2rQjcnN" (Koala UI Free). ≤ 14 icons.
const WANT = { 'arrow-right': 'ArrowRight', menu: 'List', cart: 'ShoppingCart' };
const page = figma.root.children.find(p => p.name.includes('Icons'));
await figma.setCurrentPageAsync(page);
const byName = new Map(page.findAllWithCriteria({ types: ['COMPONENT'] }).map(c => [c.name, c]));
const icons = {}, missing = [];
for (const [key, phosphor] of Object.entries(WANT)) {
  const c = byName.get('Icon_Bold/' + phosphor);
  if (!c) { missing.push(key); continue; }
  const svg = await c.exportAsync({ format: 'SVG_STRING' });
  icons[key] = { src: phosphor, body: svg.replace(/^[\s\S]*?<svg[^>]*>/, '').replace(/<\/svg>\s*$/, '')
    .replace(/\s*fill="black"/g, '').replace(/-?\d+\.\d+/g, m => String(Math.round(parseFloat(m) * 10) / 10))
    .replace(/\s*\n\s{0,}/g, '').trim() };   // note: never write "*" + "/" inside this comment block
}
return { missing, icons };
*/

/* CALL B: run in the target file with the bodies returned by call A. */
async function createIconComponent(key, src, body, size, colorName, parent) {
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">${body.replace(/<path /g, '<path fill="#000000" ')}</svg>`;
  const tmp = figma.createNodeFromSvg(svg);
  tmp.rescale(size / 32);
  const vecs = tmp.findAll(n => n.type === 'VECTOR');
  const glyph = vecs.length > 1 ? figma.flatten(vecs, tmp) : vecs[0];
  const gx = glyph.x, gy = glyph.y;
  const comp = figma.createComponent();
  comp.name = `Icon/${key}`; comp.resize(size, size); comp.fills = []; comp.clipsContent = false;
  comp.appendChild(glyph); glyph.x = gx; glyph.y = gy; glyph.name = 'Glyph';
  glyph.fills = [paint(colorName)]; glyph.strokes = [];
  glyph.constraints = { horizontal: 'SCALE', vertical: 'SCALE' };
  tmp.remove();
  comp.description = `Phosphor Bold · ${src} (Koala UI kit). Resize instances to 16 / 20 / 24 / 32.`;
  if (parent) parent.appendChild(comp);
  return comp;
}

/* ---------------------------------------------------------------------------
 * 6. Charts (dashboards)
 * ------------------------------------------------------------------------- */
/* Smooth line through points (Catmull-Rom → cubic Bézier). pts in plot coords. */
function smoothPath(pts) {
  let d = `M ${pts[0].x.toFixed(1)} ${pts[0].y.toFixed(1)}`;
  for (let i = 0; i < pts.length - 1; i++) {
    const p0 = pts[i - 1] || pts[i], p1 = pts[i], p2 = pts[i + 1], p3 = pts[i + 2] || p2;
    const c1x = p1.x + (p2.x - p0.x) / 6, c1y = p1.y + (p2.y - p0.y) / 6;
    const c2x = p2.x - (p3.x - p1.x) / 6, c2y = p2.y - (p3.y - p1.y) / 6;
    d += ` C ${c1x.toFixed(1)} ${c1y.toFixed(1)} ${c2x.toFixed(1)} ${c2y.toFixed(1)} ${p2.x.toFixed(1)} ${p2.y.toFixed(1)}`;
  }
  return d;
}
/* Line + soft area in a non-auto-layout "Plot" frame of size (W, H). */
function areaChart(plot, values, { accent = 'background/primary', areaOpacity = 0.08, top = 8, bottom = 0 } = {}) {
  const W = plot.width, H = plot.height, max = Math.max(...values), min = Math.min(0, ...values);
  const pts = values.map((v, i) => ({ x: (i / (values.length - 1)) * W, y: top + (1 - (v - min) / (max - min || 1)) * (H - top - bottom) }));
  const line = smoothPath(pts);
  const area = figma.createVector(); area.name = 'Area';
  area.vectorPaths = [{ windingRule: 'NONZERO', data: `${line} L ${W.toFixed(1)} ${H.toFixed(1)} L 0 ${H.toFixed(1)} Z` }];
  area.fills = [paint(accent, areaOpacity)]; area.strokes = [];
  const stroke = figma.createVector(); stroke.name = 'Line';
  stroke.vectorPaths = [{ windingRule: 'NONE', data: line }];
  stroke.fills = []; stroke.strokes = [paint(accent)]; stroke.strokeWeight = 2; stroke.strokeCap = 'ROUND'; stroke.strokeJoin = 'ROUND';
  plot.appendChild(area); plot.appendChild(stroke);
  area.x = 0; area.y = 0; stroke.x = 0; stroke.y = 0;
  return { pts, area, line: stroke };
}

/* ---------------------------------------------------------------------------
 * 7. Audits (read-only; run at QA)
 * ------------------------------------------------------------------------- */
/* Unbound solid paints inside a node (skips component-set dashed outline). */
function auditUnboundPaints(root, allow = []) {
  const out = [];
  const walk = (n, isRoot) => {
    for (const prop of ['fills', 'strokes']) {
      if (isRoot && n.type === 'COMPONENT_SET' && prop === 'strokes') continue;
      const arr = n[prop];
      if (Array.isArray(arr)) arr.forEach(p => {
        if (p.type === 'SOLID' && p.visible !== false && !(p.boundVariables && p.boundVariables.color) && !allow.some(a => n.name.includes(a))) out.push(`${n.name} (${prop})`);
      });
    }
    if (n.type === 'INSTANCE' && !isRoot) return;
    if ('children' in n) for (const c of n.children) walk(c, false);
  };
  walk(root, true);
  return out;
}
/* 4px grid: padding, gaps and radii that are not multiples of 4. */
function auditGrid(root) {
  const off = [];
  for (const n of root.findAll(n => 'layoutMode' in n && n.layoutMode !== 'NONE')) {
    for (const k of ['paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'itemSpacing', 'counterAxisSpacing']) {
      const v = n[k]; if (typeof v === 'number' && v > 0 && v % 4 !== 0) off.push(`${n.name}.${k}=${v}`);
    }
    if (typeof n.cornerRadius === 'number' && n.cornerRadius > 0 && n.cornerRadius < 999 && n.cornerRadius % 4 !== 0) off.push(`${n.name}.radius=${n.cornerRadius}`);
  }
  return off;
}
/* Icon instances whose glyph vanished after a main-component edit. */
async function auditEmptyIcons(root) {
  const empty = [];
  for (const i of root.findAllWithCriteria({ types: ['INSTANCE'] })) {
    const m = await i.getMainComponentAsync();
    if (m && m.name.startsWith('Icon/') && i.findAll(n => n.type === 'VECTOR').length === 0) empty.push(i.id);
  }
  return empty; // fix: instance.resetOverrides(); then recolorGlyphs(instance, token)
}
/* Variables missing scopes or code syntax. */
async function auditVariables() {
  const bad = [];
  for (const v of await figma.variables.getLocalVariablesAsync()) {
    if (v.scopes.includes('ALL_SCOPES')) bad.push(`${v.name}: ALL_SCOPES`);
    if (!v.codeSyntax || !v.codeSyntax.WEB) bad.push(`${v.name}: no WEB code syntax`);
  }
  return bad;
}
