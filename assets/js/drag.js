// Sortable lists with a lifted, tilted item and a dashed placeholder. Used by the task board,
// the LinkedIn pipeline and the buy list. Pointer events cover mouse, pen and touch (long press).
// Only transform and opacity are animated; neighbours slide into place with FLIP.

const DRAG_START_PX = 5;
const TOUCH_HOLD_MS = 220;
const SLIDE_MS = 160;
const DROP_MS = 180;
const EDGE_PX = 64;
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * @param {HTMLElement} root     container with the lists
 * @param {object} opts
 *   item:  selector for draggable items
 *   list:  selector for drop lists; an optional [data-drop-end] child marks where the items end
 *   column: selector for the element around a list whose bounds count as "over this list" (defaults to list)
 *   ignore: selector for children that must not start a drag (buttons, inputs)
 *   onDrop(item, list, fromList): called after the item lands; return a promise
 *   onClick(item, event): a press without movement
 */
export function sortable(root, opts) {
  const ignore = opts.ignore ?? 'button, a, input, label, select, textarea, .menu';
  let press = null;
  let drag = null;

  root.addEventListener('pointerdown', (e) => {
    const item = e.target.closest(opts.item);
    if (!item || !root.contains(item) || e.button !== 0 || e.target.closest(ignore)) return;
    press = { item, x: e.clientX, y: e.clientY, id: e.pointerId, touch: e.pointerType === 'touch', timer: 0 };
    if (press.touch) press.timer = setTimeout(() => press && begin(e.clientX, e.clientY), TOUCH_HOLD_MS);
    window.addEventListener('pointermove', onMove);
    window.addEventListener('pointerup', onUp);
    window.addEventListener('pointercancel', onCancel);
  });

  // Stop the page from scrolling while a touch drag is active.
  root.addEventListener('touchmove', (e) => { if (drag) e.preventDefault(); }, { passive: false });

  function onMove(e) {
    if (!press || e.pointerId !== press.id) return;
    if (!drag) {
      const moved = Math.hypot(e.clientX - press.x, e.clientY - press.y);
      if (press.touch) {
        if (moved > 8) cleanup(); // the finger is scrolling, not holding
        return;
      }
      if (moved < DRAG_START_PX) return;
      begin(press.x, press.y);
    }
    drag.px = e.clientX;
    drag.py = e.clientY;
    if (!drag.raf) drag.raf = requestAnimationFrame(frame);
  }

  function begin(x, y) {
    const { item } = press;
    const rect = item.getBoundingClientRect();
    const placeholder = document.createElement('div');
    placeholder.className = 'drag-placeholder';
    placeholder.style.height = `${rect.height}px`;
    const fromList = item.closest(opts.list);
    const origin = { list: fromList, next: item.nextSibling };
    item.before(placeholder);
    item.classList.add('is-dragging');
    item.style.width = `${rect.width}px`;
    item.style.left = `${rect.left}px`;
    item.style.top = `${rect.top}px`;
    document.body.append(item);
    document.body.classList.add('is-drag-active');
    drag = { item, placeholder, rect, fromList, origin, dx: x - rect.left, dy: y - rect.top, px: x, py: y, raf: 0 };
    window.addEventListener('keydown', onKey);
    frame();
  }

  function frame() {
    if (!drag) return;
    drag.raf = 0;
    const { item, rect, px, py } = drag;
    const tilt = reduceMotion ? '' : ' rotate(2deg) scale(1.02)';
    item.style.transform = `translate3d(${px - drag.dx - rect.left}px, ${py - drag.dy - rect.top}px, 0)${tilt}`;
    const list = listAt(px, py);
    if (list) placeIn(list, py);
    autoScroll(py);
  }

  function listAt(x, y) {
    let best = null;
    for (const list of root.querySelectorAll(opts.list)) {
      const r = list.closest(opts.column ?? opts.list).getBoundingClientRect();
      if (x >= r.left && x <= r.right) {
        if (y >= r.top - 40 && y <= r.bottom + 200) return list;
        best ??= list;
      }
    }
    return best;
  }

  function placeIn(list, y) {
    const { placeholder } = drag;
    const items = [...list.querySelectorAll(opts.item)].filter((el) => el.offsetParent !== null);
    const before = items.find((el) => {
      const r = el.getBoundingClientRect();
      return y < r.top + r.height / 2;
    });
    const anchor = before ?? list.querySelector('[data-drop-end]');
    const inPlace = placeholder.parentNode === list && (anchor ? placeholder.nextElementSibling === anchor : placeholder === list.lastElementChild);
    if (inPlace) return;
    flip([placeholder.parentNode, list], () => (anchor ? list.insertBefore(placeholder, anchor) : list.append(placeholder)));
    opts.onHover?.(list);
  }

  /** Records positions, runs the DOM change, then slides moved items from old to new spot. */
  function flip(lists, change) {
    const items = [...new Set(lists)].flatMap((l) => (l ? [...l.querySelectorAll(opts.item)] : []));
    const first = new Map(items.map((el) => [el, el.getBoundingClientRect()]));
    change();
    if (reduceMotion) return;
    for (const el of items) {
      const a = first.get(el);
      const b = el.getBoundingClientRect();
      const dx = a.left - b.left;
      const dy = a.top - b.top;
      if (dx || dy) el.animate([{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'none' }], { duration: SLIDE_MS, easing: 'cubic-bezier(.2,.8,.2,1)' });
    }
  }

  function autoScroll(y) {
    if (y < EDGE_PX) window.scrollBy(0, -Math.ceil((EDGE_PX - y) / 4));
    else if (y > innerHeight - EDGE_PX) window.scrollBy(0, Math.ceil((y - innerHeight + EDGE_PX) / 4));
    if (drag && (y < EDGE_PX || y > innerHeight - EDGE_PX)) drag.raf ||= requestAnimationFrame(frame);
  }

  function onUp(e) {
    if (!press || e.pointerId !== press.id) return;
    if (!drag) {
      const { item } = press;
      cleanup();
      opts.onClick?.(item, e);
      return;
    }
    land(false);
  }

  function onCancel() {
    if (drag) land(true);
    else cleanup();
  }

  function onKey(e) {
    if (e.key === 'Escape' && drag) land(true);
  }

  function land(cancelled) {
    const { item, placeholder, origin, fromList } = drag;
    cancelAnimationFrame(drag.raf);
    if (cancelled) {
      if (origin.next && origin.next.parentNode === origin.list) origin.list.insertBefore(placeholder, origin.next);
      else origin.list.append(placeholder);
    }
    const target = placeholder.getBoundingClientRect();
    const finish = () => {
      placeholder.replaceWith(item);
      item.classList.remove('is-dragging', 'is-landing');
      item.style.cssText = '';
      if (!cancelled) opts.onDrop?.(item, item.closest(opts.list), fromList);
    };
    item.classList.add('is-landing');
    item.style.transform = `translate3d(${target.left - drag.rect.left}px, ${target.top - drag.rect.top}px, 0)`;
    drag = null;
    cleanup();
    if (reduceMotion) finish();
    else setTimeout(finish, DROP_MS);
  }

  function cleanup() {
    if (press) clearTimeout(press.timer);
    press = null;
    document.body.classList.remove('is-drag-active');
    window.removeEventListener('pointermove', onMove);
    window.removeEventListener('pointerup', onUp);
    window.removeEventListener('pointercancel', onCancel);
    window.removeEventListener('keydown', onKey);
  }

  return { flip };
}

/** Ids of the items in a list, in order. */
export function orderOf(list, itemSelector) {
  return [...list.querySelectorAll(itemSelector)].map((el) => Number(el.dataset.id));
}
