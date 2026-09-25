<?php
/** @var string $active */
$counts = nav_counts();
$byCategory = open_counts_by_category();
$groups = [
    'Menu' => [
        ['overview', 'Overview', 'layout-dashboard', ''],
        ['tasks', 'Tasks', 'square-check-big', $counts['tasks']],
        ['schedule', 'Schedule', 'calendar-days', ''],
        ['goals', 'Goals', 'target', $counts['goals']],
        ['figma', 'Figma', 'figma', $counts['figma']],
    ],
    'Money' => [
        ['income', 'Income', 'circle-dollar-sign', ''],
        ['expenses', 'Expenses', 'wallet', ''],
        ['buy-list', 'Buy list', 'shopping-cart', $counts['buy']],
    ],
    'Growth' => [
        ['linkedin', 'LinkedIn', 'megaphone', $counts['li_due'] ? $counts['li_due'] . ' due' : ''],
        ['analytics', 'Analytics', 'chart-column', ''],
    ],
    'General' => [
        ['settings', 'Settings', 'settings', ''],
        ['help', 'Help and support', 'circle-help', ''],
    ],
];
$me = admin();
?>
<aside class="side" id="side">
  <a class="brand" href="<?= e(url()) ?>">
    <span class="brand-mark"><?= icon('zap', 18) ?></span>
    <span>Workflow</span>
  </a>
  <nav class="side-nav" id="side-nav" aria-label="Main">
    <?php foreach ($groups as $label => $items): ?>
      <?php if ($label === 'General'): ?><hr class="side-rule"><?php endif ?>
      <p class="side-label"><?= e($label) ?></p>
      <?php foreach ($items as [$slug, $name, $ic, $count]): ?>
        <a class="nav-item<?= $slug === $active ? ' is-active' : '' ?>" href="<?= e(url($slug === 'overview' ? '' : $slug)) ?>"<?= $slug === $active ? ' aria-current="page"' : '' ?>>
          <?= icon($ic) ?><span><?= e($name) ?></span>
          <?php if ($count !== '' && $count !== 0 && $count !== '0'): ?>
            <em class="nav-count<?= $slug === 'linkedin' ? ' is-urgent' : '' ?>"><?= e($count) ?></em>
          <?php endif ?>
        </a>
      <?php endforeach ?>
    <?php endforeach ?>
    <hr class="side-rule">
    <div class="side-label side-label-row">
      <span>Categories</span>
      <a class="icon-btn icon-btn-xs" href="<?= e(url('settings')) ?>#categories" aria-label="Manage categories"><?= icon('plus', 16) ?></a>
    </div>
    <?php foreach (categories() as $c): ?>
      <a class="nav-item nav-cat" href="<?= e(url('tasks', ['cat' => $c['id']])) ?>">
        <span class="cat-tile c-<?= e($c['color']) ?>"><?= icon($c['icon'], 16) ?></span>
        <span><?= e($c['name']) ?></span>
        <em class="nav-num"><?= (int) ($byCategory[$c['id']] ?? 0) ?></em>
      </a>
    <?php endforeach ?>
  </nav>
  <div class="menu-wrap profile-wrap">
    <button class="profile" type="button" data-menu="profile-menu">
      <span class="avatar"><?= e(initials($me['name'])) ?></span>
      <span class="profile-text"><b><?= e($me['name']) ?></b><small>Admin</small></span>
      <?= icon('chevrons-up-down', 16) ?>
    </button>
    <div class="menu menu-up" id="profile-menu" role="menu" hidden>
      <a class="menu-item" href="<?= e(url('settings')) ?>"><?= icon('settings', 16) ?>Settings</a>
      <form method="post" action="<?= e(url('logout')) ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button class="menu-item" type="submit"><?= icon('log-out', 16) ?>Sign out</button>
      </form>
    </div>
  </div>
</aside>
<div class="side-scrim" data-action="side-close"></div>
