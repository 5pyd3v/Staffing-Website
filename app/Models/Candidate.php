<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Candidate extends Model
{
    protected static string $table = 'candidates';

    public static function findWithUser(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT c.*, u.email, u.status AS account_status
             FROM candidates c
             INNER JOIN users u ON u.id = c.user_id
             WHERE c.id = :id AND c.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function skillsFor(int $candidateId): array
    {
        $stmt = self::db()->prepare(
            'SELECT s.id, s.name, cs.proficiency
             FROM candidate_skills cs
             INNER JOIN skills s ON s.id = cs.skill_id
             WHERE cs.candidate_id = :id
             ORDER BY s.name ASC'
        );
        $stmt->execute(['id' => $candidateId]);

        return $stmt->fetchAll();
    }

    public static function experienceFor(int $candidateId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM candidate_experience WHERE candidate_id = :id ORDER BY is_current DESC, start_date DESC'
        );
        $stmt->execute(['id' => $candidateId]);

        return $stmt->fetchAll();
    }

    public static function educationFor(int $candidateId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM candidate_education WHERE candidate_id = :id ORDER BY end_year DESC'
        );
        $stmt->execute(['id' => $candidateId]);

        return $stmt->fetchAll();
    }

    public static function replaceSkills(int $candidateId, array $skillIds): void
    {
        $db = self::db();
        $delete = $db->prepare('DELETE FROM candidate_skills WHERE candidate_id = :id');
        $delete->execute(['id' => $candidateId]);

        if ($skillIds === []) {
            return;
        }

        $insert = $db->prepare(
            'INSERT INTO candidate_skills (candidate_id, skill_id, proficiency) VALUES (:candidate_id, :skill_id, :proficiency)'
        );

        foreach (array_unique($skillIds) as $skillId) {
            $insert->execute([
                'candidate_id' => $candidateId,
                'skill_id' => $skillId,
                'proficiency' => 'intermediate',
            ]);
        }
    }

    /**
     * Rule-based candidate search used by both the admin AJAX filter UI and
     * the talent-matching workflow. Returns ['rows' => [...], 'total' => int].
     */
    public static function search(array $filters, int $page = 1, int $perPage = 15): array
    {
        [$whereSql, $params] = self::buildSearchWhere($filters);

        $countSql = "SELECT COUNT(DISTINCT c.id)
                     FROM candidates c
                     INNER JOIN users u ON u.id = c.user_id
                     WHERE {$whereSql}";
        $countStmt = self::db()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, min(100, $perPage));
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $dataSql = "SELECT c.*, u.email,
                           GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS skill_names
                    FROM candidates c
                    INNER JOIN users u ON u.id = c.user_id
                    LEFT JOIN candidate_skills cs ON cs.candidate_id = c.id
                    LEFT JOIN skills s ON s.id = cs.skill_id
                    WHERE {$whereSql}
                    GROUP BY c.id
                    ORDER BY c.created_at DESC
                    LIMIT {$perPage} OFFSET {$offset}";

        $dataStmt = self::db()->prepare($dataSql);
        $dataStmt->execute($params);

        return [
            'rows' => $dataStmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    private static function buildSearchWhere(array $filters): array
    {
        $where = ['c.deleted_at IS NULL'];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            // MySQL's native prepared-statement protocol (used here since
            // EMULATE_PREPARES is off) rejects the same named parameter
            // appearing more than once, so each OR branch gets its own name.
            $where[] = '(c.first_name LIKE :qlike1 OR c.last_name LIKE :qlike2 OR c.headline LIKE :qlike3 OR c.current_title LIKE :qlike4 OR c.summary LIKE :qlike5)';
            $like = '%' . $q . '%';
            $params['qlike1'] = $like;
            $params['qlike2'] = $like;
            $params['qlike3'] = $like;
            $params['qlike4'] = $like;
            $params['qlike5'] = $like;
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status !== '' && in_array($status, ['new', 'in_review', 'shortlisted', 'placed', 'inactive'], true)) {
            $where[] = 'c.status = :status';
            $params['status'] = $status;
        }

        $availability = (string) ($filters['availability'] ?? '');
        if ($availability !== '' && in_array($availability, ['immediate', '2_weeks', '1_month', 'not_looking'], true)) {
            $where[] = 'c.availability = :availability';
            $params['availability'] = $availability;
        }

        $location = trim((string) ($filters['location'] ?? ''));
        if ($location !== '') {
            $where[] = '(c.location_city LIKE :location1 OR c.location_state LIKE :location2)';
            $like = '%' . $location . '%';
            $params['location1'] = $like;
            $params['location2'] = $like;
        }

        $minExperience = $filters['min_experience'] ?? '';
        if ($minExperience !== '' && is_numeric($minExperience)) {
            $where[] = 'c.experience_years >= :min_experience';
            $params['min_experience'] = (float) $minExperience;
        }

        $maxExperience = $filters['max_experience'] ?? '';
        if ($maxExperience !== '' && is_numeric($maxExperience)) {
            $where[] = 'c.experience_years <= :max_experience';
            $params['max_experience'] = (float) $maxExperience;
        }

        $remoteOnly = $filters['remote_ok'] ?? '';
        if ($remoteOnly === '1') {
            $where[] = 'c.is_remote_ok = 1';
        }

        $skillId = $filters['skill_id'] ?? '';
        if ($skillId !== '' && is_numeric($skillId)) {
            $where[] = 'EXISTS (SELECT 1 FROM candidate_skills cs2 WHERE cs2.candidate_id = c.id AND cs2.skill_id = :skill_id)';
            $params['skill_id'] = (int) $skillId;
        }

        return [implode(' AND ', $where), $params];
    }
}
