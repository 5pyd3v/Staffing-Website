<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Company extends Model
{
    protected static string $table = 'companies';

    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }

    public static function uniqueSlug(string $base): string
    {
        $slug = self::slugify($base);
        $candidate = $slug;
        $i = 2;

        while (self::findBySlug($candidate) !== null) {
            $candidate = $slug . '-' . $i;
            $i++;
        }

        return $candidate;
    }

    public static function slugify(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-') ?: 'company';
    }

    public static function listWithIndustry(string $search = '', string $status = '', int $limit = 25, int $offset = 0): array
    {
        $where = ['c.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(c.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'c.status = :status';
            $params['status'] = $status;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT c.*, i.name AS industry_name,
                       (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.deleted_at IS NULL AND j.status = 'open') AS open_jobs_count
                FROM companies c
                LEFT JOIN industries i ON i.id = c.industry_id
                WHERE {$whereSql}
                ORDER BY c.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function countWithFilters(string $search = '', string $status = ''): int
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        return self::count(implode(' AND ', $where), $params);
    }

    public static function findWithIndustry(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT c.*, i.name AS industry_name
             FROM companies c
             LEFT JOIN industries i ON i.id = c.industry_id
             WHERE c.id = :id AND c.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
