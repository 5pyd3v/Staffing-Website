<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\AppSetting;

final class SettingController extends Controller
{
    private const KEYS = ['site_tagline', 'support_email'];

    public function index(Request $request): void
    {
        $rows = AppSetting::all();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['value'];
        }

        $this->view('admin/settings/index', [
            'title' => 'Settings',
            'settings' => $settings,
            'success' => Session::getFlash('success'),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $this->verifyCsrf($request);

        foreach (self::KEYS as $key) {
            AppSetting::set($key, trim((string) $request->input($key, '')) ?: null);
        }

        Session::flash('success', 'Settings saved.');
        $this->redirect('/admin/settings');
    }
}
