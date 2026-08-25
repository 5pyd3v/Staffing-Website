<?php $isEdit = $job !== null; ?>
<h1 class="page-title"><?= $isEdit ? 'Edit Job' : 'Post a Job' ?></h1>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="panel panel--form">
    <form action="<?= $isEdit ? '/admin/jobs/' . (int) $job['id'] : '/admin/jobs' ?>" method="post" class="form">
        <?= $csrfField ?>
        <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

        <label class="form-field">
            <span>Employer</span>
            <select name="company_id" required>
                <option value="">Select employer&hellip;</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= ($isEdit && (int) $job['company_id'] === (int) $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="form-field">
            <span>Job title</span>
            <input type="text" name="title" required value="<?= e($isEdit ? $job['title'] : \App\Core\Session::old('title')) ?>">
        </label>

        <div class="form-grid">
            <label class="form-field">
                <span>Industry</span>
                <select name="industry_id">
                    <option value="">&mdash;</option>
                    <?php foreach ($industries as $i): ?>
                        <option value="<?= (int) $i['id'] ?>" <?= ($isEdit && (int) $job['industry_id'] === (int) $i['id']) ? 'selected' : '' ?>><?= e($i['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span>Service</span>
                <select name="service_id">
                    <option value="">&mdash;</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= ($isEdit && (int) $job['service_id'] === (int) $s['id']) ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="form-grid">
            <label class="form-field">
                <span>Employment type</span>
                <select name="employment_type" required>
                    <?php foreach ($employmentTypes as $t): ?>
                        <option value="<?= e($t) ?>" <?= ($isEdit && $job['employment_type'] === $t) ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $t))) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span>Status</span>
                <select name="status" required>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= e($s) ?>" <?= ($isEdit && $job['status'] === $s) ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="form-grid">
            <label class="form-field">
                <span>City</span>
                <input type="text" name="location_city" value="<?= e($isEdit ? ($job['location_city'] ?? '') : '') ?>">
            </label>
            <label class="form-field">
                <span>State</span>
                <input type="text" name="location_state" value="<?= e($isEdit ? ($job['location_state'] ?? '') : '') ?>">
            </label>
        </div>

        <label class="form-field form-field--checkbox">
            <input type="checkbox" name="is_remote" value="1" <?= ($isEdit && $job['is_remote']) ? 'checked' : '' ?>>
            <span>Remote-friendly</span>
        </label>

        <div class="form-grid">
            <label class="form-field">
                <span>Salary min</span>
                <input type="number" name="salary_min" value="<?= e($isEdit ? ($job['salary_min'] ?? '') : '') ?>">
            </label>
            <label class="form-field">
                <span>Salary max</span>
                <input type="number" name="salary_max" value="<?= e($isEdit ? ($job['salary_max'] ?? '') : '') ?>">
            </label>
            <label class="form-field">
                <span>Period</span>
                <select name="salary_period">
                    <option value="year" <?= (!$isEdit || $job['salary_period'] === 'year') ? 'selected' : '' ?>>Per Year</option>
                    <option value="hour" <?= ($isEdit && $job['salary_period'] === 'hour') ? 'selected' : '' ?>>Per Hour</option>
                </select>
            </label>
            <label class="form-field">
                <span>Positions available</span>
                <input type="number" name="positions_available" min="1" value="<?= e($isEdit ? $job['positions_available'] : 1) ?>">
            </label>
        </div>

        <label class="form-field">
            <span>Description</span>
            <textarea name="description" rows="5" required><?= e($isEdit ? $job['description'] : '') ?></textarea>
        </label>

        <label class="form-field">
            <span>Requirements</span>
            <textarea name="requirements" rows="4"><?= e($isEdit ? ($job['requirements'] ?? '') : '') ?></textarea>
        </label>

        <label class="form-field">
            <span>Benefits</span>
            <textarea name="benefits" rows="3"><?= e($isEdit ? ($job['benefits'] ?? '') : '') ?></textarea>
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Save Changes' : 'Post Job' ?></button>
            <a href="/admin/jobs" class="btn btn--ghost">Cancel</a>
        </div>
    </form>
</div>
