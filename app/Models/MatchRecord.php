<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Named MatchRecord (not Match) because `match` is a reserved keyword as of
 * PHP 8.0 and cannot be used as a class name.
 */
final class MatchRecord extends Model
{
    protected static string $table = 'matches';
    protected static bool $softDeletes = false;

    public static function existsFor(int $candidateId, ?int $staffingRequestId, ?int $jobId): bool
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM matches
             WHERE candidate_id = :candidate_id
               AND staffing_request_id <=> :staffing_request_id
               AND job_id <=> :job_id'
        );
        $stmt->execute([
            'candidate_id' => $candidateId,
            'staffing_request_id' => $staffingRequestId,
            'job_id' => $jobId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function forStaffingRequest(int $staffingRequestId): array
    {
        $stmt = self::db()->prepare(
            "SELECT m.*, c.first_name, c.last_name, c.headline, u.email
             FROM matches m
             INNER JOIN candidates c ON c.id = m.candidate_id
             INNER JOIN users u ON u.id = c.user_id
             WHERE m.staffing_request_id = :id
             ORDER BY m.score DESC"
        );
        $stmt->execute(['id' => $staffingRequestId]);

        return $stmt->fetchAll();
    }

    public static function listWithDetails(string $status = '', string $matchType = '', int $limit = 25, int $offset = 0): array
    {
        $where = ['1=1'];
        $params = [];

        if ($status !== '') {
            $where[] = 'm.status = :status';
            $params['status'] = $status;
        }

        if ($matchType !== '') {
            $where[] = 'm.match_type = :match_type';
            $params['match_type'] = $matchType;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT m.*, c.first_name, c.last_name,
                       sr.role_title AS request_title, j.title AS job_title
                FROM matches m
                INNER JOIN candidates c ON c.id = m.candidate_id
                LEFT JOIN staffing_requests sr ON sr.id = m.staffing_request_id
                LEFT JOIN jobs j ON j.id = m.job_id
                WHERE {$whereSql}
                ORDER BY m.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function countWithFilters(string $status = '', string $matchType = ''): int
    {
        $where = ['1=1'];
        $params = [];

        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($matchType !== '') {
            $where[] = 'match_type = :match_type';
            $params['match_type'] = $matchType;
        }

        return self::count(implode(' AND ', $where), $params);
    }

    public static function findWithDetails(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT m.*, c.first_name, c.last_name, c.headline, u.email,
                    sr.role_title AS request_title, sr.company_id AS request_company_id,
                    j.title AS job_title, j.company_id AS job_company_id
             FROM matches m
             INNER JOIN candidates c ON c.id = m.candidate_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN staffing_requests sr ON sr.id = m.staffing_request_id
             LEFT JOIN jobs j ON j.id = m.job_id
             WHERE m.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
