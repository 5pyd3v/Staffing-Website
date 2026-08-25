<?php
/**
 * Lighter alert partial for forms with inline per-field errors (see
 * field_error()/field_class() in app/helpers.php) — only renders top-level
 * $success/$error flashes, not the full $errors list, so messages aren't
 * shown twice.
 */
?>
<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>
