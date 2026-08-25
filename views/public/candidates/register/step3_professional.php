<?php $types = $draft['employment_types'] ?? ['full_time']; ?>
<div class="wizard-form-inner">

    <h1>Your professional background</h1>
    <p class="wizard-card__subtitle">This helps our recruiters match you to the right opportunities.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <form action="/candidates/register/professional" method="post" class="form" novalidate>
        <?= $csrfField ?>

        <label class="<?= field_class($errors, 'headline') ?>">
            <span>Headline</span>
            <input type="text" name="headline" placeholder="e.g. Warehouse Operations Lead with 6 years in 3PL" value="<?= e($draft['headline'] ?? '') ?>">
            <?php if ($msg = field_error($errors, 'headline')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
        </label>

        <div class="form-grid">
            <label class="<?= field_class($errors, 'current_title') ?>">
                <span>Current / most recent title</span>
                <input type="text" name="current_title" value="<?= e($draft['current_title'] ?? '') ?>">
                <?php if ($msg = field_error($errors, 'current_title')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
            </label>
            <label class="<?= field_class($errors, 'experience_years') ?>">
                <span>Years of experience</span>
                <input type="number" name="experience_years" min="0" step="0.5" value="<?= e($draft['experience_years'] ?? '') ?>">
                <?php if ($msg = field_error($errors, 'experience_years')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
            </label>
        </div>

        <label class="form-field">
            <span>Professional summary</span>
            <textarea name="summary" rows="4" placeholder="A few sentences about your experience and what you're looking for."><?= e($draft['summary'] ?? '') ?></textarea>
        </label>

        <label class="<?= field_class($errors, 'availability') ?>">
            <span>Availability</span>
            <select name="availability" required>
                <option value="immediate" <?= ($draft['availability'] ?? 'immediate') === 'immediate' ? 'selected' : '' ?>>Immediate</option>
                <option value="2_weeks" <?= ($draft['availability'] ?? '') === '2_weeks' ? 'selected' : '' ?>>2 weeks notice</option>
                <option value="1_month" <?= ($draft['availability'] ?? '') === '1_month' ? 'selected' : '' ?>>1 month notice</option>
                <option value="not_looking" <?= ($draft['availability'] ?? '') === 'not_looking' ? 'selected' : '' ?>>Not actively looking</option>
            </select>
            <?php if ($msg = field_error($errors, 'availability')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
        </label>

        <fieldset class="form-field">
            <legend>Open to</legend>
            <div class="checkbox-grid">
                <?php foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'temp' => 'Temporary', 'temp_to_hire' => 'Temp-to-hire'] as $value => $label): ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="employment_types[]" value="<?= e($value) ?>" <?= in_array($value, $types, true) ? 'checked' : '' ?>>
                        <?= e($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="form-grid">
            <label class="form-field">
                <span>Salary expectation (min)</span>
                <input type="number" name="salary_expectation_min" value="<?= e($draft['salary_expectation_min'] ?? '') ?>">
            </label>
            <label class="form-field">
                <span>Salary expectation (max)</span>
                <input type="number" name="salary_expectation_max" value="<?= e($draft['salary_expectation_max'] ?? '') ?>">
            </label>
        </div>

        <label class="form-field form-field--checkbox">
            <input type="checkbox" name="is_remote_ok" value="1" <?= !empty($draft['is_remote_ok']) ? 'checked' : '' ?>>
            <span>I'm open to remote work</span>
        </label>

        <div class="form-actions form-actions--wizard">
            <a href="/candidates/register/profile" class="btn btn--ghost">Back</a>
            <button type="submit" class="btn btn--primary btn--lg">Continue</button>
        </div>
    </form>
</div>
