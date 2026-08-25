<?php $user = $currentUser ?? null; ?>
<header class="admin-topbar">
    <div class="admin-topbar__title"><?= e($title ?? '') ?></div>
    <div class="admin-topbar__user">
        <?= avatar($user['email'] ?? '?', 'sm') ?>
        <span><?= e($user['email'] ?? '') ?></span>
        <form action="/logout" method="post" class="inline-form">
            <?= $csrfField ?>
            <button type="submit" class="btn btn--text"><?= icon('log-out', 'icon icon--sm') ?>Sign Out</button>
        </form>
    </div>
</header>
