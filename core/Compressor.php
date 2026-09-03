<?php

namespace Core;

/**
 * Clase para comprimir documentos (imágenes y PDFs) a un tamaño máximo.
 * VERSIÓN SIMPLIFICADA: Solo usa GD (disponible en PHP por defecto)
 */
class Compressor
{
    private static $maxSizeBytes = 5 * 1024 * 1024; // 5 MB default
    private static $compressionQuality = 75; // Calidad JPEG (1-100)
    private static $maxWidth = 2000;
    private static $maxHeight = 2000;
    private static $enabled = true;
    private static $configLoaded = false;

    private static function loadConfig(): void
    {
        if (self::$configLoaded) {
            return;
        }
        $configFile = __DIR__ . '/../config/compression.php';
        if (is_file($configFile)) {
            $cfg = require $configFile;
            if (is_array($cfg)) {
                if (isset($cfg['max_size_bytes'])) self::$maxSizeBytes = (int) $cfg['max_size_bytes'];
                if (isset($cfg['compression_quality'])) self::$compressionQuality = (int) $cfg['compression_quality'];
                if (isset($cfg['max_image_width'])) self::$maxWidth = (int) $cfg['max_image_width'];
                if (isset($cfg['max_image_height'])) self::$maxHeight = (int) $cfg['max_image_height'];
                if (isset($cfg['enabled'])) self::$enabled = (bool) $cfg['enabled'];
            }
        }
        self::$configLoaded = true;
    }

