<?php
// Маршрутизатор только для встроенного локального PHP-сервера.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (preg_match('~(?:^|/)(?:\.[^/]*|local-access\.txt|wp-cli\.phar|wordpress\.zip|acf\.zip|(?:check|update)-[^/]*\.py|migrate-services\.php|start-local\.ps1|router\.php|README\.md|local-server[^/]*|theme)(?:/|$)~i', $path)) {
    http_response_code(404);
    exit('Not found');
}
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}
if (is_dir(__DIR__ . $path) && is_file(__DIR__ . rtrim($path, '/') . '/index.php')) {
    return false;
}
require __DIR__ . '/index.php';
