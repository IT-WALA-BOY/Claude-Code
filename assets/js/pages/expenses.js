// Expenses: filter the table, buy a Connects pack in one tap.
import { filterRows } from '../shared.js';

export function init(root, app) {
  filterRows(root);
  root.addEventListener('click', async (e) => {
    const buy = e.target.closest('[data-buy-connects]');
    if (!buy) return;
    buy.classList.add('is-loading');
    try {
      const res = await app.api('expenses/buy_connects');
      app.toast('200 Connects added and logged as an expense');
      await app.refresh({ flash: res.id });
    } catch (err) {
      app.toast(err.message, { error: true });
    } finally {
      buy.classList.remove('is-loading');
    }
  });
}
