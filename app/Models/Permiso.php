<?php

namespace App\Models;

use Core\Model;

/**
 * Catálogo fijo de permisos del sistema (uno por módulo + acción).
 * No se crea ni se edita desde ninguna pantalla — corresponde 1 a 1
 * con las rutas reales de config/routes.php.
 */
class Permiso extends Model
{
    protected static string $table = 'permisos';

    public static function porSlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM permisos WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
