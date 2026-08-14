<?php

namespace Core;

class Controller
{
    /**
     * Renderiza una vista dentro del layout principal.
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../app/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            die("Vista no encontrada: {$view}");
        }

        $content = function () use ($viewFile, $data) {
            extract($data);
            require $viewFile;
        };

        require __DIR__ . '/../app/Views/layouts/app.php';
    }

    /**
     * Renderiza una vista SIN layout (ej. login, partials para AJAX).
     */
    protected function viewOnly(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../app/Views/' . $view . '.php';
    }

    protected function input(string $key, $default = null)
    {
        $valor = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($valor) ? trim($valor) : $valor;
    }

    protected function redirect(string $path): void
    {
        Response::redirect($path);
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
            http_response_code(419);
            die('Token de seguridad inválido o expirado. Vuelve a intentarlo (F5) y reenvía el formulario.');
        }
    }

    /**
     * Procesa la subida de un PDF escaneado desde un campo <input type="file">.
     * - Si no se subió archivo nuevo, conserva el que ya existía ($oldFile).
     * - Valida que el contenido real sea application/pdf (no solo la extensión).
     * - Limita el tamaño a 10 MB.
     * - Si se sube uno nuevo y ya existía uno anterior, borra el anterior.
     *
     * @param string      $field   Nombre del input file (ej. 'documento_pdf')
     * @param string      $subdir  Subcarpeta dentro de public/uploads/ (ej. 'gestiones')
     * @param string|null $oldFile Nombre de archivo previamente guardado (para conservar/borrar)
     */
    protected function handleUpload(string $field, string $subdir, ?string $oldFile = null): ?string
    {
        if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return $oldFile;
        }

        $file = $_FILES[$field];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Ocurrió un error al subir el archivo. Inténtalo de nuevo.');
            return $oldFile;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            $this->flash('error', 'El archivo PDF no debe superar 10 MB.');
            return $oldFile;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            $this->flash('error', 'Solo se permiten archivos en formato PDF.');
            return $oldFile;
        }

        $dir = __DIR__ . '/../public/uploads/' . $subdir;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $subdir . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            $this->flash('error', 'No se pudo guardar el archivo en el servidor.');
            return $oldFile;
        }

        // Elimina el archivo anterior, si existía, para no acumular basura.
        if ($oldFile) {
            $oldPath = $dir . '/' . $oldFile;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return $filename;
    }
}