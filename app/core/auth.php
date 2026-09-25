<?php
declare(strict_types=1);

const LOGIN_MAX_TRIES = 5;
const LOGIN_LOCK_MINUTES = 15;
const SESSION_IDLE_HOURS = 12;
const SESSION_REMEMBER_DAYS = 30;

function is_signed_in(): bool
{
    if (empty($_SESSION['admin'])) {
        return false;
    }
    $idle = empty($_SESSION['remember']) ? SESSION_IDLE_HOURS * 3600 : SESSION_REMEMBER_DAYS * 86400;
    if (time() - ($_SESSION['seen'] ?? 0) > $idle) {
        sign_out();
        return false;
    }
    $_SESSION['seen'] = time();
    return true;
}

function require_sign_in(): void
{
    if (!is_signed_in()) {
        str_starts_with(route_path(), 'api/') ? json_out(['error' => 'Signed out. Reload the page.'], 401) : redirect('login');
    }
}

function admin(): array
{
    static $admin = null;
    return $admin ??= row('SELECT id, username, name FROM admin WHERE id = 1') ?? ['id' => 1, 'username' => '', 'name' => 'Admin'];
}

/** The name used in greetings: the "Call me" setting, else the first word of the admin name. */
function greeting_name(): string
{
    return setting('greeting_name') ?: (explode(' ', admin()['name'])[0] ?: 'there');
}

/** Minutes left on the lock for this IP, or 0 if it can try again. */
function login_locked_minutes(): int
{
    $since = date('Y-m-d H:i:s', time() - LOGIN_LOCK_MINUTES * 60);
    $tries = rows(
        'SELECT attempted_at FROM login_attempts WHERE ip = ? AND attempted_at > ? ORDER BY attempted_at DESC',
        [client_ip(), $since]
    );
    if (count($tries) < LOGIN_MAX_TRIES) {
        return 0;
    }
    $unlock = strtotime($tries[LOGIN_MAX_TRIES - 1]['attempted_at']) + LOGIN_LOCK_MINUTES * 60;
    return max(1, (int) ceil(($unlock - time()) / 60));
}

/** Returns an error message, or null when signed in. */
function attempt_sign_in(string $username, string $password, bool $remember): ?string
{
    if ($minutes = login_locked_minutes()) {
        return "Too many wrong tries. Try again in $minutes min.";
    }
    $admin = row('SELECT id, username, password_hash FROM admin WHERE id = 1');
    $ok = $admin && hash_equals(strtolower($admin['username']), strtolower($username))
        && password_verify($password, $admin['password_hash']);

    if (!$ok) {
        insert('login_attempts', ['ip' => client_ip(), 'attempted_at' => date('Y-m-d H:i:s')]);
        $left = LOGIN_MAX_TRIES - count(rows(
            'SELECT id FROM login_attempts WHERE ip = ? AND attempted_at > ?',
            [client_ip(), date('Y-m-d H:i:s', time() - LOGIN_LOCK_MINUTES * 60)]
        ));
        return $left > 0
            ? "Wrong username or password. $left " . ($left === 1 ? 'try' : 'tries') . ' left.'
            : 'Too many wrong tries. Try again in ' . LOGIN_LOCK_MINUTES . ' min.';
    }

    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        update('admin', 1, ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }
    q('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
    session_regenerate_id(true);
    $_SESSION = ['admin' => 1, 'seen' => time(), 'remember' => $remember, 'csrf' => bin2hex(random_bytes(32))];
    if ($remember) {
        $p = session_get_cookie_params();
        setcookie(session_name(), session_id(), [
            'expires' => time() + SESSION_REMEMBER_DAYS * 86400,
            'path' => $p['path'],
            'secure' => $p['secure'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    return null;
}

function sign_out(): void
{
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path']]);
    session_destroy();
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function check_csrf(): void
{
    $sent = $_SERVER['HTTP_X_CSRF'] ?? ($_POST['csrf'] ?? '');
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        str_starts_with(route_path(), 'api/') ? json_out(['error' => 'Session expired. Reload the page.'], 419) : fail('Session expired. Reload the page.');
    }
}
