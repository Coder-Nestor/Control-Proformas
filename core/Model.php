<?php

namespace Core;

use PDO;

abstract class Model
{
    protected static string $table = '';

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        $stmt = static::db()->query('SELECT * FROM ' . static::$table . ' ORDER BY ' . $orderBy);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = static::db()->prepare($sql);
        $stmt->execute($data);

        return (int) static::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $assignments = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', static::$table, $assignments);

        $data['id'] = $id;
        $stmt = static::db()->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        $stmt = static::db()->prepare('DELETE FROM ' . static::$table . ' WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
