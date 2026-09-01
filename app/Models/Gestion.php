<?php

namespace App\Models;

use Core\Model;

class Gestion extends Model
{
    protected static string $table = 'gestiones';

    private static function construirFiltros(array $filtros): array
    {
        $where = 'WHERE g.eliminado_en IS NULL';
        $params = [];

        if (!empty($filtros['proveedor_id'])) {
            $where .= ' AND g.proveedor_id = :proveedor_id';
            $params['proveedor_id'] = $filtros['proveedor_id'];
        }
        if (isset($filtros['sin_asignar']) && $filtros['sin_asignar'] === '1') {
            $where .= ' AND EXISTS (SELECT 1 FROM trabajos t2 WHERE t2.gestion_id = g.id AND t2.proforma_id IS NULL)';
        }
        if (!empty($filtros['buscar'])) {
            $where .= ' AND (g.n_cotizacion LIKE :buscar1
                        OR EXISTS (SELECT 1 FROM trabajos t3 WHERE t3.gestion_id = g.id AND t3.descripcion LIKE :buscar2))';
            $params['buscar1'] = '%' . $filtros['buscar'] . '%';
            $params['buscar2'] = '%' . $filtros['buscar'] . '%';
        }

        return [$where, $params];
    }

    public static function allConDetalle(array $filtros = [], ?int $porPagina = null, ?int $offset = null): array
    {
        [$where, $params] = self::construirFiltros($filtros);

        $sql = "SELECT g.*, pr.nombre AS proveedor_nombre,
                       (SELECT COALESCE(SUM(t.valor), 0) FROM trabajos t WHERE t.gestion_id = g.id) AS valor_total,
                       (SELECT COUNT(*) FROM trabajos t WHERE t.gestion_id = g.id) AS total_trabajos
                FROM gestiones g
                LEFT JOIN proveedores pr ON pr.id = g.proveedor_id
                $where
                ORDER BY g.id DESC";

        if ($porPagina !== null) {
            $sql .= ' LIMIT :limite OFFSET :offset';
        }

        $stmt = self::db()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if ($porPagina !== null) {
            $stmt->bindValue('limite', $porPagina, \PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset ?? 0, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function contarConDetalle(array $filtros = []): int
    {
        [$where, $params] = self::construirFiltros($filtros);
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM gestiones g $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function numeroCotizacionExiste(string $numero, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM gestiones WHERE n_cotizacion = :numero AND eliminado_en IS NULL';
        $params = ['numero' => $numero];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Solo devuelve cotizaciones de proveedores habilitados para pasar a
     * Proforma (pr.habilitado_proforma = 1) — así el buscador en el
     * formulario de Proforma nunca ofrece una cotización de un proveedor
     * que debe quedarse encerrado en Gestiones.
     */
    public static function cotizacionesDisponibles(?int $incluirProformaId = null): array
    {
        $sql = "SELECT g.n_cotizacion, g.id AS gestion_id, g.proveedor_id, pr.nombre AS proveedor_nombre, g.solicitado_por,
                       COUNT(t.id) AS total_trabajos,
                       SUM(CASE WHEN t.proforma_id IS NULL OR (:pid1 IS NOT NULL AND t.proforma_id = :pid2) THEN 1 ELSE 0 END) AS trabajos_sin_asignar
                FROM gestiones g
                INNER JOIN trabajos t ON t.gestion_id = g.id
                INNER JOIN proveedores pr ON pr.id = g.proveedor_id
                WHERE g.n_cotizacion IS NOT NULL 
                  AND TRIM(g.n_cotizacion) != '' 
                  AND g.eliminado_en IS NULL
                  AND pr.habilitado_proforma = 1
                GROUP BY g.id, g.n_cotizacion, g.proveedor_id, pr.nombre, g.solicitado_por
                HAVING trabajos_sin_asignar > 0
                ORDER BY g.n_cotizacion ASC";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            'pid1' => $incluirProformaId,
            'pid2' => $incluirProformaId,
        ]);
        return $stmt->fetchAll();
    }

    public static function findConDetalle(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT g.*, pr.nombre AS proveedor_nombre
             FROM gestiones g
             LEFT JOIN proveedores pr ON pr.id = g.proveedor_id
             WHERE g.id = :id AND g.eliminado_en IS NULL"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function contarSinAsignar(): int
    {
        $stmt = self::db()->query(
            "SELECT COUNT(DISTINCT g.id)
             FROM gestiones g
             INNER JOIN trabajos t ON t.gestion_id = g.id
             WHERE t.proforma_id IS NULL AND g.eliminado_en IS NULL"
        );
        return (int) $stmt->fetchColumn();
    }

    public static function atrasadas(int $umbralDias): array
    {
        $stmt = self::db()->prepare(
            "SELECT g.*, pr.nombre AS proveedor_nombre,
                    DATEDIFF(CURDATE(), g.fecha_finalizacion_trabajo) AS dias_transcurridos
             FROM gestiones g
             LEFT JOIN proveedores pr ON pr.id = g.proveedor_id
             WHERE g.fecha_finalizacion_trabajo IS NOT NULL
               AND g.fecha_revision_cotizacion IS NULL
               AND g.eliminado_en IS NULL
               AND DATEDIFF(CURDATE(), g.fecha_finalizacion_trabajo) >= :umbral
             ORDER BY dias_transcurridos DESC"
        );
        $stmt->bindValue(':umbral', $umbralDias, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function valorPorProveedor(int $limite = 8): array
    {
        $stmt = self::db()->prepare(
            "SELECT pr.nombre, COALESCE(SUM(t.valor), 0) AS total
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             INNER JOIN proveedores pr ON pr.id = g.proveedor_id
             WHERE g.eliminado_en IS NULL
             GROUP BY pr.id, pr.nombre
             ORDER BY total DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function tendenciaMensual(int $meses = 6): array
    {
        $stmt = self::db()->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes, COUNT(*) AS total
             FROM gestiones
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :meses MONTH)
               AND eliminado_en IS NULL
             GROUP BY mes
             ORDER BY mes"
        );
        $stmt->bindValue(':meses', $meses, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function registrarHistorial(int $id, ?int $usuarioId, string $accion): void
    {
        $stmt = self::db()->prepare(
            "INSERT INTO historial (entidad, entidad_id, usuario_id, accion, creado_en) VALUES ('gestion', :id, :uid, :accion, NOW())"
        );
        $stmt->execute(['id' => $id, 'uid' => $usuarioId, 'accion' => $accion]);
    }

    public static function historialDe(int $id): array
    {
        $stmt = self::db()->prepare(
            "SELECT h.*, u.nombre AS usuario_nombre
             FROM historial h LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.entidad = 'gestion' AND h.entidad_id = :id
             ORDER BY h.creado_en DESC"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    public static function softDelete(int $id, ?int $usuarioId): void
    {
        $stmt = self::db()->prepare('UPDATE gestiones SET eliminado_en = NOW(), eliminado_por = :uid WHERE id = :id');
        $stmt->execute(['id' => $id, 'uid' => $usuarioId]);
    }
}