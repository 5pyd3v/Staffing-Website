<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Uuid;
use App\Models\Candidate;
use App\Models\FileRecord;
use App\Models\Job;
use App\Models\Role;
use App\Models\Skill;
use App\Models\User;
use App\Services\FileUploadService;

/**
 * Anonymous multi-step candidate onboarding wizard. Progress is held in the
 * session (`candidate_draft`) until the final review step, when the user +
 * candidate rows are created in one transaction — nothing touches the
 * database until the candidate actually finishes. See ARCHITECTURE.md for
 * why this is session-backed rather than a persisted draft table.
 */
final class CandidateRegistrationController extends Controller
{
    private const TOTAL_STEPS = 6;
    private const DRAFT_KEY = 'candidate_draft';

    public function stepAccount(Request $request): void
    {
        $this->renderStep(1, 'public/candidates/register/step1_account', []);
    }

    public function storeAccount(Request $request): void
    {
        $this->verifyCsrf($request);

        $validator = $this->validate($request, [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/candidates/register', $validator, ['email' => $request->input('email')]);
        }

        $draft = $this->draft();
        $draft['email'] = mb_strtolower(trim((string) $request->input('email')));
        $draft['password_hash'] = password_hash((string) $request->input('password'), PASSWORD_BCRYPT, ['cost' => 12]);
        $draft['step'] = max($draft['step'] ?? 1, 2);
        $this->saveDraft($draft);

        $this->redirect('/candidates/register/profile');
    }

    public function stepProfile(Request $request): void
    {
        $this->renderStep(2, 'public/candidates/register/step2_profile', []);
    }

    public function storeProfile(Request $request): void
    {
        $this->verifyCsrf($request);
        $this->requireStep(2);

        $validator = $this->validate($request, [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'phone' => 'max:30',
            'location_city' => 'max:100',
            'location_state' => 'max:100',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/candidates/register/profile', $validator, $request->only(['first_name', 'last_name', 'phone', 'location_city', 'location_state']));
        }

        $draft = $this->draft();
        $draft['first_name'] = trim((string) $request->input('first_name'));
        $draft['last_name'] = trim((string) $request->input('last_name'));
        $draft['phone'] = trim((string) $request->input('phone', '')) ?: null;
        $draft['location_city'] = trim((string) $request->input('location_city', '')) ?: null;
        $draft['location_state'] = trim((string) $request->input('location_state', '')) ?: null;
        $draft['location_country'] = trim((string) $request->input('location_country', 'United States')) ?: 'United States';
        $draft['step'] = max($draft['step'] ?? 2, 3);
        $this->saveDraft($draft);

        $this->redirect('/candidates/register/professional');
    }

    public function stepProfessional(Request $request): void
    {
        $this->renderStep(3, 'public/candidates/register/step3_professional', []);
    }

    public function storeProfessional(Request $request): void
    {
        $this->verifyCsrf($request);
        $this->requireStep(3);

        $validator = $this->validate($request, [
            'headline' => 'max:180',
            'current_title' => 'max:150',
            'experience_years' => 'numeric',
            'availability' => 'required|in:immediate,2_weeks,1_month,not_looking',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/candidates/register/professional', $validator, $request->only(['headline', 'current_title', 'experience_years', 'availability']));
        }

        $employmentTypes = (array) $request->input('employment_types', []);
        $allowedTypes = ['full_time', 'part_time', 'contract', 'temp', 'temp_to_hire'];
        $employmentTypes = array_values(array_intersect($employmentTypes, $allowedTypes));
        if ($employmentTypes === []) {
            $employmentTypes = ['full_time'];
        }

        $draft = $this->draft();
        $draft['headline'] = trim((string) $request->input('headline', '')) ?: null;
        $draft['current_title'] = trim((string) $request->input('current_title', '')) ?: null;
        $draft['experience_years'] = $request->input('experience_years') !== '' ? (float) $request->input('experience_years') : null;
        $draft['availability'] = $request->input('availability');
        $draft['employment_types'] = $employmentTypes;
        $draft['salary_expectation_min'] = $request->input('salary_expectation_min') ?: null;
        $draft['salary_expectation_max'] = $request->input('salary_expectation_max') ?: null;
        $draft['is_remote_ok'] = $request->input('is_remote_ok') ? true : false;
        $draft['summary'] = trim((string) $request->input('summary', '')) ?: null;
        $draft['step'] = max($draft['step'] ?? 3, 4);
        $this->saveDraft($draft);

        $this->redirect('/candidates/register/skills');
    }

    public function stepSkills(Request $request): void
    {
        $this->renderStep(4, 'public/candidates/register/step4_skills', []);
    }

    public function storeSkills(Request $request): void
    {
        $this->verifyCsrf($request);
        $this->requireStep(4);

        $skillsRaw = trim((string) $request->input('skills', ''));
        $skills = array_values(array_filter(array_map('trim', explode(',', $skillsRaw))));
        $skills = array_slice(array_unique($skills), 0, 25);

        $draft = $this->draft();
        $draft['skills'] = $skills;
        $draft['linkedin_url'] = trim((string) $request->input('linkedin_url', '')) ?: null;
        $draft['portfolio_url'] = trim((string) $request->input('portfolio_url', '')) ?: null;
        $draft['step'] = max($draft['step'] ?? 4, 5);
        $this->saveDraft($draft);

        $this->redirect('/candidates/register/resume');
    }

    public function stepResume(Request $request): void
    {
        $this->renderStep(5, 'public/candidates/register/step5_resume', []);
    }

    public function storeResume(Request $request): void
    {
        $this->verifyCsrf($request);
        $this->requireStep(5);

        $draft = $this->draft();

        $file = $request->file('resume');
        if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            $result = FileUploadService::store($file, 'resume');
            if (!$result['ok']) {
                Session::flash('error', $result['error']);
                $this->redirect('/candidates/register/resume');
            }
            $draft['resume_file_id'] = (int) $result['file']['id'];
            $draft['resume_file_name'] = $result['file']['original_name'];
        }

        $draft['step'] = max($draft['step'] ?? 5, 6);
        $this->saveDraft($draft);

        $this->redirect('/candidates/register/review');
    }

    public function stepReview(Request $request): void
    {
        $this->renderStep(6, 'public/candidates/register/step6_review', []);
    }

    public function submit(Request $request): void
    {
        $this->verifyCsrf($request);
        $this->requireStep(6);

        $draft = $this->draft();

        if (empty($draft['email']) || empty($draft['password_hash']) || empty($draft['first_name'])) {
            Session::flash('error', 'Your session expired before you finished. Please start again.');
            Session::remove(self::DRAFT_KEY);
            $this->redirect('/candidates/register');
        }

        // Re-check uniqueness at the finish line in case of a race/duplicate tab.
        if (User::findByEmail($draft['email']) !== null) {
            Session::flash('error', 'That email address was registered while you were completing this form. Please sign in instead.');
            $this->redirect('/login');
        }

        $userId = User::create([
            'uuid' => Uuid::v4(),
            'email' => $draft['email'],
            'password_hash' => $draft['password_hash'],
            'role_id' => Role::idForSlug('candidate'),
            'status' => 'active',
            'email_verified_at' => null,
        ]);

        $candidateId = Candidate::create([
            'uuid' => Uuid::v4(),
            'user_id' => $userId,
            'first_name' => $draft['first_name'],
            'last_name' => $draft['last_name'],
            'phone' => $draft['phone'] ?? null,
            'location_city' => $draft['location_city'] ?? null,
            'location_state' => $draft['location_state'] ?? null,
            'location_country' => $draft['location_country'] ?? 'United States',
            'headline' => $draft['headline'] ?? null,
            'summary' => $draft['summary'] ?? null,
            'current_title' => $draft['current_title'] ?? null,
            'experience_years' => $draft['experience_years'] ?? null,
            'availability' => $draft['availability'] ?? 'immediate',
            'employment_types' => implode(',', $draft['employment_types'] ?? ['full_time']),
            'salary_expectation_min' => $draft['salary_expectation_min'] ?? null,
            'salary_expectation_max' => $draft['salary_expectation_max'] ?? null,
            'is_remote_ok' => !empty($draft['is_remote_ok']) ? 1 : 0,
            'resume_file_id' => $draft['resume_file_id'] ?? null,
            'linkedin_url' => $draft['linkedin_url'] ?? null,
            'portfolio_url' => $draft['portfolio_url'] ?? null,
            'status' => 'new',
            'source' => 'self_registered',
        ]);

        if (!empty($draft['resume_file_id'])) {
            FileRecord::attach((int) $draft['resume_file_id'], 'candidate_resume', (int) $candidateId);
        }

        if (!empty($draft['skills'])) {
            $skillIds = [];
            foreach ($draft['skills'] as $skillName) {
                $skill = Skill::findOrCreateByName($skillName);
                $skillIds[] = (int) $skill['id'];
            }
            Candidate::replaceSkills((int) $candidateId, $skillIds);
        }

        \App\Services\NotificationService::queueAdminAlert(
            'admin_new_candidate',
            'New candidate: ' . $draft['first_name'] . ' ' . $draft['last_name'],
            \App\Services\NotificationService::render('admin_new_candidate', [
                'candidateName' => $draft['first_name'] . ' ' . $draft['last_name'],
                'headline' => $draft['headline'] ?? '',
                'profileUrl' => rtrim((string) (require ROOT_PATH . '/config/app.php')['url'], '/') . "/admin/candidates/{$candidateId}",
            ]),
            'candidate',
            (int) $candidateId
        );

        Auth::login(['id' => $userId, 'role_slug' => 'candidate']);

        // If they arrived here via "Apply" on a job post, complete that application now.
        $applySlug = Session::get('apply_job_slug');
        if ($applySlug) {
            $job = Job::findOpenBySlug($applySlug);
            if ($job) {
                $insert = \App\Core\Database::connection()->prepare(
                    'INSERT IGNORE INTO job_applications (uuid, job_id, candidate_id, status) VALUES (:uuid, :job_id, :candidate_id, :status)'
                );
                $insert->execute(['uuid' => Uuid::v4(), 'job_id' => $job['id'], 'candidate_id' => $candidateId, 'status' => 'applied']);
            }
            Session::remove('apply_job_slug');
        }

        Session::remove(self::DRAFT_KEY);
        Session::flash('welcome', true);
        $this->redirect('/candidates/register/complete');
    }

    public function complete(Request $request): void
    {
        if (!Session::getFlash('welcome')) {
            $this->redirect('/');
        }

        $this->view('public/candidates/register/complete', ['title' => 'Welcome to TalentBridge']);
    }

    private const STEP_LABELS = ['Account', 'Profile', 'Professional', 'Skills', 'Resume', 'Review'];

    private function renderStep(int $step, string $view, array $extra): void
    {
        $this->requireStep($step);

        $this->view($view, array_merge([
            'title' => 'Join the Talent Pool',
            'step' => $step,
            'totalSteps' => self::TOTAL_STEPS,
            'stepLabels' => self::STEP_LABELS,
            'panelImage' => '/assets/img/stock/warehouse.jpg',
            'panelHeadline' => "Join the talent pool employers actually search first.",
            'draft' => $this->draft(),
            'errors' => Session::getFlash('errors', []),
        ], $extra), 'layouts/wizard');
    }

    /**
     * A step may only be visited once every prior step has been completed
     * (draft['step'] tracks the furthest step reached); anything further
     * ahead bounces back to wherever the user actually left off.
     */
    private function requireStep(int $step): void
    {
        $furthest = $this->draft()['step'] ?? 1;
        if ($step > $furthest) {
            $paths = [
                1 => '/candidates/register',
                2 => '/candidates/register/profile',
                3 => '/candidates/register/professional',
                4 => '/candidates/register/skills',
                5 => '/candidates/register/resume',
                6 => '/candidates/register/review',
            ];
            Response::redirect($paths[$furthest] ?? '/candidates/register');
        }
    }

    private function draft(): array
    {
        return Session::get(self::DRAFT_KEY, ['step' => 1]);
    }

    private function saveDraft(array $draft): void
    {
        Session::set(self::DRAFT_KEY, $draft);
    }
}
