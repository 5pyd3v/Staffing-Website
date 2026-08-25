<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static bool $softDeletes = true;
    protected static bool $timestamps = true;

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function find(int $id): ?array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id';
        $sql .= static::$softDeletes ? ' AND deleted_at IS NULL' : '';

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function findByUuid(string $uuid): ?array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE uuid = :uuid';
        $sql .= static::$softDeletes ? ' AND deleted_at IS NULL' : '';

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['uuid' => $uuid]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . self::assertColumn($column) . ' = :value';
        $sql .= static::$softDeletes ? ' AND deleted_at IS NULL' : '';

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['value' => $value]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        $sql .= static::$softDeletes ? ' WHERE deleted_at IS NULL' : '';
        $sql .= ' ORDER BY ' . self::assertOrderBy($orderBy);

        return static::db()->query($sql)->fetchAll();
    }

    public static function create(array $data): string|int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn ($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', array_map([self::class, 'assertColumn'], $columns)),
            implode(', ', $placeholders)
        );

        $stmt = static::db()->prepare($sql);
        $stmt->execute($data);

        return static::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $assignments = implode(', ', array_map(
            static fn ($c) => self::assertColumn($c) . ' = :' . $c,
            array_keys($data)
        ));

        $sql = 'UPDATE ' . static::$table . ' SET ' . $assignments;
        $sql .= static::$timestamps ? ', updated_at = NOW()' : '';
        $sql .= ' WHERE ' . static::$primaryKey . ' = :__id';
        $data['__id'] = $id;

        $stmt = static::db()->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        if (static::$softDeletes) {
            $sql = 'UPDATE ' . static::$table . ' SET deleted_at = NOW() WHERE ' . static::$primaryKey . ' = :id';
        } else {
            $sql = 'DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id';
        }

        $stmt = static::db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . static::$table . ' WHERE ' . $where;
        $sql .= static::$softDeletes ? ' AND deleted_at IS NULL' : '';

        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Guards raw identifiers that get interpolated into SQL (table/column names
     * are never bound as parameters). Restricting to a safe charset prevents
     * accidental or malicious injection via dynamic field names.
     */
    protected static function assertColumn(string $column): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: {$column}");
        }

        return $column;
    }

    protected static function assertOrderBy(string $orderBy): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+(\s+(ASC|DESC))?$/i', trim($orderBy))) {
            throw new \InvalidArgumentException("Invalid order by clause: {$orderBy}");
        }

        return $orderBy;
    }
}
