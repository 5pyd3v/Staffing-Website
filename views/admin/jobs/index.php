<div class="panel-header-row">
    <h1 class="page-title">Jobs</h1>
    <a href="/admin/jobs/create" class="btn btn--primary"><?= icon('plus', 'icon icon--sm') ?>Post a Job</a>
</div>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<form action="/admin/jobs" method="get" class="filter-bar">
    <input type="search" name="q" placeholder="Search by title..." value="<?= e($search) ?>">
    <select name="status">
        <option value="">All Statuses</option>
        <?php foreach ($statuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn--ghost">Filter</button>
</form>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr><th scope="col">Title</th><th scope="col">Employer</th><th scope="col">Type</th><th scope="col">Location</th><th scope="col">Status</th><th scope="col"></th></tr>
        </thead>
        <tbody>
            <?php if (empty($jobs)): ?>
                <tr><td colspan="6" class="empty-state">No jobs match these filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><?= e($job['title']) ?></td>
                    <td><?= e($job['company_name']) ?></td>
                    <td class="text-muted"><?= e(ucfirst(str_replace('_', ' ', $job['employment_type']))) ?></td>
                    <td class="text-muted"><?= e($job['is_remote'] ? 'Remote' : trim(($job['location_city'] ?? '') . ', ' . ($job['location_state'] ?? ''), ', ')) ?></td>
                    <td><span class="badge badge--<?= e($job['status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $job['status']))) ?></span></td>
                    <td class="text-right">
                        <a href="/admin/jobs/<?= (int) $job['id'] ?>/edit" class="btn btn--text">Edit</a>
                        <form action="/admin/jobs/<?= (int) $job['id'] ?>" method="post" class="inline-form" onsubmit="return confirm('Delete this job?');">
                            <?= $csrfField ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn--text btn--danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$baseUrl = '/admin/jobs?q=' . urlencode($search) . '&status=' . urlencode($status);
include VIEW_PATH . '/partials/pagination.php';
?>
