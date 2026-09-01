<?php

namespace App\Models;

use Core\Model;

class Historial extends Model
{
    protected static string $table = 'historial';

    public const ENTIDADES = [
        'gestion'   => 'Gestión',
        'proforma'  => 'Proforma',
        'oc'        => 'Orden de Compra',
        'factura'   => 'Factura',
        'entrega'   => 'Entrega',
        'proveedor' => 'Proveedor',
        'area'      => 'Área',
        'usuario'   => 'Usuario',
    ];

    public const RUTAS = [
        'gestion'   => '/gestiones/',
        'proforma'  => '/proformas/',
        'oc'        => '/ordenes/',
        'factura'   => '/facturas/',
        'entrega'   => '/entregas/',
        'proveedor' => '/proveedores',
        'area'      => '/areas',
        'usuario'   => '/usuarios',
    ];

    public static function registrar(string $entidad, int $entidadId, ?int $usuarioId, string $accion): void
    {
        $stmt = self::db()->prepare(
            "INSERT INTO historial (entidad, entidad_id, usuario_id, accion, creado_en) VALUES (:entidad, :id, :uid, :accion, NOW())"
        );
        $stmt->execute([
            'entidad' => $entidad,
            'id'      => $entidadId,
            'uid'     => $usuarioId,
            'accion'  => $accion,
        ]);
    }

    private static function construirFiltros(array $filtros): array
    {
        $where = 'WHERE 1 = 1';
        $params = [];
        if (!empty($filtros['entidad'])) { $where .= ' AND h.entidad = :entidad'; $params['entidad'] = $filtros['entidad']; }
        if (!empty($filtros['usuario_id'])) { $where .= ' AND h.usuario_id = :usuario_id'; $params['usuario_id'] = $filtros['usuario_id']; }
        if (!empty($filtros['buscar'])) { $where .= ' AND h.accion LIKE :buscar'; $params['buscar'] = '%' . $filtros['buscar'] . '%'; }
        if (!empty($filtros['fecha_desde'])) { $where .= ' AND DATE(h.creado_en) >= :fecha_desde'; $params['fecha_desde'] = $filtros['fecha_desde']; }
        if (!empty($filtros['fecha_hasta'])) { $where .= ' AND DATE(h.creado_en) <= :fecha_hasta'; $params['fecha_hasta'] = $filtros['fecha_hasta']; }
        return [$where, $params];
    }

    private static function sqlBase(): string
    {
        return "SELECT h.*, u.nombre AS usuario_nombre,
                    (CASE h.entidad
                        WHEN 'gestion'   THEN (SELECT COUNT(*) FROM gestiones g WHERE g.id = h.entidad_id AND g.eliminado_en IS NULL)
                        WHEN 'proforma'  THEN (SELECT COUNT(*) FROM proformas p WHERE p.id = h.entidad_id AND p.eliminado_en IS NULL)
                        WHEN 'oc'        THEN (SELECT COUNT(*) FROM ordenes_compra oc WHERE oc.id = h.entidad_id AND oc.eliminado_en IS NULL)
                        WHEN 'factura'   THEN (SELECT COUNT(*) FROM facturas f WHERE f.id = h.entidad_id AND f.eliminado_en IS NULL)
                        WHEN 'entrega'   THEN (SELECT COUNT(*) FROM entregas_factura e WHERE e.id = h.entidad_id AND e.eliminado_en IS NULL)
                        WHEN 'proveedor' THEN (SELECT COUNT(*) FROM proveedores pr WHERE pr.id = h.entidad_id)
                        WHEN 'area'      THEN (SELECT COUNT(*) FROM areas a WHERE a.id = h.entidad_id)
                        WHEN 'usuario'   THEN (SELECT COUNT(*) FROM usuarios us WHERE us.id = h.entidad_id)
                        ELSE 0
                    END) AS existe
                FROM historial h
                LEFT JOIN usuarios u ON u.id = h.usuario_id";
    }

    public static function allConDetalle(array $filtros = [], ?int $porPagina = null, ?int $offset = null): array
    {
        [$where, $params] = self::construirFiltros($filtros);
        $sql = self::sqlBase() . " $where ORDER BY h.creado_en DESC";
        if ($porPagina !== null) $sql .= ' LIMIT :limite OFFSET :offset';
        $stmt = self::db()->prepare($sql);
        foreach ($params as $key => $value) $stmt->bindValue($key, $value);
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
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM historial h $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
