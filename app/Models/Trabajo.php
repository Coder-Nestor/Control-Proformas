<?php

namespace App\Models;

use Core\Model;

class Trabajo extends Model
{
    protected static string $table = 'trabajos';

    public static function deGestion(int $gestionId): array
    {
        $stmt = self::db()->prepare(
            "SELECT t.*, p.n_proforma
             FROM trabajos t
             LEFT JOIN proformas p ON p.id = t.proforma_id
             WHERE t.gestion_id = :gid
             ORDER BY t.orden, t.id"
        );
        $stmt->execute(['gid' => $gestionId]);
        return $stmt->fetchAll();
    }

    public static function deProforma(int $proformaId): array
    {
        $stmt = self::db()->prepare(
            "SELECT t.*, g.n_cotizacion, g.proveedor_id, pr.nombre AS proveedor_nombre
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             LEFT JOIN proveedores pr ON pr.id = g.proveedor_id
             WHERE t.proforma_id = :pid AND g.eliminado_en IS NULL
             ORDER BY t.id"
        );
        $stmt->execute(['pid' => $proformaId]);
        return $stmt->fetchAll();
    }

    public static function reemplazarDeGestion(int $gestionId, array $trabajos): void
    {
        $db = self::db();
        $del = $db->prepare('DELETE FROM trabajos WHERE gestion_id = :gid');
        $del->execute(['gid' => $gestionId]);

        $ins = $db->prepare(
            'INSERT INTO trabajos (gestion_id, proforma_id, descripcion, valor, orden) VALUES (:gid, :pid, :desc, :valor, :orden)'
        );
        foreach ($trabajos as $i => $t) {
            $ins->execute([
                'gid' => $gestionId,
                'pid' => $t['proforma_id'] ?? null,
                'desc' => $t['descripcion'],
                'valor' => $t['valor'] ?? null,
                'orden' => $i,
            ]);
        }
    }

    public static function contarPorGestion(int $gestionId): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM trabajos WHERE gestion_id = :gid');
        $stmt->execute(['gid' => $gestionId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Se usa desde el buscador de cotización del formulario de Proforma
     * (tanto al escribir el número a mano como al seleccionarlo de la
     * lista). Solo devuelve resultados si el proveedor de esa gestión
     * está habilitado para pasar a Proforma — si no, se queda encerrado
     * en Gestiones sin importar cómo se busque su cotización.
     */
    public static function porNumeroCotizacion(string $numero): array
    {
        $stmt = self::db()->prepare(
            "SELECT t.id, t.descripcion, t.valor, t.proforma_id, g.n_cotizacion, pr.nombre AS proveedor_nombre
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             INNER JOIN proveedores pr ON pr.id = g.proveedor_id
             WHERE TRIM(COALESCE(g.n_cotizacion, '')) = :numero
               AND g.eliminado_en IS NULL
               AND pr.habilitado_proforma = 1
             ORDER BY t.orden, t.id"
        );
        $stmt->execute(['numero' => trim($numero)]);
        return $stmt->fetchAll();
    }

    public static function asignarProforma(int $trabajoId, int $proformaId): bool
    {
        $stmt = self::db()->prepare('UPDATE trabajos SET proforma_id = :pid WHERE id = :id');
        return $stmt->execute(['pid' => $proformaId, 'id' => $trabajoId]);
    }

    public static function desasignarPorProforma(int $proformaId): int
    {
        $stmt = self::db()->prepare('UPDATE trabajos SET proforma_id = NULL WHERE proforma_id = :pid');
        $stmt->execute(['pid' => $proformaId]);
        return $stmt->rowCount();
    }

    public static function findConGestion(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT t.id, t.descripcion, t.valor, t.proforma_id, t.gestion_id,
                    g.n_cotizacion, g.proveedor_id, g.solicitado_por
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             WHERE t.id = :id AND g.eliminado_en IS NULL"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}