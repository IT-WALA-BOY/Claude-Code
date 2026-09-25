<?php
declare(strict_types=1);

/** The folder the app lives in, e.g. "" at the domain root or "/dashboard". */
function base_path(): string
{
    static $base = null;
    return $base ??= rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
}

function url(string $path = '', array $query = []): string
{
    $url = base_path() . '/' . ltrim($path, '/');
    return $query ? $url . '?' . http_build_query($query) : $url;
}

/** Asset URL with a version stamp so browsers can cache it for a year. */
function asset(string $path): string
{
    $file = APP_ROOT . '/assets/' . $path;
    return url('assets/' . $path) . '?v=' . (is_file($file) ? filemtime($file) : 0);
}

/** Request path relative to the app folder, without slashes at either end. */
function route_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return trim(preg_replace('#^/?index\.php#', '', $path), '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 303);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function json_out(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** A problem the user can fix. The message is shown in the UI. */
final class UserError extends RuntimeException
{
}

function fail(string $message): never
{
    throw new UserError($message);
}

function handle_exception(Throwable $e): void
{
    $isApi = str_starts_with(route_path(), 'api/');
    if (!$e instanceof UserError) {
        error_log((string) $e);
    }
    $message = $e instanceof UserError ? $e->getMessage() : 'Something went wrong. Try again.';
    if ($isApi) {
        json_out(['error' => $message], $e instanceof UserError ? 422 : 500);
    }
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Error</title><p style="font:14px system-ui;padding:40px">' . e($message) . '</p>';
}

function start_session(): void
{
    $https = ($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off';
    // Own session folder, so shared hosting cleanup can't sign the admin out early.
    $dir = APP_ROOT . '/storage/sessions';
    if (is_dir($dir) || @mkdir($dir, 0700, true)) {
        session_save_path($dir);
    }
    ini_set('session.gc_maxlifetime', (string) (SESSION_REMEMBER_DAYS * 86400));
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');
    session_name('wfd');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => base_path() . '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Input helpers for JSON API bodies. */
function input(): array
{
    static $data = null;
    if ($data === null) {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        $data = is_array($data) ? $data : $_POST;
    }
    return $data;
}

function in_str(string $key, int $max = 255, bool $required = false): string
{
    $v = trim((string) (input()[$key] ?? ''));
    if ($required && $v === '') {
        fail('Fill in ' . str_replace('_', ' ', $key) . '.');
    }
    return mb_substr($v, 0, $max);
}

function in_int(string $key, ?int $default = null): ?int
{
    $v = input()[$key] ?? null;
    return is_numeric($v) ? (int) $v : $default;
}

function in_num(string $key, ?float $default = null): ?float
{
    $v = str_replace(',', '', (string) (input()[$key] ?? ''));
    return is_numeric($v) ? (float) $v : $default;
}

function in_bool(string $key): int
{
    return filter_var(input()[$key] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
}

/** Returns the value only if it is one of the allowed options. */
function in_enum(string $key, array $allowed, ?string $default = null): ?string
{
    $v = (string) (input()[$key] ?? '');
    return in_array($v, $allowed, true) ? $v : $default;
}

function in_date(string $key): ?string
{
    $v = (string) (input()[$key] ?? '');
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $v);
    return $d && $d->format('Y-m-d') === $v ? $v : null;
}

function in_time(string $key): ?string
{
    $v = (string) (input()[$key] ?? '');
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v) ? $v : null;
}

function in_ids(string $key): array
{
    $v = input()[$key] ?? [];
    return is_array($v) ? array_values(array_filter(array_map('intval', $v))) : [];
}
