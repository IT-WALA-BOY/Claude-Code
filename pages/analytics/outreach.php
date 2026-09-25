<?php
/** Analytics, Outreach tab: daily counters against targets, and the pipeline funnel. */
$history = li_history(14);
$targets = li_targets();
$funnel = li_funnel();
$top = max(1, reset($funnel));
?>
<section class="card">
  <?= card_head('send', 'Reach-outs and follow-ups, last 14 days', '<span class="legend"><span><i class="dot dot-ink"></i>Reach-outs</span><span><i class="dot dot-peri"></i>Follow-ups</span></span>') ?>
  <div class="panel">
    <?php
    $bars = [];
    foreach ($history as $d => $c) {
        $bars[] = ['label' => day($d)->format('j'), 'segments' => [[$c['reachouts'], 'seg-ink', 'Reach-outs'], [$c['followups'], 'seg-peri', 'Follow-ups']],
            'tip' => '<b>' . e(day($d)->format('D j M')) . '</b><div class="tip-row">Reach-outs<span>' . $c['reachouts'] . ' / ' . $targets['reachouts'] . '</span></div><div class="tip-row">Follow-ups<span>' . $c['followups'] . ' / ' . $targets['followups'] . '</span></div>'];
    }
    echo bar_chart($bars, ['grouped' => true, 'height' => 150, 'ticks' => 2, 'line' => [$targets['reachouts'], 'Reach-out target'], 'format' => fn ($v) => num($v)]);
    ?>
  </div>
</section>
<section class="card section-gap">
  <?= card_head('chart-bar', 'Pipeline funnel', '<span class="meta">' . e(plural(li_counts()['pipeline'], 'open lead')) . '</span>') ?>
  <div class="panel funnel">
    <?php foreach ($funnel as $stage => $n): ?>
      <div class="funnel-row"><span><?= e(LI_STAGES[$stage]) ?></span><?= progress_bar(pct($n, $top), $stage === 'won' ? 'c-green' : 'c-blue') ?><b><?= $n ?></b></div>
    <?php endforeach ?>
  </div>
</section>
