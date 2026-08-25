<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Company;
use App\Models\EmployerActivity;
use App\Models\StaffingRequest;

final class StaffingRequestController extends Controller
{
    private const STATUSES = ['new', 'contacted', 'qualified', 'in_progress', 'matched', 'closed_won', 'closed_lost'];

    public function index(Request $request): void
    {
        $status = (string) $request->query('status', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;

        $requests = StaffingRequest::listWithCompany($status, $perPage, ($page - 1) * $perPage);
        $total = StaffingRequest::countWithStatus($status);

        $this->view('admin/staffing_requests/index', [
            'title' => 'Staffing Requests',
            'requests' => $requests,
            'status' => $status,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'statuses' => self::STATUSES,
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $staffingRequest = StaffingRequest::findWithCompany($id);
        if (!$staffingRequest) {
            Response::abort(404, 'Staffing request not found');
        }

        $this->view('admin/staffing_requests/show', [
            'title' => $staffingRequest['role_title'],
            'req' => $staffingRequest,
            'statuses' => self::STATUSES,
            'matches' => \App\Models\MatchRecord::forStaffingRequest($id),
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    public function updateStatus(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');
        $staffingRequest = StaffingRequest::find($id);
        if (!$staffingRequest) {
            Response::abort(404, 'Staffing request not found');
        }

        $status = (string) $request->input('status');
        if (!in_array($status, self::STATUSES, true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect("/admin/staffing-requests/{$id}");
        }

        StaffingRequest::update($id, ['status' => $status]);

        if ($staffingRequest['company_id']) {
            EmployerActivity::create([
                'company_id' => (int) $staffingRequest['company_id'],
                'staffing_request_id' => $id,
                'created_by' => Auth::id(),
                'activity_type' => 'status_change',
                'subject' => 'Staffing request status changed: ' . $staffingRequest['role_title'],
                'body' => ucfirst(str_replace('_', ' ', $staffingRequest['status'])) . ' → ' . ucfirst(str_replace('_', ' ', $status)),
                'occurred_at' => date('Y-m-d H:i:s'),
            ]);
        }

        Session::flash('success', 'Status updated.');
        $this->redirect("/admin/staffing-requests/{$id}");
    }
}
