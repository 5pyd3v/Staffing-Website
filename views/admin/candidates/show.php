<div class="panel-header-row">
    <div style="display:flex;gap:1rem;align-items:center;">
        <?= avatar($candidate['first_name'] . ' ' . $candidate['last_name'], 'lg') ?>
        <div>
            <h1 class="page-title"><?= e($candidate['first_name'] . ' ' . $candidate['last_name']) ?></h1>
            <p class="page-subtitle" style="margin-bottom:0;"><?= e($candidate['headline'] ?? $candidate['current_title'] ?? '') ?></p>
        </div>
    </div>
    <?php if (!empty($candidate['resume_file_id'])): ?>
        <a href="/admin/candidates/<?= (int) $candidate['id'] ?>/resume" class="btn btn--ghost"><?= icon('file-text', 'icon icon--sm') ?>Download Resume</a>
    <?php endif; ?>
</div>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="dash-columns">
    <div class="panel-stack">
        <div class="panel">
            <div class="panel__header"><h2>Profile</h2></div>
            <dl class="detail-list">
                <dt>Email</dt><dd><a href="mailto:<?= e($candidate['email']) ?>"><?= e($candidate['email']) ?></a></dd>
                <dt>Phone</dt><dd><?= e($candidate['phone'] ?? '&mdash;') ?></dd>
                <dt>Location</dt><dd><?= e(trim(($candidate['location_city'] ?? '') . ', ' . ($candidate['location_state'] ?? ''), ', ') ?: '&mdash;') ?></dd>
                <dt>Experience</dt><dd><?= $candidate['experience_years'] !== null ? e($candidate['experience_years']) . ' years' : '&mdash;' ?></dd>
                <dt>Availability</dt><dd><?= e(ucfirst(str_replace('_', ' ', $candidate['availability']))) ?></dd>
                <dt>Salary Expectation</dt><dd><?= $candidate['salary_expectation_min'] || $candidate['salary_expectation_max'] ? '$' . e($candidate['salary_expectation_min']) . ' &ndash; $' . e($candidate['salary_expectation_max']) : '&mdash;' ?></dd>
                <dt>Remote OK</dt><dd><?= $candidate['is_remote_ok'] ? 'Yes' : 'No' ?></dd>
                <dt>Summary</dt><dd><?= nl2br(e($candidate['summary'] ?? '&mdash;')) ?></dd>
            </dl>
        </div>

        <div class="panel">
            <div class="panel__header"><h2>Skills</h2></div>
            <?php if (empty($skills)): ?>
                <p class="empty-state">No skills listed.</p>
            <?php else: ?>
                <div class="chip-list">
                    <?php foreach ($skills as $skill): ?>
                        <span class="chip"><?= e($skill['name']) ?> <small>(<?= e($skill['proficiency']) ?>)</small></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel__header"><h2>Experience</h2></div>
            <?php if (empty($experience)): ?>
                <p class="empty-state">No work history on file.</p>
            <?php else: ?>
                <ul class="simple-list">
                    <?php foreach ($experience as $exp): ?>
                        <li>
                            <strong><?= e($exp['job_title']) ?></strong> at <?= e($exp['company_name']) ?>
                            <br><span class="text-muted"><?= e(date('M Y', strtotime($exp['start_date']))) ?> &ndash; <?= $exp['is_current'] ? 'Present' : ($exp['end_date'] ? e(date('M Y', strtotime($exp['end_date']))) : '') ?></span>
                            <?php if (!empty($exp['description'])): ?><p><?= nl2br(e($exp['description'])) ?></p><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!empty($education)): ?>
        <div class="panel">
            <div class="panel__header"><h2>Education</h2></div>
            <ul class="simple-list">
                <?php foreach ($education as $edu): ?>
                    <li>
                        <strong><?= e($edu['institution']) ?></strong>
                        <?php if (!empty($edu['degree'])): ?> &mdash; <?= e($edu['degree']) ?><?php endif; ?>
                        <?php if (!empty($edu['field_of_study'])): ?>, <?= e($edu['field_of_study']) ?><?php endif; ?>
                        <br><span class="text-muted"><?= e($edu['start_year'] ?? '') ?><?= $edu['end_year'] ? ' – ' . e($edu['end_year']) : '' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <div class="panel-stack">
        <div class="panel">
            <div class="panel__header"><h2>Status</h2></div>
            <p class="badge badge--<?= e($candidate['status']) ?> badge--lg"><?= e(ucfirst(str_replace('_', ' ', $candidate['status']))) ?></p>
            <form action="/admin/candidates/<?= (int) $candidate['id'] ?>/status" method="post" class="form form--compact">
                <?= $csrfField ?>
                <input type="hidden" name="_method" value="PUT">
                <label class="form-field">
                    <span>Update status</span>
                    <select name="status">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= e($s) ?>" <?= $candidate['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="form-actions">
                    <button type="submit" class="btn btn--primary btn--block">Save Status</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel__header"><h2>Admin Notes</h2></div>
            <form action="/admin/candidates/<?= (int) $candidate['id'] ?>/notes" method="post" class="form form--compact">
                <?= $csrfField ?>
                <input type="hidden" name="_method" value="PUT">
                <label class="form-field">
                    <textarea name="admin_notes" rows="5" placeholder="Internal notes about this candidate&hellip;"><?= e($candidate['admin_notes'] ?? '') ?></textarea>
                </label>
                <div class="form-actions">
                    <button type="submit" class="btn btn--primary btn--block">Save Notes</button>
                </div>
            </form>
        </div>
    </div>
</div>
