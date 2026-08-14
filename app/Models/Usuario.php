<?php

namespace App\Models;

use Core\Model;

class Usuario extends Model
{
    protected static string $table = 'usuarios';

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT u.*, r.slug AS rol_slug, r.nombre AS rol_nombre
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function allConRol(): array
    {
        $stmt = self::db()->query(
            'SELECT u.*, r.nombre AS rol_nombre
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             ORDER BY u.nombre'
        );
        return $stmt->fetchAll();
    }

    public static function emailExiste(string $email, ?int $excluirId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE email = :email';
        $params = ['email' => $email];
        if ($excluirId) {
            $sql .= ' AND id != :id';
            $params['id'] = $excluirId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
