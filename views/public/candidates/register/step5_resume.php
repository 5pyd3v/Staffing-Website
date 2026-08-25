<div class="wizard-form-inner">

    <h1>Upload your resume</h1>
    <p class="wizard-card__subtitle">PDF, DOC, or DOCX up to 8MB. You can skip this step and add it later from your dashboard.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <form action="/candidates/register/resume" method="post" class="form" enctype="multipart/form-data">
        <?= $csrfField ?>

        <div class="dropzone" data-dropzone>
            <input type="file" name="resume" id="resume-input" accept=".pdf,.doc,.docx" class="dropzone__input">
            <label for="resume-input" class="dropzone__label">
                <span class="dropzone__icon"><?= icon('upload-cloud', 'icon icon--xl') ?></span>
                <strong>Drag &amp; drop your resume here</strong>
                <span>or click to browse</span>
            </label>
            <p class="dropzone__filename" data-dropzone-filename>
                <?= !empty($draft['resume_file_name']) ? 'Current file: ' . e($draft['resume_file_name']) : '' ?>
            </p>
        </div>

        <div class="form-actions form-actions--wizard">
            <a href="/candidates/register/skills" class="btn btn--ghost">Back</a>
            <button type="submit" class="btn btn--primary btn--lg"><?= !empty($draft['resume_file_name']) ? 'Continue' : 'Continue (skip for now)' ?></button>
        </div>
    </form>
</div>

<script src="<?= asset_url('/assets/js/dropzone.js') ?>" defer></script>
