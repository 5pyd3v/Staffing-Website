<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class EmployerActivity extends Model
{
    protected static string $table = 'employer_activities';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = false;

    public static function timelineForCompany(int $companyId): array
    {
        $stmt = self::db()->prepare(
            'SELECT ea.*, u.email AS created_by_email
             FROM employer_activities ea
             LEFT JOIN users u ON u.id = ea.created_by
             WHERE ea.company_id = :company_id
             ORDER BY ea.occurred_at DESC, ea.id DESC'
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }
}
