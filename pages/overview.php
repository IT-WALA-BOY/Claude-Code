<?php
declare(strict_types=1);

$counts = task_counts();
$openChange = task_open_change();
$income = income_month();
$weeks = income_weeks();
$spend = spend_month();
$plan = schedule_today();
$li = li_counts();
$due = tasks_list(['open' => true, 'due_until' => today(), 'order' => 't.due_on, t.due_time IS NULL, t.due_time, t.position']);
$checklists = task_checklists(array_column($due, 'id'));
$goals = goals_list();
$attention = attention_items();
$monthName = now()->format('F');
$first = greeting_name();

// Focus heatmap: weeks of this month, Sunday first.
$monthStart = day(month_start());
$gridStart = $monthStart->modify('-' . $monthStart->format('w') . ' days');
$gridEnd = day(month_end())->modify('+' . (6 - (int) day(month_end())->format('w')) . ' days');
$focus = focus_hours($gridStart->format('Y-m-d'), $gridEnd->format('Y-m-d'));
$monthFocus = array_filter($focus, fn ($d) => $d >= month_start() && $d <= month_end(), ARRAY_FILTER_USE_KEY);
$bestDay = $monthFocus ? array_search(max($monthFocus), $monthFocus, true) : null;
$level = fn (float $h): int => match (true) { $h <= 0 => 0, $h < 3 => 1, $h < 4.5 => 2, $h < 6 => 3, default => 4 };

$dueCats = array_unique(array_filter(array_column($due, 'cat_name')));
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">Welcome back, <?= e($first) ?></h2>
    <p><?= e(now()->format('l, j M')) ?> · <?= e(plural(count($due), 'task')) ?> due tonight and <?= e(usd($income['left'])) ?> left to your <?= e($monthName) ?> goal</p>
  </div>
  <div class="actions">
    <button class="btn" type="button" data-open="income-dialog"><?= icon('plus', 16) ?>Log income</button>
    <button class="btn btn-primary" type="button" data-open="task-dialog" data-fill="<?= e(json_encode(['due_on' => today()])) ?>"><?= icon('plus', 16) ?>Add task</button>
  </div>
</div>

<div class="grid grid-4">
  <?= kpi_card(
      'list-todo', 'Pending tasks', (string) $counts['open'],
      trend_chip((float) $openChange, ($openChange > 0 ? '+' : '') . $openChange . ' this week', false),
      '<b class="' . ($counts['overdue'] ? 'tone-negative' : '') . '">' . $counts['overdue'] . '</b> overdue · ' . $counts['today'] . ' due today',
      url('tasks')
  ) ?>
  <?= kpi_card(
      'calendar-clock', 'Planned today', fmt_hours($plan['hours']),
      trend_chip($plan['change'], ($plan['change'] > 0 ? '+' : '') . fmt_hours($plan['change']), true),
      '<b>' . $plan['left'] . '</b> ' . ($plan['left'] === 1 ? 'block' : 'blocks') . ' left' . ($plan['next'] ? ' · next at ' . e(fmt_time($plan['next']['starts_at'])) : ''),
      url('schedule')
  ) ?>
  <?= kpi_card(
      'wallet', 'Spent this month', usd(round($spend['total'])),
      trend_chip($spend['change'], signed_pct($spend['change']), false),
      '<b>' . e(usd(round($spend['connects']))) . '</b> on Connects this month',
      url('expenses')
  ) ?>
  <?= kpi_card(
      'send', 'Follow-ups due', (string) $li['due'],
      $li['due_today'] ? trend_chip(1, '+' . $li['due_today'] . ' today', false) : '',
      '<b class="' . ($li['overdue'] ? 'tone-negative' : '') . '">' . $li['overdue'] . '</b> overdue · ' . e(plural($li['pipeline'], 'lead')) . ' in pipeline',
      url('linkedin')
  ) ?>
</div>

