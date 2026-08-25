<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class CompanyContact extends Model
{
    protected static string $table = 'company_contacts';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = false;

    public static function forCompany(int $companyId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM company_contacts WHERE company_id = :company_id ORDER BY is_primary DESC, id ASC'
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }
}
