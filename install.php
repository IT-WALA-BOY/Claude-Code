<?php
declare(strict_types=1);

/**
 * First run: connects to MySQL, creates the tables and the single admin account,
 * then writes config.php. Refuses to run again once an admin exists.
 */

require __DIR__ . '/app/core/db.php';
require __DIR__ . '/app/core/http.php';
require __DIR__ . '/app/lib/format.php';

const APP_ROOT = __DIR__;

function config(): array
{
    static $config = null;
    return $config ??= require APP_ROOT . '/config.php';
}

function setting(string $key, string $default = ''): string
{
    return $default;
}

function run_sql_file(PDO $pdo, string $file): void
{
    $sql = preg_replace('/^--.*$/m', '', (string) file_get_contents($file));
    foreach (array_filter(array_map('trim', explode(";\n", $sql))) as $statement) {
        $pdo->exec($statement);
    }
}

function admin_exists(PDO $pdo): bool
{
    try {
        return (bool) $pdo->query('SELECT COUNT(*) FROM admin')->fetchColumn();
    } catch (PDOException) {
        return false;
    }
}

session_start();
$_SESSION['install_csrf'] ??= bin2hex(random_bytes(16));
$hasConfig = is_file(APP_ROOT . '/config.php');
$error = null;
$done = false;
$configText = null;

if ($hasConfig) {
    try {
        if (admin_exists(db())) {
            http_response_code(403);
            exit('Already installed. Delete config.php to run the installer again.');
        }
    } catch (PDOException) {
        $error = 'config.php exists but the database connection failed. Check its details.';
    }
}

if (is_post() && !$error) {
    $f = fn (string $k) => trim((string) ($_POST[$k] ?? ''));
    try {
        if (!hash_equals($_SESSION['install_csrf'], (string) ($_POST['csrf'] ?? ''))) {
            throw new UserError('Session expired. Reload the page.');
        }
        if (!preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $f('username'))) {
            throw new UserError('Username: 3 to 60 letters, numbers, dots or dashes.');
        }
        if (strlen($f('password')) < 10) {
            throw new UserError('Password: at least 10 characters.');
        }
        if ($f('password') !== $f('password2')) {
            throw new UserError('The two passwords do not match.');
        }

        $db = $hasConfig ? config()['db'] : ['host' => $f('db_host') ?: 'localhost', 'name' => $f('db_name'), 'user' => $f('db_user'), 'pass' => (string) ($_POST['db_pass'] ?? '')];
        try {
            $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4", $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException) {
            throw new UserError('Could not connect to the database. Check the host, name, user and password.');
        }
        if (admin_exists($pdo)) {
            throw new UserError('An admin already exists in this database.');
        }

        run_sql_file($pdo, APP_ROOT . '/database/schema.sql');
        if (!empty($_POST['demo'])) {
            require APP_ROOT . '/database/demo.php';
            seed_demo($pdo);
        }
        $pdo->prepare('INSERT INTO admin (id, username, name, password_hash, created_at) VALUES (1, ?, ?, ?, NOW())')
            ->execute([$f('username'), $f('name') ?: 'Admin', password_hash($f('password'), PASSWORD_DEFAULT)]);
        $pdo->prepare("INSERT INTO settings (k, v) VALUES ('greeting_name', ?) ON DUPLICATE KEY UPDATE v = VALUES(v)")
            ->execute([$f('greeting_name')]);

        if (!$hasConfig) {
            $configText = "<?php\nreturn " . var_export(['db' => $db, 'secret' => bin2hex(random_bytes(32))], true) . ";\n";
            if (@file_put_contents(APP_ROOT . '/config.php', $configText) !== false) {
                $configText = null;
            }
        }
        $done = true;
    } catch (UserError $e) {
        $error = $e->getMessage();
    }
}

$v = fn (string $k, string $d = '') => e($_POST[$k] ?? $d);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install · Workflow</title>
<link rel="stylesheet" href="assets/css/tokens.css">
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="auth auth-single">
<section class="auth-form">
  <div class="brand brand-center"><span class="brand-mark"><svg class="i" width="18" height="18"><use href="assets/icons.svg#zap"/></svg></span><span>Workflow</span></div>
  <?php if ($done): ?>
    <div class="auth-card">
      <h1 class="t-display-sm">You're set up</h1>
      <?php if ($configText): ?>
        <p class="auth-lead">The installer could not write config.php. Create it next to index.php with this content, then sign in.</p>
        <textarea class="input code" rows="12" readonly><?= e($configText) ?></textarea>
      <?php else: ?>
        <p class="auth-lead">The tables and your admin account are ready. The installer is now locked.</p>
      <?php endif ?>
      <a class="btn btn-primary btn-block btn-lg" href="login">Sign in</a>
    </div>
  <?php else: ?>
    <form class="auth-card" method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['install_csrf']) ?>">
      <h1 class="t-display-sm">Set up your dashboard</h1>
      <p class="auth-lead">One admin account, created once. Takes a minute.</p>
      <?php if ($error): ?><p class="notice notice-negative" role="alert"><?= e($error) ?></p><?php endif ?>

      <?php if (!$hasConfig): ?>
        <p class="t-caps form-group-label">Database</p>
        <div class="form-grid">
          <label class="field"><span class="field-label">Host</span><span class="control"><input name="db_host" value="<?= $v('db_host', 'localhost') ?>" required></span></label>
          <label class="field"><span class="field-label">Database name</span><span class="control"><input name="db_name" value="<?= $v('db_name') ?>" required></span></label>
          <label class="field"><span class="field-label">User</span><span class="control"><input name="db_user" value="<?= $v('db_user', 'root') ?>" required></span></label>
          <label class="field"><span class="field-label">Password</span><span class="control"><input name="db_pass" type="password"></span></label>
        </div>
      <?php endif ?>

      <p class="t-caps form-group-label">Admin account</p>
      <div class="form-grid">
        <label class="field"><span class="field-label">Your name</span><span class="control"><input name="name" value="<?= $v('name', 'Muhammad Ahmad') ?>"></span></label>
        <label class="field"><span class="field-label">Call me</span><span class="control"><input name="greeting_name" value="<?= $v('greeting_name', 'Ahmad') ?>"></span><span class="field-help">Used in greetings.</span></label>
      </div>
      <label class="field"><span class="field-label">Username</span><span class="control"><input name="username" value="<?= $v('username') ?>" required></span></label>
      <div class="form-grid">
        <label class="field"><span class="field-label">Password</span><span class="control"><input name="password" type="password" minlength="10" required></span><span class="field-help">At least 10 characters.</span></label>
        <label class="field"><span class="field-label">Repeat password</span><span class="control"><input name="password2" type="password" minlength="10" required></span></label>
      </div>
      <label class="check-row"><input class="check" type="checkbox" name="demo" value="1"<?= isset($_POST['demo']) ? ' checked' : '' ?>><span>Load sample data to try things out</span></label>
      <button class="btn btn-primary btn-block btn-lg" type="submit">Create account</button>
    </form>
  <?php endif ?>
</section>
</body>
</html>
