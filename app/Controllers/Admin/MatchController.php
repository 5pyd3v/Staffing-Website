<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Uuid;
use App\Models\Candidate;
use App\Models\EmployerActivity;
use App\Models\MatchRecord;
use App\Models\StaffingRequest;
use App\Services\MatchingService;
use App\Services\NotificationService;

final class MatchController extends Controller
{
    private const STATUSES = ['proposed', 'presented_to_employer', 'interviewing', 'hired', 'rejected'];
    private const MATCH_TYPES = ['strong_match', 'needs_review', 'rejected'];

    public function index(Request $request): void
    {
        $status = (string) $request->query('status', '');
        $matchType = (string) $request->query('match_type', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;

        $matches = MatchRecord::listWithDetails($status, $matchType, $perPage, ($page - 1) * $perPage);
        $total = MatchRecord::countWithFilters($status, $matchType);

        $this->view('admin/matches/index', [
            'title' => 'Matching',
            'matches' => $matches,
            'status' => $status,
            'matchType' => $matchType,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'statuses' => self::STATUSES,
            'matchTypes' => self::MATCH_TYPES,
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    /**
     * Runs MatchingService against every active candidate for one staffing
     * request and displays the ranked results — nothing is written to the
     * `matches` table until the admin explicitly saves a candidate below.
     */
    public function forRequest(Request $request): void
    {
        $requestId = (int) $request->param('id');
        $staffingRequest = StaffingRequest::find($requestId);
        if (!$staffingRequest) {
            Response::abort(404, 'Staffing request not found');
        }

        $ranked = MatchingService::rankCandidatesForRequest($staffingRequest);
        $savedCandidateIds = array_column(MatchRecord::forStaffingRequest($requestId), 'candidate_id');

        $this->view('admin/matches/for_request', [
            'title' => 'Matches for ' . $staffingRequest['role_title'],
            'req' => $staffingRequest,
            'ranked' => $ranked,
            'savedCandidateIds' => array_map('intval', $savedCandidateIds),
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    public function saveForRequest(Request $request): void
    {
        $this->verifyCsrf($request);
        $requestId = (int) $request->param('id');
        $candidateId = (int) $request->input('candidate_id');

        $staffingRequest = StaffingRequest::find($requestId);
        $candidate = Candidate::find($candidateId);
        if (!$staffingRequest || !$candidate) {
            Response::abort(404, 'Not found');
        }

        if (MatchRecord::existsFor($candidateId, $requestId, null)) {
            Session::flash('error', 'This candidate is already matched to this request.');
            $this->redirect("/admin/staffing-requests/{$requestId}/matches");
        }

        $result = MatchingService::score($staffingRequest, array_merge($candidate, [
            'skill_names' => implode('|', array_column(Candidate::skillsFor($candidateId), 'name')),
        ]));

        MatchRecord::create([
            'uuid' => Uuid::v4(),
            'staffing_request_id' => $requestId,
            'job_id' => null,
            'candidate_id' => $candidateId,
            'match_type' => $result['match_type'],
            'score' => $result['score'],
            'status' => 'proposed',
            'matched_by' => Auth::id(),
        ]);

        if ($staffingRequest['company_id']) {
            EmployerActivity::create([
                'company_id' => (int) $staffingRequest['company_id'],
                'staffing_request_id' => $requestId,
                'created_by' => Auth::id(),
                'activity_type' => 'note',
                'subject' => 'Candidate matched: ' . $candidate['first_name'] . ' ' . $candidate['last_name'],
                'body' => 'Match score: ' . $result['score'] . '/100 (' . str_replace('_', ' ', $result['match_type']) . ')',
                'occurred_at' => date('Y-m-d H:i:s'),
            ]);
        }

        NotificationService::queueUserNotification(
            (int) $candidate['user_id'],
            'candidate_matched',
            "You've been matched to a new opportunity",
            NotificationService::render('candidate_matched', [
                'candidateName' => $candidate['first_name'],
                'roleTitle' => $staffingRequest['role_title'],
            ])
        );

        Session::flash('success', 'Match saved and candidate notified.');
        $this->redirect("/admin/staffing-requests/{$requestId}/matches");
    }

    public function updateStatus(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');
        $match = MatchRecord::find($id);
        if (!$match) {
            Response::abort(404, 'Match not found');
        }

        $status = (string) $request->input('status');
        if (!in_array($status, self::STATUSES, true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect('/admin/matches');
        }

        MatchRecord::update($id, ['status' => $status]);

        Session::flash('success', 'Match status updated.');
        $this->redirect('/admin/matches');
    }
}
