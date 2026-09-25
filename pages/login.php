<?php
declare(strict_types=1);

if (is_signed_in()) {
    redirect('');
}

$error = null;
$username = '';
if (is_post()) {
    check_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $error = attempt_sign_in($username, (string) ($_POST['password'] ?? ''), !empty($_POST['remember']));
    if ($error === null) {
        redirect('');
    }
}
$locked = login_locked_minutes() > 0;
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sign in · Workflow</title>
<link rel="preload" href="<?= e(url('assets/fonts/inter-var.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('css/tokens.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<link rel="icon" href="<?= e(asset('favicon.svg')) ?>" type="image/svg+xml">
<script type="module" src="<?= e(asset('js/login.js')) ?>"></script>
</head>
<body class="auth">
  <section class="auth-form">
    <a class="brand brand-center" href="<?= e(url('login')) ?>">
      <span class="brand-mark"><?= icon('zap', 18) ?></span><span>Workflow</span>
    </a>
    <form class="auth-card" method="post" action="<?= e(url('login')) ?>" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <h1 class="t-display-sm">Welcome back, <?= e(greeting_name()) ?></h1>
      <p class="auth-lead">Sign in to see tonight's tasks, your income pace and who needs a follow-up.</p>

      <label class="field">
        <span class="field-label">Username</span>
        <span class="control has-icon">
          <?= icon('users', 16) ?>
          <input name="username" autocomplete="username" required autofocus value="<?= e($username) ?>"<?= $locked ? ' disabled' : '' ?>>
        </span>
      </label>

      <label class="field<?= $error ? ' is-error' : '' ?>">
        <span class="field-label">Password</span>
        <span class="control has-icon">
          <?= icon('lock', 16) ?>
          <input name="password" type="password" autocomplete="current-password" required<?= $locked ? ' disabled' : '' ?>>
          <button class="control-btn" type="button" data-reveal aria-label="Show password"><?= icon('eye', 16) ?></button>
        </span>
        <?php if ($error): ?><span class="field-help" role="alert"><?= e($error) ?></span><?php endif ?>
      </label>

      <label class="check-row">
        <input class="check" type="checkbox" name="remember" value="1" checked>
        <span>Keep me signed in on this computer</span>
      </label>

      <button class="btn btn-primary btn-block btn-lg" type="submit"<?= $locked ? ' disabled' : '' ?>>Sign in</button>

      <p class="auth-note"><?= icon('circle-alert', 16) ?>One admin account, no sign-ups. After 5 wrong passwords, sign-in locks for 15 minutes.</p>
    </form>
    <footer class="auth-foot">
      <span><?= e(fmt_time(now())) ?> Pacific · <?= e(fmt_time(second_clock())) ?> in Depalpur</span>
      <span>Private dashboard</span>
    </footer>
  </section>
  <aside class="auth-preview" aria-hidden="true">
    <p class="t-caps">Your night at a glance</p>
    <p class="auth-headline">Tasks, income pace, LinkedIn follow-ups and study goals in one calm place.</p>
    <img src="<?= e(asset('img/preview.png')) ?>" alt="" width="1200" height="800" decoding="async">
  </aside>
</body>
</html>
