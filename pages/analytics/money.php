<?php
/** Analytics, Money tab: income against the goal per month, spend by kind, what is left. */
$year = (int) now()->format('Y');
$byMonth = income_by_month($year);
$thisMonth = (int) now()->format('n');
$spend = spend_between($from, today());
$income = income_between($from, today());
$months = spend_by_month(6);
?>
<div class="grid grid-main">
  <section class="card">
    <?= card_head('circle-dollar-sign', 'Income against the goal', '<span class="meta">' . $year . '</span>') ?>
    <div class="panel">
      <?php
      $bars = [];
      foreach (array_slice($byMonth, 0, $thisMonth, true) as $n => $x) {
          $name = date('M', mktime(0, 0, 0, $n, 1));
          $bars[] = [
              'label' => $name,
              'segments' => [[$x['upwork'], 'seg-upwork', 'Upwork'], [$x['direct'], 'seg-direct', 'Direct']],
              'tip' => '<b>' . e($name) . '</b><div class="tip-row">Upwork<span>' . e(usd($x['upwork'])) . '</span></div><div class="tip-row">Direct<span>' . e(usd($x['direct'])) . '</span></div>',
          ];
      }
      echo bar_chart($bars, ['line' => [income_goal(), usd_short(income_goal()) . ' goal'], 'height' => 190]);
      ?>
    </div>
  </section>
  <section class="card">
    <?= card_head('wallet', 'Where money went', '<span class="meta">' . e(period_label($period)) . '</span>') ?>
    <div class="panel">
      <div class="donut-row donut-row-plain">
        <?php $segs = [[$spend['daily'], 'var(--orange-bar)', 'Daily costs'], [$spend['connects'], 'var(--green-bar)', 'Connects'], [$spend['tools'], 'var(--purple-bar)', 'Tools'], [$spend['other'], 'var(--border-strong)', 'Other']]; ?>
        <?= donut($segs, 140, 26, usd(round($spend['total'])), 'spent') ?>
        <ul class="legend-list">
          <?php foreach ($segs as [$n, $color, $label]): ?>
            <li><i class="dot" style="--c-bar: <?= e($color) ?>"></i><span><?= e($label) ?></span><b><?= e(usd(round($n))) ?></b></li>
          <?php endforeach ?>
        </ul>
      </div>
      <div class="net">
        <span>Earned <b><?= e(usd($income['total'])) ?></b></span><span>Spent <b><?= e(usd(round($spend['total']))) ?></b></span>
        <span>Kept <b class="<?= $income['total'] - $spend['total'] >= 0 ? 'tone-positive' : 'tone-negative' ?>"><?= e(usd(round($income['total'] - $spend['total']))) ?></b></span>
      </div>
    </div>
  </section>
</div>
<section class="card section-gap">
  <?= card_head('chart-column', 'Spend per month', '<span class="meta">Last 6 months</span>') ?>
  <div class="panel">
    <?php
    $bars = [];
    foreach ($months as $label => $x) {
        $bars[] = ['label' => $label, 'segments' => [[$x['daily'], 'seg-orange', 'Daily'], [$x['connects'], 'seg-mint', 'Connects'], [$x['tools'], 'seg-purple', 'Tools'], [$x['other'], 'seg-muted', 'Other']],
            'tip' => '<b>' . e($label) . '</b> ' . e(usd(round($x['total'])))];
    }
    echo bar_chart($bars, ['height' => 150]);
    ?>
  </div>
</section>
