<?php

namespace App\Models;

use Core\Model;

class Factura extends Model
{
    protected static string $table = 'facturas';

    public const ESTADOS = [
        'correcta'     => 'Correcta',
        'pendiente'    => 'Pendiente',
        'con_problema' => 'Con problema',
    ];

    public static function allConDetalle(array $filtros = [], int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT f.*, oc.n_oce_interna, oc.fecha_envio_oce, p.n_proforma, pr.nombre AS proveedor_nombre
                FROM facturas f
                INNER JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
                INNER JOIN proformas p ON p.id = oc.proforma_id
                LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filtros['estado'])) {
            $sql .= ' AND f.estado = :estado';
            $params['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= ' AND (oc.n_oce_interna LIKE :buscar1 OR p.n_proforma LIKE :buscar2 OR f.n_factura LIKE :buscar3)';
            $params['buscar1'] = '%' . $filtros['buscar'] . '%';
            $params['buscar2'] = '%' . $filtros['buscar'] . '%';
            $params['buscar3'] = '%' . $filtros['buscar'] . '%';
        }

        $sql .= ' ORDER BY f.id DESC LIMIT :limit OFFSET :offset';

        $stmt = self::db()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Cuenta el total de facturas que cumplen los mismos filtros que allConDetalle(), para calcular la paginación. */
    public static function contarConDetalle(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*)
                FROM facturas f
                INNER JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
                INNER JOIN proformas p ON p.id = oc.proforma_id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filtros['estado'])) {
            $sql .= ' AND f.estado = :estado';
            $params['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= ' AND (oc.n_oce_interna LIKE :buscar1 OR p.n_proforma LIKE :buscar2 OR f.n_factura LIKE :buscar3)';
            $params['buscar1'] = '%' . $filtros['buscar'] . '%';
            $params['buscar2'] = '%' . $filtros['buscar'] . '%';
            $params['buscar3'] = '%' . $filtros['buscar'] . '%';
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public static function findConDetalle(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT f.*, oc.n_oce_interna, oc.fecha_envio_oce, p.n_proforma, p.id AS proforma_id, pr.nombre AS proveedor_nombre
             FROM facturas f
             INNER JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
             INNER JOIN proformas p ON p.id = oc.proforma_id
             LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
             WHERE f.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findPorOrdenCompra(int $ordenCompraId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM facturas WHERE orden_compra_id = :ocid');
        $stmt->execute(['ocid' => $ordenCompraId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Facturas que todavía no tienen registro de Entrega (para el <select> del formulario de Entrega). */
    public static function sinEntrega(?int $incluirId = null): array
    {
        $sql = "SELECT f.*, oc.n_oce_interna, p.n_proforma, pr.nombre AS proveedor_nombre
                FROM facturas f
                INNER JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
                INNER JOIN proformas p ON p.id = oc.proforma_id
                LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
                WHERE NOT EXISTS (SELECT 1 FROM entregas_factura e WHERE e.factura_id = f.id)";
        if ($incluirId) {
            $sql .= ' OR f.id = :id';
        }
        $sql .= ' ORDER BY f.id DESC';

        $stmt = self::db()->prepare($sql);
        if ($incluirId) {
            $stmt->execute(['id' => $incluirId]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public static function contarPorEstado(): array
    {
        $stmt = self::db()->query('SELECT estado, COUNT(*) AS total FROM facturas GROUP BY estado');
        $rows = $stmt->fetchAll();
        $result = array_fill_keys(array_keys(self::ESTADOS), 0);
        foreach ($rows as $row) {
            $result[$row['estado']] = (int) $row['total'];
        }
        return $result;
    }

    public static function registrarHistorial(int $id, ?int $usuarioId, string $accion): void
    {
        $stmt = self::db()->prepare(
            "INSERT INTO historial (entidad, entidad_id, usuario_id, accion, creado_en) VALUES ('factura', :id, :uid, :accion, NOW())"
        );
        $stmt->execute(['id' => $id, 'uid' => $usuarioId, 'accion' => $accion]);
    }

    public static function historialDe(int $id): array
    {
        $stmt = self::db()->prepare(
            "SELECT h.*, u.nombre AS usuario_nombre
             FROM historial h LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.entidad = 'factura' AND h.entidad_id = :id
             ORDER BY h.creado_en DESC"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }
}