// Schedule: schedule maker, drag blocks to move them (snaps to 15 minutes), drag the bottom edge to resize,
// click an empty slot to add a block there.
const SNAP = 15;
const MIN_LEN = 15;

const pad = (n) => String(n).padStart(2, '0');
const clock = (min) => {
  const m = ((min % 1440) + 1440) % 1440;
  const h = Math.floor(m / 60);
  return `${h % 12 || 12}:${pad(m % 60)} ${h < 12 ? 'AM' : 'PM'}`;
};
const hhmm = (min) => {
  const m = ((min % 1440) + 1440) % 1440;
  return `${pad(Math.floor(m / 60))}:${pad(m % 60)}`;
};

export function init(root, app) {
  root.addEventListener('click', async (e) => {
    const action = e.target.closest('[data-make], [data-accept], [data-dismiss]');
    if (!action) return;
    const path = action.matches('[data-make]') ? 'schedule/make' : action.matches('[data-accept]') ? 'schedule/accept' : 'schedule/dismiss';
    action.classList.add('is-loading');
    try {
      const res = await app.api(path);
      if (path === 'schedule/make') {
        app.toast(res.count ? `${res.count} blocks suggested. Accept them or drag them around.` : 'No free time left to fill this week.');
      } else {
        app.toast(path === 'schedule/accept' ? 'Suggestions added to your schedule' : 'Suggestions removed');
      }
      await app.refresh();
    } catch (err) {
      app.toast(err.message, { error: true });
    } finally {
      action.classList.remove('is-loading');
    }
  });

  const week = root.querySelector('[data-week]');
  if (week) weekGrid(week, app);
}

function weekGrid(week, app) {
  const gridStart = Number(week.dataset.gridStart);
  const gridEnd = Number(week.dataset.gridEnd);
  const pxPerMin = parseFloat(getComputedStyle(week).getPropertyValue('--hour')) / 60;
  let drag = null;

  week.addEventListener('pointerdown', (e) => {
    const block = e.target.closest('.block');
    const col = e.target.closest('.week-col');
    if (!col || e.button !== 0) return;
    if (!block) {
      // Empty slot: add a block at that time.
      const y = e.clientY - col.getBoundingClientRect().top;
      const start = gridStart + Math.floor(y / pxPerMin / 30) * 30;
      app.openRecord('block-dialog', { day: col.dataset.night, start: hhmm(start), end: hhmm(start + 60) });
      return;
    }
    e.preventDefault();
    const top = Number(block.style.getPropertyValue('--top'));
    const len = Number(block.style.getPropertyValue('--len'));
    drag = {
      block,
      mode: e.target.closest('[data-resize]') ? 'resize' : 'move',
      x: e.clientX,
      y: e.clientY,
      top,
      len,
      newTop: top,
      newLen: len,
      col,
      newCol: col,
      cols: [...week.querySelectorAll('.week-col')].map((c) => [c, c.getBoundingClientRect()]),
      moved: false,
    };
    block.setPointerCapture(e.pointerId);
  });

  week.addEventListener('pointermove', (e) => {
    if (!drag) return;
    const dx = e.clientX - drag.x;
    const dy = e.clientY - drag.y;
    if (!drag.moved && Math.hypot(dx, dy) < 4) return;
    if (!drag.moved) {
      drag.moved = true;
      drag.block.classList.add('is-active');
    }
    const steps = Math.round(dy / pxPerMin / SNAP) * SNAP;
    const { block } = drag;
    if (drag.mode === 'resize') {
      drag.newLen = Math.max(MIN_LEN, Math.min(gridEnd - gridStart - drag.top, drag.len + steps));
      block.style.setProperty('--len', drag.newLen);
    } else {
      drag.newTop = Math.max(0, Math.min(gridEnd - gridStart - drag.len, drag.top + steps));
      drag.newCol = drag.cols.find(([, r]) => e.clientX >= r.left && e.clientX <= r.right)?.[0] ?? drag.newCol;
      block.style.transform = `translate(${dx}px, ${dy}px)`;
    }
    block.querySelector('.block-time').textContent = `${clock(gridStart + drag.newTop)} to ${clock(gridStart + drag.newTop + drag.newLen)}`;
  });

  const end = async () => {
    if (!drag) return;
    const d = drag;
    drag = null;
    const { block } = d;
    block.classList.remove('is-active');
    if (!d.moved) {
      app.openRecord('block-dialog', JSON.parse(block.dataset.record));
      return;
    }
    const before = block.getBoundingClientRect();
    block.style.transform = '';
    block.style.setProperty('--top', d.newTop);
    if (d.newCol !== d.col) d.newCol.append(block);
    const after = block.getBoundingClientRect();
    block.animate([{ transform: `translate(${before.left - after.left}px, ${before.top - after.top}px)` }, { transform: 'none' }], { duration: 160, easing: 'cubic-bezier(.2,.8,.2,1)' });
    block.classList.remove('is-suggested');
    try {
      await app.api('schedule/move', {
        id: block.dataset.id,
        night: d.newCol.dataset.night,
        start: gridStart + d.newTop,
        end: gridStart + d.newTop + d.newLen,
      });
      app.refresh();
    } catch (err) {
      app.toast(err.message, { error: true });
      app.refresh();
    }
  };
  week.addEventListener('pointerup', end);
  week.addEventListener('pointercancel', end);
}
