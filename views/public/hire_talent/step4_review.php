<?php
$typeLabels = ['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'temp' => 'Temporary', 'temp_to_hire' => 'Temp-to-Hire'];
?>
<div class="wizard-form-inner wizard-form-inner--wide">

    <h1>Review your request</h1>
    <p class="wizard-card__subtitle">Take one last look before we send this to our recruiting team.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <div class="review-grid">
        <div class="review-block">
            <div class="review-block__header"><h3>Company</h3><a href="/hire-talent">Edit</a></div>
            <p><?= e($draft['company_name'] ?? '') ?><br><?= e($draft['contact_name'] ?? '') ?> &middot; <?= e($draft['contact_email'] ?? '') ?></p>
        </div>

        <div class="review-block">
            <div class="review-block__header"><h3>Role</h3><a href="/hire-talent/role">Edit</a></div>
            <p><?= e($draft['role_title'] ?? '') ?><br>
            <?= e($typeLabels[$draft['employment_type'] ?? 'full_time'] ?? '') ?> &middot; <?= (int) ($draft['positions_needed'] ?? 1) ?> position(s)<br>
            <?= !empty($draft['is_remote_ok']) ? 'Remote OK' : e(trim(($draft['location_city'] ?? '') . ', ' . ($draft['location_state'] ?? ''), ', ') ?: 'Location not set') ?></p>
        </div>

        <div class="review-block">
            <div class="review-block__header"><h3>Requirements</h3><a href="/hire-talent/requirements">Edit</a></div>
            <p><?= e($draft['must_have_skills'] ?? 'Not specified') ?></p>
            <?php if (!empty($draft['budget_min']) || !empty($draft['budget_max'])): ?>
                <p>Budget: $<?= e($draft['budget_min'] ?? '?') ?> &ndash; $<?= e($draft['budget_max'] ?? '?') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <form action="/hire-talent/review" method="post" class="form">
        <?= $csrfField ?>
        <div class="form-actions form-actions--wizard">
            <a href="/hire-talent/requirements" class="btn btn--ghost">Back</a>
            <button type="submit" class="btn btn--primary btn--lg">Submit Request</button>
        </div>
    </form>
</div>
