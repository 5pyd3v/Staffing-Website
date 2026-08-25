<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Uuid;
use App\Models\Industry;
use App\Models\Service;
use App\Models\StaffingRequest;

/**
 * Anonymous multi-step "Hire Talent" intake form. Same session-draft pattern
 * as candidate registration (see CandidateRegistrationController), but no
 * account is created — this maps directly onto `staffing_requests`, which
 * is designed to capture a brand-new employer inline (see DATABASE.md).
 */
final class HireTalentController extends Controller
{
    private const TOTAL_STEPS = 4;
    private const DRAFT_KEY = 'hire_talent_draft';

    public function stepCompany(Request $request): void
    {
        $this->renderStep(1, 'public/hire_talent/step1_company', [
            'industries' => Industry::all('name ASC'),
        ]);
    }

    public function storeCompany(Request $request): void
    {
        $this->verifyCsrf($request);

        $validator = $this->validate($request, [
            'company_name' => 'required|max:150',
            'contact_name' => 'required|max:150',
            'contact_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/hire-talent', $validator, $request->only(['company_name', 'contact_name', 'contact_email', 'contact_phone']));
        }

        $draft = $this->draft();
        $draft['company_name'] = trim((string) $request->input('company_name'));
        $draft['contact_name'] = trim((string) $request->input('contact_name'));
        $draft['contact_email'] = mb_strtolower(trim((string) $request->input('contact_email')));
        $draft['contact_phone'] = trim((string) $request->input('contact_phone', '')) ?: null;
        $draft['industry_id'] = $request->input('industry_id') ?: null;
        $draft['step'] = max($draft['step'] ?? 1, 2);
        $this->saveDraft($draft);

        $this->redirect('/hire-talent/role');
    }

    public function stepRole(Request $request): void
    {
        $this->renderStep(2, 'public/hire_talent/step2_role', [
            'services' => Service::allOrdered(),
        ]);
    }

