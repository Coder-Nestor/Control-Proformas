<?php

declare(strict_types=1);

require __DIR__ . '/../core/Autoload.php';
require __DIR__ . '/../core/helpers.php';

$config = require __DIR__ . '/../config/config.php';

date_default_timezone_set($config['app']['timezone']);
error_reporting($config['app']['debug'] ? E_ALL : 0);
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');

// --- Sesión segura ---
session_name($config['session']['name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    // 'secure' => true, // habilitar cuando el sitio corra bajo HTTPS
]);
session_start();

$sessionVersion = (int) ($config['session']['version'] ?? 1);
if (($_SESSION['_session_version'] ?? null) !== $sessionVersion) {
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['_session_version'] = $sessionVersion;
}

use Core\Router;

$router = new Router();
$router->load(require __DIR__ . '/../config/routes.php');

$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = rtrim((string) parse_url($config['app']['url'], PHP_URL_PATH), '/');
if ($basePath !== '' && $basePath !== '/' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
    if ($uri === '') {
        $uri = '/';
    }
}
if ($uri === '/public' || str_starts_with($uri, '/public/')) {
    $uri = substr($uri, 7);
    if ($uri === '') {
        $uri = '/';
    }
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($method, $uri);