<div class="grid grid-main section-gap">
  <section class="card">
    <?= card_head('circle-dollar-sign', 'Income this month', '<a class="btn btn-sm" href="' . e(url('income')) . '">' . icon('calendar', 16) . e(day(month_start())->format('j') . ' to ' . day(month_end())->format('j M Y')) . '</a>') ?>
    <div class="panel income-panel">
      <div class="row-between">
        <p class="income-figure"><span class="t-display"><?= e(usd($income['total'])) ?></span><span class="muted">of <?= e(usd($income['goal'])) ?> goal</span></p>
        <?= trend_chip($income['change'], signed_pct($income['change']) . ' vs ' . $income['last_month_label'], true) ?>
      </div>
      <?= progress_bar($income['percent'], '', 'bar-thick') ?>
      <div class="row-between meta income-meta">
        <span><b class="strong"><?= $income['percent'] ?>%</b> of goal · Upwork <?= e(usd($income['upwork'])) ?> · Direct <?= e(usd($income['direct'])) ?></span>
        <?php if ($income['left'] > 0): ?>
          <span><b class="tone-negative"><?= e(usd(round($income['per_day']))) ?>/day</b> needed for the last <?= e(plural($income['days_left'], 'day')) ?></span>
        <?php else: ?>
          <span class="tone-positive">Goal reached</span>
        <?php endif ?>
      </div>
      <div class="legend">
        <span><i class="dot legend-upwork"></i>Upwork</span>
        <span><i class="dot legend-direct"></i>Direct clients</span>
        <span><i class="dot legend-short"></i>Short of weekly pace</span>
        <span><i class="dot legend-future"></i>Weeks ahead</span>
      </div>
      <?php
      $bars = [];
      foreach ($weeks['weeks'] as $w) {
          $short = $w['state'] === 'current' ? max(0, $w['pace'] - $w['total']) : 0;
          $tip = '<b>' . e($w['label']) . ($w['state'] === 'current' ? ' · so far' : '') . '</b>'
              . '<div class="tip-row"><i class="dot legend-upwork"></i>Upwork<span>' . e(usd($w['upwork'])) . '</span></div>'
              . '<div class="tip-row"><i class="dot legend-direct"></i>Direct<span>' . e(usd($w['direct'])) . '</span></div>'
              . ($short > 0 ? '<div class="tip-row tone-negative"><i class="dot legend-short"></i>Short<span>' . e(usd(round($short))) . '</span></div>' : '');
          $bars[] = [
              'label' => $w['label'],
              'segments' => [[$w['upwork'], 'seg-upwork', 'Upwork'], [$w['direct'], 'seg-direct', 'Direct']],
              'short' => $short,
              'state' => $w['state'] === 'future' ? 'future' : '',
              'tip' => $w['state'] === 'future' ? null : $tip,
          ];
      }
      echo bar_chart($bars, ['line' => [$weeks['pace'], 'Pace ' . usd(round($weeks['pace'])) . ' a week'], 'height' => 190]);
      ?>
    </div>
  </section>

  <section class="card">
    <?= card_head('calendar-days', "Today's schedule", '<a class="link" href="' . e(url('schedule')) . '">Open' . icon('chevron-right', 16) . '</a>') ?>
    <div class="panel agenda">
      <?php if (!$plan['blocks']): ?>
        <?= empty_state('calendar-plus', 'Nothing planned tonight', 'Add a block or let the schedule maker fill your evening.', '<a class="btn btn-sm" href="' . e(url('schedule')) . '">Plan tonight</a>', 'empty-sm') ?>
      <?php endif ?>
      <?php $nowShown = false; $nowTime = now()->format('Y-m-d H:i:s'); ?>
      <?php foreach ($plan['blocks'] as $b): ?>
        <?php if (!$nowShown && $b['starts_at'] > $nowTime): $nowShown = true; ?>
          <div class="now-line"><span>Now · <?= e(fmt_time(now())) ?></span></div>
        <?php endif ?>
        <div class="agenda-item c-<?= e($b['cat_color'] ?: 'gray') ?><?= $b['ends_at'] < $nowTime ? ' is-past' : '' ?>">
          <span class="agenda-time"><?= icon('clock', 14) ?><?= e(fmt_range($b['starts_at'], $b['ends_at'])) ?></span>
          <b><?= e($b['title']) ?></b>
          <small><?= e(implode(' · ', array_filter([$b['cat_name'], $b['note']]))) ?></small>
        </div>
      <?php endforeach ?>
      <?php if ($plan['blocks'] && !$nowShown): ?>
        <div class="now-line"><span>Now · <?= e(fmt_time(now())) ?></span></div>
      <?php endif ?>
    </div>
  </section>
</div>

