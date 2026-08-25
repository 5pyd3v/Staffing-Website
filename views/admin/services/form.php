<?php $isEdit = $service !== null; ?>
<h1 class="page-title"><?= $isEdit ? 'Edit Service' : 'Add Service' ?></h1>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="panel panel--form">
    <form action="<?= $isEdit ? '/admin/services/' . (int) $service['id'] : '/admin/services' ?>" method="post" class="form">
        <?= $csrfField ?>
        <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

        <label class="form-field">
            <span>Service name</span>
            <input type="text" name="name" required value="<?= e($isEdit ? $service['name'] : \App\Core\Session::old('name')) ?>">
        </label>

        <label class="form-field">
            <span>Description</span>
            <textarea name="description" rows="3"><?= e($isEdit ? ($service['description'] ?? '') : \App\Core\Session::old('description')) ?></textarea>
        </label>

        <label class="form-field">
            <span>Sort order</span>
            <input type="number" name="sort_order" value="<?= e($isEdit ? $service['sort_order'] : (\App\Core\Session::old('sort_order') ?: 0)) ?>">
        </label>

        <label class="form-field form-field--checkbox">
            <input type="checkbox" name="is_active" value="1" <?= (!$isEdit || $service['is_active']) ? 'checked' : '' ?>>
            <span>Visible on public site</span>
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Save Changes' : 'Add Service' ?></button>
            <a href="/admin/services" class="btn btn--ghost">Cancel</a>
        </div>
    </form>
</div>
