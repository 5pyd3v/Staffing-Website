<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Company;
use App\Models\Service;

final class ServiceController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/services/index', [
            'title' => 'Services',
            'services' => Service::allOrdered(),
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/services/form', [
            'title' => 'Add Service',
            'service' => null,
            'errors' => Session::getFlash('errors', []),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $this->verifyCsrf($request);

        $validator = $this->validate($request, [
            'name' => 'required|max:100|unique:services,name',
            'description' => 'max:500',
            'sort_order' => 'numeric',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/admin/services/create', $validator, $request->only(['name', 'description', 'sort_order']));
        }

        $name = trim((string) $request->input('name'));
        Service::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'description' => trim((string) $request->input('description', '')) ?: null,
            'icon' => trim((string) $request->input('icon', '')) ?: null,
            'is_active' => $request->input('is_active') ? 1 : 0,
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        Session::flash('success', 'Service added.');
        $this->redirect('/admin/services');
    }

    public function edit(Request $request): void
    {
        $service = Service::find((int) $request->param('id'));
        if (!$service) {
            Response::abort(404, 'Service not found');
        }

        $this->view('admin/services/form', [
            'title' => 'Edit Service',
            'service' => $service,
            'errors' => Session::getFlash('errors', []),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');

        $service = Service::find($id);
        if (!$service) {
            Response::abort(404, 'Service not found');
        }

        $validator = $this->validate($request, [
            'name' => 'required|max:100',
            'description' => 'max:500',
            'sort_order' => 'numeric',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors("/admin/services/{$id}/edit", $validator, $request->only(['name', 'description', 'sort_order']));
        }

        $name = trim((string) $request->input('name'));
        $data = [
            'name' => $name,
            'description' => trim((string) $request->input('description', '')) ?: null,
            'icon' => trim((string) $request->input('icon', '')) ?: null,
            'is_active' => $request->input('is_active') ? 1 : 0,
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
        if (strcasecmp($name, $service['name']) !== 0) {
            $data['slug'] = $this->uniqueSlug($name);
        }

        Service::update($id, $data);

        Session::flash('success', 'Service updated.');
        $this->redirect('/admin/services');
    }

    public function destroy(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');

        Service::delete($id);
        Session::flash('success', 'Service deleted.');
        $this->redirect('/admin/services');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Company::slugify($name);
        $slug = $base;
        $i = 2;
        while (Service::findBySlug($slug) !== null) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