    /**
     * Comprime un archivo si la compresión está habilitada y supera el límite o es imagen.
     */
    public static function compress(string $filePath, string $mimeType): array
    {
        self::loadConfig();

        if (!self::$enabled) {
            $size = is_file($filePath) ? filesize($filePath) : 0;
            return ['success' => true, 'message' => 'Compresión deshabilitada', 'compressed' => false, 'sizeBefore' => $size, 'sizeAfter' => $size];
        }

        if (!is_file($filePath)) {
            return ['success' => false, 'message' => 'Archivo no encontrado', 'compressed' => false, 'sizeBefore' => 0, 'sizeAfter' => 0];
        }

        $sizeBefore = filesize($filePath);

        // Si es imagen o supera 1 MB, intentamos optimizarlo
        if (strpos($mimeType, 'image/') === 0) {
            return self::compressImage($filePath, $mimeType, $sizeBefore);
        } elseif ($mimeType === 'application/pdf') {
            return self::compressPDF($filePath, $sizeBefore);
        }

        return ['success' => true, 'message' => 'Tipo no requiere compresión adicional', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
    }

    /**
     * Comprime una imagen usando GD Library.
     */
    private static function compressImage(string $filePath, string $mimeType, int $sizeBefore): array
    {
        if (!extension_loaded('gd')) {
            return ['success' => false, 'message' => 'GD no habilitada', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
        }

        try {
            $image = null;
            $saveFunc = null;
            $quality = self::$compressionQuality;
            
            if ($mimeType === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
                $image = @imagecreatefromjpeg($filePath);
                $saveFunc = 'imagejpeg';
            } elseif ($mimeType === 'image/png' && function_exists('imagecreatefrompng')) {
                $image = @imagecreatefrompng($filePath);
                $saveFunc = 'imagepng';
                $quality = 9;
            } elseif ($mimeType === 'image/gif' && function_exists('imagecreatefromgif')) {
                $image = @imagecreatefromgif($filePath);
                $saveFunc = 'imagegif';
            } elseif ($mimeType === 'image/webp' && function_exists('imagecreatefromwebp')) {
                $image = @imagecreatefromwebp($filePath);
                $saveFunc = 'imagewebp';
            } else {
                return ['success' => false, 'message' => 'Formato no soportado', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
            }

            if ($image === false) {
                return ['success' => false, 'message' => 'No se pudo cargar imagen', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Redimensiona si es muy grande
            if ($width > self::$maxWidth || $height > self::$maxHeight) {
                $ratio = min(self::$maxWidth / $width, self::$maxHeight / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                if ($mimeType === 'image/png') {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                }
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }

            // Guarda con compresión
            if ($saveFunc === 'imagejpeg' || $saveFunc === 'imagewebp') {
                @$saveFunc($image, $filePath, $quality);
            } elseif ($saveFunc === 'imagepng') {
                @imagepng($image, $filePath, 9);
            } else {
                @$saveFunc($image, $filePath);
            }

            imagedestroy($image);
            $sizeAfter = filesize($filePath);

            return [
                'success' => true,
                'message' => 'Imagen comprimida',
                'compressed' => $sizeAfter < $sizeBefore,
                'sizeBefore' => $sizeBefore,
                'sizeAfter' => $sizeAfter,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
        }
    }

    /**
     * Comprime PDF usando Ghostscript si está disponible.
     */
    private static function compressPDF(string $filePath, int $sizeBefore): array
    {
        // Intenta Ghostscript
        if (self::isGhostscriptAvailable()) {
            $result = self::compressPDFWithGhostscript($filePath, $sizeBefore);
            if ($result['compressed']) return $result;
        }

        // Intenta qpdf  
        if (self::isQpdfAvailable()) {
            $result = self::compressPDFWithQpdf($filePath, $sizeBefore);
            if ($result['compressed']) return $result;
        }

        // Fallback: sin compresión
        return ['success' => true, 'message' => 'PDF sin herramientas de compresión', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
    }

    private static function compressPDFWithGhostscript(string $filePath, int $sizeBefore): array
    {
        $tmpFile = $filePath . '.tmp.pdf';
        $cmd = sprintf('gswin64c -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -r150x150 -o "%s" "%s"', $tmpFile, $filePath);
        $ret = 0;
        @exec($cmd, $out, $ret);
        if ($ret !== 0 || !is_file($tmpFile)) return ['success' => false, 'message' => 'Ghostscript error', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
        $after = filesize($tmpFile);
        if ($after < $sizeBefore) {
            unlink($filePath);
            rename($tmpFile, $filePath);
            return ['success' => true, 'message' => 'PDF comprimido Ghostscript', 'compressed' => true, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $after];
        }
        unlink($tmpFile);
        return ['success' => true, 'message' => 'PDF ya comprimido', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
    }

    private static function compressPDFWithQpdf(string $filePath, int $sizeBefore): array
    {
        $tmpFile = $filePath . '.tmp.pdf';
        $cmd = sprintf('qpdf --stream-data=compress "%s" "%s"', $filePath, $tmpFile);
        $ret = 0;
        @exec($cmd, $out, $ret);
        if ($ret !== 0 || !is_file($tmpFile)) return ['success' => false, 'message' => 'qpdf error', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
        $after = filesize($tmpFile);
        if ($after < $sizeBefore) {
            unlink($filePath);
            rename($tmpFile, $filePath);
            return ['success' => true, 'message' => 'PDF comprimido qpdf', 'compressed' => true, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $after];
        }
        unlink($tmpFile);
        return ['success' => true, 'message' => 'PDF ya comprimido', 'compressed' => false, 'sizeBefore' => $sizeBefore, 'sizeAfter' => $sizeBefore];
    }

    private static function isGhostscriptAvailable(): bool
    {
        static $avail = null;
        if ($avail === null) {
            $cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where gswin64c' : 'which gs';
            $ret = 0;
            @exec($cmd, $out, $ret);
            $avail = $ret === 0;
        }
        return $avail;
    }

    private static function isQpdfAvailable(): bool
    {
        static $avail = null;
        if ($avail === null) {
            $cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where qpdf' : 'which qpdf';
            $ret = 0;
            @exec($cmd, $out, $ret);
            $avail = $ret === 0;
        }
        return $avail;
    }

    public static function formatBytes(int $bytes): string
    {
        $u = ['B', 'KB', 'MB', 'GB'];
        $b = max($bytes, 0);
        $p = floor(($b ? log($b) : 0) / log(1024));
        $p = min($p, count($u) - 1);
        $b /= (1 << (10 * $p));
        return round($b, 2) . ' ' . $u[$p];
    }
}
