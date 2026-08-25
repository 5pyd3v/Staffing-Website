<?php $isEdit = $industry !== null; ?>
<h1 class="page-title"><?= $isEdit ? 'Edit Industry' : 'Add Industry' ?></h1>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="panel panel--form">
    <form action="<?= $isEdit ? '/admin/industries/' . (int) $industry['id'] : '/admin/industries' ?>" method="post" class="form">
        <?= $csrfField ?>
        <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

        <label class="form-field">
            <span>Industry name</span>
            <input type="text" name="name" required value="<?= e($isEdit ? $industry['name'] : \App\Core\Session::old('name')) ?>">
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Save Changes' : 'Add Industry' ?></button>
            <a href="/admin/industries" class="btn btn--ghost">Cancel</a>
        </div>
    </form>
</div>