<div class="grid grid-main section-gap">
  <section class="card" data-filter-scope>
    <?php
    $tools = '<div class="seg seg-sm" role="tablist"><button class="is-active" type="button" data-filter="">All</button>';
    foreach ($dueCats as $cat) {
        $tools .= '<button type="button" data-filter="' . e($cat) . '">' . e($cat) . '</button>';
    }
    $tools .= '</div><a class="link" href="' . e(url('tasks', ['view' => 'list'])) . '">All tasks' . icon('chevron-right', 16) . '</a>';
    echo card_head('square-check-big', 'Due today', $tools, count($due));
    ?>
    <div class="panel panel-flush">
      <?php if (!$due): ?>
        <?= empty_state('party-popper', 'Nothing due today', 'Enjoy the quiet or pull a task forward from the board.', '<a class="btn btn-sm" href="' . e(url('tasks')) . '">Open board</a>') ?>
      <?php else: ?>
      <div class="table-scroll">
        <table class="tbl tbl-fixed">
          <thead><tr><th class="col-check"><span class="sr-only">Done</span></th><th>Task</th><th class="w-100">Category</th><th class="w-120">Priority</th><th class="w-120">Due</th><th class="col-menu"><span class="sr-only">Actions</span></th></tr></thead>
          <tbody>
          <?php foreach ($due as $t): ?>
            <tr data-id="<?= (int) $t['id'] ?>" data-record="<?= e(task_json($t, $checklists[$t['id']] ?? [])) ?>" data-cat="<?= e($t['cat_name']) ?>">
              <td class="col-check"><input class="check" type="checkbox" data-done aria-label="Mark done"></td>
              <td>
                <div class="cell-title"><?= e($t['title']) ?></div>
                <div class="cell-sub"><?= e($t['notes'] ?: ($t['checks_total'] ? $t['checks_done'] . ' of ' . $t['checks_total'] . ' checklist items' : 'No notes')) ?></div>
              </td>
              <td><?= category_pill($t['cat_name'], $t['cat_color']) ?></td>
              <td><?= priority_pill($t['priority']) ?></td>
              <td><?= due_html($t['due_text'], $t['due_tone']) ?></td>
              <td class="col-menu"><button class="icon-btn icon-btn-sm" type="button" data-edit="task-dialog" aria-label="Edit task"><?= icon('pencil', 16) ?></button></td>
            </tr>
          <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <?php endif ?>
    </div>
  </section>

  <section class="card">
    <?= card_head('circle-alert', 'Needs attention', '', null) ?>
    <div class="panel attention">
      <?php if (!$attention): ?>
        <?= empty_state('circle-check-big', 'All clear', 'Nothing is late or running low.', '', 'empty-sm') ?>
      <?php endif ?>
      <?php foreach ($attention as $a): ?>
        <div class="attention-item">
          <span class="note-icon tone-<?= e($a['tone']) ?>"><?= icon($a['icon'], 16) ?></span>
          <div class="attention-text"><b class="truncate"><?= e($a['title']) ?></b><small class="tone-<?= e($a['tone']) ?>"><?= e($a['meta']) ?></small></div>
          <a class="btn btn-sm" href="<?= e($a['url']) ?>"><?= e($a['action']) ?></a>
        </div>
      <?php endforeach ?>
    </div>
  </section>
</div>

<div class="grid grid-2 section-gap">
  <section class="card">
    <?= card_head('target', 'Goals', '<a class="link" href="' . e(url('goals')) . '">All goals' . icon('chevron-right', 16) . '</a>') ?>
    <div class="panel goal-list">
      <?php if (!$goals): ?>
        <?= empty_state('target', 'No goals yet', 'Break a big goal into daily steps with the wizard.', '<a class="btn btn-sm" href="' . e(url('goals')) . '">Add goal</a>', 'empty-sm') ?>
      <?php endif ?>
      <?php foreach ($goals as $g): ?>
        <a class="goal-row" href="<?= e(url('goals', ['goal' => $g['id']])) ?>">
          <span class="row-between"><b class="truncate"><?= e($g['title']) ?></b><small class="meta"><?= e(goal_amount($g)) ?> · <?= $g['percent'] ?>%</small></span>
          <?= progress_bar($g['percent'], 'c-' . ($g['cat_color'] ?: 'gray')) ?>
        </a>
      <?php endforeach ?>
    </div>
  </section>

  <section class="card">
    <?= card_head('timer', 'Focus hours', '<span class="meta">' . e($monthName) . '</span>') ?>
    <div class="panel">
      <p class="focus-sum"><b><?= e(fmt_hours(array_sum($monthFocus))) ?></b> in <?= count($monthFocus) ?> working days<?= $bestDay ? ' · best day ' . e(day($bestDay)->format('D j M')) . ', ' . e(fmt_hours($monthFocus[$bestDay])) : '' ?></p>
      <div class="heatmap">
        <?php for ($dt = $gridStart; $dt <= $gridEnd; $dt = $dt->modify('+1 day')): ?>
          <?php
          $key = $dt->format('Y-m-d');
          $h = $focus[$key] ?? 0;
          $inMonth = $key >= month_start() && $key <= month_end();
          $state = $key > today() ? ' is-future' : (!$inMonth ? ' is-out' : '');
          ?>
          <i class="hm-cell hm-<?= $level($h) ?><?= $state ?><?= $key === today() ? ' is-today' : '' ?>"<?= $key <= today() ? ' data-tip="' . e('<b>' . e($dt->format('D j M')) . '</b> ' . e(fmt_hours($h))) . '"' : '' ?>></i>
        <?php endfor ?>
        <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $wd): ?><span class="hm-day"><?= $wd ?></span><?php endforeach ?>
      </div>
      <div class="hm-legend"><span>Less</span><i class="hm-cell hm-0"></i><i class="hm-cell hm-1"></i><i class="hm-cell hm-2"></i><i class="hm-cell hm-4"></i><span>More</span></div>
    </div>
  </section>
</div>
<?= partial('income-dialog') ?>
