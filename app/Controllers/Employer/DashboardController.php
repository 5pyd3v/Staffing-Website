<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Core\Controller;
use App\Core\Request;

final class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('employer/dashboard', ['title' => 'Employer Dashboard'], 'layouts/app');
    }
}
