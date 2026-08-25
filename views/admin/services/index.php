<div class="panel-header-row">
    <h1 class="page-title">Services</h1>
    <a href="/admin/services/create" class="btn btn--primary"><?= icon('plus', 'icon icon--sm') ?>Add Service</a>
</div>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr><th scope="col">Name</th><th scope="col">Description</th><th scope="col">Active</th><th scope="col"></th></tr>
        </thead>
        <tbody>
            <?php if (empty($services)): ?>
                <tr><td colspan="4" class="empty-state">No services yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= e($service['name']) ?></td>
                    <td class="text-muted"><?= e($service['description'] ?? '') ?></td>
                    <td><?= $service['is_active'] ? '<span class="badge badge--active">Active</span>' : '<span class="badge badge--inactive">Hidden</span>' ?></td>
                    <td class="text-right">
                        <a href="/admin/services/<?= (int) $service['id'] ?>/edit" class="btn btn--text">Edit</a>
                        <form action="/admin/services/<?= (int) $service['id'] ?>" method="post" class="inline-form" onsubmit="return confirm('Delete this service?');">
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
