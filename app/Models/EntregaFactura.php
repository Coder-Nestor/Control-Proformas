<?php

namespace App\Models;

use Core\Model;

class EntregaFactura extends Model
{
    protected static string $table = 'entregas_factura';

    public static function allConDetalle(array $filtros = []): array
    {
        $sql = "SELECT e.*, f.n_factura, f.fecha_entrega_factura, oc.n_oce_interna, p.n_proforma, pr.nombre AS proveedor_nombre
                FROM entregas_factura e
                INNER JOIN facturas f ON f.id = e.factura_id
                INNER JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
                INNER JOIN proformas p ON p.id = oc.proforma_id
                LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
                WHERE e.eliminado_en IS NULL";
        $params = [];
        if (!empty($filtros['buscar'])) {
            $sql .= ' AND (oc.n_oce_interna LIKE :buscar1 OR p.n_proforma LIKE :buscar2 OR f.n_factura LIKE :buscar3)';
            $params['buscar1'] = '%' . $filtros['buscar'] . '%';
            $params['buscar2'] = '%' . $filtros['buscar'] . '%';
            $params['buscar3'] = '%' . $filtros['buscar'] . '%';
        }
        $sql .= ' ORDER BY e.id DESC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findConDetalle(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT e.*, f.n_factura, f.fecha_entrega_factura, oc.n_oce_interna, p.n_proforma, pr.nombre AS proveedor_nombre
             FROM entregas_factura e
             INNER JOIN facturas f ON f.id = e.factura_id
             INNER JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
             INNER JOIN proformas p ON p.id = oc.proforma_id
             LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
             WHERE e.id = :id AND e.eliminado_en IS NULL"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findPorFactura(int $facturaId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM entregas_factura WHERE factura_id = :fid AND eliminado_en IS NULL');
        $stmt->execute(['fid' => $facturaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function atrasadas(int $umbralDias): array
    {
        $stmt = self::db()->prepare(
            "SELECT e.*, f.n_factura, oc.n_oce_interna, p.n_proforma, pr.nombre AS proveedor_nombre,
                    DATEDIFF(CURDATE(), e.fecha_entrega_dueno) AS dias_transcurridos
             FROM entregas_factura e
             INNER JOIN facturas f ON f.id = e.factura_id
             INNER JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
             INNER JOIN proformas p ON p.id = oc.proforma_id
             LEFT JOIN proveedores pr ON pr.id = p.proveedor_id
             WHERE e.fecha_entrega_dueno IS NOT NULL
               AND e.fecha_solicitud_revision_pago IS NULL
               AND e.eliminado_en IS NULL
               AND DATEDIFF(CURDATE(), e.fecha_entrega_dueno) >= :umbral
             ORDER BY dias_transcurridos DESC"
        );
        $stmt->bindValue(':umbral', $umbralDias, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function registrarHistorial(int $id, ?int $usuarioId, string $accion): void
    {
        $stmt = self::db()->prepare(
            "INSERT INTO historial (entidad, entidad_id, usuario_id, accion, creado_en) VALUES ('entrega', :id, :uid, :accion, NOW())"
        );
        $stmt->execute(['id' => $id, 'uid' => $usuarioId, 'accion' => $accion]);
    }

    public static function historialDe(int $id): array
    {
        $stmt = self::db()->prepare(
            "SELECT h.*, u.nombre AS usuario_nombre
             FROM historial h LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.entidad = 'entrega' AND h.entidad_id = :id
             ORDER BY h.creado_en DESC"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    public static function softDelete(int $id, ?int $usuarioId): void
    {
        $stmt = self::db()->prepare('UPDATE entregas_factura SET eliminado_en = NOW(), eliminado_por = :uid WHERE id = :id');
        $stmt->execute(['id' => $id, 'uid' => $usuarioId]);
    }
}