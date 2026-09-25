<?php
/** Analytics, Schedule tab: planned vs done hours per week, time by category, busiest weekdays. */
$weeks = hours_by_week(8);
$time = time_vs_plan($from);
$focus = focus_hours($from, today());
$byWeekday = array_fill(0, 7, 0.0);
foreach ($focus as $d => $h) {
    $byWeekday[(int) day($d)->format('w')] += $h;
}
$weekdayMax = max(1, max($byWeekday));
?>
<section class="card">
  <?= card_head('calendar-days', 'Planned vs done hours, per week', '<span class="legend"><span><i class="dot dot-peri"></i>Planned</span><span><i class="dot dot-ink"></i>Done</span></span>') ?>
  <div class="panel">
    <?php
    $bars = [];
    foreach ($weeks as $monday => $w) {
        $bars[] = [
            'label' => $weekLabel($monday),
            'segments' => [[$w['planned'], 'seg-peri', 'Planned'], [$w['done'], 'seg-ink', 'Done']],
            'tip' => '<b>Week of ' . e(day($monday)->format('j M')) . '</b><div class="tip-row">Planned<span>' . e(fmt_hours($w['planned'])) . '</span></div><div class="tip-row">Done<span>' . e(fmt_hours($w['done'])) . '</span></div>',
        ];
    }
    echo bar_chart($bars, ['grouped' => true, 'height' => 170, 'format' => fn ($v) => num($v) . ' h']);
    ?>
  </div>
</section>
<div class="grid grid-2 section-gap">
  <section class="card">
    <?= card_head('tag', 'Hours by category', '<span class="meta">' . e(period_label($period)) . '</span>') ?>
    <div class="panel hbars">
      <?php if (!$time): ?><p class="muted">No blocks in this period.</p><?php endif ?>
      <?php $maxH = max(1, ...array_column($time ?: [['planned' => 0]], 'planned')); ?>
      <?php foreach ($time as $r): ?>
        <div class="hbar-row"><span><?= e($r['name']) ?></span><b><?= e(fmt_hours($r['done'])) ?> <small class="muted">of <?= e(fmt_hours($r['planned'])) ?></small></b><?= progress_bar(pct($r['done'], $maxH), 'c-' . $r['color']) ?></div>
      <?php endforeach ?>
    </div>
  </section>
  <section class="card">
    <?= card_head('timer', 'Focus by weekday', '<span class="meta">' . e(fmt_hours(array_sum($focus))) . ' done</span>') ?>
    <div class="panel hbars">
      <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $i => $wd): ?>
        <div class="hbar-row"><span><?= $wd ?></span><b><?= e(fmt_hours($byWeekday[$i])) ?></b><?= progress_bar(pct($byWeekday[$i], $weekdayMax), $byWeekday[$i] === $weekdayMax ? '' : 'c-steel') ?></div>
      <?php endforeach ?>
    </div>
  </section>
</div>
