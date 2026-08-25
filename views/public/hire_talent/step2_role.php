<div class="wizard-form-inner">

    <h1>What role are you hiring for?</h1>
    <p class="wizard-card__subtitle">The more detail you give us, the faster we can shortlist candidates.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <form action="/hire-talent/role" method="post" class="form" data-role-form novalidate>
        <?= $csrfField ?>

        <label class="<?= field_class($errors, 'role_title') ?>">
            <span>Job title</span>
            <input type="text" name="role_title" required autofocus value="<?= e($draft['role_title'] ?? '') ?>">
            <?php if ($msg = field_error($errors, 'role_title')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
        </label>

        <div class="form-grid">
            <label class="form-field">
                <span>Employment type</span>
                <select name="employment_type" data-employment-type required>
                    <?php foreach (['full_time' => 'Full-time (Direct Hire)', 'part_time' => 'Part-time', 'contract' => 'Contract', 'temp' => 'Temporary', 'temp_to_hire' => 'Temp-to-Hire'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($draft['employment_type'] ?? 'full_time') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span>Service</span>
                <select name="service_id">
                    <option value="">Not sure yet</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= (int) ($draft['service_id'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <p class="form-hint" data-temp-hint style="display:none;">Temporary and temp-to-hire roles are typically billed hourly &mdash; keep that in mind on the budget step.</p>

        <div class="form-grid">
            <label class="<?= field_class($errors, 'positions_needed') ?>">
                <span>Positions needed</span>
                <input type="number" name="positions_needed" min="1" required value="<?= e($draft['positions_needed'] ?? 1) ?>">
                <?php if ($msg = field_error($errors, 'positions_needed')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
            </label>
            <label class="form-field">
                <span>Start date needed</span>
                <input type="date" name="start_date_needed" value="<?= e($draft['start_date_needed'] ?? '') ?>">
            </label>
        </div>

        <label class="form-field form-field--checkbox">
            <input type="checkbox" name="is_remote_ok" data-remote-toggle value="1" <?= !empty($draft['is_remote_ok']) ? 'checked' : '' ?>>
            <span>This role can be fully remote</span>
        </label>

        <div class="form-grid" data-location-fields>
            <label class="form-field">
                <span>City</span>
                <input type="text" name="location_city" value="<?= e($draft['location_city'] ?? '') ?>">
            </label>
            <label class="form-field">
                <span>State</span>
                <input type="text" name="location_state" value="<?= e($draft['location_state'] ?? '') ?>">
            </label>
        </div>

        <div class="form-actions form-actions--wizard">
            <a href="/hire-talent" class="btn btn--ghost">Back</a>
            <button type="submit" class="btn btn--primary btn--lg">Continue</button>
        </div>
    </form>
</div>

<script src="<?= asset_url('/assets/js/hire-talent-wizard.js') ?>" defer></script>
