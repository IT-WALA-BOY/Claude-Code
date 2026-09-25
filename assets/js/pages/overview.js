// Overview: complete tasks from the Due today table and filter it by category.
import { completeTask, filterRows } from '../shared.js';

export function init(root, app) {
  filterRows(root);
  root.addEventListener('change', (e) => {
    if (e.target.matches('[data-done]')) completeTask(e.target, app);
  });
}
