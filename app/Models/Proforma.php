<?php

namespace App\Models;

use Core\Model;

class Proforma extends Model
{
    protected static string $table = 'proformas';

    private static function construirFiltros(array $filtros): array
    {
        $where = 'WHERE p.eliminado_en IS NULL';
        $params = [];
        if (!empty($filtros['proveedor_id'])) {
            $where .= ' AND p.proveedor_id = :proveedor_id';
            $params['proveedor_id'] = $filtros['proveedor_id'];
        }
        if (!empty($filtros['buscar'])) {
            $where .= ' AND (p.n_proforma LIKE :buscar1 OR p.solicitado_por LIKE :buscar2)';
            $params['buscar1'] = '%' . $filtros['buscar'] . '%';
            $params['buscar2'] = '%' . $filtros['buscar'] . '%';
        }
        if (!empty($filtros['sin_oc'])) {
            $where .= ' AND NOT EXISTS (SELECT 1 FROM ordenes_compra oc WHERE oc.proforma_id = p.id AND oc.eliminado_en IS NULL)';
        }
        return [$where, $params];
    }

    public static function allConDetalle(array $filtros = [], ?int $porPagina = null, ?int $offset = null): array
    {
        [$where, $params] = self::construirFiltros($filtros);
        $sql = "SELECT p.*, pr.nombre AS proveedor_nombre,
                       (SELECT COUNT(*) FROM trabajos t INNER JOIN gestiones g ON g.id = t.gestion_id WHERE t.proforma_id = p.id AND g.eliminado_en IS NULL) AS total_trabajos,
                       (SELECT GROUP_CONCAT(t.descripcion SEPARATOR ' • ') FROM trabajos t INNER JOIN gestiones g ON g.id = t.gestion_id WHERE t.proforma_id = p.id AND g.eliminado_en IS NULL) AS trabajos_desc,
                       (SELECT COUNT(*) FROM ordenes_compra oc WHERE oc.proforma_id = p.id AND oc.eliminado_en IS NULL) AS tiene_oc,
                       (SELECT oc.n_oce_interna FROM ordenes_compra oc WHERE oc.proforma_id = p.id AND oc.eliminado_en IS NULL LIMIT 1) AS n_oce_interna
                FROM proformas p
                LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
                $where
                ORDER BY p.id DESC";
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
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM proformas p $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function findConDetalle(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT p.*, pr.nombre AS proveedor_nombre
             FROM proformas p
             LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
             WHERE p.id = :id AND p.eliminado_en IS NULL"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function paraSelect(): array
    {
        $stmt = self::db()->query("SELECT id, n_proforma, fecha_solicitud FROM proformas WHERE eliminado_en IS NULL ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function sinOrdenDeCompra(?int $incluirId = null): array
    {
        $sql = "SELECT p.*, pr.nombre AS proveedor_nombre
                FROM proformas p
                LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
                WHERE p.eliminado_en IS NULL
                  AND NOT EXISTS (SELECT 1 FROM ordenes_compra oc WHERE oc.proforma_id = p.id AND oc.eliminado_en IS NULL)";
        if ($incluirId) {
            $sql .= ' OR p.id = :id';
        }
        $sql .= ' ORDER BY p.id DESC';
        $stmt = self::db()->prepare($sql);
        if ($incluirId) {
            $stmt->execute(['id' => $incluirId]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public static function registrarHistorial(int $id, ?int $usuarioId, string $accion): void
    {
        $stmt = self::db()->prepare(
            "INSERT INTO historial (entidad, entidad_id, usuario_id, accion, creado_en) VALUES ('proforma', :id, :uid, :accion, NOW())"
        );
        $stmt->execute(['id' => $id, 'uid' => $usuarioId, 'accion' => $accion]);
    }

    public static function historialDe(int $id): array
    {
        $stmt = self::db()->prepare(
            "SELECT h.*, u.nombre AS usuario_nombre
             FROM historial h LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.entidad = 'proforma' AND h.entidad_id = :id
             ORDER BY h.creado_en DESC"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    public static function softDelete(int $id, ?int $usuarioId): void
    {
        $stmt = self::db()->prepare('UPDATE proformas SET eliminado_en = NOW(), eliminado_por = :uid WHERE id = :id');
        $stmt->execute(['id' => $id, 'uid' => $usuarioId]);
    }
}