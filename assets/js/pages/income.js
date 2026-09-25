// Income: filter the month table, chase late invoices, mark invoices paid.
import { filterRows } from '../shared.js';

export function init(root, app) {
  filterRows(root);
  root.addEventListener('click', async (e) => {
    const button = e.target.closest('[data-remind], [data-paid]');
    if (!button) return;
    const id = button.closest('[data-id]').dataset.id;
    const paid = button.matches('[data-paid]');
    button.classList.add('is-loading');
    try {
      await app.api(paid ? 'income/paid' : 'income/remind', { id });
      app.toast(paid ? 'Marked as paid' : 'Reminder task added for today');
      if (paid) app.refresh();
    } catch (err) {
      app.toast(err.message, { error: true });
    } finally {
      button.classList.remove('is-loading');
    }
  });
}
