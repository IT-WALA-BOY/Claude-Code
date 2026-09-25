<?php
/** @var array $targets */
$history = li_history(14);
$metrics = [...array_keys(LI_METRICS)];
?>
<div class="grid grid-side section-gap">
  <section class="card">
    <?= card_head('calendar-days', 'Last 14 days') ?>
    <div class="panel panel-flush table-scroll">
      <table class="tbl">
        <thead><tr><th>Day</th><?php foreach ($metrics as $m): ?><th class="num"><?= e(LI_METRICS[$m]) ?></th><?php endforeach ?></tr></thead>
        <tbody>
        <?php foreach (array_reverse($history, true) as $dayKey => $counts): ?>
          <tr>
            <td><?= e(day($dayKey)->format('D j M')) ?></td>
            <?php foreach ($metrics as $m): ?>
              <td class="num <?= $counts[$m] >= $targets[$m] && $targets[$m] ? 'tone-positive' : ($counts[$m] ? '' : 'muted') ?>"><?= $counts[$m] ?> <span class="muted">/ <?= $targets[$m] ?></span></td>
            <?php endforeach ?>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </section>
  <section class="card">
    <?= card_head('target', 'Targets per day') ?>
    <form class="panel" data-api="linkedin/targets" data-success="Targets saved">
      <?php foreach ([...$metrics, 'posts'] as $m): ?>
        <label class="field"><span class="field-label"><?= e(LI_METRICS[$m] ?? 'Posts published') ?></span><span class="control"><input type="number" min="0" max="100" name="<?= e($m) ?>" value="<?= (int) $targets[$m] ?>"></span></label>
      <?php endforeach ?>
      <button class="btn btn-primary mt-16" type="submit">Save</button>
    </form>
  </section>
</div>
