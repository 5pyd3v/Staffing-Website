<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Uuid;
use App\Models\Company;
use App\Models\Industry;
use App\Models\Job;
use App\Models\Service;

final class JobController extends Controller
{
    private const STATUSES = ['draft', 'open', 'on_hold', 'filled', 'closed'];
    private const EMPLOYMENT_TYPES = ['full_time', 'part_time', 'contract', 'temp', 'temp_to_hire'];

    public function index(Request $request): void
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;

        $jobs = Job::listWithCompany($search, $status, $perPage, ($page - 1) * $perPage);
        $total = Job::countWithFilters($search, $status);

        $this->view('admin/jobs/index', [
            'title' => 'Jobs',
            'jobs' => $jobs,
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
        $this->form('Post a Job', null);
    }

    public function store(Request $request): void
    {
        $this->verifyCsrf($request);
        $validator = $this->validateJob($request);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::setOld($request->all());
            $this->redirect('/admin/jobs/create');
        }

        $title = trim((string) $request->input('title'));

        Job::create([
            'uuid' => Uuid::v4(),
            'company_id' => (int) $request->input('company_id'),
            'industry_id' => $request->input('industry_id') ?: null,
            'service_id' => $request->input('service_id') ?: null,
            'title' => $title,
            'slug' => Job::uniqueSlug($title),
            'employment_type' => $request->input('employment_type'),
            'location_city' => trim((string) $request->input('location_city', '')) ?: null,
            'location_state' => trim((string) $request->input('location_state', '')) ?: null,
            'is_remote' => $request->input('is_remote') ? 1 : 0,
            'salary_min' => $request->input('salary_min') ?: null,
            'salary_max' => $request->input('salary_max') ?: null,
            'salary_period' => $request->input('salary_period', 'year'),
            'description' => (string) $request->input('description'),
            'requirements' => trim((string) $request->input('requirements', '')) ?: null,
            'benefits' => trim((string) $request->input('benefits', '')) ?: null,
            'positions_available' => (int) $request->input('positions_available', 1),
            'status' => $request->input('status', 'draft'),
            'created_by' => \App\Core\Auth::id(),
            'published_at' => $request->input('status') === 'open' ? date('Y-m-d H:i:s') : null,
        ]);

        Session::flash('success', 'Job created.');
        $this->redirect('/admin/jobs');
    }

    public function edit(Request $request): void
    {
        $job = Job::find((int) $request->param('id'));
        if (!$job) {
            Response::abort(404, 'Job not found');
        }

        $this->form('Edit Job', $job);
    }

    public function update(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');
        $job = Job::find($id);
        if (!$job) {
            Response::abort(404, 'Job not found');
        }

        $validator = $this->validateJob($request);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::setOld($request->all());
            $this->redirect("/admin/jobs/{$id}/edit");
        }

        $title = trim((string) $request->input('title'));
        $data = [
            'company_id' => (int) $request->input('company_id'),
            'industry_id' => $request->input('industry_id') ?: null,
            'service_id' => $request->input('service_id') ?: null,
            'title' => $title,
            'employment_type' => $request->input('employment_type'),
            'location_city' => trim((string) $request->input('location_city', '')) ?: null,
            'location_state' => trim((string) $request->input('location_state', '')) ?: null,
            'is_remote' => $request->input('is_remote') ? 1 : 0,
            'salary_min' => $request->input('salary_min') ?: null,
            'salary_max' => $request->input('salary_max') ?: null,
            'salary_period' => $request->input('salary_period', 'year'),
            'description' => (string) $request->input('description'),
            'requirements' => trim((string) $request->input('requirements', '')) ?: null,
            'benefits' => trim((string) $request->input('benefits', '')) ?: null,
            'positions_available' => (int) $request->input('positions_available', 1),
            'status' => $request->input('status', 'draft'),
        ];

        if ($job['status'] !== 'open' && $data['status'] === 'open') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        Job::update($id, $data);

        Session::flash('success', 'Job updated.');
        $this->redirect('/admin/jobs');
    }

    public function destroy(Request $request): void
    {
        $this->verifyCsrf($request);
        Job::delete((int) $request->param('id'));
        Session::flash('success', 'Job deleted.');
        $this->redirect('/admin/jobs');
    }

    private function form(string $title, ?array $job): void
    {
        $this->view('admin/jobs/form', [
            'title' => $title,
            'job' => $job,
            'companies' => Company::all('name ASC'),
            'industries' => Industry::all('name ASC'),
            'services' => Service::allOrdered(),
            'statuses' => self::STATUSES,
            'employmentTypes' => self::EMPLOYMENT_TYPES,
            'errors' => Session::getFlash('errors', []),
        ], 'layouts/admin');
    }

    private function validateJob(Request $request): \App\Core\Validator
    {
        return $this->validate($request, [
            'company_id' => 'required|numeric',
            'title' => 'required|max:180',
            'employment_type' => 'required|in:' . implode(',', self::EMPLOYMENT_TYPES),
            'status' => 'required|in:' . implode(',', self::STATUSES),
            'description' => 'required',
            'positions_available' => 'numeric',
            'salary_min' => 'numeric',
            'salary_max' => 'numeric',
        ]);
    }
}
