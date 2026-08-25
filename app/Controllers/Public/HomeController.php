<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Job;
use App\Models\Service;

final class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('public/home', [
            'title' => 'Home',
            'jobs' => Job::latestOpen(6),
            'services' => Service::allOrdered(),
        ]);
    }
}
