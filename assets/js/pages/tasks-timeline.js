// Timeline: drag a bar to move its dates, drag an edge to change start or due. Snaps to whole days.
const DAY = 86400000;
const fmt = new Intl.DateTimeFormat('en-GB', { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'UTC' });
const toDate = (iso) => new Date(`${iso}T00:00:00Z`);
const toIso = (d) => d.toISOString().slice(0, 10);
const addDays = (iso, n) => toIso(new Date(toDate(iso).getTime() + n * DAY));
const span = (a, b) => Math.round((toDate(b) - toDate(a)) / DAY);

export function init(root, app) {
  const tl = root.querySelector('.tl');
  if (!tl) return;
  const from = tl.dataset.from;
  const days = Number(tl.dataset.days);
  const tip = document.createElement('div');
  tip.className = 'tl-tip';
  let drag = null;

  root.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-collapse]');
    if (!toggle) return;
    const collapsed = toggle.closest('[data-group]').classList.toggle('is-collapsed');
    toggle.setAttribute('aria-expanded', String(!collapsed));
  });

  tl.addEventListener('pointerdown', (e) => {
    const bar = e.target.closest('.tl-bar');
    if (!bar || e.button !== 0) return;
    e.preventDefault();
    const track = bar.parentElement;
    drag = {
      bar,
      mode: e.target.dataset.handle ?? 'move',
      x: e.clientX,
      dayPx: track.getBoundingClientRect().width / days,
      start: bar.dataset.start,
      due: bar.dataset.due,
      shift: 0,
      moved: false,
    };
    bar.setPointerCapture(e.pointerId);
    bar.classList.add('is-active');
    bar.append(tip);
  });

  tl.addEventListener('pointermove', (e) => {
    if (!drag) return;
    const dx = e.clientX - drag.x;
    if (Math.abs(dx) > 3) drag.moved = true;
    const shift = Math.round(dx / drag.dayPx);
    const { bar, mode } = drag;
    if (mode === 'move') bar.style.transform = `translateX(${dx}px)`;
    if (shift === drag.shift && drag.moved) return;
    drag.shift = shift;
    const [start, due] = range(drag, shift);
    if (mode !== 'move') {
      bar.style.setProperty('--s', Math.max(0, span(from, start)));
      bar.style.setProperty('--e', Math.min(days - 1, span(from, due)));
    }
    const n = span(start, due) + 1;
    tip.textContent = `${fmt.format(toDate(start))} to ${fmt.format(toDate(due))} · ${n} ${n === 1 ? 'day' : 'days'}`;
  });

  const end = async () => {
    if (!drag) return;
    const { bar, moved } = drag;
    const [start, due] = range(drag, drag.shift);
    const old = [bar.dataset.start, bar.dataset.due];
    drag = null;
    tip.remove();
    bar.classList.remove('is-active');
    if (!moved) {
      bar.style.transform = '';
      app.openRecord('task-dialog', JSON.parse(bar.closest('[data-record]').dataset.record));
      return;
    }
    place(bar, start, due);
    if (start === old[0] && due === old[1]) return;
    try {
      await app.api('tasks/dates', { id: bar.closest('[data-id]').dataset.id, start_on: start, due_on: due });
      app.toast('Dates saved');
    } catch (err) {
      place(bar, ...old);
      app.toast(err.message, { error: true });
    }
  };
  tl.addEventListener('pointerup', end);
  tl.addEventListener('pointercancel', end);

  function range({ mode, start, due }, shift) {
    if (mode === 'move') return [addDays(start, shift), addDays(due, shift)];
    if (mode === 'start') {
      const s = addDays(start, shift);
      return [s <= due ? s : due, due];
    }
    const d = addDays(due, shift);
    return [start, d >= start ? d : start];
  }

  /** Snaps the bar to its dates; the move animates from where the pointer left it. */
  function place(bar, start, due) {
    const before = bar.getBoundingClientRect().left;
    bar.dataset.start = start;
    bar.dataset.due = due;
    bar.style.transform = '';
    bar.style.setProperty('--s', Math.max(0, span(from, start)));
    bar.style.setProperty('--e', Math.min(days - 1, span(from, due)));
    const dx = before - bar.getBoundingClientRect().left;
    if (dx) bar.animate([{ transform: `translateX(${dx}px)` }, { transform: 'none' }], { duration: 160, easing: 'cubic-bezier(.2,.8,.2,1)' });
  }
}
