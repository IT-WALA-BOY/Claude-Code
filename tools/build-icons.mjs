// Builds assets/icons.svg: a sprite with only the Lucide icons the code uses.
// Usage: node tools/build-icons.mjs <lucide-static/icons dir> [fallback dir for brand icons]
// Strokes use vector-effect="non-scaling-stroke", so --icon-stroke is the exact stroke in px at any size.

import { readFileSync, writeFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const [primary, fallback] = process.argv.slice(2);
if (!primary) {
  console.error('Usage: node tools/build-icons.mjs <lucide-static/icons> [fallback icons dir]');
  process.exit(1);
}

// Any quoted kebab-case token that is a Lucide icon name counts as used. Icon names travel through
// arrays and helpers (card_head('target', ...)), so matching only icon('...') calls would miss them.
const token = /['"]([a-z][a-z0-9]*(?:-[a-z0-9]+)*)['"]/g;
const known = (name) => [primary, fallback].filter(Boolean).some((d) => existsSync(join(d, `${name}.svg`)));

function walk(dir, out = []) {
  for (const name of readdirSync(dir)) {
    if (['.git', 'node_modules', 'vendor', 'storage'].includes(name)) continue;
    const path = join(dir, name);
    if (statSync(path).isDirectory()) walk(path, out);
    else if (/\.(php|js)$/.test(name)) out.push(path);
  }
  return out;
}

const names = new Set(readFileSync(join(root, 'tools/icons-extra.txt'), 'utf8').split(/\s+/).filter(Boolean));
for (const dir of ['app', 'pages', 'partials', 'assets/js', 'install.php']) {
  const path = join(root, dir);
  const files = statSync(path).isDirectory() ? walk(path) : [path];
  for (const file of files) {
    for (const m of readFileSync(file, 'utf8').matchAll(token)) if (known(m[1])) names.add(m[1]);
  }
}

const symbols = [];
const missing = [];
for (const name of [...names].sort()) {
  const file = [primary, fallback].filter(Boolean).map((d) => join(d, `${name}.svg`)).find(existsSync);
  if (!file) { missing.push(name); continue; }
  const body = readFileSync(file, 'utf8')
    .replace(/<!--.*?-->/gs, '')
    .replace(/^[\s\S]*?<svg[^>]*>/, '')
    .replace(/<\/svg>\s*$/, '')
    .replace(/\s*\n\s*/g, '')
    .replace(/<(path|circle|rect|line|polyline|polygon|ellipse)\b/g, '<$1 vector-effect="non-scaling-stroke"')
    .replace(/\s*\/>/g, '/>');
  symbols.push(`<symbol id="${name}" viewBox="0 0 24 24">${body}</symbol>`);
}

writeFileSync(join(root, 'assets/icons.svg'), `<svg xmlns="http://www.w3.org/2000/svg">\n<!-- Lucide icons, ISC license. Built by tools/build-icons.mjs -->\n${symbols.join('\n')}\n</svg>\n`);
console.log(`${symbols.length} icons written.` + (missing.length ? ` Missing: ${missing.join(', ')}` : ''));
if (missing.length) process.exit(1);
