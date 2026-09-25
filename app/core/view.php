<?php
declare(strict_types=1);

/** Every screen: URL slug => title shown in the top bar and the nav key it highlights. */
const PAGES = [
    'overview' => ['title' => 'Dashboard', 'nav' => 'overview'],
    'tasks' => ['title' => 'Tasks', 'nav' => 'tasks'],
    'schedule' => ['title' => 'Schedule', 'nav' => 'schedule'],
    'goals' => ['title' => 'Goals', 'nav' => 'goals'],
    'figma' => ['title' => 'Figma projects', 'nav' => 'figma'],
    'income' => ['title' => 'Income', 'nav' => 'income'],
    'expenses' => ['title' => 'Expenses', 'nav' => 'expenses'],
    'buy-list' => ['title' => 'Buy list', 'nav' => 'buy-list'],
    'linkedin' => ['title' => 'LinkedIn', 'nav' => 'linkedin'],
    'analytics' => ['title' => 'Analytics', 'nav' => 'analytics'],
    'settings' => ['title' => 'Settings', 'nav' => 'settings'],
    'help' => ['title' => 'Help and support', 'nav' => 'help'],
    'not-found' => ['title' => 'Page not found', 'nav' => ''],
];

/** Renders a page template inside the app shell. */
function render_page(string $slug): void
{
    $page = PAGES[$slug] + ['slug' => $slug, 'module' => $slug];
    ob_start();
    require APP_ROOT . '/pages/' . $slug . '.php';
    $content = ob_get_clean();
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    require APP_ROOT . '/partials/layout.php';
}

/** Renders a partial and returns its HTML. */
function partial(string $name, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require APP_ROOT . '/partials/' . $name . '.php';
    return (string) ob_get_clean();
}

function security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'inline-speculation-rules'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
}
