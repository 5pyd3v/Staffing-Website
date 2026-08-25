<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Job;

final class JobBoardController extends Controller
{
    public function index(Request $request): void
    {
        $filters = [
            'q' => $request->query('q', ''),
            'location' => $request->query('location', ''),
            'employment_type' => $request->query('employment_type', ''),
            'remote' => $request->query('remote', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $result = Job::publicSearch($filters, $page, 9);

        $this->view('public/jobs/index', [
            'title' => 'Find Work',
            'jobs' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['total_pages'],
            'filters' => $filters,
        ]);
    }

    public function show(Request $request): void
    {
        $slug = (string) $request->param('slug');
        $job = Job::findOpenBySlug($slug);
        if (!$job) {
            Response::abort(404, 'This job is no longer available.');
        }

        $alreadyApplied = false;
        if (Auth::check() && Auth::role() === 'candidate') {
            $candidate = \App\Models\Candidate::findBy('user_id', Auth::id());
            if ($candidate) {
                $stmt = Database::connection()->prepare(
                    'SELECT COUNT(*) FROM job_applications WHERE job_id = :job_id AND candidate_id = :candidate_id'
                );
                $stmt->execute(['job_id' => $job['id'], 'candidate_id' => $candidate['id']]);
                $alreadyApplied = (int) $stmt->fetchColumn() > 0;
            }
        }

        $this->view('public/jobs/show', [
            'title' => $job['title'] . ' at ' . $job['company_name'],
            'job' => $job,
            'alreadyApplied' => $alreadyApplied,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ]);
    }

    public function apply(Request $request): void
    {
        $this->verifyCsrf($request);
        $slug = (string) $request->param('slug');
        $job = Job::findOpenBySlug($slug);
        if (!$job) {
            Response::abort(404, 'This job is no longer available.');
        }

        if (!Auth::check()) {
            Session::set('apply_job_slug', $slug);
            Session::flash('error', 'Please join the talent pool to apply — it only takes a few minutes.');
            $this->redirect('/candidates/register');
        }

        if (Auth::role() !== 'candidate') {
            Session::flash('error', 'Only candidate accounts can apply to jobs.');
            $this->redirect("/jobs/{$slug}");
        }

        $candidate = \App\Models\Candidate::findBy('user_id', Auth::id());
        if (!$candidate) {
            Response::abort(404, 'Candidate profile not found.');
        }

        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM job_applications WHERE job_id = :job_id AND candidate_id = :candidate_id'
        );
        $stmt->execute(['job_id' => $job['id'], 'candidate_id' => $candidate['id']]);

        if ((int) $stmt->fetchColumn() > 0) {
            Session::flash('error', 'You have already applied to this job.');
            $this->redirect("/jobs/{$slug}");
        }

        $insert = Database::connection()->prepare(
            'INSERT INTO job_applications (uuid, job_id, candidate_id, cover_note, status)
             VALUES (:uuid, :job_id, :candidate_id, :cover_note, :status)'
        );
        $insert->execute([
            'uuid' => \App\Helpers\Uuid::v4(),
            'job_id' => $job['id'],
            'candidate_id' => $candidate['id'],
            'cover_note' => trim((string) $request->input('cover_note', '')) ?: null,
            'status' => 'applied',
        ]);

        \App\Services\NotificationService::queueAdminAlert(
            'admin_new_application',
            'New application: ' . $job['title'],
            \App\Services\NotificationService::render('admin_new_application', [
                'candidateName' => $candidate['first_name'] . ' ' . $candidate['last_name'],
                'jobTitle' => $job['title'],
                'jobUrl' => rtrim((string) (require ROOT_PATH . '/config/app.php')['url'], '/') . "/admin/jobs/{$job['id']}/edit",
            ]),
            'job_application',
            (int) $job['id']
        );

        Session::flash('success', 'Your application has been submitted. A member of our team will be in touch.');
        $this->redirect("/jobs/{$slug}");
    }
}
