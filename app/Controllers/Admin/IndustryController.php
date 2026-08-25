<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Industry;

final class IndustryController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/industries/index', [
            'title' => 'Industries',
            'industries' => Industry::all('name ASC'),
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/industries/form', [
            'title' => 'Add Industry',
            'industry' => null,
            'errors' => Session::getFlash('errors', []),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $this->verifyCsrf($request);

        $validator = $this->validate($request, [
            'name' => 'required|max:100|unique:industries,name',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/admin/industries/create', $validator, $request->only(['name']));
        }

        $name = trim((string) $request->input('name'));
        Industry::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
        ]);

        Session::flash('success', 'Industry added.');
        $this->redirect('/admin/industries');
    }

    public function edit(Request $request): void
    {
        $industry = Industry::find((int) $request->param('id'));
        if (!$industry) {
            Response::abort(404, 'Industry not found');
        }

        $this->view('admin/industries/form', [
            'title' => 'Edit Industry',
            'industry' => $industry,
            'errors' => Session::getFlash('errors', []),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');

        $industry = Industry::find($id);
        if (!$industry) {
            Response::abort(404, 'Industry not found');
        }

        $validator = $this->validate($request, [
            'name' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors("/admin/industries/{$id}/edit", $validator, $request->only(['name']));
        }

        $name = trim((string) $request->input('name'));
        $data = ['name' => $name];
        if (strcasecmp($name, $industry['name']) !== 0) {
            $data['slug'] = $this->uniqueSlug($name);
        }

        Industry::update($id, $data);

        Session::flash('success', 'Industry updated.');
        $this->redirect('/admin/industries');
    }

    public function destroy(Request $request): void
    {
        $this->verifyCsrf($request);
        $id = (int) $request->param('id');

        if (Industry::inUseCount($id) > 0) {
            Session::flash('error', 'This industry is in use by jobs, companies, or staffing requests and cannot be deleted.');
            $this->redirect('/admin/industries');
        }

        Industry::delete($id);
        Session::flash('success', 'Industry deleted.');
        $this->redirect('/admin/industries');
    }

    private function uniqueSlug(string $name): string
    {
        $base = \App\Models\Company::slugify($name);
        $slug = $base;
        $i = 2;
        while (Industry::findBySlug($slug) !== null) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
