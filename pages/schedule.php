<?php
declare(strict_types=1);

const SCHEDULE_VIEWS = ['day' => 'Day', 'week' => 'Week', 'month' => 'Month'];
const HOUR_PX = 70;

$view = isset(SCHEDULE_VIEWS[$_GET['view'] ?? '']) ? $_GET['view'] : 'week';
$tonight = night_of(now()->format('Y-m-d H:i:s'));
$dateParam = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($_GET['date'] ?? ''));
$focusDay = $dateParam ? $dateParam->format('Y-m-d') : $tonight;
$weekStart = day($focusDay)->modify('-' . day($focusDay)->format('w') . ' days')->format('Y-m-d');
[$from, $to] = match ($view) {
    'day' => [$focusDay, $focusDay],
    'month' => [month_start($focusDay), month_end($focusDay)],
    default => [$weekStart, add_days($weekStart, 6)],
};
$byNight = blocks_by_night($from, $to);
[$gridStart, $gridEnd] = grid_window();
$allBlocks = $byNight ? array_merge(...array_values($byNight)) : [];
$suggested = array_filter($allBlocks, fn ($b) => $b['suggested']);
$planned = array_sum(array_map(fn ($b) => $b['suggested'] ? 0 : block_minutes($b), $allBlocks)) / 60;
$workStart = setting('work_start', '18:00');
$tonightPlan = schedule_today();
$link = fn (array $change) => url('schedule', array_filter(array_merge(['view' => $view, 'date' => $focusDay === $tonight ? null : $focusDay], $change), fn ($v) => $v !== null));
$step = ['day' => '1 day', 'week' => '7 days', 'month' => '1 month'][$view];
$rangeLabel = match ($view) {
    'day' => day($from)->format('D j M'),
    'month' => day($from)->format('F Y'),
    default => day($from)->format('j') . ' to ' . day($to)->format('j M'),
};
$title = match ($view) {
    'day' => fmt_long_day($from),
    'month' => day($from)->format('F Y'),
    default => 'Week of ' . day($from)->format('j F'),
};
$secondTz = new DateTimeZone(setting('second_timezone', 'Asia/Karachi'));

// Mini calendar and agenda
$miniMonth = day(month_start($focusDay));
$miniStart = $miniMonth->modify('-' . $miniMonth->format('w') . ' days');
$miniDays = (int) ceil(((int) $miniMonth->format('w') + (int) $miniMonth->format('t')) / 7) * 7;
$busyDays = array_keys(blocks_by_night($miniStart->format('Y-m-d'), $miniStart->modify('+' . ($miniDays - 1) . ' days')->format('Y-m-d')));
$agenda = blocks_by_night($tonight, add_days($tonight, 1));
$blockAttrs = fn (array $b) => ' data-id="' . (int) $b['id'] . '" data-record="' . e(block_json($b)) . '"';
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome"><?= e($title) ?></h2>
    <p><?= e(fmt_hours($planned)) ?> planned · <?= e(fmt_hours($tonightPlan['hours'])) ?> tonight · Pacific time, <?= e(tz_short($secondTz->getName())) ?> on the right</p>
  </div>
  <div class="actions">
    <button class="btn" type="button" data-open="availability-dialog"><?= icon('clock', 16) ?>Availability</button>
    <button class="btn btn-primary" type="button" data-make><?= icon('sparkles', 16) ?>Generate schedule</button>
  </div>
</div>

