<?php

namespace App\Models;

use Core\Model;

class Documento extends Model
{
    protected static string $table = 'documentos';

    public static function crearDelArchivo(
        string $tipoEntidad,
        int $idEntidad,
        string $nombreArchivo,
        string $mimeType,
        int $tamanoBytesDeArchivo,
        ?int $creadoPor = null,
        ?string $nombreOriginal = null,
        int $orden = 1
    ): int {
        return self::insert([
            'tipo_entidad'    => $tipoEntidad,
            'id_entidad'      => $idEntidad,
            'nombre_archivo'  => $nombreArchivo,
            'nombre_original' => $nombreOriginal ?: $nombreArchivo,
            'mime_type'       => $mimeType,
            'tamano_bytes'    => $tamanoBytesDeArchivo,
            'creado_por'      => $creadoPor,
            'orden'           => $orden,
        ]);
    }

    public static function deEntidad(string $tipoEntidad, int $idEntidad): array
    {
        $sql = 'SELECT * FROM ' . self::$table . ' 
                WHERE tipo_entidad = ? AND id_entidad = ? AND eliminado_en IS NULL
                ORDER BY orden ASC, id ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$tipoEntidad, $idEntidad]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function deEntidades(string $tipoEntidad, array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'SELECT * FROM ' . self::$table . ' 
                WHERE tipo_entidad = ? AND id_entidad IN (' . $placeholders . ') AND eliminado_en IS NULL
                ORDER BY orden ASC, id ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(array_merge([$tipoEntidad], $ids));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['id_entidad']][] = $row;
        }
        return $result;
    }

    public static function contar(string $tipoEntidad, int $idEntidad): int
    {
        $sql = 'SELECT COUNT(*) as cnt FROM ' . self::$table . ' 
                WHERE tipo_entidad = ? AND id_entidad = ? AND eliminado_en IS NULL';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$tipoEntidad, $idEntidad]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) ($result['cnt'] ?? 0);
    }

    public static function eliminarDeEntidad(string $tipoEntidad, int $idEntidad, ?int $eliminadoPor = null): void
    {
        $sql = 'UPDATE ' . self::$table . ' SET eliminado_en = NOW(), eliminado_por = ? 
                WHERE tipo_entidad = ? AND id_entidad = ? AND eliminado_en IS NULL';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$eliminadoPor, $tipoEntidad, $idEntidad]);
    }

    public static function softDelete(int $id, ?int $eliminadoPor = null): void
    {
        $sql = 'UPDATE ' . self::$table . ' SET eliminado_en = NOW(), eliminado_por = ? WHERE id = ?';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$eliminadoPor, $id]);
    }

    public static function find(int $id): ?array
    {
        $sql = 'SELECT * FROM ' . self::$table . ' WHERE id = ? AND eliminado_en IS NULL';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function formatearTamano(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public static function esPdf(string $mimeType, string $nombreArchivo = ''): bool
    {
        return $mimeType === 'application/pdf' || strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION)) === 'pdf';
    }
}

