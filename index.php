<?php
declare(strict_types=1);

require __DIR__ . '/app/core/boot.php';

if (!is_installed()) {
    header('Location: ' . url('install.php'));
    exit;
}

foreach (glob(__DIR__ . '/app/repo/*.php') as $file) {
    require $file;
}

boot();
security_headers();

$path = route_path();

if ($path === 'login') {
    require __DIR__ . '/pages/login.php';
    exit;
}

if ($path === 'logout') {
    if (is_post()) {
        check_csrf();
        sign_out();
    }
    redirect('login');
}

require_sign_in();

if (preg_match('#^api/([a-z]+)/([a-z_]+)$#', $path, $m)) {
    if (!is_post()) {
        json_out(['error' => 'Use POST.'], 405);
    }
    check_csrf();
    $file = __DIR__ . '/app/api/' . $m[1] . '.php';
    $fn = 'api_' . $m[1] . '_' . $m[2];
    if (is_file($file)) {
        require $file;
    }
    if (!function_exists($fn)) {
        json_out(['error' => 'Not found.'], 404);
    }
    json_out(['ok' => true] + ($fn() ?? []));
}

if ($path === 'export') {
    require __DIR__ . '/app/export.php';
    export_csv((string) ($_GET['type'] ?? ''));
    exit;
}

$slug = $path === '' ? 'overview' : $path;
if (!isset(PAGES[$slug])) {
    http_response_code(404);
    $slug = 'not-found';
}
render_page($slug);
