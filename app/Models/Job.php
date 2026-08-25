<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Job extends Model
{
    protected static string $table = 'jobs';

    public static function uniqueSlug(string $title): string
    {
        $slug = Company::slugify($title);
        $candidate = $slug;
        $i = 2;

        while (self::findBySlug($candidate) !== null) {
            $candidate = $slug . '-' . $i;
            $i++;
        }

        return $candidate;
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }

    public static function listWithCompany(string $search = '', string $status = '', int $limit = 25, int $offset = 0): array
    {
        [$whereSql, $params] = self::buildFilters($search, $status);

        $sql = "SELECT j.*, c.name AS company_name
                FROM jobs j
                INNER JOIN companies c ON c.id = j.company_id
                WHERE {$whereSql}
                ORDER BY j.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function countWithFilters(string $search = '', string $status = ''): int
    {
        [$whereSql, $params] = self::buildFilters($search, $status, 'jobs');
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM jobs WHERE {$whereSql}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public static function forCompany(int $companyId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM jobs WHERE company_id = :company_id AND deleted_at IS NULL ORDER BY created_at DESC'
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    public static function findWithCompany(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT j.*, c.name AS company_name
             FROM jobs j
             INNER JOIN companies c ON c.id = j.company_id
             WHERE j.id = :id AND j.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function findOpenBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT j.*, c.name AS company_name, c.slug AS company_slug, i.name AS industry_name
             FROM jobs j
             INNER JOIN companies c ON c.id = j.company_id
             LEFT JOIN industries i ON i.id = j.industry_id
             WHERE j.slug = :slug AND j.status = 'open' AND j.deleted_at IS NULL"
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Public job-board search: always scoped to open, non-deleted postings.
     */
    public static function publicSearch(array $filters, int $page = 1, int $perPage = 9): array
    {
        [$whereSql, $params] = self::buildPublicFilters($filters);

        $countStmt = self::db()->prepare(
            "SELECT COUNT(*) FROM jobs j INNER JOIN companies c ON c.id = j.company_id WHERE {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, min(50, $perPage));
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $dataStmt = self::db()->prepare(
            "SELECT j.*, c.name AS company_name, c.slug AS company_slug
             FROM jobs j
             INNER JOIN companies c ON c.id = j.company_id
             WHERE {$whereSql}
             ORDER BY j.published_at DESC, j.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $dataStmt->execute($params);

        return [
            'rows' => $dataStmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public static function latestOpen(int $limit = 6): array
    {
        $stmt = self::db()->prepare(
            "SELECT j.*, c.name AS company_name, c.slug AS company_slug
             FROM jobs j
             INNER JOIN companies c ON c.id = j.company_id
             WHERE j.status = 'open' AND j.deleted_at IS NULL
             ORDER BY j.published_at DESC, j.created_at DESC
             LIMIT {$limit}"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private static function buildPublicFilters(array $filters): array
    {
        $where = ["j.status = 'open'", 'j.deleted_at IS NULL'];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(j.title LIKE :q1 OR j.description LIKE :q2 OR c.name LIKE :q3)';
            $like = '%' . $q . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
        }

        $location = trim((string) ($filters['location'] ?? ''));
        if ($location !== '') {
            $where[] = '(j.location_city LIKE :loc1 OR j.location_state LIKE :loc2)';
            $like = '%' . $location . '%';
            $params['loc1'] = $like;
            $params['loc2'] = $like;
        }

        $employmentType = (string) ($filters['employment_type'] ?? '');
        if ($employmentType !== '' && in_array($employmentType, ['full_time', 'part_time', 'contract', 'temp', 'temp_to_hire'], true)) {
            $where[] = 'j.employment_type = :employment_type';
            $params['employment_type'] = $employmentType;
        }

        if (($filters['remote'] ?? '') === '1') {
            $where[] = 'j.is_remote = 1';
        }

        return [implode(' AND ', $where), $params];
    }

    private static function buildFilters(string $search, string $status, string $alias = 'j'): array
    {
        $prefix = $alias === 'j' ? 'j.' : '';
        $where = [$prefix . 'deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = "({$prefix}title LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = "{$prefix}status = :status";
            $params['status'] = $status;
        }

        return [implode(' AND ', $where), $params];
    }
}
