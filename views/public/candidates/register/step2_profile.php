<div class="wizard-form-inner">

    <h1>Tell us about you</h1>
    <p class="wizard-card__subtitle">This is what employers will see first.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <form action="/candidates/register/profile" method="post" class="form" novalidate>
        <?= $csrfField ?>

        <div class="form-grid">
            <label class="<?= field_class($errors, 'first_name') ?>">
                <span>First name</span>
                <input type="text" name="first_name" required autofocus value="<?= e($draft['first_name'] ?? '') ?>">
                <?php if ($msg = field_error($errors, 'first_name')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
            </label>
            <label class="<?= field_class($errors, 'last_name') ?>">
                <span>Last name</span>
                <input type="text" name="last_name" required value="<?= e($draft['last_name'] ?? '') ?>">
                <?php if ($msg = field_error($errors, 'last_name')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
            </label>
        </div>

        <label class="form-field">
            <span>Phone number</span>
            <input type="tel" name="phone" value="<?= e($draft['phone'] ?? '') ?>">
        </label>

        <div class="form-grid">
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
            <a href="/candidates/register" class="btn btn--ghost">Back</a>
            <button type="submit" class="btn btn--primary btn--lg">Continue</button>
        </div>
    </form>
</div>
