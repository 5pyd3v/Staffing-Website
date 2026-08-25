<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Core\Controller;
use App\Core\Request;

final class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('candidate/dashboard', ['title' => 'Candidate Dashboard'], 'layouts/app');
    }
}
