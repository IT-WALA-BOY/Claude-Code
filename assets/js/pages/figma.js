// Figma projects: collapse groups, remember when a file was opened.
export function init(root, app) {
  root.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-collapse]');
    if (toggle) {
      const collapsed = toggle.closest('[data-group]').classList.toggle('is-collapsed');
      toggle.setAttribute('aria-expanded', String(!collapsed));
      return;
    }
    const opened = e.target.closest('[data-opened]');
    if (opened) app.api('figma/opened', { id: opened.closest('[data-id]').dataset.id }).catch(() => {});
  });
}