<div class="toolbar">
  <div class="actions">
    <div class="range-nav">
      <a class="icon-btn icon-btn-sm" href="<?= e($link(['date' => day($focusDay)->modify('-' . $step)->format('Y-m-d')])) ?>" aria-label="Earlier"><?= icon('chevron-left', 16) ?></a>
      <span><?= e($rangeLabel) ?></span>
      <a class="icon-btn icon-btn-sm" href="<?= e($link(['date' => day($focusDay)->modify('+' . $step)->format('Y-m-d')])) ?>" aria-label="Later"><?= icon('chevron-right', 16) ?></a>
    </div>
    <a class="btn" href="<?= e($link(['date' => null])) ?>">Today</a>
    <div class="seg">
      <?php foreach (SCHEDULE_VIEWS as $key => $label): ?>
        <a class="<?= $key === $view ? 'is-active' : '' ?>" href="<?= e($link(['view' => $key])) ?>"><?= e($label) ?></a>
      <?php endforeach ?>
    </div>
  </div>
  <button class="btn" type="button" data-open="block-dialog" data-fill="<?= e(json_encode(['day' => $focusDay, 'start' => $workStart, 'end' => date('H:i', strtotime($workStart . ' +1 hour'))])) ?>"><?= icon('plus', 16) ?>Add block</button>
</div>

