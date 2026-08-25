<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Uuid;
use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\EmployerActivity;
use App\Models\Industry;
use App\Models\Job;
use App\Models\StaffingRequest;

final class CompanyController extends Controller
{
    private const STATUSES = ['lead', 'active', 'inactive'];
    private const ACTIVITY_TYPES = ['note', 'call', 'email', 'meeting', 'status_change'];

    public function index(Request $request): void
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;

        $companies = Company::listWithIndustry($search, $status, $perPage, ($page - 1) * $perPage);
        $total = Company::countWithFilters($search, $status);

        $this->view('admin/companies/index', [
            'title' => 'Employers',
            'companies' => $companies,
            'search' => $search,
            'status' => $status,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'statuses' => self::STATUSES,
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/companies/form', [
            'title' => 'Add Employer',
            'company' => null,
            'industries' => Industry::all('name ASC'),
            'statuses' => self::STATUSES,
            'errors' => Session::getFlash('errors', []),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $this->verifyCsrf($request);

        $validator = $this->validate($request, [
            'name' => 'required|max:150',
            'contact_name' => 'required|max:150',
            'contact_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/admin/companies/create', $validator, $request->only(['name', 'website', 'contact_name', 'contact_email', 'contact_phone']));
        }

        $name = trim((string) $request->input('name'));

        $companyId = Company::create([
            'uuid' => Uuid::v4(),
            'name' => $name,
            'slug' => Company::uniqueSlug($name),
            'industry_id' => $request->input('industry_id') ?: null,
            'website' => trim((string) $request->input('website', '')) ?: null,
            'size_range' => $request->input('size_range') ?: null,
            'description' => trim((string) $request->input('description', '')) ?: null,
            'headquarters_city' => trim((string) $request->input('headquarters_city', '')) ?: null,
            'headquarters_state' => trim((string) $request->input('headquarters_state', '')) ?: null,
            'status' => $request->input('status', 'lead'),
            'owner_admin_id' => Auth::id(),
        ]);

        CompanyContact::create([
            'company_id' => (int) $companyId,
            'full_name' => trim((string) $request->input('contact_name')),
            'email' => trim((string) $request->input('contact_email')),
            'phone' => trim((string) $request->input('contact_phone', '')) ?: null,
            'job_title' => trim((string) $request->input('contact_title', '')) ?: null,
            'is_primary' => 1,
        ]);

        EmployerActivity::create([
            'company_id' => (int) $companyId,
            'created_by' => Auth::id(),
            'activity_type' => 'note',
            'subject' => 'Employer record created',
            'body' => 'Added to TalentBridge as a new employer record.',
            'occurred_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', 'Employer added.');
        $this->redirect('/admin/companies');
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $company = Company::findWithIndustry($id);
        if (!$company) {
            Response::abort(404, 'Employer not found');
        }

        $this->view('admin/companies/show', [
            'title' => $company['name'],
            'company' => $company,
            'contacts' => CompanyContact::forCompany($id),
            'activities' => EmployerActivity::timelineForCompany($id),
            'jobs' => Job::forCompany($id),
            'staffingRequests' => StaffingRequest::forCompany($id),
            'activityTypes' => self::ACTIVITY_TYPES,
            'success' => Session::getFlash('success'),
            'errors' => Session::getFlash('errors', []),
        ], 'layouts/admin');
    }

    public function edit(Request $request): void
    {
        $company = Company::find((int) $request->param('id'));
        if (!$company) {
            Response::abort(404, 'Employer not found');
        }

        $this->view('admin/companies/form', [
            'title' => 'Edit Employer',
            'company' => $company,
            'industries' => Industry::all('name ASC'),
            'statuses' => self::STATUSES,
            'errors' => Session::getFlash('errors', []),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');
        $company = Company::find($id);
        if (!$company) {
            Response::abort(404, 'Employer not found');
        }

        $validator = $this->validate($request, [
            'name' => 'required|max:150',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors("/admin/companies/{$id}/edit", $validator, $request->only(['name', 'website']));
        }

        $name = trim((string) $request->input('name'));
        $data = [
            'name' => $name,
            'industry_id' => $request->input('industry_id') ?: null,
            'website' => trim((string) $request->input('website', '')) ?: null,
            'size_range' => $request->input('size_range') ?: null,
            'description' => trim((string) $request->input('description', '')) ?: null,
            'headquarters_city' => trim((string) $request->input('headquarters_city', '')) ?: null,
            'headquarters_state' => trim((string) $request->input('headquarters_state', '')) ?: null,
            'status' => $request->input('status', 'lead'),
        ];

        if (strcasecmp($name, $company['name']) !== 0) {
            $data['slug'] = Company::uniqueSlug($name);
        }

        if ($company['status'] !== $data['status']) {
            EmployerActivity::create([
                'company_id' => $id,
                'created_by' => Auth::id(),
                'activity_type' => 'status_change',
                'subject' => 'Status changed',
                'body' => ucfirst($company['status']) . ' → ' . ucfirst($data['status']),
                'occurred_at' => date('Y-m-d H:i:s'),
            ]);
        }

        Company::update($id, $data);

        Session::flash('success', 'Employer updated.');
        $this->redirect("/admin/companies/{$id}");
    }

    public function destroy(Request $request): void
    {
        $this->verifyCsrf($request);
        Company::delete((int) $request->param('id'));
        Session::flash('success', 'Employer archived.');
        $this->redirect('/admin/companies');
    }

    public function storeActivity(Request $request): void
    {
        $this->verifyCsrf($request);
        $companyId = (int) $request->param('id');

        if (!Company::find($companyId)) {
            Response::abort(404, 'Employer not found');
        }

        $validator = $this->validate($request, [
            'activity_type' => 'required|in:' . implode(',', self::ACTIVITY_TYPES),
            'subject' => 'required|max:200',
            'body' => 'max:2000',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect("/admin/companies/{$companyId}");
        }

        EmployerActivity::create([
            'company_id' => $companyId,
            'created_by' => Auth::id(),
            'activity_type' => $request->input('activity_type'),
            'subject' => trim((string) $request->input('subject')),
            'body' => trim((string) $request->input('body', '')) ?: null,
            'occurred_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', 'Activity logged.');
        $this->redirect("/admin/companies/{$companyId}");
    }
}
