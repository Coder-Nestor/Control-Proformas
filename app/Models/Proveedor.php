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

    /**
     * Solo los proveedores marcados como "habilitado_proforma" pueden
     * continuar el proceso más allá de Gestiones — es decir, aparecer
     * como opción al crear una Proforma (venga de una cotización o de
     * una mensualidad).
     */
    public static function habilitadosParaProforma(): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM proveedores WHERE activo = 1 AND habilitado_proforma = 1 ORDER BY nombre"
        );
        return $stmt->fetchAll();
    }

    public static function estaHabilitadoParaProforma(int $id): bool
    {
        $stmt = self::db()->prepare('SELECT habilitado_proforma FROM proveedores WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}
