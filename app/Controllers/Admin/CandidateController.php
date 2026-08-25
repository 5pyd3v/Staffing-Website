<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Candidate;
use App\Models\FileRecord;
use App\Models\Skill;

final class CandidateController extends Controller
{
    private const STATUSES = ['new', 'in_review', 'shortlisted', 'placed', 'inactive'];

    public function index(Request $request): void
    {
        $this->view('admin/candidates/index', [
            'title' => 'Candidates',
            'skills' => Skill::all('name ASC'),
            'statuses' => self::STATUSES,
        ], 'layouts/admin');
    }

    /**
     * AJAX search endpoint backing the candidate filter UI. Always returns
     * JSON — this is the only place in Phase 3 that speaks JSON instead of
     * rendering a view, by design (see ARCHITECTURE.md).
     */
    public function search(Request $request): void
    {
        $filters = [
            'q' => $request->query('q', ''),
            'status' => $request->query('status', ''),
            'availability' => $request->query('availability', ''),
            'location' => $request->query('location', ''),
            'min_experience' => $request->query('min_experience', ''),
            'max_experience' => $request->query('max_experience', ''),
            'remote_ok' => $request->query('remote_ok', ''),
            'skill_id' => $request->query('skill_id', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $result = Candidate::search($filters, $page, 12);

        $rows = array_map(static function (array $c): array {
            return [
                'id' => (int) $c['id'],
                'uuid' => $c['uuid'],
                'name' => trim($c['first_name'] . ' ' . $c['last_name']),
                'headline' => $c['headline'],
                'current_title' => $c['current_title'],
                'location' => trim(($c['location_city'] ?? '') . (isset($c['location_state']) && $c['location_state'] ? ', ' . $c['location_state'] : '')),
                'experience_years' => $c['experience_years'],
                'availability' => $c['availability'],
                'status' => $c['status'],
                'is_remote_ok' => (bool) $c['is_remote_ok'],
                'skills' => $c['skill_names'] ? explode(', ', $c['skill_names']) : [],
                'email' => $c['email'],
                'profile_url' => "/admin/candidates/{$c['id']}",
            ];
        }, $result['rows']);

        $this->json([
            'data' => $rows,
            'meta' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total_pages' => $result['total_pages'],
            ],
        ]);
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $candidate = Candidate::findWithUser($id);
        if (!$candidate) {
            Response::abort(404, 'Candidate not found');
        }

        $this->view('admin/candidates/show', [
            'title' => trim($candidate['first_name'] . ' ' . $candidate['last_name']),
            'candidate' => $candidate,
            'skills' => Candidate::skillsFor($id),
            'experience' => Candidate::experienceFor($id),
            'education' => Candidate::educationFor($id),
            'statuses' => self::STATUSES,
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    public function updateStatus(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');

        $candidate = Candidate::find($id);
        if (!$candidate) {
            Response::abort(404, 'Candidate not found');
        }

        $status = (string) $request->input('status');
        if (!in_array($status, self::STATUSES, true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect("/admin/candidates/{$id}");
        }

        Candidate::update($id, ['status' => $status]);

        if ($status !== $candidate['status'] && in_array($status, ['shortlisted', 'placed'], true)) {
            $statusLabels = [
                'shortlisted' => "You've been shortlisted!",
                'placed' => "Congratulations — you've been placed!",
            ];
            $messages = [
                'shortlisted' => 'A recruiter has shortlisted your profile for an active opportunity and will follow up soon.',
                'placed' => 'We are thrilled to let you know you have been placed. Welcome aboard!',
            ];

            \App\Services\NotificationService::queueUserNotification(
                (int) $candidate['user_id'],
                'candidate_status_update',
                $statusLabels[$status],
                \App\Services\NotificationService::render('candidate_status_update', [
                    'candidateName' => $candidate['first_name'],
                    'statusLabel' => $statusLabels[$status],
                    'message' => $messages[$status],
                ]),
                'candidate',
                $id
            );
        }

        Session::flash('success', 'Candidate status updated.');
        $this->redirect("/admin/candidates/{$id}");
    }

    public function updateNotes(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');

        if (!Candidate::find($id)) {
            Response::abort(404, 'Candidate not found');
        }

        Candidate::update($id, ['admin_notes' => trim((string) $request->input('admin_notes', '')) ?: null]);

        Session::flash('success', 'Notes saved.');
        $this->redirect("/admin/candidates/{$id}");
    }

    public function downloadResume(Request $request): void
    {
        $id = (int) $request->param('id');
        $candidate = Candidate::find($id);
        if (!$candidate || empty($candidate['resume_file_id'])) {
            Response::abort(404, 'No resume on file for this candidate.');
        }

        $file = FileRecord::find((int) $candidate['resume_file_id']);
        // Belt-and-suspenders: confirm the file record actually belongs to
        // this candidate rather than trusting the candidate row's pointer
        // alone, since file IDs are sequential and guessable.
        if (!$file || $file['entity_type'] !== 'candidate_resume' || (int) $file['entity_id'] !== $id) {
            Response::abort(404, 'Resume file not found.');
        }

        if (!is_file($file['disk_path'])) {
            Response::abort(404, 'Resume file is missing from storage.');
        }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . addslashes(basename($file['original_name'])) . '"');
        header('Content-Length: ' . (string) filesize($file['disk_path']));
        header('X-Content-Type-Options: nosniff');
        readfile($file['disk_path']);
        exit;
    }
}
