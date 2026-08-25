<div class="panel-header-row">
    <h1 class="page-title">Industries</h1>
    <a href="/admin/industries/create" class="btn btn--primary"><?= icon('plus', 'icon icon--sm') ?>Add Industry</a>
</div>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr><th scope="col">Name</th><th scope="col">Slug</th><th scope="col"></th></tr>
        </thead>
        <tbody>
            <?php if (empty($industries)): ?>
                <tr><td colspan="3" class="empty-state">No industries yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($industries as $industry): ?>
                <tr>
                    <td><?= e($industry['name']) ?></td>
                    <td class="text-muted"><?= e($industry['slug']) ?></td>
                    <td class="text-right">
                        <a href="/admin/industries/<?= (int) $industry['id'] ?>/edit" class="btn btn--text">Edit</a>
                        <form action="/admin/industries/<?= (int) $industry['id'] ?>" method="post" class="inline-form" onsubmit="return confirm('Delete this industry?');">
                            <?= $csrfField ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn--text btn--danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
