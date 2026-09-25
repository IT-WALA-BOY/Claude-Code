// Goals: page actions and the breakdown wizard. The plan is worked out here, live, with no AI needed.
const DAY = 86400000;
const toDate = (iso) => new Date(`${iso}T00:00:00Z`);
const toIso = (d) => d.toISOString().slice(0, 10);
const addDays = (iso, n) => toIso(new Date(toDate(iso).getTime() + n * DAY));
const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
// Same style as the server: "26 Sep", "Sat 26 Sep".
const short = { format: (d) => `${d.getUTCDate()} ${MONTHS[d.getUTCMonth()]}` };
const long = { format: (d) => `${WEEKDAYS[d.getUTCDay()]} ${short.format(d)}` };
const num = (n) => String(Math.round(n * 10) / 10);
const OFF_DAYS = [[], [0], [0, 6]]; // rest days: none, Sunday, weekend

/** Study days from `start`, skipping rest days, until `count` days or the date `until`. */
function studyDays(start, rest, { count = Infinity, until = null } = {}) {
  const out = [];
  for (let d = start; out.length < count && (!until || d <= until) && out.length < 1000; d = addDays(d, 1)) {
    if (!OFF_DAYS[rest].includes(toDate(d).getUTCDay())) out.push(d);
  }
  return out;
}

function amount(n, unit) {
  if (unit !== 'h') return `${num(n)} ${unit}`;
  const h = Math.floor(n);
  const m = Math.round((n - h) * 60);
  return m ? (h ? `${h} h ${m} m` : `${m} m`) : `${h} h`;
}

/** The plan: per day amount, finish date and dates for each part. */
export function plan({ target, done, unit, mode, due, perDay, rest, buffer, parts, today }) {
  const start = addDays(today, 1);
  const left = Math.max(0, target - done) * (buffer ? 1.1 : 1);
  let days;
  if (mode === 'daily') {
    if (!(perDay > 0)) return null;
    days = studyDays(start, rest, { count: Math.max(1, Math.ceil(left / perDay)) });
  } else {
    if (!due || due < start) return null;
    days = studyDays(start, rest, { until: due });
    if (!days.length) return null;
    perDay = left / days.length;
  }
  let cursor = 0;
  const scale = buffer ? 1.1 : 1;
  const partDates = parts.map((p) => {
    const size = Math.max(0, p.target - (p.done || 0)) * scale;
    if (!size) return { ...p, done: true };
    const s = Math.floor(cursor / perDay);
    cursor += size;
    const e = Math.max(s, Math.ceil(cursor / perDay) - 1);
    return { ...p, from: days[Math.min(s, days.length - 1)], to: days[Math.min(e, days.length - 1)] };
  });
  const span = Math.round((toDate(days[days.length - 1]) - toDate(start)) / DAY) + 1;
  return { perDay, days: days.length, span, start, finish: days[days.length - 1], parts: partDates, left, unit };
}

