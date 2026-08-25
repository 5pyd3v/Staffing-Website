<?php

declare(strict_types=1);

use App\Controllers\Admin\CandidateController;
use App\Controllers\Admin\CompanyController;
use App\Controllers\Admin\DashboardController as AdminDashboard;
use App\Controllers\Admin\IndustryController;
use App\Controllers\Admin\JobController;
use App\Controllers\Admin\MatchController;
use App\Controllers\Admin\NotificationController;
use App\Controllers\Admin\ServiceController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\StaffingRequestController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Candidate\DashboardController as CandidateDashboard;
use App\Controllers\Employer\DashboardController as EmployerDashboard;
use App\Controllers\Public\CandidateRegistrationController;
use App\Controllers\Public\HireTalentController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\JobBoardController;
use App\Core\Router;

/** @var Router $router */

// Public
$router->get('/', [HomeController::class, 'index']);

// Job board
$router->get('/jobs', [JobBoardController::class, 'index']);
$router->get('/jobs/{slug}', [JobBoardController::class, 'show']);
$router->post('/jobs/{slug}/apply', [JobBoardController::class, 'apply']);

// Candidate registration wizard (anonymous, session-backed draft)
$router->get('/candidates/register', [CandidateRegistrationController::class, 'stepAccount'], ['guest']);
$router->post('/candidates/register', [CandidateRegistrationController::class, 'storeAccount'], ['guest']);
$router->get('/candidates/register/profile', [CandidateRegistrationController::class, 'stepProfile'], ['guest']);
$router->post('/candidates/register/profile', [CandidateRegistrationController::class, 'storeProfile'], ['guest']);
$router->get('/candidates/register/professional', [CandidateRegistrationController::class, 'stepProfessional'], ['guest']);
$router->post('/candidates/register/professional', [CandidateRegistrationController::class, 'storeProfessional'], ['guest']);
$router->get('/candidates/register/skills', [CandidateRegistrationController::class, 'stepSkills'], ['guest']);
$router->post('/candidates/register/skills', [CandidateRegistrationController::class, 'storeSkills'], ['guest']);
$router->get('/candidates/register/resume', [CandidateRegistrationController::class, 'stepResume'], ['guest']);
$router->post('/candidates/register/resume', [CandidateRegistrationController::class, 'storeResume'], ['guest']);
$router->get('/candidates/register/review', [CandidateRegistrationController::class, 'stepReview'], ['guest']);
$router->post('/candidates/register/review', [CandidateRegistrationController::class, 'submit'], ['guest']);
$router->get('/candidates/register/complete', [CandidateRegistrationController::class, 'complete']);

// Hire Talent wizard (anonymous, session-backed draft)
$router->get('/hire-talent', [HireTalentController::class, 'stepCompany']);
$router->post('/hire-talent', [HireTalentController::class, 'storeCompany']);
$router->get('/hire-talent/role', [HireTalentController::class, 'stepRole']);
$router->post('/hire-talent/role', [HireTalentController::class, 'storeRole']);
$router->get('/hire-talent/requirements', [HireTalentController::class, 'stepRequirements']);
$router->post('/hire-talent/requirements', [HireTalentController::class, 'storeRequirements']);
$router->get('/hire-talent/review', [HireTalentController::class, 'stepReview']);
$router->post('/hire-talent/review', [HireTalentController::class, 'submit']);
$router->get('/hire-talent/complete', [HireTalentController::class, 'complete']);

// Auth
$router->get('/login', [LoginController::class, 'show'], ['guest']);
$router->post('/login', [LoginController::class, 'store'], ['guest']);
$router->post('/logout', [LoginController::class, 'destroy'], ['auth']);

// Admin (Super Admin + Admin)
$adminMiddleware = ['auth', 'role:super_admin,admin'];

$router->get('/admin/dashboard', [AdminDashboard::class, 'index'], $adminMiddleware);

// Industries
$router->get('/admin/industries', [IndustryController::class, 'index'], $adminMiddleware);
$router->get('/admin/industries/create', [IndustryController::class, 'create'], $adminMiddleware);
$router->post('/admin/industries', [IndustryController::class, 'store'], $adminMiddleware);
$router->get('/admin/industries/{id}/edit', [IndustryController::class, 'edit'], $adminMiddleware);
$router->put('/admin/industries/{id}', [IndustryController::class, 'update'], $adminMiddleware);
$router->delete('/admin/industries/{id}', [IndustryController::class, 'destroy'], $adminMiddleware);

