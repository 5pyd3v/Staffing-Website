<div class="wizard-form-inner">

    <h1>Skills &amp; budget</h1>
    <p class="wizard-card__subtitle">Ballpark numbers are fine &mdash; we'll fine-tune this with you directly.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <form action="/hire-talent/requirements" method="post" class="form">
        <?= $csrfField ?>

        <label class="form-field">
            <span>Must-have skills</span>
            <textarea name="must_have_skills" rows="3" placeholder="e.g. Forklift certification, 1+ year warehouse experience"><?= e($draft['must_have_skills'] ?? '') ?></textarea>
        </label>

        <label class="form-field">
            <span>Nice-to-have skills</span>
            <textarea name="nice_to_have_skills" rows="2"><?= e($draft['nice_to_have_skills'] ?? '') ?></textarea>
        </label>

        <div class="form-grid">
            <label class="form-field">
                <span>Budget (min)</span>
                <input type="number" name="budget_min" value="<?= e($draft['budget_min'] ?? '') ?>">
            </label>
            <label class="form-field">
                <span>Budget (max)</span>
                <input type="number" name="budget_max" value="<?= e($draft['budget_max'] ?? '') ?>">
            </label>
        </div>

        <label class="form-field">
            <span>Anything else we should know?</span>
            <textarea name="additional_notes" rows="3"><?= e($draft['additional_notes'] ?? '') ?></textarea>
        </label>

        <div class="form-actions form-actions--wizard">
            <a href="/hire-talent/role" class="btn btn--ghost">Back</a>
            <button type="submit" class="btn btn--primary btn--lg">Continue</button>
        </div>
    </form>
</div>
