// Analytics: "Export report" prints the page (save as PDF from the print dialog).
export function init(root) {
  root.querySelector('[data-print]')?.addEventListener('click', () => window.print());
}