    public function storeRole(Request $request): void
    {
        $this->verifyCsrf($request);
        $this->requireStep(2);

        $validator = $this->validate($request, [
            'role_title' => 'required|max:180',
            'employment_type' => 'required|in:full_time,part_time,contract,temp,temp_to_hire',
            'positions_needed' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/hire-talent/role', $validator, $request->only(['role_title', 'employment_type', 'positions_needed']));
        }

        $draft = $this->draft();
        $draft['role_title'] = trim((string) $request->input('role_title'));
        $draft['employment_type'] = $request->input('employment_type');
        $draft['positions_needed'] = (int) $request->input('positions_needed');
        $draft['service_id'] = $request->input('service_id') ?: null;
        $draft['location_city'] = trim((string) $request->input('location_city', '')) ?: null;
        $draft['location_state'] = trim((string) $request->input('location_state', '')) ?: null;
        $draft['is_remote_ok'] = $request->input('is_remote_ok') ? true : false;
        $draft['start_date_needed'] = $request->input('start_date_needed') ?: null;
        $draft['step'] = max($draft['step'] ?? 2, 3);
        $this->saveDraft($draft);

        $this->redirect('/hire-talent/requirements');
    }

    public function stepRequirements(Request $request): void
    {
        $this->renderStep(3, 'public/hire_talent/step3_requirements', []);
    }

    public function storeRequirements(Request $request): void
    {
        $this->verifyCsrf($request);
        $this->requireStep(3);

        $draft = $this->draft();
        $draft['must_have_skills'] = trim((string) $request->input('must_have_skills', '')) ?: null;
        $draft['nice_to_have_skills'] = trim((string) $request->input('nice_to_have_skills', '')) ?: null;
        $draft['budget_min'] = $request->input('budget_min') ?: null;
        $draft['budget_max'] = $request->input('budget_max') ?: null;
        $draft['additional_notes'] = trim((string) $request->input('additional_notes', '')) ?: null;
        $draft['step'] = max($draft['step'] ?? 3, 4);
        $this->saveDraft($draft);

        $this->redirect('/hire-talent/review');
    }

    public function stepReview(Request $request): void
    {
        $this->renderStep(4, 'public/hire_talent/step4_review', []);
    }

    public function submit(Request $request): void
    {
        $this->verifyCsrf($request);
        $this->requireStep(4);

        $draft = $this->draft();

        if (empty($draft['contact_email']) || empty($draft['role_title'])) {
            Session::flash('error', 'Your session expired before you finished. Please start again.');
            Session::remove(self::DRAFT_KEY);
            $this->redirect('/hire-talent');
        }

        $requestId = StaffingRequest::create([
            'uuid' => Uuid::v4(),
            'company_id' => null,
            'contact_name' => $draft['contact_name'],
            'contact_email' => $draft['contact_email'],
            'contact_phone' => $draft['contact_phone'] ?? null,
            'role_title' => $draft['role_title'] . ' (' . $draft['company_name'] . ')',
            'industry_id' => $draft['industry_id'] ?? null,
            'service_id' => $draft['service_id'] ?? null,
            'employment_type' => $draft['employment_type'],
            'positions_needed' => $draft['positions_needed'] ?? 1,
            'budget_min' => $draft['budget_min'] ?? null,
            'budget_max' => $draft['budget_max'] ?? null,
            'must_have_skills' => $draft['must_have_skills'] ?? null,
            'nice_to_have_skills' => $draft['nice_to_have_skills'] ?? null,
            'location_city' => $draft['location_city'] ?? null,
            'location_state' => $draft['location_state'] ?? null,
            'is_remote_ok' => !empty($draft['is_remote_ok']) ? 1 : 0,
            'start_date_needed' => $draft['start_date_needed'] ?? null,
            'additional_notes' => $draft['additional_notes'] ?? null,
            'status' => 'new',
        ]);

        \App\Services\NotificationService::queueAdminAlert(
            'admin_new_staffing_request',
            'New staffing request: ' . $draft['role_title'],
            \App\Services\NotificationService::render('admin_new_staffing_request', [
                'roleTitle' => $draft['role_title'],
                'companyName' => $draft['company_name'],
                'contactName' => $draft['contact_name'],
                'contactEmail' => $draft['contact_email'],
                'requestUrl' => rtrim((string) (require ROOT_PATH . '/config/app.php')['url'], '/') . "/admin/staffing-requests/{$requestId}",
            ]),
            'staffing_request',
            (int) $requestId
        );

        Session::remove(self::DRAFT_KEY);
        Session::flash('welcome', true);
        $this->redirect('/hire-talent/complete');
    }

    public function complete(Request $request): void
    {
        if (!Session::getFlash('welcome')) {
            $this->redirect('/');
        }

        $this->view('public/hire_talent/complete', ['title' => 'Request Received']);
    }

    private const STEP_LABELS = ['Company', 'Role', 'Requirements', 'Review'];

    private function renderStep(int $step, string $view, array $extra): void
    {
        $this->requireStep($step);

        $this->view($view, array_merge([
            'title' => 'Hire Talent',
            'step' => $step,
            'totalSteps' => self::TOTAL_STEPS,
            'stepLabels' => self::STEP_LABELS,
            'panelImage' => '/assets/img/stock/office-collab.jpg',
            'panelHeadline' => "Tell us who you need. We'll find them fast.",
            'draft' => $this->draft(),
            'errors' => Session::getFlash('errors', []),
        ], $extra), 'layouts/wizard');
    }

    private function requireStep(int $step): void
    {
        $furthest = $this->draft()['step'] ?? 1;
        if ($step > $furthest) {
            $paths = [
                1 => '/hire-talent',
                2 => '/hire-talent/role',
                3 => '/hire-talent/requirements',
                4 => '/hire-talent/review',
            ];
            Response::redirect($paths[$furthest] ?? '/hire-talent');
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
