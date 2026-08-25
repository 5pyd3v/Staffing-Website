<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Job;
use App\Models\StaffingRequest;

final class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $newCandidates = Candidate::count('status = :status', ['status' => 'new']);
        $openRequests = StaffingRequest::count(
            "status NOT IN ('closed_won','closed_lost')"
        );
        $openJobs = Job::count("status = 'open'");

        $placementsStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM matches WHERE status = 'hired' AND updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $placementsStmt->execute();
        $placements = (int) $placementsStmt->fetchColumn();

        $recentCandidatesStmt = Database::connection()->query(
            "SELECT c.id, c.first_name, c.last_name, c.headline, c.status, c.created_at
             FROM candidates c WHERE c.deleted_at IS NULL ORDER BY c.created_at DESC LIMIT 5"
        );
        $recentActivityStmt = Database::connection()->query(
            "SELECT ea.subject, ea.activity_type, ea.occurred_at, c.name AS company_name
             FROM employer_activities ea
             INNER JOIN companies c ON c.id = ea.company_id
             ORDER BY ea.occurred_at DESC LIMIT 6"
        );

        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => [
                'new_candidates' => $newCandidates,
                'open_requests' => $openRequests,
                'open_jobs' => $openJobs,
                'placements_30d' => $placements,
            ],
            'recentCandidates' => $recentCandidatesStmt->fetchAll(),
            'recentActivity' => $recentActivityStmt->fetchAll(),
        ], 'layouts/admin');
    }
}
