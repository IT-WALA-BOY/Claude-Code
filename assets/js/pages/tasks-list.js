// Tasks list: tick to finish, collapse status groups, open a task from search.
import { completeTask } from '../shared.js';

export function init(root, app) {
  root.addEventListener('change', (e) => {
    if (e.target.matches('[data-done]')) completeTask(e.target, app);
  });
  root.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-collapse]');
    if (!toggle) return;
    const group = toggle.closest('[data-group]');
    const collapsed = group.classList.toggle('is-collapsed');
    toggle.setAttribute('aria-expanded', String(!collapsed));
  });
  const openId = root.querySelector('[data-open-id]')?.dataset.openId;
  const row = openId && root.querySelector(`tr[data-id="${openId}"]`);
  if (row) {
    row.scrollIntoView({ block: 'center' });
    row.classList.add('flash');
    app.openRecord('task-dialog', JSON.parse(row.dataset.record));
  }
}
