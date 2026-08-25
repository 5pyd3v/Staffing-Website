<div class="panel-header-row">
    <div style="display:flex;gap:1rem;align-items:center;">
        <?= avatar($company['name'], 'lg') ?>
        <div>
            <h1 class="page-title"><?= e($company['name']) ?></h1>
            <p class="page-subtitle" style="margin-bottom:0;">
                <span class="badge badge--<?= e($company['status']) ?>"><?= e(ucfirst($company['status'])) ?></span>
                <?php if (!empty($company['industry_name'])): ?> &middot; <?= e($company['industry_name']) ?><?php endif; ?>
                <?php if (!empty($company['headquarters_city'])): ?> &middot; <?= e($company['headquarters_city']) ?><?= !empty($company['headquarters_state']) ? ', ' . e($company['headquarters_state']) : '' ?><?php endif; ?>
            </p>
        </div>
    </div>
    <a href="/admin/companies/<?= (int) $company['id'] ?>/edit" class="btn btn--ghost"><?= icon('edit', 'icon icon--sm') ?>Edit Employer</a>
</div>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="dash-columns dash-columns--wide">
    <div class="panel-stack">
        <div class="panel">
            <div class="panel__header"><h2>Overview</h2></div>
            <dl class="detail-list">
                <dt>Website</dt><dd><?= !empty($company['website']) ? '<a href="' . e($company['website']) . '" target="_blank" rel="noopener">' . e($company['website']) . '</a>' : '&mdash;' ?></dd>
                <dt>Size</dt><dd><?= e($company['size_range'] ?? '&mdash;') ?></dd>
                <dt>Description</dt><dd><?= nl2br(e($company['description'] ?? '&mdash;')) ?></dd>
            </dl>
        </div>

        <div class="panel">
            <div class="panel__header"><h2>Contacts</h2></div>
            <?php if (empty($contacts)): ?>
                <p class="empty-state">No contacts on file.</p>
            <?php else: ?>
                <ul class="simple-list">
                    <?php foreach ($contacts as $contact): ?>
                        <li>
                            <strong><?= e($contact['full_name']) ?></strong>
                            <?= $contact['is_primary'] ? '<span class="badge badge--active">Primary</span>' : '' ?>
                            <br><span class="text-muted"><?= e($contact['job_title'] ?? '') ?></span>
                            <br><a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a>
                            <?= !empty($contact['phone']) ? ' &middot; ' . e($contact['phone']) : '' ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel__header"><h2>Jobs (<?= count($jobs) ?>)</h2></div>
            <?php if (empty($jobs)): ?>
                <p class="empty-state">No jobs posted yet.</p>
            <?php else: ?>
                <ul class="simple-list">
                    <?php foreach ($jobs as $job): ?>
                        <li>
                            <a href="/admin/jobs/<?= (int) $job['id'] ?>/edit"><?= e($job['title']) ?></a>
                            <span class="badge badge--<?= e($job['status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $job['status']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel__header"><h2>Staffing Requests (<?= count($staffingRequests) ?>)</h2></div>
            <?php if (empty($staffingRequests)): ?>
                <p class="empty-state">No staffing requests yet.</p>
            <?php else: ?>
                <ul class="simple-list">
                    <?php foreach ($staffingRequests as $sr): ?>
                        <li>
                            <a href="/admin/staffing-requests/<?= (int) $sr['id'] ?>"><?= e($sr['role_title']) ?></a>
                            <span class="badge badge--<?= e($sr['status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $sr['status']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel__header"><h2>Activity Timeline</h2></div>

        <form action="/admin/companies/<?= (int) $company['id'] ?>/activities" method="post" class="form form--compact">
            <?= $csrfField ?>
            <div class="form-grid">
                <label class="form-field">
                    <span>Type</span>
                    <select name="activity_type">
                        <?php foreach ($activityTypes as $type): ?>
                            <option value="<?= e($type) ?>"><?= e(ucfirst(str_replace('_', ' ', $type))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="form-field">
                    <span>Subject</span>
                    <input type="text" name="subject" required placeholder="e.g. Called about Q3 hiring plan">
                </label>
            </div>
            <label class="form-field">
                <span>Notes</span>
                <textarea name="body" rows="2"></textarea>
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Log Activity</button>
            </div>
        </form>

        <ul class="timeline">
            <?php if (empty($activities)): ?>
                <li class="empty-state">No activity logged yet.</li>
            <?php endif; ?>
            <?php foreach ($activities as $activity): ?>
                <li class="timeline__item">
                    <span class="timeline__type badge badge--neutral"><?= e(ucfirst(str_replace('_', ' ', $activity['activity_type']))) ?></span>
                    <div class="timeline__body">
                        <strong><?= e($activity['subject']) ?></strong>
                        <?php if (!empty($activity['body'])): ?><p><?= nl2br(e($activity['body'])) ?></p><?php endif; ?>
                        <small class="text-muted"><?= e(date('M j, Y g:ia', strtotime($activity['occurred_at']))) ?> &middot; <?= e($activity['created_by_email'] ?? 'System') ?></small>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
