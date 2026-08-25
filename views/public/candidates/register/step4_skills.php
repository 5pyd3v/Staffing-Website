<?php $skills = $draft['skills'] ?? []; ?>
<div class="wizard-form-inner">

    <h1>Skills &amp; links</h1>
    <p class="wizard-card__subtitle">Add the skills you want recruiters to search for. Press Enter or comma after each one.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <form action="/candidates/register/skills" method="post" class="form">
        <?= $csrfField ?>

        <div class="form-field">
            <span>Skills</span>
            <div class="tag-input" data-tag-input>
                <div class="tag-input__tags"></div>
                <input type="text" class="tag-input__field" placeholder="e.g. Forklift Operation, Excel, Customer Service">
                <input type="hidden" name="skills" data-tag-input-value value="<?= e(implode(', ', $skills)) ?>">
            </div>
        </div>

        <div class="form-grid">
            <label class="form-field">
                <span>LinkedIn URL</span>
                <input type="url" name="linkedin_url" placeholder="https://linkedin.com/in/..." value="<?= e($draft['linkedin_url'] ?? '') ?>">
            </label>
            <label class="form-field">
                <span>Portfolio / website URL</span>
                <input type="url" name="portfolio_url" placeholder="https://" value="<?= e($draft['portfolio_url'] ?? '') ?>">
            </label>
        </div>

        <div class="form-actions form-actions--wizard">
            <a href="/candidates/register/professional" class="btn btn--ghost">Back</a>
            <button type="submit" class="btn btn--primary btn--lg">Continue</button>
        </div>
    </form>
</div>

<script src="<?= asset_url('/assets/js/tag-input.js') ?>" defer></script>
