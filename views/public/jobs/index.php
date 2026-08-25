<section class="page-band">
    <div class="page-band__inner">
        <h1 class="section-title">Open Opportunities</h1>
        <p class="section-subtitle" style="margin-bottom:0;">Browse current openings from our employer partners. New to TalentBridge? <a href="/candidates/register">Join the talent pool</a> to get matched even when the right role isn't posted yet.</p>

        <form action="/jobs" method="get" class="filter-bar filter-bar--wrap job-search-form">
            <input type="search" name="q" placeholder="Job title, keyword, or company" value="<?= e($filters['q']) ?>">
            <input type="text" name="location" placeholder="City or state" value="<?= e($filters['location']) ?>">
            <select name="employment_type">
                <option value="">Any Type</option>
                <?php foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'temp' => 'Temporary', 'temp_to_hire' => 'Temp-to-Hire'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['employment_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="checkbox-inline">
                <input type="checkbox" name="remote" value="1" <?= $filters['remote'] === '1' ? 'checked' : '' ?>> Remote only
            </label>
            <button type="submit" class="btn btn--primary"><?= icon('search', 'icon icon--sm') ?>Search</button>
        </form>
    </div>
</section>

<section class="page-band page-band--muted">
    <div class="page-band__inner">
        <p class="text-muted"><?= (int) $total ?> open <?= $total === 1 ? 'role' : 'roles' ?></p>

        <?php if (empty($jobs)): ?>
            <div class="empty-state">
                <?= icon('search') ?>
                <p>No open roles match these filters right now &mdash; <a href="/candidates/register">join the talent pool</a> and we'll reach out when one opens up.</p>
            </div>
        <?php else: ?>
            <div class="job-grid">
                <?php foreach ($jobs as $job): ?>
                    <a class="job-card" href="/jobs/<?= e($job['slug']) ?>">
                        <div class="job-card__header">
                            <h3><?= e($job['title']) ?></h3>
                            <span class="badge badge--neutral"><?= e(ucfirst(str_replace('_', ' ', $job['employment_type']))) ?></span>
                        </div>
                        <p class="job-card__company"><?= icon('building', 'icon icon--sm') ?><?= e($job['company_name']) ?></p>
                        <p class="job-card__meta">
                            <?= icon('map-pin', 'icon icon--sm') ?>
                            <?= $job['is_remote'] ? 'Remote' : e(trim(($job['location_city'] ?? '') . ', ' . ($job['location_state'] ?? ''), ', ') ?: 'Location on request') ?>
                            <?php if ($job['salary_min'] || $job['salary_max']): ?>
                                &middot; <?= icon('dollar-sign', 'icon icon--sm') ?><?= e(number_format((float) $job['salary_min'])) ?>&ndash;<?= e(number_format((float) $job['salary_max'])) ?>/<?= e($job['salary_period']) ?>
                            <?php endif; ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php
            $baseUrl = '/jobs?q=' . urlencode($filters['q']) . '&location=' . urlencode($filters['location']) . '&employment_type=' . urlencode($filters['employment_type']) . '&remote=' . urlencode($filters['remote']);
            include VIEW_PATH . '/partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>
</section>
