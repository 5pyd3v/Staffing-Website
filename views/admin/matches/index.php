<h1 class="page-title">Matching</h1>
<p class="page-subtitle">All candidate matches proposed across staffing requests.</p>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<form action="/admin/matches" method="get" class="filter-bar">
    <select name="match_type">
        <option value="">Any Match Type</option>
        <?php foreach ($matchTypes as $t): ?>
            <option value="<?= e($t) ?>" <?= $matchType === $t ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $t))) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status">
        <option value="">Any Status</option>
        <?php foreach ($statuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn--ghost">Filter</button>
</form>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr><th scope="col">Candidate</th><th scope="col">Matched To</th><th scope="col">Score</th><th scope="col">Match Type</th><th scope="col">Status</th><th scope="col"></th></tr>
        </thead>
        <tbody>
            <?php if (empty($matches)): ?>
                <tr><td colspan="6" class="empty-state">No matches yet &mdash; open a staffing request and click "Find Matches" to generate some.</td></tr>
            <?php endif; ?>
            <?php foreach ($matches as $m): ?>
                <tr>
                    <td><a href="/admin/candidates/<?= (int) $m['candidate_id'] ?>"><?= e($m['first_name'] . ' ' . $m['last_name']) ?></a></td>
                    <td class="text-muted"><?= e($m['request_title'] ?? $m['job_title'] ?? '&mdash;') ?></td>
                    <td><?= e($m['score']) ?>/100</td>
                    <td><span class="badge badge--<?= e($m['match_type']) ?>"><?= e(ucfirst(str_replace('_', ' ', $m['match_type']))) ?></span></td>
                    <td>
                        <form action="/admin/matches/<?= (int) $m['id'] ?>/status" method="post" class="inline-form">
                            <?= $csrfField ?>
                            <input type="hidden" name="_method" value="PUT">
                            <select name="status" onchange="this.form.submit()" class="status-select">
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= e($s) ?>" <?= $m['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td class="text-right text-muted"><?= e(date('M j', strtotime($m['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$baseUrl = '/admin/matches?status=' . urlencode($status) . '&match_type=' . urlencode($matchType);
include VIEW_PATH . '/partials/pagination.php';
?>
