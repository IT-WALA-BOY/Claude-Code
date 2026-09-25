// Shared runtime: API calls, dialogs, menus, toasts, soft refresh, palette, clock, shortcuts.
// Page modules live in pages/*.js and export init(root, app), where app is the runtime below.

const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content ?? '';
const base = meta('base');
const sprite = meta('sprite');

export const $ = (sel, root = document) => root.querySelector(sel);
export const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

export function icon(name, size = 16) {
  return `<svg class="i" width="${size}" height="${size}" aria-hidden="true"><use href="${sprite}#${name}"/></svg>`;
}

export function esc(value) {
  return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
}

/* ---------- API ---------- */
export async function api(path, data = {}) {
  let res;
  try {
    res = await fetch(`${base}/api/${path}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF': meta('csrf') },
      body: JSON.stringify(data),
    });
  } catch {
    throw new Error('No connection. Check your internet and try again.');
  }
  const body = await res.json().catch(() => ({}));
  if (res.status === 401) location.reload();
  if (!res.ok) throw new Error(body.error || 'Something went wrong. Try again.');
  resetPrerender();
  return body;
}

/** Runs an optimistic change: apply now, send, undo on failure. */
export async function optimistic(apply, send, undo) {
  apply();
  try {
    return await send();
  } catch (err) {
    undo();
    toast(err.message, { error: true });
    return null;
  }
}

// Prerendered pages may hold old data after a write, so drop and re-add the rules.
function resetPrerender() {
  const rules = $('script[type="speculationrules"]');
  if (!rules) return;
  const copy = rules.cloneNode(true);
  rules.remove();
  document.head.append(copy);
}

/* ---------- Toasts ---------- */
export function toast(message, { error = false, action = null, onAction = null, ms = 3200 } = {}) {
  const el = document.createElement('div');
  el.className = 'toast' + (error ? ' is-error' : '');
  el.setAttribute('role', error ? 'alert' : 'status');
  el.innerHTML = `<span>${esc(message)}</span>${action ? `<button type="button">${esc(action)}</button>` : ''}`;
  const close = () => {
    el.classList.add('is-closing');
    el.addEventListener('animationend', () => el.remove(), { once: true });
  };
  el.querySelector('button')?.addEventListener('click', () => { onAction?.(); close(); });
  $('#toasts').append(el);
  setTimeout(close, error ? ms + 2000 : ms);
}

/* ---------- Menus ---------- */
let openMenu = null;

function closeMenu() {
  if (!openMenu) return;
  const menu = openMenu;
  openMenu = null;
  document.querySelector(`[data-menu="${menu.id}"]`)?.setAttribute('aria-expanded', 'false');
  menu.classList.add('is-closing');
  menu.addEventListener('animationend', () => { menu.hidden = true; menu.classList.remove('is-closing'); }, { once: true });
}

function toggleMenu(button) {
  const menu = document.getElementById(button.dataset.menu);
  if (!menu) return;
  const wasOpen = openMenu === menu;
  closeMenu();
  if (wasOpen) return;
  menu.hidden = false;
  button.setAttribute('aria-expanded', 'true');
  openMenu = menu;
}

/* ---------- Dialogs ---------- */
export function openDialog(dialog) {
  if (typeof dialog === 'string') dialog = document.getElementById(dialog);
  closeMenu();
  dialog.showModal();
  requestAnimationFrame(() => $('[autofocus], input:not([type=hidden]), textarea, select', dialog)?.focus());
  return dialog;
}

export function closeDialog(dialog) {
  if (!dialog?.open || dialog.classList.contains('is-closing')) return;
  dialog.classList.add('is-closing');
  dialog.addEventListener('animationend', () => {
    dialog.classList.remove('is-closing');
    dialog.close();
  }, { once: true });
}

/** Asks for confirmation with a small dialog. Resolves true or false. */
export function confirmAction(message, actionLabel = 'Delete') {
  const dialog = $('#confirm-dialog');
  $('[data-confirm-text]', dialog).textContent = message;
  $('[data-confirm-yes]', dialog).textContent = actionLabel;
  openDialog(dialog);
  return new Promise((resolve) => {
    const done = (value) => () => { closeDialog(dialog); resolve(value); cleanup(); };
    const yes = done(true);
    const no = done(false);
    const cleanup = () => {
      $('[data-confirm-yes]', dialog).removeEventListener('click', yes);
      $('[data-confirm-no]', dialog).removeEventListener('click', no);
      dialog.removeEventListener('cancel', no);
    };
    $('[data-confirm-yes]', dialog).addEventListener('click', yes);
    $('[data-confirm-no]', dialog).addEventListener('click', no);
    dialog.addEventListener('cancel', no);
  });
}

/* ---------- Forms ---------- */

/** Turns a form into a plain object. Repeating rows live in [data-list] with inputs marked data-key. */
export function formData(form) {
  const out = {};
  for (const el of form.elements) {
    if (!el.name || el.disabled || el.closest('[data-list]')) continue;
    if (el.type === 'radio') {
      if (el.checked) out[el.name] = el.value;
    } else if (el.type === 'checkbox') {
      out[el.name] = el.checked;
    } else {
      out[el.name] = el.value;
    }
  }
  for (const list of $$('[data-list]', form)) {
    out[list.dataset.list] = $$('[data-row]', list).map((row) => {
      const item = {};
      for (const el of $$('[data-key]', row)) {
        item[el.dataset.key] = el.type === 'checkbox' ? el.checked : el.value;
      }
      return item;
    });
  }
  return out;
}

/** Fills a form from an object. Lists are handled by the page that owns them. */
export function fillForm(form, data) {
  form.reset();
  for (const el of form.elements) {
    if (!el.name || !(el.name in data)) continue;
    const value = data[el.name];
    if (el.type === 'radio') el.checked = String(el.value) === String(value);
    else if (el.type === 'checkbox') el.checked = Boolean(value);
    else el.value = value ?? '';
  }
}

async function submitForm(form, submitter) {
  const button = submitter ?? $('[type=submit]', form);
  button?.classList.add('is-loading');
  try {
    const result = await api(form.dataset.api, formData(form));
    closeDialog(form.closest('dialog'));
    if (form.dataset.success) toast(form.dataset.success);
    form.dispatchEvent(new CustomEvent('saved', { detail: result, bubbles: true }));
    if (form.dataset.after !== 'none') await refresh({ flash: result.id });
  } catch (err) {
    toast(err.message, { error: true });
  } finally {
    button?.classList.remove('is-loading');
  }
}

/* ---------- Soft refresh: re-render the page from the server without a reload ---------- */
const pageModules = new Map();

export async function refresh({ flash = null } = {}) {
  const res = await fetch(location.href);
  if (res.redirected || !res.ok) return location.reload();
  const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
  for (const sel of ['#page', '#side-nav', '.bell', '#bell-menu']) {
    const next = $(sel, doc);
    const current = $(sel);
    if (next && current) current.replaceWith(next);
  }
  initPage();
  if (flash) $(`[data-id="${flash}"]`)?.classList.add('flash');
}

async function initPage() {
  const root = $('#page');
  const name = root?.dataset.module;
  if (!name) return;
  try {
    if (!pageModules.has(name)) pageModules.set(name, await import(`./pages/${name}.js?v=${root.dataset.v}`));
    pageModules.get(name).init?.(root, runtime);
  } catch (err) {
    console.error(err);
  }
}

/* ---------- Clock (app clock + Depalpur) ---------- */
function startClock() {
  const [tzA, tzB] = meta('timezones').split('|');
  const a = $('[data-clock-a]');
  const b = $('[data-clock-b]');
  if (!a || !tzA) return;
  const fmt = (tz) => new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', timeZone: tz });
  const [fa, fb] = [fmt(tzA), fmt(tzB)];
  const labelA = a.textContent.trim().split(' ').pop();
  const labelB = b.textContent.trim().split(' ').pop();
  const tick = () => {
    const now = new Date();
    a.textContent = `${fa.format(now)} ${labelA}`;
    b.textContent = `${fb.format(now)} ${labelB}`;
  };
  setInterval(tick, 15000);
}

/* ---------- Command palette ---------- */
function palette() {
  const dialog = $('#palette');
  const input = $('input', dialog);
  const list = $('.palette-list', dialog);
  let items = [];
  let active = 0;
  let timer = 0;

  const render = () => {
    list.innerHTML = items.length
      ? items.map((it, i) => `<a class="palette-item${i === active ? ' is-active' : ''}" href="${esc(it.url)}" data-i="${i}">${icon(it.icon, 16)}<span class="truncate">${esc(it.title)}</span><small>${esc(it.kind)}</small></a>`).join('')
      : `<p class="palette-empty">${input.value.trim() ? 'No matches.' : 'Type to search tasks, leads, goals and projects.'}</p>`;
    list.querySelector('.is-active')?.scrollIntoView({ block: 'nearest' });
  };
  const search = async () => {
    const q = input.value.trim();
    if (!q) { items = []; render(); return; }
    try {
      items = (await api('search/query', { q })).results;
    } catch { items = []; }
    active = 0;
    render();
  };
  input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(search, 120); });
  input.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      active = (active + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % Math.max(items.length, 1);
      render();
    } else if (e.key === 'Enter' && items[active]) {
      location.href = items[active].url;
    }
  });
  list.addEventListener('mousemove', (e) => {
    const i = Number(e.target.closest('[data-i]')?.dataset.i ?? -1);
    if (i >= 0 && i !== active) { active = i; render(); }
  });
  return () => { input.value = ''; items = []; render(); openDialog(dialog); };
}

/* ---------- Chart tooltips: any element with data-tip="<html>" ---------- */
function tooltips() {
  const tip = document.createElement('div');
  tip.className = 'tip';
  document.body.append(tip);
  let current = null;
  document.addEventListener('pointerover', (e) => {
    const el = e.target.closest('[data-tip]');
    if (el === current) return;
    current = el;
    if (!el) { tip.classList.remove('is-on'); return; }
    tip.innerHTML = el.dataset.tip;
    tip.classList.add('is-on');
  });
  document.addEventListener('pointermove', (e) => {
    if (!current) return;
    const { width, height } = tip.getBoundingClientRect();
    const x = Math.min(e.clientX + 14, innerWidth - width - 8);
    const y = e.clientY - height - 12 < 8 ? e.clientY + 16 : e.clientY - height - 12;
    tip.style.setProperty('--x', `${x}px`);
    tip.style.setProperty('--y', `${y}px`);
  }, { passive: true });
}

/* ---------- Record dialogs ----------
 * Any <dialog> with a form[data-api] works as an add/edit dialog:
 *   [data-open="dialog-id"] data-fill='{"status":"doing"}'  opens it empty (plus defaults)
 *   [data-edit="dialog-id"] inside [data-record='{...}']      opens it filled for editing
 * Repeating rows: [data-list="name"] + <template data-row-for="name">, [data-add-row="name"], [data-remove-row].
 * Delete: a [data-delete] button, with the API path in form[data-delete-api].
 */
function addRow(list, item = {}, focus = false) {
  const template = $(`template[data-row-for="${list.dataset.list}"]`, list.closest('form'));
  const row = template.content.firstElementChild.cloneNode(true);
  for (const el of $$('[data-key]', row)) {
    const value = item[el.dataset.key];
    if (el.type === 'checkbox') el.checked = Boolean(value);
    else if (value !== undefined) el.value = value;
  }
  list.append(row);
  if (focus) $('input[type=text], input:not([type])', row)?.focus();
}

export function openRecord(dialog, data = {}) {
  if (typeof dialog === 'string') dialog = document.getElementById(dialog);
  const form = $('form', dialog);
  const isEdit = Boolean(data.id);
  fillForm(form, { ...JSON.parse(form.dataset.defaults || '{}'), ...data });
  for (const list of $$('[data-list]', form)) {
    list.innerHTML = '';
    (data[list.dataset.list] ?? []).forEach((item) => addRow(list, item));
  }
  const title = $('[data-title-new]', dialog);
  if (title) title.textContent = isEdit ? title.dataset.titleEdit : title.dataset.titleNew;
  const del = $('[data-delete]', form);
  if (del) del.hidden = !isEdit;
  openDialog(dialog);
  form.dispatchEvent(new CustomEvent('opened', { detail: data, bubbles: true }));
}

function recordDialogs() {
  document.addEventListener('click', async (e) => {
    // Edit first: an item inside a clickable day cell should open itself, not a new record.
    const editor = e.target.closest('[data-edit]');
    if (editor) {
      e.preventDefault();
      openRecord(editor.dataset.edit, JSON.parse(editor.closest('[data-record]').dataset.record));
      return;
    }
    const opener = e.target.closest('[data-open]');
    if (opener) {
      e.preventDefault();
      openRecord(opener.dataset.open, JSON.parse(opener.dataset.fill || '{}'));
      return;
    }
    const adder = e.target.closest('[data-add-row]');
    if (adder) {
      addRow($(`[data-list="${adder.dataset.addRow}"]`, adder.closest('form')), {}, true);
      return;
    }
    e.target.closest('[data-remove-row]')?.closest('[data-row]').remove();

    const del = e.target.closest('[data-delete]');
    if (del) {
      const form = del.closest('form');
      if (!(await confirmAction(del.dataset.confirm || 'This will be deleted.'))) return;
      try {
        await api(form.dataset.deleteApi, { id: form.elements.id.value });
        closeDialog(form.closest('dialog'));
        toast('Deleted');
        refresh();
      } catch (err) {
        toast(err.message, { error: true });
      }
    }
  });
  // Enter in a row's text field adds the next row instead of submitting.
  document.addEventListener('keydown', (e) => {
    const list = e.target.closest?.('[data-list]');
    if (list && e.key === 'Enter' && e.target.type === 'text') {
      e.preventDefault();
      addRow(list, {}, true);
    }
  });
  document.addEventListener('task:new', () => openRecord('task-dialog'));
}

/* ---------- Global events ---------- */
function isTyping(e) {
  const t = e.target;
  return t.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(t.tagName);
}

function boot() {
  const openPalette = palette();

  document.addEventListener('click', (e) => {
    const menuButton = e.target.closest('[data-menu]');
    if (menuButton) { toggleMenu(menuButton); return; }
    if (openMenu && !e.target.closest('.menu')) closeMenu();
    if (openMenu && e.target.closest('.menu-item')) closeMenu();

    const action = e.target.closest('[data-action]')?.dataset.action;
    if (action === 'side-open') $('#side').classList.add('is-open');
    if (action === 'side-close') $('#side').classList.remove('is-open');
    if (action === 'palette') openPalette();
    if (action === 'close-dialog') closeDialog(e.target.closest('dialog'));
  });

  // Click on the dimmed backdrop closes a dialog.
  document.addEventListener('mousedown', (e) => {
    if (e.target.tagName === 'DIALOG' && !e.target.classList.contains('no-backdrop-close')) {
      const r = e.target.getBoundingClientRect();
      const inside = e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom;
      if (!inside) closeDialog(e.target);
    }
  });

  // Esc animates the dialog out instead of snapping it shut.
  document.addEventListener('cancel', (e) => {
    if (e.target.tagName === 'DIALOG') { e.preventDefault(); closeDialog(e.target); }
  }, true);

  document.addEventListener('submit', (e) => {
    const form = e.target.closest('form[data-api]');
    if (!form) return;
    e.preventDefault();
    submitForm(form, e.submitter);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { closeMenu(); $('#side')?.classList.remove('is-open'); }
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openPalette(); return; }
    if (isTyping(e) || e.metaKey || e.ctrlKey || e.altKey || document.querySelector('dialog[open]')) return;
    if (e.key === '/') { e.preventDefault(); openPalette(); }
    if (e.key.toLowerCase() === 'n') { e.preventDefault(); document.dispatchEvent(new CustomEvent('task:new')); }
  });

  startClock();
  tooltips();
  recordDialogs();
  initPage();
}

// Page modules get the runtime passed in, so app.js is never loaded twice.
const runtime = { $, $$, api, optimistic, toast, openDialog, closeDialog, openRecord, confirmAction, formData, fillForm, refresh, icon, esc };

boot();
