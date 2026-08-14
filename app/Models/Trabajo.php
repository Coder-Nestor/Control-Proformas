<?php

namespace App\Models;

use Core\Model;

/**
 * Un Trabajo es cada línea individual de una Gestión (cotización).
 * Una Gestión puede tener 1 o varios Trabajos, y cada Trabajo puede
 * pertenecer a una Proforma distinta (o ninguna todavía).
 */
class Trabajo extends Model
{
    protected static string $table = 'trabajos';

    /** Todos los trabajos de una Gestión, en el orden en que se capturaron. */
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

    /**
     * Un trabajo puntual, con los datos de su Gestión (proveedor, N° cotización).
     * Se usa para prellenar el formulario de "Nueva proforma" cuando se crea
     * desde el botón "Crear proforma" de un trabajo específico.
     */
    public static function findConGestion(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT t.id, t.descripcion, t.valor, t.proforma_id, t.gestion_id,
                    g.n_cotizacion, g.proveedor_id
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             WHERE t.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Todos los trabajos que pertenecen a una Proforma (para su ficha de detalle). */
    public static function deProforma(int $proformaId): array
    {
        $stmt = self::db()->prepare(
            "SELECT t.*, g.n_cotizacion, g.proveedor_id, pr.nombre AS proveedor_nombre
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             LEFT JOIN proveedores pr ON pr.id = g.proveedor_id
             WHERE t.proforma_id = :pid
             ORDER BY t.id"
        );
        $stmt->execute(['pid' => $proformaId]);
        return $stmt->fetchAll();
    }

    /** Reemplaza todos los trabajos de una Gestión (se usa al guardar el formulario). */
    public static function reemplazarDeGestion(int $gestionId, array $trabajos): void
    {
        $pdo = self::db();
        $pdo->prepare('DELETE FROM trabajos WHERE gestion_id = :gid')->execute(['gid' => $gestionId]);

        $stmt = $pdo->prepare(
            'INSERT INTO trabajos (gestion_id, proforma_id, descripcion, valor, orden)
             VALUES (:gestion_id, :proforma_id, :descripcion, :valor, :orden)'
        );

        $orden = 0;
        foreach ($trabajos as $t) {
            if (trim($t['descripcion'] ?? '') === '') {
                continue;
            }
            $stmt->execute([
                'gestion_id'  => $gestionId,
                'proforma_id' => !empty($t['proforma_id']) ? (int) $t['proforma_id'] : null,
                'descripcion' => trim($t['descripcion']),
                'valor'       => ($t['valor'] ?? '') !== '' ? $t['valor'] : null,
                'orden'       => $orden++,
            ]);
        }
    }

    /** Suma de los valores de los trabajos de una Gestión (para mostrar el total de la cotización). */
    public static function totalDeGestion(int $gestionId): float
    {
        $stmt = self::db()->prepare('SELECT COALESCE(SUM(valor), 0) FROM trabajos WHERE gestion_id = :gid');
        $stmt->execute(['gid' => $gestionId]);
        return (float) $stmt->fetchColumn();
    }

    public static function contarPorGestion(int $gestionId): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM trabajos WHERE gestion_id = :gid');
        $stmt->execute(['gid' => $gestionId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca trabajos por el N° de Cotización de su Gestión (se usa en el
     * formulario de Proforma, para elegir un trabajo existente en vez de
     * escribirlo de nuevo). Solo devuelve trabajos que todavía no tienen
     * proforma asignada, para no reasignar uno por error.
     */
    public static function porNumeroCotizacion(string $numero): array
    {
        $stmt = self::db()->prepare(
            "SELECT t.id, t.descripcion, t.valor, t.proforma_id, g.n_cotizacion, pr.nombre AS proveedor_nombre
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             LEFT JOIN proveedores pr ON pr.id = g.proveedor_id
             WHERE TRIM(COALESCE(g.n_cotizacion, '')) = :numero
             ORDER BY t.orden, t.id"
        );
        $stmt->execute(['numero' => trim($numero)]);
        return $stmt->fetchAll();
    }

    /** Asigna un trabajo puntual a una Proforma (usado al crear/editar la Proforma). */
    public static function asignarProforma(int $trabajoId, int $proformaId): bool
    {
        $stmt = self::db()->prepare('UPDATE trabajos SET proforma_id = :pid WHERE id = :id');
        return $stmt->execute(['pid' => $proformaId, 'id' => $trabajoId]);
    }

    /** Quita la proforma de todos los trabajos que la tenían asignada (para reasignar limpio). */
    public static function desasignarPorProforma(int $proformaId): int
    {
        $stmt = self::db()->prepare('UPDATE trabajos SET proforma_id = NULL WHERE proforma_id = :pid');
        $stmt->execute(['pid' => $proformaId]);
        return $stmt->rowCount();
    }
}