<div class="sched">
  <aside class="stack">
    <section class="card">
      <div class="panel mini-cal">
        <div class="row-between mini-cal-head">
          <b><?= e($miniMonth->format('F Y')) ?></b>
          <span class="row">
            <a class="icon-btn icon-btn-xs" href="<?= e($link(['date' => $miniMonth->modify('-1 month')->format('Y-m-d')])) ?>" aria-label="Previous month"><?= icon('chevron-left', 16) ?></a>
            <a class="icon-btn icon-btn-xs" href="<?= e($link(['date' => $miniMonth->modify('+1 month')->format('Y-m-d')])) ?>" aria-label="Next month"><?= icon('chevron-right', 16) ?></a>
          </span>
        </div>
        <div class="mini-grid">
          <?php foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $wd): ?><span class="mini-wd"><?= $wd ?></span><?php endforeach ?>
          <?php for ($i = 0; $i < $miniDays; $i++): ?>
            <?php $d = $miniStart->modify("+$i days"); $key = $d->format('Y-m-d'); ?>
            <a class="mini-day<?= $d->format('m') !== $miniMonth->format('m') ? ' is-out' : '' ?><?= $key === $tonight ? ' is-today' : '' ?><?= $key >= $from && $key <= $to && $view !== 'month' ? ' is-range' : '' ?><?= in_array($key, $busyDays, true) ? ' has-blocks' : '' ?>" href="<?= e($link(['date' => $key])) ?>"><?= $d->format('j') ?></a>
          <?php endfor ?>
        </div>
      </div>
      <div class="panel agenda agenda-side">
        <?php foreach ([$tonight => 'Today', add_days($tonight, 1) => 'Tomorrow'] as $night => $label): ?>
          <p class="agenda-label"><span class="t-caps"><?= e($label) ?></span> <span class="meta"><?= e(day($night)->format('D j M')) ?></span></p>
          <?php $list = array_values(array_filter($agenda[$night] ?? [], fn ($b) => !$b['suggested'] && $b['ends_at'] > now()->format('Y-m-d H:i:s'))); ?>
          <?php if (!$list): ?><p class="meta agenda-none">Nothing left.</p><?php endif ?>
          <?php foreach ($list as $b): ?>
            <button class="agenda-item c-<?= e($b['cat_color'] ?: 'gray') ?>" type="button"<?= $blockAttrs($b) ?> data-edit="block-dialog">
              <span class="agenda-time"><?= icon('clock', 14) ?><?= e(fmt_range($b['starts_at'], $b['ends_at'])) ?></span>
              <b><?= e($b['title']) ?></b>
              <small><?= e(implode(' · ', array_filter([$b['cat_name'], $b['note'] ?: fmt_minutes(block_minutes($b))]))) ?></small>
            </button>
          <?php endforeach ?>
        <?php endforeach ?>
      </div>
    </section>
  </aside>

  <section class="card week-card">
    <?php if ($suggested): ?>
      <div class="maker-bar">
        <?= icon('sparkles', 18) ?>
        <span><?= e(plural(count($suggested), 'block')) ?> suggested by the schedule maker. Dashed blocks are suggestions.</span>
        <button class="btn btn-sm" type="button" data-accept>Accept all</button>
        <button class="btn btn-quiet btn-sm" type="button" data-dismiss>Dismiss</button>
      </div>
    <?php endif ?>
    <?php if ($view === 'month'): ?>
      <?= partial('schedule-month', ['from' => $from, 'byNight' => $byNight, 'tonight' => $tonight, 'link' => $link, 'blockAttrs' => $blockAttrs]) ?>
    <?php else: ?>
      <?php
      $nights = [];
      for ($d = $from; $d <= $to; $d = add_days($d, 1)) {
          $nights[] = $d;
      }
      $nowMin = minutes_into_night(now()->format('Y-m-d H:i:s'), $tonight);
      $hours = range($gridStart / 60, $gridEnd / 60);
      ?>
      <div class="panel panel-flush week" style="--cols:<?= count($nights) ?>;--hour:<?= HOUR_PX ?>px;--rows:<?= count($hours) - 1 ?>" data-week data-grid-start="<?= $gridStart ?>" data-grid-end="<?= $gridEnd ?>">
        <div class="week-head">
          <span class="week-tz">PT</span>
          <?php foreach ($nights as $n): ?>
            <a class="week-day<?= $n === $tonight ? ' is-today' : '' ?>" href="<?= e($link(['view' => 'day', 'date' => $n])) ?>"><small><?= e(day($n)->format('D')) ?></small><b><?= e(day($n)->format('j')) ?></b></a>
          <?php endforeach ?>
          <span class="week-tz"><?= e(tz_short($secondTz->getName())) ?></span>
        </div>
        <div class="week-body">
          <div class="week-hours">
            <?php foreach (array_slice($hours, 0, -1) as $h): ?><span><?= e(date('g A', mktime($h % 24, 0))) ?></span><?php endforeach ?>
          </div>
          <div class="week-cols">
            <?php foreach ($nights as $n): ?>
              <div class="week-col<?= $n === $tonight ? ' is-today' : '' ?>" data-night="<?= e($n) ?>">
                <?php foreach ($byNight[$n] ?? [] as $b): ?>
                  <?php
                  $s = max($gridStart, minutes_into_night($b['starts_at'], $n));
                  $en = min($gridEnd, minutes_into_night($b['ends_at'], $n));
                  if ($en <= $gridStart || $s >= $gridEnd) {
                      continue;
                  }
                  $past = $b['ends_at'] < now()->format('Y-m-d H:i:s');
                  ?>
                  <div class="block c-<?= e($b['cat_color'] ?: 'gray') ?><?= $past ? ' is-past' : '' ?><?= $b['suggested'] ? ' is-suggested' : '' ?><?= $b['done'] ? ' is-done' : '' ?><?= $en - $s <= 45 ? ' is-short' : '' ?>"
                       style="--top:<?= $s - $gridStart ?>;--len:<?= $en - $s ?>;--lines:<?= max(1, (int) floor((($en - $s) * HOUR_PX / 60 - 28) / 16)) ?>"<?= $blockAttrs($b) ?>>
                    <?php if ($b['suggested']): ?><small class="block-flag">Suggested</small><?php endif ?>
                    <span class="block-time"><?= e(fmt_time($b['starts_at'])) ?></span>
                    <b class="block-title"><?= e($b['title']) ?></b>
                    <i class="block-resize" data-resize></i>
                  </div>
                <?php endforeach ?>
                <?php if ($n === $tonight && $nowMin > $gridStart && $nowMin < $gridEnd): ?>
                  <div class="week-now" style="--top:<?= $nowMin - $gridStart ?>"><span><?= e(now()->format('g:i')) ?></span></div>
                <?php endif ?>
              </div>
            <?php endforeach ?>
          </div>
          <div class="week-hours week-hours-2">
            <?php foreach (array_slice($hours, 0, -1) as $h): ?>
              <span><?= e((new DateTimeImmutable($from . ' 00:00:00'))->modify('+' . $h . ' hours')->setTimezone($secondTz)->format('g A')) ?></span>
            <?php endforeach ?>
          </div>
        </div>
      </div>
    <?php endif ?>
  </section>
</div>
<?= partial('schedule-dialogs') ?>
