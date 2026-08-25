<?php
$types = $draft['employment_types'] ?? ['full_time'];
$typeLabels = ['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'temp' => 'Temporary', 'temp_to_hire' => 'Temp-to-hire'];
?>
<div class="wizard-form-inner wizard-form-inner--wide">

    <h1>Review your profile</h1>
    <p class="wizard-card__subtitle">Take one last look before joining the talent pool.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <div class="review-grid">
        <div class="review-block">
            <div class="review-block__header"><h3>Account</h3><a href="/candidates/register">Edit</a></div>
            <p><?= e($draft['email'] ?? '') ?></p>
        </div>

        <div class="review-block">
            <div class="review-block__header"><h3>Profile</h3><a href="/candidates/register/profile">Edit</a></div>
            <p><?= e(($draft['first_name'] ?? '') . ' ' . ($draft['last_name'] ?? '')) ?><br>
            <?= e(trim(($draft['location_city'] ?? '') . ', ' . ($draft['location_state'] ?? ''), ', ') ?: 'Location not set') ?><br>
            <?= e($draft['phone'] ?? 'No phone on file') ?></p>
        </div>

        <div class="review-block">
            <div class="review-block__header"><h3>Professional</h3><a href="/candidates/register/professional">Edit</a></div>
            <p><?= e($draft['headline'] ?? $draft['current_title'] ?? 'No headline set') ?><br>
            <?= e($draft['experience_years'] ?? '0') ?> years experience &middot; <?= e(ucfirst(str_replace('_', ' ', $draft['availability'] ?? 'immediate'))) ?><br>
            Open to: <?= e(implode(', ', array_map(fn($t) => $typeLabels[$t] ?? $t, $types))) ?></p>
        </div>

        <div class="review-block">
            <div class="review-block__header"><h3>Skills &amp; Links</h3><a href="/candidates/register/skills">Edit</a></div>
            <?php if (!empty($draft['skills'])): ?>
                <div class="chip-list"><?php foreach ($draft['skills'] as $skill): ?><span class="chip"><?= e($skill) ?></span><?php endforeach; ?></div>
            <?php else: ?>
                <p class="text-muted">No skills added yet.</p>
            <?php endif; ?>
        </div>

        <div class="review-block">
            <div class="review-block__header"><h3>Resume</h3><a href="/candidates/register/resume">Edit</a></div>
            <p><?= !empty($draft['resume_file_name']) ? e($draft['resume_file_name']) : 'No resume uploaded &mdash; you can add one later.' ?></p>
        </div>
    </div>

    <form action="/candidates/register/review" method="post" class="form">
        <?= $csrfField ?>
        <label class="form-field form-field--checkbox">
            <input type="checkbox" required>
            <span>I confirm this information is accurate and agree to be contacted about opportunities.</span>
        </label>
        <div class="form-actions form-actions--wizard">
            <a href="/candidates/register/resume" class="btn btn--ghost">Back</a>
            <button type="submit" class="btn btn--primary btn--lg">Join the Talent Pool</button>
        </div>
    </form>
</div>
