<?php
declare(strict_types=1);

const ANALYTICS_TABS = ['tasks' => 'Tasks', 'schedule' => 'Schedule', 'money' => 'Money', 'outreach' => 'Outreach'];

$period = isset(ANALYTICS_PERIODS[$_GET['period'] ?? '']) ? $_GET['period'] : 'month';
$tab = isset(ANALYTICS_TABS[$_GET['tab'] ?? '']) ? $_GET['tab'] : 'tasks';
$from = period_start($period);
$link = fn (array $change) => url('analytics', array_filter(array_merge(['period' => $period === 'month' ? null : $period, 'tab' => $tab === 'tasks' ? null : $tab], $change), fn ($v) => $v !== null));
$weekLabel = fn (string $monday) => $monday === week_start() ? day($monday)->format('j M') . ' · so far' : day($monday)->format('j M');
?>
<div class="page-head">
  <div>
    <h2 class="t-welcome">Analytics</h2>
    <p><?= e(period_label($period)) ?> · how the work, the time and the money are trending</p>
  </div>
  <div class="actions">
    <div class="seg">
      <?php foreach (ANALYTICS_PERIODS as $key => $label): ?>
        <a class="<?= $key === $period ? 'is-active' : '' ?>" href="<?= e($link(['period' => $key === 'month' ? null : $key])) ?>"><?= e($label) ?></a>
      <?php endforeach ?>
    </div>
    <button class="btn btn-primary" type="button" data-print><?= icon('printer', 16) ?>Export report</button>
  </div>
</div>
<nav class="tabs" aria-label="Analytics sections">
  <?php foreach (ANALYTICS_TABS as $key => $label): ?>
    <a class="<?= $key === $tab ? 'is-active' : '' ?>" href="<?= e($link(['tab' => $key === 'tasks' ? null : $key])) ?>"<?= $key === $tab ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach ?>
</nav>
<?php require __DIR__ . '/analytics/' . $tab . '.php'; ?>
