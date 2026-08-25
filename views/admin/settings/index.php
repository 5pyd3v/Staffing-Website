<h1 class="page-title">Settings</h1>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="panel panel--form">
    <form action="/admin/settings" method="post" class="form">
        <?= $csrfField ?>
        <input type="hidden" name="_method" value="PUT">

        <label class="form-field">
            <span>Site tagline</span>
            <input type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>">
        </label>

        <label class="form-field">
            <span>Support email</span>
            <input type="email" name="support_email" value="<?= e($settings['support_email'] ?? '') ?>">
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Save Settings</button>
        </div>
    </form>
</div>
