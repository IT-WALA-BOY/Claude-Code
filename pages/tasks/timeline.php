<?php
/** Timeline view (Gantt): tasks grouped by category, bars from start to due. Drag to move or resize. */
const TIMELINE_ZOOMS = ['day' => [7, 'Day'], 'week' => [14, 'Week'], 'month' => [35, 'Month'], 'quarter' => [91, 'Quarter']];

$zoom = isset(TIMELINE_ZOOMS[$_GET['zoom'] ?? '']) ? $_GET['zoom'] : 'week';
[$days, $zoomLabel] = TIMELINE_ZOOMS[$zoom];
$monday = now()->modify('monday this week')->format('Y-m-d');
$fromParam = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($_GET['from'] ?? ''));
$from = $fromParam ? $fromParam->format('Y-m-d') : $monday;
$to = add_days($from, $days - 1);
$todayIdx = days_between($from, today());
$nowFraction = ((int) now()->format('G') * 60 + (int) now()->format('i')) / 1440;
$tlLink = fn (array $change) => $link(array_merge(['zoom' => $zoom, 'from' => $from], $change));

$open = array_values(array_filter($tasks, fn ($t) => $t['status'] !== 'done'));
$groups = [];
foreach ($open as $t) {
    $groups[$t['category_id'] ?? 0][] = $t;
}
$groupCats = [];
foreach (categories() as $c) {
    $groupCats[(int) $c['id']] = $c;
}
$groupCats[0] = ['id' => 0, 'name' => 'No category', 'color' => 'gray', 'icon' => 'tag'];
$rangeLabel = day($from)->format('j M') . ' to ' . day($to)->format('j M');
?>
<div class="toolbar">
  <div class="actions">
    <div class="range-nav">
      <a class="icon-btn icon-btn-sm" href="<?= e($tlLink(['from' => add_days($from, -$days)])) ?>" aria-label="Earlier"><?= icon('chevron-left', 16) ?></a>
      <span><?= e($rangeLabel) ?></span>
      <a class="icon-btn icon-btn-sm" href="<?= e($tlLink(['from' => add_days($from, $days)])) ?>" aria-label="Later"><?= icon('chevron-right', 16) ?></a>
    </div>
    <a class="btn" href="<?= e($tlLink(['from' => null])) ?>">Today</a>
    <div class="seg" role="tablist" aria-label="Zoom">
      <?php foreach (TIMELINE_ZOOMS as $key => [, $label]): ?>
        <a class="<?= $key === $zoom ? 'is-active' : '' ?>" href="<?= e($tlLink(['zoom' => $key, 'from' => null])) ?>"><?= e($label) ?></a>
      <?php endforeach ?>
    </div>
  </div>
  <?= partial('tasks-tools', ['link' => $link, 'prio' => $prio, 'sort' => $sort, 'cat' => $cat]) ?>
</div>

<div class="tl zoom-<?= e($zoom) ?>" style="--days:<?= $days ?>" data-from="<?= e($from) ?>" data-days="<?= $days ?>">
  <div class="tl-scroll">
    <div class="tl-inner">
      <div class="tl-head">
        <div class="tl-corner">Task</div>
        <div class="tl-dates">
          <?php for ($i = 0; $i < $days; $i++): ?>
            <?php $d = day(add_days($from, $i)); $isToday = $i === $todayIdx; $showLabel = $zoom !== 'quarter' || $d->format('N') === '1'; ?>
            <div class="tl-date<?= $d->format('N') >= 6 ? ' is-weekend' : '' ?><?= $isToday ? ' is-today' : '' ?>">
              <?php if ($showLabel): ?>
                <?php if ($zoom !== 'quarter' && $zoom !== 'month'): ?><small><?= e($d->format('D')) ?></small><?php endif ?>
                <b><?= e($zoom === 'quarter' ? $d->format('j M') : $d->format('j')) ?></b>
              <?php endif ?>
            </div>
          <?php endfor ?>
        </div>
      </div>
      <div class="tl-body">
        <div class="tl-cols" aria-hidden="true">
          <?php for ($i = 0; $i < $days; $i++): ?><i<?= day(add_days($from, $i))->format('N') >= 6 ? ' class="is-weekend"' : '' ?>></i><?php endfor ?>
          <?php if ($todayIdx >= 0 && $todayIdx < $days): ?><b class="tl-now" style="--at:<?= round($todayIdx + $nowFraction, 4) ?>"></b><?php endif ?>
        </div>
        <?php if (!$open): ?>
          <?= empty_state('chart-gantt', 'No open tasks', 'Add a task with a start and due date to see it here.', '<button class="btn btn-sm" type="button" data-open="task-dialog">Add task</button>') ?>
        <?php endif ?>
        <?php foreach ($groupCats as $cid => $c): ?>
          <?php if (empty($groups[$cid])) continue; ?>
          <section class="tl-group" data-group>
            <header class="tl-group-head">
              <button class="group-toggle" type="button" data-collapse aria-expanded="true"><?= icon('chevron-down', 16) ?></button>
              <span class="cat-tile c-<?= e($c['color']) ?>"><?= icon($c['icon'], 14) ?></span>
              <b><?= e($c['name']) ?></b><em class="muted"><?= count($groups[$cid]) ?></em>
            </header>
            <?php foreach ($groups[$cid] as $t): ?>
              <?php
              $start = $t['start_on'] ?: $t['due_on'];
              $due = $t['due_on'] ?: $t['start_on'];
              $s = $start ? days_between($from, $start) : null;
              $en = $due ? days_between($from, $due) : null;
              $visible = $s !== null && $en >= 0 && $s < $days;
              $late = $t['overdue'] ? days_between($t['due_on'], today()) : 0;
              $lateS = max(0, (int) $en + 1);
              $lateE = min($days - 1, $todayIdx);
              ?>
              <div class="tl-row" data-id="<?= (int) $t['id'] ?>" data-record="<?= e(task_json($t, $checklists[$t['id']] ?? [])) ?>">
                <button class="tl-name" type="button" data-edit="task-dialog"><?= icon(STATUS_ICONS[$t['status']], 16) ?><span class="truncate"><?= e($t['title']) ?></span></button>
                <div class="tl-track">
                  <?php if ($late && $lateS <= $lateE): ?>
                    <span class="tl-late" style="--s:<?= $lateS ?>;--e:<?= $lateE ?>"><?= e(plural($late, 'day') . ' late') ?></span>
                  <?php endif ?>
                  <?php if ($visible): ?>
                    <div class="tl-bar c-<?= e($t['cat_color'] ?: 'gray') ?><?= $s < 0 ? ' cut-start' : '' ?><?= $en >= $days ? ' cut-end' : '' ?>"
                         style="--s:<?= max(0, $s) ?>;--e:<?= min($days - 1, $en) ?>" data-start="<?= e($start) ?>" data-due="<?= e($due) ?>"
                         title="<?= e($t['title']) ?>">
                      <span class="tl-fill" style="--p:<?= $t['progress'] / 100 ?>"></span>
                      <span class="tl-label"><?= e($t['title']) ?></span>
                      <i class="tl-handle is-start" data-handle="start"></i><i class="tl-handle is-end" data-handle="end"></i>
                    </div>
                  <?php elseif (!$start): ?>
                    <span class="tl-nodate">No dates</span>
                  <?php endif ?>
                </div>
              </div>
            <?php endforeach ?>
          </section>
        <?php endforeach ?>
      </div>
    </div>
  </div>
</div>
