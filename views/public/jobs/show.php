<?php
$currentRole = $currentUser['role_slug'] ?? null;
$isCandidate = $currentRole === 'candidate';
?>
<section class="page-band">
    <div class="page-band__inner page-band__inner--narrow">
        <a href="/jobs" class="back-link"><?= icon('chevron-right', 'icon icon--sm') ?> All Jobs</a>

        <div class="job-detail__header">
            <div style="display:flex;gap:1rem;align-items:flex-start;">
                <?= avatar($job['company_name'], 'lg') ?>
                <div>
                    <h1><?= e($job['title']) ?></h1>
                    <p class="job-detail__company"><?= icon('building', 'icon icon--sm') ?><?= e($job['company_name']) ?><?php if (!empty($job['industry_name'])): ?> &middot; <?= e($job['industry_name']) ?><?php endif; ?></p>
                    <p class="job-detail__meta">
                        <span class="badge badge--neutral"><?= e(ucfirst(str_replace('_', ' ', $job['employment_type']))) ?></span>
                        <span><?= icon('map-pin', 'icon icon--sm') ?><?= $job['is_remote'] ? 'Remote' : e(trim(($job['location_city'] ?? '') . ', ' . ($job['location_state'] ?? ''), ', ') ?: '') ?></span>
                        <?php if ($job['salary_min'] || $job['salary_max']): ?>
                            <span><?= icon('dollar-sign', 'icon icon--sm') ?><?= e(number_format((float) $job['salary_min'])) ?>&ndash;<?= e(number_format((float) $job['salary_max'])) ?>/<?= e($job['salary_period']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

        <div class="job-detail__body">
            <h3>About this role</h3>
            <p><?= nl2br(e($job['description'])) ?></p>

            <?php if (!empty($job['requirements'])): ?>
                <h3>What you'll need</h3>
                <p><?= nl2br(e($job['requirements'])) ?></p>
            <?php endif; ?>

            <?php if (!empty($job['benefits'])): ?>
                <h3>Benefits</h3>
                <p><?= nl2br(e($job['benefits'])) ?></p>
            <?php endif; ?>
        </div>

        <div class="job-detail__apply">
            <?php if ($alreadyApplied): ?>
                <p class="badge badge--active badge--lg"><?= icon('check-circle', 'icon icon--sm') ?> You've already applied to this job</p>
            <?php elseif ($isCandidate): ?>
                <form action="/jobs/<?= e($job['slug']) ?>/apply" method="post" class="form">
                    <?= $csrfField ?>
                    <label class="form-field">
                        <span>Add a short note (optional)</span>
                        <textarea name="cover_note" rows="3" placeholder="Why are you a great fit for this role?"></textarea>
                    </label>
                    <button type="submit" class="btn btn--primary btn--lg"><?= icon('check-circle', 'icon') ?>Apply Now</button>
                </form>
            <?php elseif ($currentUser): ?>
                <p class="text-muted">Only candidate accounts can apply directly. Contact us if you'd like to refer someone for this role.</p>
            <?php else: ?>
                <form action="/jobs/<?= e($job['slug']) ?>/apply" method="post">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn--primary btn--lg"><?= icon('sparkle', 'icon') ?>Apply Now &mdash; Join the Talent Pool</button>
                </form>
                <p class="text-muted">You'll create your profile in a few quick steps, then we'll submit your application automatically.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
