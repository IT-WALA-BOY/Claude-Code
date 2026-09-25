// LinkedIn: drag leads between stages, mark follow-ups done, count today's outreach.
import { sortable, orderOf } from '../drag.js';

const LEAD = '.lead';

export function init(root, app) {
  const pipeline = root.querySelector('[data-pipeline]');
  if (pipeline) {
    const recount = () => {
      for (const col of pipeline.querySelectorAll('.pcol')) col.querySelector('[data-count]').textContent = col.querySelectorAll(LEAD).length;
    };
    sortable(pipeline, {
      item: LEAD,
      list: '.pcol-list',
      column: '.pcol',
      onDrop: async (lead, list) => {
        const stage = list.closest('.pcol').dataset.stage;
        recount();
        try {
          await app.api('linkedin/move', { id: lead.dataset.id, stage, order: orderOf(list, LEAD) });
          if (stage === 'won') app.toast('Nice work. Lead marked as won.');
        } catch (err) {
          app.toast(err.message, { error: true });
          app.refresh();
        }
      },
      onClick: (lead, e) => {
        if (!e.target.closest('a, button')) app.openRecord('lead-dialog', JSON.parse(lead.dataset.record));
      },
    });
    pipeline.addEventListener('click', async (e) => {
      const done = e.target.closest('[data-followed]');
      if (!done) return;
      const lead = done.closest(LEAD);
      lead.classList.remove('is-late');
      done.disabled = true;
      try {
        const res = await app.api('linkedin/followed', { id: lead.dataset.id });
        app.toast(`Follow-up logged. Next one on ${res.next}.`);
        app.refresh();
      } catch (err) {
        done.disabled = false;
        app.toast(err.message, { error: true });
      }
    });
  }

  // Counters: +1 / -1, painted now and saved in the background.
  root.addEventListener('click', async (e) => {
    const button = e.target.closest('[data-step]');
    if (!button) return;
    const counter = button.closest('[data-metric]');
    const step = Number(button.dataset.step);
    const before = Number(counter.dataset.count);
    const paint = (n) => {
      counter.dataset.count = n;
      counter.querySelector('[data-value]').textContent = n;
      const target = Number(counter.dataset.target);
      const segs = counter.querySelectorAll('.counter-bar i');
      const on = Math.min(segs.length, Math.round((n / target) * segs.length));
      segs.forEach((s, i) => s.classList.toggle('on', i < on));
      counter.querySelector('.counter-bar').classList.toggle('is-met', n >= target);
    };
    paint(Math.max(0, before + step));
    try {
      const res = await app.api('linkedin/count', { metric: counter.dataset.metric, step });
      paint(res.count);
    } catch (err) {
      paint(before);
      app.toast(err.message, { error: true });
    }
  });
}
