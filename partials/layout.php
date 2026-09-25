<?php
/** @var array $page  @var string $content */
$attention = attention_items();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<meta name="base" content="<?= e(base_path()) ?>">
<meta name="today" content="<?= e(today()) ?>">
<meta name="sprite" content="<?= e(asset('icons.svg')) ?>">
<meta name="timezones" content="<?= e(setting('timezone', 'America/Los_Angeles') . '|' . setting('second_timezone', 'Asia/Karachi')) ?>">
<title><?= e($page['title']) ?> · Workflow</title>
<link rel="preload" href="<?= e(url('assets/fonts/inter-var.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('css/tokens.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<link rel="icon" href="<?= e(asset('favicon.svg')) ?>" type="image/svg+xml">
<script type="module" src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body>
<div class="app">
  <?= partial('sidebar', ['active' => $page['nav']]) ?>
  <div class="main">
    <header class="topbar">
      <button class="icon-btn topbar-menu" type="button" data-action="side-open" aria-label="Open menu"><?= icon('panel-left') ?></button>
      <h1 class="t-page"><?= e($page['title']) ?></h1>
      <button class="search" type="button" data-action="palette">
        <?= icon('search', 16) ?><span>Search tasks, leads, notes</span><kbd>⌘K</kbd>
      </button>
      <div class="clock" data-clock title="App clock and Depalpur time">
        <?= icon('clock', 18) ?>
        <b data-clock-a><?= e(fmt_time(now())) ?> <?= e(tz_short(setting('timezone', 'America/Los_Angeles'))) ?></b>
        <span data-clock-b><?= e(fmt_time(second_clock())) ?> <?= e(tz_short(setting('second_timezone', 'Asia/Karachi'))) ?></span>
      </div>
      <div class="menu-wrap">
        <button class="icon-btn bell<?= $attention ? ' has-dot' : '' ?>" type="button" data-menu="bell-menu" aria-label="Notifications"><?= icon('bell') ?></button>
        <div class="menu menu-wide" id="bell-menu" role="menu" hidden>
          <p class="menu-title">Needs attention</p>
          <?php if (!$attention): ?>
            <p class="menu-empty">All clear. Nothing is late.</p>
          <?php endif ?>
          <?php foreach (array_slice($attention, 0, 6) as $a): ?>
            <a class="menu-item menu-note" href="<?= e($a['url']) ?>">
              <span class="note-icon tone-<?= e($a['tone']) ?>"><?= icon($a['icon'], 16) ?></span>
              <span><b><?= e($a['title']) ?></b><small class="tone-<?= e($a['tone']) ?>"><?= e($a['meta']) ?></small></span>
            </a>
          <?php endforeach ?>
        </div>
      </div>
    </header>
    <?php $module = APP_ROOT . '/assets/js/pages/' . $page['module'] . '.js' ?>
    <main class="page" id="page" data-page="<?= e($page['slug']) ?>"<?= is_file($module) ? attrs(['data-module' => $page['module'], 'data-v' => filemtime($module)]) : '' ?>>
      <?= $content ?>
    </main>
  </div>
</div>
<?= partial('dialogs') ?>
<div class="toasts" id="toasts" aria-live="polite"></div>
</body>
</html>
