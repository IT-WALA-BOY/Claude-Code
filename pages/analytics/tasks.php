<?php
/** Analytics, Tasks tab: summary, status donut, goal progress, time vs plan, workload, weekly trend. */
$counts = task_counts();
$time = time_vs_plan($from);
$planned = array_sum(array_column($time, 'planned'));
$doneHours = array_sum(array_column($time, 'done'));
$timePct = pct($doneHours, $planned);
$status = row(
    "SELECT SUM(status = 'doing') AS doing, SUM(status = 'todo') AS todo, SUM(status = 'review') AS review,
            SUM(status = 'done' AND completed_at >= ?) AS done FROM tasks",
    [$from . ' 00:00:00']
);
$status = array_map('intval', $status ?? []);
$goals = goals_list();
$load = workload($from);
$loadMax = max(1, ...array_map(fn ($r) => $r['completed'] + $r['remaining'] + $r['overdue'], $load ?: [['completed' => 0, 'remaining' => 0, 'overdue' => 0]]));
$weeks = tasks_by_week(8);
$focus = array_sum(focus_hours($from, today()));
?>
<div class="grid grid-3">
  <section class="card">
    <?= card_head('list-todo', 'Summary') ?>
    <div class="panel panel-flush">
      <table class="tbl kpi-table">
        <thead><tr><th>KPI</th><th><?= e(period_label($period)) ?></th></tr></thead>
        <tbody>
          <tr><td>Time</td><td><?= $planned ? ($timePct >= ON_TIME_PERCENT ? e($timePct . '% of planned hours done') : '<span class="tone-negative">' . e((100 - $timePct) . '% behind plan') . '</span>') : 'No blocks planned' ?></td></tr>
          <tr><td>Progress</td><td><?= planned_done_percent($from) ?>% of tasks due are done</td></tr>
          <tr><td>Tasks</td><td><?= $counts['open'] ?> open · <?= $counts['today'] ?> due today</td></tr>
          <tr><td>Workload</td><td class="<?= $counts['overdue'] ? 'tone-negative' : '' ?>"><?= e(plural($counts['overdue'], 'task')) ?> overdue</td></tr>
          <tr><td>Focus</td><td><?= e(fmt_hours($focus)) ?> logged</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card">
    <?= card_head('chart-pie', 'Tasks', '<span class="meta">' . e(period_label($period)) . '</span>') ?>
    <div class="panel donut-row donut-row-plain">
      <?php $segs = [[$status['doing'], 'var(--chart-ink)', 'In progress'], [$status['todo'], 'var(--chart-periwinkle)', 'To do'], [$status['review'], 'var(--chart-steel)', 'Review'], [$status['done'], 'var(--chart-mint)', 'Done']]; ?>
      <?= donut($segs, 140, 26, (string) array_sum(array_column($segs, 0)), 'tasks') ?>
      <ul class="legend-list">
        <?php foreach ($segs as [$n, $color, $label]): ?>
          <li><i class="dot" style="--c-bar: <?= e($color) ?>"></i><span><?= e($label) ?></span><b><?= $n ?></b></li>
        <?php endforeach ?>
      </ul>
    </div>
  </section>

  <section class="card">
    <?= card_head('target', 'Progress', '<span class="meta">Goals</span>') ?>
    <div class="panel hbars">
      <?php if (!$goals): ?><p class="muted">No active goals.</p><?php endif ?>
      <?php foreach ($goals as $g): ?>
        <div class="hbar-row"><span class="truncate"><?= e($g['title']) ?></span><b class="tone-<?= $g['pace_tone'] === 'positive' ? 'positive' : 'default' ?>"><?= $g['percent'] ?>%</b><?= progress_bar($g['percent'], 'c-' . ($g['cat_color'] ?: 'gray')) ?></div>
      <?php endforeach ?>
    </div>
  </section>
</div>

<div class="grid grid-2 section-gap">
  <section class="card">
    <?= card_head('clock', 'Time vs plan', '<span class="meta">Hours done as % of planned</span>') ?>
    <div class="panel">
      <div class="legend"><span><i class="dot dot-steel"></i>Behind</span><span><i class="dot dot-mint"></i>On time</span></div>
      <?php if (!$time): ?><p class="muted mt-8">Plan blocks in Schedule to compare them with what got done.</p><?php endif ?>
      <div class="diverge">
        <?php foreach ($time as $r): ?>
          <?php $p = pct($r['done'], $r['planned']); $ok = $p >= ON_TIME_PERCENT; ?>
          <div class="diverge-row" data-tip="<?= e('<b>' . e($r['name']) . '</b> ' . e(fmt_hours($r['done'])) . ' of ' . e(fmt_hours($r['planned']))) ?>">
            <span><?= e($r['name']) ?></span>
            <div class="diverge-left"><?php if (!$ok): ?><i style="--w: <?= $p ?>%"></i><small><?= $p ?>%</small><?php endif ?></div>
            <div class="diverge-right"><?php if ($ok): ?><small class="tone-positive"><?= $p ?>%</small><i style="--w: <?= $p ?>%"></i><?php endif ?></div>
          </div>
        <?php endforeach ?>
      </div>
    </div>
  </section>

  <section class="card">
    <?= card_head('layout-dashboard', 'Workload', '<span class="meta">Tasks per category</span>') ?>
    <div class="panel">
      <div class="legend"><span><i class="dot dot-mint"></i>Completed</span><span><i class="dot dot-peri"></i>Remaining</span><span><i class="dot dot-rose"></i>Overdue</span></div>
      <div class="workload">
        <?php foreach ($load as $r): ?>
          <div class="workload-row">
            <span><?= e($r['name']) ?></span>
            <?= hstack([[$r['completed'], 'seg-mint', 'Completed'], [$r['remaining'], 'seg-peri', 'Remaining'], [$r['overdue'], 'seg-rose', 'Overdue']], $loadMax) ?>
            <b><?= $r['completed'] + $r['remaining'] + $r['overdue'] ?></b>
          </div>
        <?php endforeach ?>
      </div>
    </div>
  </section>
</div>

<section class="card section-gap">
  <?= card_head('chart-column', 'Completed vs added, per week', '<span class="legend"><span><i class="dot dot-ink"></i>Completed</span><span><i class="dot dot-peri"></i>Added</span></span>') ?>
  <div class="panel">
    <?php
    $bars = [];
    foreach ($weeks as $monday => $w) {
        $bars[] = [
            'label' => $weekLabel($monday),
            'segments' => [[$w['done'], 'seg-ink', 'Completed'], [$w['added'], 'seg-peri', 'Added']],
            'tip' => '<b>Week of ' . e(day($monday)->format('j M')) . '</b><div class="tip-row">Completed<span>' . $w['done'] . '</span></div><div class="tip-row">Added<span>' . $w['added'] . '</span></div>',
        ];
    }
    echo bar_chart($bars, ['grouped' => true, 'height' => 150, 'ticks' => 2, 'format' => fn ($v) => num($v)]);
    ?>
  </div>
</section>
