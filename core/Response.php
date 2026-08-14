<?php

namespace Core;

class Response
{
    public static function redirect(string $path): void
    {
        $config = require __DIR__ . '/../config/config.php';
        $base = rtrim(parse_url($config['app']['url'], PHP_URL_PATH) ?? '', '/');
        header('Location: ' . $base . $path);
        exit;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