export function init(root, app) {
  const wizard = document.getElementById('goal-wizard');
  const form = wizard?.querySelector('form');
  if (!form) return;
  const f = form.elements;
  const partsList = form.querySelector('[data-list="parts"]');
  const today = document.querySelector('meta[name="today"]').content; // app clock (Pacific), from the server
  let step = 1;
  let partsTouched = false;
  let current = null;

  const values = () => ({
    target: parseFloat(f.target.value) || 0,
    done: parseFloat(f.done.value) || 0,
    unit: f.unit.value.trim() || 'h',
    mode: f.mode.value || 'date',
    due: f.due_on.value,
    perDay: parseFloat(f.per_day.value),
    rest: Number(f.rest_days.value) || 0,
    buffer: f.buffer.checked,
    parts: [...partsList.querySelectorAll('[data-row]')].map((row) => ({
      title: row.querySelector('[data-key="title"]').value.trim(),
      target: parseFloat(row.querySelector('[data-key="target"]').value) || 0,
      done: parseFloat(row.querySelector('[data-key="done"]').value) || 0,
    })).filter((p) => p.title && p.target > 0),
    today,
  });

  // Parts: regenerate equal parts from the count until the user edits a row by hand.
  const syncParts = () => {
    if (partsTouched) return;
    const count = Math.min(40, Math.max(0, parseInt(f.part_count.value, 10) || 0));
    const total = parseFloat(f.target.value) || 0;
    const rows = [...partsList.querySelectorAll('[data-row]')];
    const size = count ? Math.round((total / count) * 2) / 2 : 0;
    while (rows.length > count) rows.pop().remove();
    for (let i = 0; i < count; i++) {
      let row = rows[i];
      if (!row) {
        form.querySelector(`[data-add-row="parts"]`).click();
        row = partsList.lastElementChild;
      }
      const title = row.querySelector('[data-key="title"]');
      if (!title.value || /^Part \d+$/.test(title.value)) title.value = `Part ${i + 1}`;
      row.querySelector('[data-key="target"]').value = size || '';
    }
  };

  const render = () => {
    const v = values();
    current = plan(v);
    form.querySelector('[data-unit-label]').textContent = v.unit;
    for (const el of form.querySelectorAll('[data-mode]')) el.hidden = el.dataset.mode !== v.mode;
    form.querySelector('[data-starts]').textContent = `Starts tomorrow, ${long.format(toDate(addDays(today, 1)))}`;
    const big = form.querySelector('[data-plan-big]');
    const per = form.querySelector('[data-plan-per]');
    const sub = form.querySelector('[data-plan-sub]');
    const list = form.querySelector('[data-plan-parts]');
    const toggle = form.querySelector('[data-block-toggle]');
    if (!current || !v.target) {
      big.textContent = '0';
      per.textContent = 'a day';
      sub.textContent = v.mode === 'daily' ? 'Set how much per day to see the finish date.' : 'Pick a finish date to see the daily pace.';
      list.innerHTML = '';
      toggle.hidden = true;
      return;
    }
    const perWeek = current.perDay * (7 - v.rest);
    const useWeek = v.unit !== 'h' && current.perDay < 1;
    if (v.unit === 'h') {
      big.textContent = amount(Math.round(current.perDay * 12) / 12, 'h'); // nearest 5 minutes
      per.textContent = 'a day';
    } else {
      big.textContent = num(useWeek ? perWeek : current.perDay);
      per.textContent = `${v.unit} a ${useWeek ? 'week' : 'day'}`;
    }
    const restNote = ['', ', Sundays off', ', weekends off'][v.rest];
    sub.textContent = `${amount(current.left, v.unit)} over ${current.days} study days (${current.span} days${restNote}). Done by ${long.format(toDate(current.finish))}.`;
    list.innerHTML = current.parts.length
      ? current.parts.map((p, i) => `<li><span class="truncate">${app.esc(`Part ${i + 1} · ${p.title}`)}</span><small>${p.done ? 'Done' : `${short.format(toDate(p.from))}${p.from !== p.to ? ` to ${short.format(toDate(p.to))}` : ''}`}</small></li>`).join('')
      : '<li class="muted">No parts. Add them in step 1 to get sub-goals.</li>';
    toggle.hidden = v.unit !== 'h';
    form.querySelector('[data-block-text]').textContent = `Add a ${amount(Math.ceil(current.perDay * 4) / 4, 'h')} daily block to Schedule`;
    const review = form.querySelector('[data-review]');
    review.innerHTML = [
      ['Goal', app.esc(f.title.value || 'Untitled')],
      ['Total', amount(v.target, v.unit) + (v.done ? `, ${amount(v.done, v.unit)} done` : '')],
      ['Pace', `${amount(current.perDay, v.unit)} a day, ${7 - v.rest} days a week${v.buffer ? ', with a 10% buffer' : ''}`],
      ['Finish', long.format(toDate(current.finish))],
      ['Parts', current.parts.length ? String(current.parts.length) : 'None'],
    ].map(([k, val]) => `<dt>${k}</dt><dd>${val}</dd>`).join('');
  };

  const go = (n) => {
    if (n > step && step === 1 && (!f.title.value.trim() || !(parseFloat(f.target.value) > 0))) {
      app.toast('Add a goal name and a total first.', { error: true });
      (f.title.value.trim() ? f.target : f.title).focus();
      return;
    }
    if (n > step && step === 2 && !current) {
      app.toast(f.mode.value === 'daily' ? 'Set how much per day.' : 'Pick a finish date after today.', { error: true });
      return;
    }
    step = n;
    for (const s of form.querySelectorAll('[data-step]')) s.hidden = Number(s.dataset.step) !== step;
    for (const dot of form.querySelectorAll('[data-step-dot]')) {
      const d = Number(dot.dataset.stepDot);
      dot.classList.toggle('is-done', d < step);
      dot.classList.toggle('is-current', d === step);
    }
    form.querySelector('[data-back]').style.visibility = step > 1 ? 'visible' : 'hidden';
    form.querySelector('[data-next]').hidden = step === 3;
    form.querySelector('[data-save]').hidden = step !== 3;
  };

  const open = (data = {}) => {
    partsTouched = Boolean(data.id);
    app.openRecord(wizard, {
      mode: 'date',
      rest_days: 1,
      unit: 'h',
      due_on: addDays(today, 30),
      ...data,
      part_count: data.parts?.length ?? '',
      per_day: data.daily_minutes ? data.daily_minutes / 60 : '',
    });
    go(1);
    render();
  };

  form.addEventListener('input', (e) => {
    if (e.target.closest('[data-list="parts"]')) partsTouched = true;
    if (e.target.name === 'part_count' || e.target.name === 'target') syncParts();
    render();
  });
  form.addEventListener('change', render);
  form.addEventListener('click', async (e) => {
    const ask = e.target.closest('[data-ai-split]');
    if (ask) {
      ask.classList.add('is-loading');
      try {
        const { parts } = await app.api('goals/split', { title: f.title.value, target: f.target.value, unit: f.unit.value, part_count: f.part_count.value });
        partsList.innerHTML = '';
        parts.forEach(() => form.querySelector('[data-add-row="parts"]').click());
        [...partsList.querySelectorAll('[data-row]')].forEach((row, i) => {
          row.querySelector('[data-key="title"]').value = parts[i].title;
          row.querySelector('[data-key="target"]').value = parts[i].target;
        });
        f.part_count.value = parts.length;
        partsTouched = true;
        render();
        app.toast(`Claude suggested ${parts.length} parts. Edit anything you like.`);
      } catch (err) {
        app.toast(err.message, { error: true });
      } finally {
        ask.classList.remove('is-loading');
      }
      return;
    }
    if (e.target.closest('[data-next]')) go(step + 1);
    if (e.target.closest('[data-back]')) go(step - 1);
    if (e.target.closest('[data-remove-row], [data-add-row]')) {
      partsTouched = true;
      requestAnimationFrame(render);
    }
  });
  form.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && step < 3 && !e.target.closest('[data-list]')) {
      e.preventDefault();
      go(step + 1);
    }
  });
  // Before saving, turn the plan into the fields the server stores.
  form.addEventListener('submit', () => {
    if (!current) return;
    f.due_on.value = current.finish;
    f.per_day.value = current.perDay;
  }, true);
  form.addEventListener('saved', async (e) => {
    const { id, blocks } = e.detail;
    if (blocks) app.toast(`${blocks} study blocks added to Schedule`);
    location.href = `${location.pathname}?goal=${id}`;
  });

  root.addEventListener('click', async (e) => {
    if (e.target.closest('[data-wizard]')) open();
    const card = e.target.closest('[data-goal]');
    if (!card) return;
    const id = card.dataset.goal;
    if (e.target.closest('[data-wizard-edit]')) open(JSON.parse(card.dataset.record));
    const run = async (path, data, message, after = () => app.refresh()) => {
      try {
        const res = await app.api(path, { id, ...data });
        app.toast(typeof message === 'function' ? message(res) : message);
        after();
      } catch (err) {
        app.toast(err.message, { error: true });
      }
    };
    if (e.target.closest('[data-goal-schedule]')) {
      run('goals/schedule', {}, (res) => (res.count ? `${res.count} study blocks added to Schedule` : 'Your next 7 nights already have this block'));
    }
    const status = e.target.closest('[data-goal-status]');
    if (status) run('goals/status', { status: status.dataset.goalStatus }, status.dataset.goalStatus === 'done' ? 'Goal marked as done' : 'Goal reopened');
    if (e.target.closest('[data-goal-delete]') && (await app.confirmAction('This goal, its parts and its log will be deleted.'))) {
      run('goals/delete', {}, 'Goal deleted', () => { location.href = location.pathname; });
    }
  });
}
