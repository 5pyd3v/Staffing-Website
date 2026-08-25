<div class="wizard-form-inner">

    <h1>Let's find your next hire</h1>
    <p class="wizard-card__subtitle">Tell us a bit about your company. No account needed &mdash; a recruiter will follow up directly.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <form action="/hire-talent" method="post" class="form" novalidate>
        <?= $csrfField ?>

        <label class="<?= field_class($errors, 'company_name') ?>">
            <span>Company name</span>
            <input type="text" name="company_name" required autofocus value="<?= e($draft['company_name'] ?? '') ?>">
            <?php if ($msg = field_error($errors, 'company_name')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
        </label>

        <label class="form-field">
            <span>Industry</span>
            <select name="industry_id">
                <option value="">Select industry&hellip;</option>
                <?php foreach ($industries as $i): ?>
                    <option value="<?= (int) $i['id'] ?>" <?= (int) ($draft['industry_id'] ?? 0) === (int) $i['id'] ? 'selected' : '' ?>><?= e($i['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="form-grid">
            <label class="<?= field_class($errors, 'contact_name') ?>">
                <span>Your name</span>
                <input type="text" name="contact_name" required value="<?= e($draft['contact_name'] ?? '') ?>">
                <?php if ($msg = field_error($errors, 'contact_name')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
            </label>
            <label class="<?= field_class($errors, 'contact_email') ?>">
                <span>Work email</span>
                <input type="email" name="contact_email" required value="<?= e($draft['contact_email'] ?? '') ?>">
                <?php if ($msg = field_error($errors, 'contact_email')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
            </label>
        </div>

        <label class="form-field">
            <span>Phone (optional)</span>
            <input type="tel" name="contact_phone" value="<?= e($draft['contact_phone'] ?? '') ?>">
        </label>

        <div class="form-actions form-actions--wizard">
            <span></span>
            <button type="submit" class="btn btn--primary btn--lg">Continue</button>
        </div>
    </form>
</div>
