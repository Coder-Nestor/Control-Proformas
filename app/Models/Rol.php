<?php

namespace App\Models;

use Core\Model;

class Rol extends Model
{
    protected static string $table = 'roles';

    public static function todos(): array
    {
        $stmt = self::db()->query('SELECT * FROM roles ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public static function slugExiste(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM roles WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Cuántos usuarios tiene asignado este rol (para avisar antes de eliminarlo). */
    public static function contarUsuarios(int $rolId): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM usuarios WHERE rol_id = :id');
        $stmt->execute(['id' => $rolId]);
        return (int) $stmt->fetchColumn();
    }

    /** Todos los permisos del catálogo, agrupados por módulo — para pintar la matriz de checkboxes. */
    public static function catalogoPermisosPorModulo(): array
    {
        $stmt = self::db()->query('SELECT * FROM permisos ORDER BY modulo, id');
        $permisos = $stmt->fetchAll();

        $agrupado = [];
        foreach ($permisos as $p) {
            $agrupado[$p['modulo']][] = $p;
        }
        return $agrupado;
    }

    /** IDs de los permisos que YA tiene asignados un rol (para marcar los checkboxes). */
    public static function permisosDe(int $rolId): array
    {
        $stmt = self::db()->prepare('SELECT permiso_id FROM rol_permisos WHERE rol_id = :id');
        $stmt->execute(['id' => $rolId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** Reemplaza TODOS los permisos de un rol por la lista nueva que se marcó en el formulario. */
    public static function guardarPermisos(int $rolId, array $permisoIds): void
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            $del = $db->prepare('DELETE FROM rol_permisos WHERE rol_id = :id');
            $del->execute(['id' => $rolId]);

            if (!empty($permisoIds)) {
                $ins = $db->prepare('INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (:rid, :pid)');
                foreach ($permisoIds as $permisoId) {
                    $ins->execute(['rid' => $rolId, 'pid' => (int) $permisoId]);
                }
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
