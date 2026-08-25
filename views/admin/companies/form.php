<?php $isEdit = $company !== null; ?>
<h1 class="page-title"><?= $isEdit ? 'Edit Employer' : 'Add Employer' ?></h1>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="panel panel--form">
    <form action="<?= $isEdit ? '/admin/companies/' . (int) $company['id'] : '/admin/companies' ?>" method="post" class="form">
        <?= $csrfField ?>
        <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

        <label class="form-field">
            <span>Company name</span>
            <input type="text" name="name" required value="<?= e($isEdit ? $company['name'] : \App\Core\Session::old('name')) ?>">
        </label>

        <div class="form-grid">
            <label class="form-field">
                <span>Industry</span>
                <select name="industry_id">
                    <option value="">&mdash;</option>
                    <?php foreach ($industries as $i): ?>
                        <option value="<?= (int) $i['id'] ?>" <?= ($isEdit && (int) $company['industry_id'] === (int) $i['id']) ? 'selected' : '' ?>><?= e($i['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span>Company size</span>
                <select name="size_range">
                    <option value="">&mdash;</option>
                    <?php foreach (['1-10','11-50','51-200','201-500','501-1000','1000+'] as $range): ?>
                        <option value="<?= e($range) ?>" <?= ($isEdit && ($company['size_range'] ?? '') === $range) ? 'selected' : '' ?>><?= e($range) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <label class="form-field">
            <span>Website</span>
            <input type="url" name="website" placeholder="https://" value="<?= e($isEdit ? ($company['website'] ?? '') : \App\Core\Session::old('website')) ?>">
        </label>

        <div class="form-grid">
            <label class="form-field">
                <span>HQ City</span>
                <input type="text" name="headquarters_city" value="<?= e($isEdit ? ($company['headquarters_city'] ?? '') : '') ?>">
            </label>
            <label class="form-field">
                <span>HQ State</span>
                <input type="text" name="headquarters_state" value="<?= e($isEdit ? ($company['headquarters_state'] ?? '') : '') ?>">
            </label>
        </div>

        <label class="form-field">
            <span>Status</span>
            <select name="status">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= e($s) ?>" <?= ($isEdit && $company['status'] === $s) ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="form-field">
            <span>Description</span>
            <textarea name="description" rows="4"><?= e($isEdit ? ($company['description'] ?? '') : '') ?></textarea>
        </label>

        <?php if (!$isEdit): ?>
            <hr class="form-divider">
            <h3>Primary Contact</h3>
            <label class="form-field">
                <span>Contact name</span>
                <input type="text" name="contact_name" required value="<?= e(\App\Core\Session::old('contact_name')) ?>">
            </label>
            <div class="form-grid">
                <label class="form-field">
                    <span>Contact email</span>
                    <input type="email" name="contact_email" required value="<?= e(\App\Core\Session::old('contact_email')) ?>">
                </label>
                <label class="form-field">
                    <span>Contact phone</span>
                    <input type="text" name="contact_phone" value="<?= e(\App\Core\Session::old('contact_phone')) ?>">
                </label>
            </div>
            <label class="form-field">
                <span>Contact title</span>
                <input type="text" name="contact_title">
            </label>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Save Changes' : 'Add Employer' ?></button>
            <a href="/admin/companies" class="btn btn--ghost">Cancel</a>
        </div>
    </form>
</div>
