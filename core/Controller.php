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
     * Procesa la subida de un documento desde un campo <input type="file">.
     * - Si no se subió archivo nuevo, conserva el que ya existía ($oldFile).
     * - Valida tipos permitidos (PDF e imágenes).
     * - Limita el tamaño a 5 MB.
     * - Si se sube uno nuevo y ya existía uno anterior, borra el anterior.
     *
     * @param string      $field   Nombre del input file (ej. 'documento_pdf')
     * @param string      $subdir  Subcarpeta dentro de public/uploads/ (ej. 'gestiones')
     * @param string|null $oldFile Nombre de archivo previamente guardado (para conservar/borrar)
     */
    protected function handleUpload(string $field, string $subdir, ?string $oldFile = null): ?string
    {
        $fieldKey = rtrim($field, '[]');
        $file = $_FILES[$fieldKey] ?? $_FILES[$field] ?? null;

        if (!$file || empty($file['name']) || (is_array($file['error']) ? ($file['error'][0] ?? UPLOAD_ERR_NO_FILE) : $file['error']) === UPLOAD_ERR_NO_FILE) {
            return $oldFile;
        }

        // Si vino como array múltiple pero se pide un solo archivo
        if (is_array($file['name'])) {
            $name = $file['name'][0] ?? '';
            $error = $file['error'][0] ?? UPLOAD_ERR_NO_FILE;
            $size = $file['size'][0] ?? 0;
            $tmpName = $file['tmp_name'][0] ?? '';
        } else {
            $name = $file['name'];
            $error = $file['error'];
            $size = $file['size'];
            $tmpName = $file['tmp_name'];
        }

        if ($error !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Ocurrió un error al subir el archivo. Inténtalo de nuevo.');
            return $oldFile;
        }

        if ($size > 5 * 1024 * 1024) {
            $this->flash('error', 'El archivo no debe superar 5 MB.');
            return $oldFile;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        $extensionesPermitidas = [
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/gif'       => 'gif',
            'image/webp'      => 'webp',
        ];

        if (!isset($extensionesPermitidas[$mime])) {
            $this->flash('error', 'Solo se permiten archivos PDF o imágenes (JPG, PNG, GIF, WEBP).');
            return $oldFile;
        }
        $extension = $extensionesPermitidas[$mime];

        $dir = __DIR__ . '/../public/uploads/' . $subdir;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            aplicar_permisos_iis($dir);
        }

        $filename = $subdir . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destino = $dir . '/' . $filename;

        if (!move_uploaded_file($tmpName, $destino)) {
            $this->flash('error', 'No se pudo guardar el archivo en el servidor.');
            return $oldFile;
        }

        aplicar_permisos_iis($destino);

        // Comprime el archivo si supera 1 MB
        Compressor::compress($destino, $mime);
        aplicar_permisos_iis($destino);

        if ($oldFile) {
            $oldPath = $dir . '/' . $oldFile;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return $filename;
    }

    /**
     * Procesa la subida de MÚLTIPLES archivos desde un campo <input type="file" multiple>.
     * Valida cada archivo, comprime si es necesario y retorna array de registros estructurados.
     *
     * @param string $field   Nombre del input file (ej. 'documentos' o 'documentos[]')
     * @param string $subdir  Subcarpeta dentro de public/uploads/ (ej. 'gestiones')
     * @return array Array de arrays con ['nombre_archivo', 'nombre_original', 'mime_type', 'tamano_bytes']
     */
    protected function handleMultipleUploads(string $field, string $subdir, int $maxArchivos = 2): array
    {
        $uploadedFiles = [];
        $fieldKey = rtrim($field, '[]');

        $raw = $_FILES[$fieldKey] ?? $_FILES[$field] ?? null;
        if (!$raw || empty($raw['name'])) {
            return $uploadedFiles;
        }

        // Normaliza estructura para soportar tanto un solo archivo como array de múltiples
        $names    = is_array($raw['name']) ? $raw['name'] : [$raw['name']];
        $errors   = is_array($raw['error']) ? $raw['error'] : [$raw['error']];
        $sizes    = is_array($raw['size']) ? $raw['size'] : [$raw['size']];
        $tmpNames = is_array($raw['tmp_name']) ? $raw['tmp_name'] : [$raw['tmp_name']];

        // Limita a un máximo de $maxArchivos (por defecto 2)
        if (count($names) > $maxArchivos) {
            $this->flash('warning', "Solo se permite subir un máximo de {$maxArchivos} documentos. Los archivos adicionales fueron ignorados.");
            $names    = array_slice($names, 0, $maxArchivos);
            $errors   = array_slice($errors, 0, $maxArchivos);
            $sizes    = array_slice($sizes, 0, $maxArchivos);
            $tmpNames = array_slice($tmpNames, 0, $maxArchivos);
        }

        $dir = __DIR__ . '/../public/uploads/' . $subdir;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            aplicar_permisos_iis($dir);
        }

        $extensionesPermitidas = [
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/gif'       => 'gif',
            'image/webp'      => 'webp',
        ];

        foreach ($names as $i => $originalName) {
            $error = $errors[$i] ?? UPLOAD_ERR_NO_FILE;
            $size = $sizes[$i] ?? 0;
            $tmpFile = $tmpNames[$i] ?? '';

            if ($error === UPLOAD_ERR_NO_FILE || empty($originalName) || empty($tmpFile)) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                $this->flash('warning', "Error al subir el archivo '" . htmlspecialchars($originalName) . "'.");
                continue;
            }

            if ($size > 5 * 1024 * 1024) {
                $mb = round($size / (1024 * 1024), 2);
                $this->flash('warning', "El archivo '" . htmlspecialchars($originalName) . "' supera 5 MB ({$mb} MB) y fue ignorado.");
                continue;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmpFile);
            finfo_close($finfo);

            if (!isset($extensionesPermitidas[$mime])) {
                $this->flash('warning', "El archivo '" . htmlspecialchars($originalName) . "' tiene formato no permitido. Solo se permiten PDF e imágenes.");
                continue;
            }

            $extension = $extensionesPermitidas[$mime];
            $filename = $subdir . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $destino = $dir . '/' . $filename;

            $moved = is_uploaded_file($tmpFile)
                ? @move_uploaded_file($tmpFile, $destino)
                : (@rename($tmpFile, $destino) || @copy($tmpFile, $destino));

            if (!$moved) {
                $this->flash('warning', "No se pudo guardar el archivo '" . htmlspecialchars($originalName) . "'.");
                continue;
            }

            aplicar_permisos_iis($destino);

            // Comprime si aplica
            Compressor::compress($destino, $mime);
            aplicar_permisos_iis($destino);

            $finalSize = is_file($destino) ? filesize($destino) : $size;

            $uploadedFiles[] = [
                'nombre_archivo'  => $filename,
                'nombre_original' => $originalName,
                'mime_type'       => $mime,
                'tamano_bytes'    => $finalSize,
            ];
        }

        return $uploadedFiles;
    }
}
