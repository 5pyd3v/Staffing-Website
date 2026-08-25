<div class="panel-header-row">
    <div>
        <h1 class="page-title"><?= e($req['role_title']) ?></h1>
        <p class="page-subtitle"><?= e($req['company_name'] ?? $req['contact_name']) ?></p>
    </div>
    <a href="/admin/staffing-requests/<?= (int) $req['id'] ?>/matches" class="btn btn--primary">Find Matches</a>
</div>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="dash-columns">
    <div class="panel">
        <div class="panel__header"><h2>Request Details</h2></div>
        <dl class="detail-list">
            <dt>Contact</dt><dd><?= e($req['contact_name']) ?> &middot; <a href="mailto:<?= e($req['contact_email']) ?>"><?= e($req['contact_email']) ?></a></dd>
            <dt>Employment Type</dt><dd><?= e(ucfirst(str_replace('_', ' ', $req['employment_type']))) ?></dd>
            <dt>Positions Needed</dt><dd><?= (int) $req['positions_needed'] ?></dd>
            <dt>Budget</dt><dd><?= $req['budget_min'] || $req['budget_max'] ? '$' . e($req['budget_min']) . ' &ndash; $' . e($req['budget_max']) : '&mdash;' ?></dd>
            <dt>Location</dt><dd><?= e(trim(($req['location_city'] ?? '') . ', ' . ($req['location_state'] ?? ''), ', ') ?: '&mdash;') ?> <?= $req['is_remote_ok'] ? '(Remote OK)' : '' ?></dd>
            <dt>Start Date Needed</dt><dd><?= $req['start_date_needed'] ? e(date('M j, Y', strtotime($req['start_date_needed']))) : '&mdash;' ?></dd>
            <dt>Must-Have Skills</dt><dd><?= nl2br(e($req['must_have_skills'] ?? '&mdash;')) ?></dd>
            <dt>Notes</dt><dd><?= nl2br(e($req['additional_notes'] ?? '&mdash;')) ?></dd>
        </dl>
    </div>

    <div class="panel">
        <div class="panel__header"><h2>Status</h2></div>
        <p class="badge badge--<?= e($req['status']) ?> badge--lg"><?= e(ucfirst(str_replace('_', ' ', $req['status']))) ?></p>
        <form action="/admin/staffing-requests/<?= (int) $req['id'] ?>/status" method="post" class="form form--compact">
            <?= $csrfField ?>
            <input type="hidden" name="_method" value="PUT">
            <label class="form-field">
                <span>Update status</span>
                <select name="status">
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= e($s) ?>" <?= $req['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary btn--block">Save Status</button>
            </div>
        </form>
        <?php if ($req['company_id']): ?>
            <a href="/admin/companies/<?= (int) $req['company_id'] ?>" class="btn btn--ghost btn--block">View Employer</a>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel__header"><h2>Matched Candidates (<?= count($matches) ?>)</h2></div>
    <?php if (empty($matches)): ?>
        <p class="empty-state">No candidates matched yet. Click "Find Matches" above to generate a ranked list.</p>
    <?php else: ?>
        <ul class="simple-list">
            <?php foreach ($matches as $m): ?>
                <li>
                    <a href="/admin/candidates/<?= (int) $m['candidate_id'] ?>"><?= e($m['first_name'] . ' ' . $m['last_name']) ?></a>
                    &middot; <?= e($m['score']) ?>/100
                    <span class="badge badge--<?= e($m['match_type']) ?>"><?= e(ucfirst(str_replace('_', ' ', $m['match_type']))) ?></span>
                    <span class="badge badge--neutral"><?= e(ucfirst(str_replace('_', ' ', $m['status']))) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
