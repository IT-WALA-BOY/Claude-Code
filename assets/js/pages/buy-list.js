// Buy list: drag items between the four boxes, mark them bought (with Undo).
import { sortable, orderOf } from '../drag.js';

const ITEM = '.buy';
const pkr = (n) => `Rs ${Math.round(n).toLocaleString('en-US')}`;

export function init(root, app) {
  const matrix = root.querySelector('[data-matrix]');
  if (!matrix) return;

  const totals = () => {
    for (const quad of matrix.querySelectorAll('.quad')) {
      const items = [...quad.querySelectorAll(ITEM)];
      quad.querySelector('[data-quad-total]').textContent = pkr(items.reduce((sum, it) => sum + Number(it.dataset.pkr), 0));
      quad.querySelector('[data-quad-count]').textContent = `${items.length} ${items.length === 1 ? 'item' : 'items'}`;
    }
  };

  sortable(matrix, {
    item: ITEM,
    list: '.quad-list',
    column: '.quad',
    onDrop: async (item, list) => {
      const quad = list.closest('.quad');
      totals();
      try {
        await app.api('buy/move', { id: item.dataset.id, important: quad.dataset.important === '1', urgent: quad.dataset.urgent === '1', order: orderOf(list, ITEM) });
      } catch (err) {
        app.toast(err.message, { error: true });
        app.refresh();
      }
    },
  });

  matrix.addEventListener('click', async (e) => {
    const button = e.target.closest('[data-bought]');
    if (!button) return;
    const item = button.closest(ITEM);
    const { id } = item.dataset;
    item.classList.add('is-leaving');
    try {
      await app.api('buy/bought', { id });
      app.toast('Bought and added to Expenses', {
        action: 'Undo',
        onAction: async () => {
          await app.api('buy/unbought', { id });
          app.refresh();
        },
      });
      setTimeout(() => app.refresh(), 250);
    } catch (err) {
      item.classList.remove('is-leaving');
      app.toast(err.message, { error: true });
    }
  });
}
