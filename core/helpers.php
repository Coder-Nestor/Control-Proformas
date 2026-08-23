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

/**
 * En servidores Windows con IIS, un archivo recién creado por PHP a veces
 * no hereda los permisos de lectura correctos de su carpeta — y el usuario
 * anónimo de IIS (el que sirve el archivo cuando lo abres por URL) no puede
 * leerlo, aunque la carpeta sí tenga los permisos bien puestos. Esto corre
 * "icacls" para dar permiso de lectura explícito a las identidades típicas
 * de IIS sobre ese archivo o carpeta puntual. En Linux no hace nada.
 *
 * Si tu AppPool corre bajo una cuenta de dominio específica (pregúntale a
 * IT), agrégala en config/config.php -> app.iis_permisos_identidad, por
 * ejemplo 'TUDOMINIO\\usuario_apppool'.
 */
function aplicar_permisos_iis(string $ruta): void
{
    if (stripos(PHP_OS, 'WIN') !== 0) {
        return; // no aplica fuera de Windows
    }
    if (!file_exists($ruta)) {
        return;
    }

    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }

    $identidades = ['IIS_IUSRS', 'IUSR'];
    $extra = $config['app']['iis_permisos_identidad'] ?? null;
    if (!empty($extra)) {
        $identidades[] = $extra;
    }

    foreach ($identidades as $identidad) {
        $cmd = 'icacls ' . escapeshellarg($ruta) . ' /grant ' . escapeshellarg($identidad . ':(OI)(CI)(R,W)') . ' 2>&1';
        @exec($cmd, $salida, $codigo);
        if ($codigo !== 0) {
            // No es fatal: puede que esa identidad no exista en este servidor.
            // Se sigue intentando con las demás, y se deja registro por si acaso.
            error_log('[aplicar_permisos_iis] icacls no pudo aplicar "' . $identidad . '" a ' . $ruta . ': ' . implode(' ', $salida));
        }
        $salida = [];
    }
}

/** true si el nombre de archivo es una imagen (por su extensión), para elegir el ícono correcto en la interfaz. */
function es_imagen(?string $nombreArchivo): bool
{
    if (empty($nombreArchivo)) {
        return false;
    }
    $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
}