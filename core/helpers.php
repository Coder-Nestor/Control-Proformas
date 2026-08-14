<?php

use Core\Auth;

/** Escapa texto para salida segura en HTML. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** URL base de la app (para links y assets), tomada de config/config.php. */
function base_url(string $path = ''): string
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    $base = rtrim($config['app']['url'], '/');
    return $base . '/' . ltrim($path, '/');
}

/** Ruta a un asset local (css/js/vendor) — NUNCA apunta a un CDN externo. */
/**
 * Ruta a un asset local (css/js/vendor/img) — NUNCA apunta a un CDN externo.
 * Le agrega "?v=<fecha de modificación>" automáticamente, para que el
 * navegador SIEMPRE descargue la versión más reciente en cuanto el archivo
 * cambie en el servidor, sin que el usuario tenga que forzar Ctrl+F5.
 */
function asset(string $path): string
{
    $relativo = 'assets/' . ltrim($path, '/');
    $rutaFisica = __DIR__ . '/../public/' . $relativo;

    $version = is_file($rutaFisica) ? filemtime($rutaFisica) : time();

    return base_url($relativo) . '?v=' . $version;
}

/** Genera (o reutiliza) el token CSRF de la sesión actual. */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Campo oculto listo para insertar dentro de un <form>. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

/** Recupera y limpia un mensaje flash de sesión. */
function flash_get(string $type): ?string
{
    if (!empty($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

/** Formatea una fecha (o null) a dd/mm/yyyy para mostrar en pantalla. */
function fmt_date(?string $date): string
{
    if (empty($date) || $date === '0000-00-00') {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : '—';
}

/** Formatea un valor monetario en Lempiras. */
function fmt_money($value): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    return 'L. ' . number_format((float) $value, 2);
}

/**
 * Días entre dos fechas (puede ser negativo si "hasta" es anterior a "desde").
 * Devuelve null si falta alguna fecha.
 */
function days_between(?string $desde, ?string $hasta): ?int
{
    if (empty($desde) || empty($hasta)) {
        return null;
    }
    $d1 = new DateTime($desde);
    $d2 = new DateTime($hasta);
    return (int) $d1->diff($d2)->format('%r%a');
}

/** Días transcurridos entre una fecha y HOY (para alertas de atraso en curso). */
function days_since(?string $desde): ?int
{
    if (empty($desde)) {
        return null;
    }
    return days_between($desde, date('Y-m-d'));
}

function current_user_name(): string
{
    return Auth::name();
}