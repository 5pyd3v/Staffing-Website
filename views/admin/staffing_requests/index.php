<h1 class="page-title">Staffing Requests</h1>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<form action="/admin/staffing-requests" method="get" class="filter-bar">
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
            <tr><th scope="col">Role</th><th scope="col">Employer</th><th scope="col">Positions</th><th scope="col">Needed By</th><th scope="col">Status</th><th scope="col"></th></tr>
        </thead>
        <tbody>
            <?php if (empty($requests)): ?>
                <tr><td colspan="6" class="empty-state">No staffing requests match these filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= e($r['role_title']) ?></td>
                    <td class="text-muted"><?= e($r['company_name'] ?? $r['contact_name']) ?></td>
                    <td><?= (int) $r['positions_needed'] ?></td>
                    <td class="text-muted"><?= $r['start_date_needed'] ? e(date('M j, Y', strtotime($r['start_date_needed']))) : '&mdash;' ?></td>
                    <td><span class="badge badge--<?= e($r['status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $r['status']))) ?></span></td>
                    <td class="text-right"><a href="/admin/staffing-requests/<?= (int) $r['id'] ?>" class="btn btn--text">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$baseUrl = '/admin/staffing-requests?status=' . urlencode($status);
include VIEW_PATH . '/partials/pagination.php';
?>
