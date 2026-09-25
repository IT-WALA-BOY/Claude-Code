<?php
// Router for PHP's built-in server. Mirrors .htaccess: php -S 127.0.0.1:8080 tools/dev-router.php
$root = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(app|pages|partials|database|storage|tools|docs|\.git|\.claude)(/|$)|^/(config|config\.sample)\.php$|\.md$#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if ($path === '/install.php') {
    chdir($root);
    require $root . '/install.php';
    return true;
}
if ($path !== '/' && is_file($root . $path)) {
    if (str_starts_with($path, '/assets/')) {
        header('Cache-Control: public, max-age=31536000, immutable');
    }
    return false;
}
if (str_starts_with($path, '/assets/')) {
    http_response_code(404);
    exit('Not found');
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
chdir($root);
require $root . '/index.php';
