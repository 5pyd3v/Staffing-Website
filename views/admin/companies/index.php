<div class="panel-header-row">
    <h1 class="page-title">Employers</h1>
    <a href="/admin/companies/create" class="btn btn--primary"><?= icon('plus', 'icon icon--sm') ?>Add Employer</a>
</div>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<form action="/admin/companies" method="get" class="filter-bar">
    <input type="search" name="q" placeholder="Search by name..." value="<?= e($search) ?>">
    <select name="status">
        <option value="">All Statuses</option>
        <?php foreach ($statuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn--ghost">Filter</button>
</form>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr><th scope="col">Employer</th><th scope="col">Industry</th><th scope="col">Open Jobs</th><th scope="col">Status</th><th scope="col"></th></tr>
        </thead>
        <tbody>
            <?php if (empty($companies)): ?>
                <tr><td colspan="5" class="empty-state">No employers match these filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($companies as $company): ?>
                <tr>
                    <td>
                        <a href="/admin/companies/<?= (int) $company['id'] ?>" style="display:flex;align-items:center;gap:0.6rem;">
                            <?= avatar($company['name'], 'sm') ?><?= e($company['name']) ?>
                        </a>
                    </td>
                    <td class="text-muted"><?= e($company['industry_name'] ?? '&mdash;') ?></td>
                    <td><?= (int) $company['open_jobs_count'] ?></td>
                    <td><span class="badge badge--<?= e($company['status']) ?>"><?= e(ucfirst($company['status'])) ?></span></td>
                    <td class="text-right"><a href="/admin/companies/<?= (int) $company['id'] ?>" class="btn btn--text">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$baseUrl = '/admin/companies?q=' . urlencode($search) . '&status=' . urlencode($status);
include VIEW_PATH . '/partials/pagination.php';
?>