// Services
$router->get('/admin/services', [ServiceController::class, 'index'], $adminMiddleware);
$router->get('/admin/services/create', [ServiceController::class, 'create'], $adminMiddleware);
$router->post('/admin/services', [ServiceController::class, 'store'], $adminMiddleware);
$router->get('/admin/services/{id}/edit', [ServiceController::class, 'edit'], $adminMiddleware);
$router->put('/admin/services/{id}', [ServiceController::class, 'update'], $adminMiddleware);
$router->delete('/admin/services/{id}', [ServiceController::class, 'destroy'], $adminMiddleware);

// Jobs
$router->get('/admin/jobs', [JobController::class, 'index'], $adminMiddleware);
$router->get('/admin/jobs/create', [JobController::class, 'create'], $adminMiddleware);
$router->post('/admin/jobs', [JobController::class, 'store'], $adminMiddleware);
$router->get('/admin/jobs/{id}/edit', [JobController::class, 'edit'], $adminMiddleware);
$router->put('/admin/jobs/{id}', [JobController::class, 'update'], $adminMiddleware);
$router->delete('/admin/jobs/{id}', [JobController::class, 'destroy'], $adminMiddleware);

// Employers (Companies) + CRM-lite
$router->get('/admin/companies', [CompanyController::class, 'index'], $adminMiddleware);
$router->get('/admin/companies/create', [CompanyController::class, 'create'], $adminMiddleware);
$router->post('/admin/companies', [CompanyController::class, 'store'], $adminMiddleware);
$router->get('/admin/companies/{id}', [CompanyController::class, 'show'], $adminMiddleware);
$router->get('/admin/companies/{id}/edit', [CompanyController::class, 'edit'], $adminMiddleware);
$router->put('/admin/companies/{id}', [CompanyController::class, 'update'], $adminMiddleware);
$router->delete('/admin/companies/{id}', [CompanyController::class, 'destroy'], $adminMiddleware);
$router->post('/admin/companies/{id}/activities', [CompanyController::class, 'storeActivity'], $adminMiddleware);

// Staffing Requests
$router->get('/admin/staffing-requests', [StaffingRequestController::class, 'index'], $adminMiddleware);
$router->get('/admin/staffing-requests/{id}', [StaffingRequestController::class, 'show'], $adminMiddleware);
$router->put('/admin/staffing-requests/{id}/status', [StaffingRequestController::class, 'updateStatus'], $adminMiddleware);

// Candidates
$router->get('/admin/candidates', [CandidateController::class, 'index'], $adminMiddleware);
$router->get('/admin/candidates/search', [CandidateController::class, 'search'], $adminMiddleware);
$router->get('/admin/candidates/{id}', [CandidateController::class, 'show'], $adminMiddleware);
$router->get('/admin/candidates/{id}/resume', [CandidateController::class, 'downloadResume'], $adminMiddleware);
$router->put('/admin/candidates/{id}/status', [CandidateController::class, 'updateStatus'], $adminMiddleware);
$router->put('/admin/candidates/{id}/notes', [CandidateController::class, 'updateNotes'], $adminMiddleware);

// Matching
$router->get('/admin/matches', [MatchController::class, 'index'], $adminMiddleware);
$router->get('/admin/staffing-requests/{id}/matches', [MatchController::class, 'forRequest'], $adminMiddleware);
$router->post('/admin/staffing-requests/{id}/matches', [MatchController::class, 'saveForRequest'], $adminMiddleware);
$router->put('/admin/matches/{id}/status', [MatchController::class, 'updateStatus'], $adminMiddleware);

// Notifications
$router->get('/admin/notifications', [NotificationController::class, 'index'], $adminMiddleware);

// Settings
$router->get('/admin/settings', [SettingController::class, 'index'], $adminMiddleware);
$router->put('/admin/settings', [SettingController::class, 'update'], $adminMiddleware);

// Employer
$router->get('/employer/dashboard', [EmployerDashboard::class, 'index'], ['auth', 'role:employer']);

// Candidate
$router->get('/candidate/dashboard', [CandidateDashboard::class, 'index'], ['auth', 'role:candidate']);
