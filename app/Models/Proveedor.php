<?php

namespace App\Models;

use Core\Model;

class Proveedor extends Model
{
    protected static string $table = 'proveedores';

    public static function activos(): array
    {
        $stmt = self::db()->query("SELECT * FROM proveedores WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll();
    }
}
