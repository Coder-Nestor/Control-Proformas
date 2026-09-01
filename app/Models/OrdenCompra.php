<?php

namespace App\Models;

use Core\Model;

class OrdenCompra extends Model
{
    protected static string $table = 'ordenes_compra';

    public const ESTADOS = ['correcta' => 'Correcta', 'pendiente' => 'Pendiente', 'con_problema' => 'Con problema'];

    public static function allConDetalle(array $filtros = []): array
    {
        $sql = "SELECT oc.*, p.n_proforma, p.fecha_revision_proforma, p.valor_proforma, pr.nombre AS proveedor_nombre
                FROM ordenes_compra oc
                INNER JOIN proformas p ON p.id = oc.proforma_id
                LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
                WHERE oc.eliminado_en IS NULL";
        $params = [];
        if (!empty($filtros['estado'])) {
            $sql .= ' AND oc.estado = :estado';
            $params['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= ' AND (oc.n_oce_interna LIKE :buscar1 OR p.n_proforma LIKE :buscar2)';
            $params['buscar1'] = '%' . $filtros['buscar'] . '%';
            $params['buscar2'] = '%' . $filtros['buscar'] . '%';
        }
        $sql .= ' ORDER BY oc.id DESC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findConDetalle(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT oc.*, p.n_proforma, p.fecha_revision_proforma, p.valor_proforma, pr.nombre AS proveedor_nombre
             FROM ordenes_compra oc
             INNER JOIN proformas p ON p.id = oc.proforma_id
             LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
             WHERE oc.id = :id AND oc.eliminado_en IS NULL"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findPorProforma(int $proformaId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM ordenes_compra WHERE proforma_id = :pid AND eliminado_en IS NULL');
        $stmt->execute(['pid' => $proformaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function sinFactura(?int $incluirId = null): array
    {
        $sql = "SELECT oc.*, p.n_proforma, pr.nombre AS proveedor_nombre
                FROM ordenes_compra oc
                INNER JOIN proformas p ON p.id = oc.proforma_id
                LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
                WHERE oc.eliminado_en IS NULL
                  AND NOT EXISTS (SELECT 1 FROM facturas f WHERE f.orden_compra_id = oc.id AND f.eliminado_en IS NULL)";
        if ($incluirId) {
            $sql .= ' OR oc.id = :id';
        }
        $sql .= ' ORDER BY oc.id DESC';
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
        $stmt = self::db()->query("SELECT estado, COUNT(*) AS total FROM ordenes_compra WHERE eliminado_en IS NULL GROUP BY estado");
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
            "INSERT INTO historial (entidad, entidad_id, usuario_id, accion, creado_en) VALUES ('oc', :id, :uid, :accion, NOW())"
        );
        $stmt->execute(['id' => $id, 'uid' => $usuarioId, 'accion' => $accion]);
    }

    public static function historialDe(int $id): array
    {
        $stmt = self::db()->prepare(
            "SELECT h.*, u.nombre AS usuario_nombre
             FROM historial h LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.entidad = 'oc' AND h.entidad_id = :id
             ORDER BY h.creado_en DESC"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    public static function softDelete(int $id, ?int $usuarioId): void
    {
        $stmt = self::db()->prepare('UPDATE ordenes_compra SET eliminado_en = NOW(), eliminado_por = :uid WHERE id = :id');
        $stmt->execute(['id' => $id, 'uid' => $usuarioId]);
    }
}
