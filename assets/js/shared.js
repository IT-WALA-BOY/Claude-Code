// Behaviours shared by several page modules. Page modules receive the runtime as `app`.

/** Marks a task done from a checkbox in a table row, with Undo. */
export async function completeTask(checkbox, app) {
  const row = checkbox.closest('[data-id]');
  const id = row.dataset.id;
  const done = checkbox.checked;
  row.classList.toggle('is-done', done);
  try {
    await app.api('tasks/done', { id, done });
    if (!done) return;
    app.toast('Task done', {
      action: 'Undo',
      onAction: async () => {
        await app.api('tasks/done', { id, done: false });
        app.refresh();
      },
    });
    setTimeout(() => app.refresh(), 700);
  } catch (err) {
    checkbox.checked = !done;
    row.classList.toggle('is-done', !done);
    app.toast(err.message, { error: true });
  }
}

/** Segmented filter: [data-filter="value"] buttons show rows whose data-cat matches. */
export function filterRows(root) {
  root.addEventListener('click', (e) => {
    const button = e.target.closest('[data-filter]');
    if (!button) return;
    const scope = button.closest('[data-filter-scope]');
    for (const b of scope.querySelectorAll('[data-filter]')) b.classList.toggle('is-active', b === button);
    const value = button.dataset.filter;
    for (const row of scope.querySelectorAll('[data-cat]')) row.hidden = value !== '' && row.dataset.cat !== value;
  });
}
