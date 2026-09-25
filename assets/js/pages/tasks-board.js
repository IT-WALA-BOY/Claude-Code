// Tasks board: drag cards between columns, move menu, checklist ticks, show more, Later rail.
import { sortable, orderOf } from '../drag.js';

const CARD = '.kcard';

export function init(root, app) {
  const board = root.querySelector('[data-board]');
  if (!board) return;

  const columnOf = (el) => el.closest('.kcol');
  const recount = () => {
    for (const col of board.querySelectorAll('.kcol')) {
      const n = col.querySelectorAll(CARD).length;
      for (const badge of col.querySelectorAll('[data-count]')) badge.textContent = n;
    }
  };
  const save = async (card, list, fromStatus) => {
    const status = columnOf(list).dataset.status;
    card.classList.toggle('is-done', status === 'done');
    recount();
    try {
      await app.api('tasks/move', { id: card.dataset.id, status, order: orderOf(list, CARD) });
      if ((status === 'done') !== (fromStatus === 'done')) {
        app.toast(status === 'done' ? 'Task done' : 'Task reopened');
      }
    } catch (err) {
      app.toast(err.message, { error: true });
      app.refresh();
    }
  };

  const { flip } = sortable(board, {
    item: CARD,
    list: '.kcol-list',
    column: '.kcol',
    onHover: (list) => columnOf(list).classList.add('is-open'),
    onDrop: (card, list, fromList) => {
      if (list === fromList && card.dataset.lastOrder === orderOf(list, CARD).join()) return;
      save(card, list, columnOf(fromList).dataset.status);
    },
    onClick: (card, e) => {
      if (!e.target.closest('.kcard-checks, .menu-wrap')) app.openRecord('task-dialog', JSON.parse(card.dataset.record));
    },
  });

  // Remember the order when a drag starts, so a drop in the same spot saves nothing.
  board.addEventListener('pointerdown', (e) => {
    const card = e.target.closest(CARD);
    if (card) card.dataset.lastOrder = orderOf(card.closest('.kcol-list'), CARD).join();
  });

  board.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && e.target.matches(CARD)) app.openRecord('task-dialog', JSON.parse(e.target.dataset.record));
  });

  board.addEventListener('click', (e) => {
    const move = e.target.closest('[data-move-to]');
    if (move) {
      const card = move.closest(CARD);
      const fromStatus = columnOf(card).dataset.status;
      const col = board.querySelector(`.kcol[data-status="${move.dataset.moveTo}"]`);
      const list = col.querySelector('.kcol-list');
      col.classList.add('is-open');
      flip([card.parentNode, list], () => list.prepend(card));
      save(card, list, fromStatus);
      return;
    }
    const more = e.target.closest('[data-more]');
    if (more) {
      for (const card of columnOf(more).querySelectorAll('.is-extra')) card.classList.remove('is-extra');
      more.remove();
      return;
    }
    if (e.target.closest('[data-rail]')) columnOf(e.target).classList.toggle('is-open');
  });

  // Tick a checklist item on the card: update progress now, save in the background.
  board.addEventListener('change', async (e) => {
    const box = e.target.closest('[data-check]');
    if (!box) return;
    const card = box.closest(CARD);
    const boxes = [...card.querySelectorAll('[data-check]')];
    const record = JSON.parse(card.dataset.record);
    record.checklist[Number(box.dataset.check)].done = box.checked;
    const done = record.checklist.filter((c) => c.done).length;
    const total = record.checklist.length;
    const paint = (d) => {
      const pct = Math.round((d / total) * 100);
      card.querySelector('[data-progress]').textContent = `${pct}%`;
      card.querySelectorAll('.segbar span').forEach((s, i) => s.classList.toggle('on', i < Math.round(pct / 25)));
      const checks = card.querySelector('[data-checks]');
      if (checks) checks.textContent = `${d}/${total}`;
    };
    paint(done);
    card.dataset.record = JSON.stringify(record);
    try {
      await app.api('tasks/check', { id: card.dataset.id, index: box.dataset.check, done: box.checked });
    } catch (err) {
      box.checked = !box.checked;
      record.checklist[Number(box.dataset.check)].done = box.checked;
      card.dataset.record = JSON.stringify(record);
      paint(boxes.filter((b) => b.checked).length);
      app.toast(err.message, { error: true });
    }
  });
}
