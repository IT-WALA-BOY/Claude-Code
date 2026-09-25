<?php
/** @var string $from  @var array $byNight  @var string $tonight  @var callable $link  @var callable $blockAttrs */
$month = day($from);
$gridStart = $month->modify('-' . $month->format('w') . ' days');
$weeks = (int) ceil(((int) $month->format('w') + (int) $month->format('t')) / 7);
?>
<div class="cal cal-flat">
  <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $wd): ?><div class="cal-wd"><?= $wd ?></div><?php endforeach ?>
  <?php for ($i = 0; $i < $weeks * 7; $i++): ?>
    <?php $d = $gridStart->modify("+$i days"); $key = $d->format('Y-m-d'); $list = $byNight[$key] ?? []; ?>
    <div class="cal-day<?= $d->format('m') !== $month->format('m') ? ' is-out' : '' ?><?= $key === $tonight ? ' is-today' : '' ?>">
      <a class="cal-num" href="<?= e($link(['view' => 'day', 'date' => $key])) ?>"><?= $d->format('j') ?></a>
      <?php foreach (array_slice($list, 0, 3) as $b): ?>
        <button class="cal-pill c-<?= e($b['cat_color'] ?: 'gray') ?><?= $b['suggested'] ? ' is-suggested' : '' ?>" type="button"<?= $blockAttrs($b) ?> data-edit="block-dialog">
          <i class="dot dot-solid"></i><span class="truncate"><?= e(fmt_time($b['starts_at'], true) . ' ' . $b['title']) ?></span>
        </button>
      <?php endforeach ?>
      <?php if (count($list) > 3): ?><a class="cal-more" href="<?= e($link(['view' => 'day', 'date' => $key])) ?>">+<?= e(plural(count($list) - 3, 'block')) ?></a><?php endif ?>
    </div>
  <?php endfor ?>
</div>
