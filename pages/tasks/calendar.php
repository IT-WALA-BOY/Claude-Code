<?php
/** Calendar view: month grid with task pills on their due day. Click a day to add a task there. */
const CALENDAR_PILLS = 3;

$monthParam = DateTimeImmutable::createFromFormat('!Y-m', (string) ($_GET['month'] ?? ''));
$month = $monthParam ?: day(month_start());
$gridStart = $month->modify('-' . $month->format('w') . ' days');
$weeks = (int) ceil(((int) $month->format('w') + (int) $month->format('t')) / 7);
$byDay = [];
foreach ($tasks as $t) {
    if ($t['due_on']) {
        $byDay[$t['due_on']][] = $t;
    }
}
$calLink = fn (string $m) => $link(['month' => $m]);
?>
<div class="cal-bar">
  <div class="range-nav">
    <a class="icon-btn icon-btn-sm" href="<?= e($calLink($month->modify('-1 month')->format('Y-m'))) ?>" aria-label="Previous month"><?= icon('chevron-left', 16) ?></a>
    <span><?= e($month->format('F Y')) ?></span>
    <a class="icon-btn icon-btn-sm" href="<?= e($calLink($month->modify('+1 month')->format('Y-m'))) ?>" aria-label="Next month"><?= icon('chevron-right', 16) ?></a>
  </div>
  <a class="btn btn-sm" href="<?= e($link(['month' => null])) ?>">Today</a>
</div>
<div class="cal">
  <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $wd): ?><div class="cal-wd"><?= $wd ?></div><?php endforeach ?>
  <?php for ($i = 0; $i < $weeks * 7; $i++): ?>
    <?php
    $d = $gridStart->modify("+$i days");
    $key = $d->format('Y-m-d');
    $list = $byDay[$key] ?? [];
    $out = $d->format('m') !== $month->format('m');
    ?>
    <div class="cal-day<?= $out ? ' is-out' : '' ?><?= $key === today() ? ' is-today' : '' ?>" data-open="task-dialog" data-fill="<?= e(json_encode(['due_on' => $key] + ($cat ? ['category_id' => (int) $cat['id']] : []))) ?>">
      <span class="cal-num"><?= $d->format('j') ?></span>
      <?php foreach (array_slice($list, 0, CALENDAR_PILLS) as $t): ?>
        <button class="cal-pill c-<?= e($t['cat_color'] ?: 'gray') ?><?= $t['status'] === 'done' ? ' is-done' : '' ?>" type="button" data-record="<?= e(task_json($t, $checklists[$t['id']] ?? [])) ?>" data-edit="task-dialog">
          <i class="dot dot-solid"></i><span class="truncate"><?= e($t['title']) ?></span>
        </button>
      <?php endforeach ?>
      <?php if (count($list) > CALENDAR_PILLS): ?>
        <a class="cal-more" href="<?= e($link(['view' => 'list'])) ?>">+<?= e(plural(count($list) - CALENDAR_PILLS, 'task')) ?></a>
      <?php endif ?>
    </div>
  <?php endfor ?>
</div>
