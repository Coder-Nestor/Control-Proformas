<?php
/**
 * Autoloader minimalista estilo PSR-4, sin depender de Composer
 * (el servidor no tiene salida a internet para packagist por el firewall).
 *
 * Mapea:
 *   Core\...             -> /core/...
 *   App\Controllers\...  -> /app/Controllers/...
 *   App\Models\...       -> /app/Models/...
 */

spl_autoload_register(function (string $class) {
    $map = [
        'Core\\'             => __DIR__ . '/',
        'App\\Controllers\\' => __DIR__ . '/../app/Controllers/',
        'App\\Models\\'      => __DIR__ . '/../app/Models/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
});
