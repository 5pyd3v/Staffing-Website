<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Notification;

final class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/notifications/index', [
            'title' => 'Notifications',
            'notifications' => Notification::recent(50),
        ], 'layouts/admin');
    }
}
