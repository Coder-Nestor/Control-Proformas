<?php

namespace App\Models;

use Core\Model;

class Area extends Model
{
    protected static string $table = 'areas';

    /** Todas las áreas (activas e inactivas), para la pantalla de administración. */
    public static function todas(): array
    {
        $stmt = self::db()->query('SELECT * FROM areas ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    /** Solo las áreas activas, para poblar los <select> de los formularios. */
    public static function activas(): array
    {
        $stmt = self::db()->query('SELECT * FROM areas WHERE activo = 1 ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public static function nombreExiste(string $nombre, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM areas WHERE nombre = :nombre';
        $params = ['nombre' => $nombre];

        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
