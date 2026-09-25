<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/../..';

require __DIR__ . '/db.php';
require __DIR__ . '/http.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/view.php';
require __DIR__ . '/../lib/time.php';
require __DIR__ . '/../lib/format.php';
require __DIR__ . '/../lib/charts.php';
require __DIR__ . '/../lib/ui.php';

function config(): array
{
    static $config = null;
    return $config ??= require APP_ROOT . '/config.php';
}

function is_installed(): bool
{
    return is_file(APP_ROOT . '/config.php');
}

/** All settings rows, loaded once per request. */
function setting(string $key, string $default = ''): string
{
    static $all = null;
    $all ??= array_column(rows('SELECT k, v FROM settings'), 'v', 'k');
    return $all[$key] ?? $default;
}

function save_setting(string $key, string $value): void
{
    q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$key, $value]);
}

function boot(): void
{
    ini_set('display_errors', '0');
    set_exception_handler('handle_exception');
    start_session();
    date_default_timezone_set(setting('timezone', 'America/Los_Angeles'));
}
