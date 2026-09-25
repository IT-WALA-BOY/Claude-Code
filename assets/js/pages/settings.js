// Settings: fill the category editor from the page data.
export function init(root) {
  const data = root.querySelector('[data-categories]');
  const list = root.querySelector('[data-list="categories"]');
  if (!data || !list || list.children.length) return;
  const template = root.querySelector('template[data-row-for="categories"]');
  for (const cat of JSON.parse(data.textContent)) {
    const row = template.content.firstElementChild.cloneNode(true);
    for (const el of row.querySelectorAll('[data-key]')) el.value = cat[el.dataset.key];
    list.append(row);
  }
}
