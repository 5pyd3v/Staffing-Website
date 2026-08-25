<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class StaffingRequest extends Model
{
    protected static string $table = 'staffing_requests';

    public static function forCompany(int $companyId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM staffing_requests WHERE company_id = :company_id AND deleted_at IS NULL ORDER BY created_at DESC'
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    public static function listWithCompany(string $status = '', int $limit = 25, int $offset = 0): array
    {
        $where = ['sr.deleted_at IS NULL'];
        $params = [];

        if ($status !== '') {
            $where[] = 'sr.status = :status';
            $params['status'] = $status;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT sr.*, c.name AS company_name
                FROM staffing_requests sr
                LEFT JOIN companies c ON c.id = sr.company_id
                WHERE {$whereSql}
                ORDER BY sr.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function countWithStatus(string $status = ''): int
    {
        $where = 'deleted_at IS NULL';
        $params = [];
        if ($status !== '') {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }

        return self::count($where, $params);
    }

    public static function findWithCompany(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT sr.*, c.name AS company_name
             FROM staffing_requests sr
             LEFT JOIN companies c ON c.id = sr.company_id
             WHERE sr.id = :id AND sr.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
