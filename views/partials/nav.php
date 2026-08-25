<?php
use App\Helpers\RoleHelper;
$user = $currentUser ?? null;
$dashboardPath = e(RoleHelper::dashboardPath($user['role_slug'] ?? null));
$currentPath = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
?>
<header class="site-nav">
    <div class="site-nav__inner">
        <a href="/" class="brand-mark">
            <span class="brand-mark__logo"><?= logo_mark() ?></span>
            TalentBridge<span>Partners</span>
        </a>
        <nav class="site-nav__links" aria-label="Primary">
            <a href="/jobs" class="<?= str_starts_with($currentPath, '/jobs') ? 'is-active' : '' ?>"><?= icon('search', 'icon icon--sm') ?>Find Work</a>
            <a href="/hire-talent" class="<?= str_starts_with($currentPath, '/hire-talent') ? 'is-active' : '' ?>"><?= icon('briefcase', 'icon icon--sm') ?>Hire Talent</a>
        </nav>
        <div class="site-nav__actions">
            <?php if ($user): ?>
                <a href="<?= $dashboardPath ?>" class="btn btn--ghost">Dashboard</a>
                <form action="/logout" method="post" class="inline-form">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn--text"><?= icon('log-out', 'icon icon--sm') ?>Sign Out</button>
                </form>
            <?php else: ?>
                <a href="/login" class="btn btn--ghost">Sign In</a>
                <a href="/candidates/register" class="btn btn--gradient">Join Talent Pool</a>
            <?php endif; ?>
        </div>
        <button type="button" class="site-nav__toggle" id="nav-toggle" aria-expanded="false" aria-controls="mobile-nav-panel" aria-label="Open menu">
            <?= icon('menu', 'icon nav-toggle__open') ?>
            <?= icon('x', 'icon nav-toggle__close') ?>
        </button>
    </div>
    <div class="site-nav__mobile-panel" id="mobile-nav-panel" hidden>
        <a href="/jobs"><?= icon('search', 'icon icon--sm') ?>Find Work</a>
        <a href="/hire-talent"><?= icon('briefcase', 'icon icon--sm') ?>Hire Talent</a>
        <hr>
        <?php if ($user): ?>
            <a href="<?= $dashboardPath ?>"><?= icon('grid', 'icon icon--sm') ?>Dashboard</a>
            <form action="/logout" method="post">
                <?= $csrfField ?>
                <button type="submit" class="site-nav__mobile-link-btn"><?= icon('log-out', 'icon icon--sm') ?>Sign Out</button>
            </form>
        <?php else: ?>
            <a href="/login"><?= icon('lock', 'icon icon--sm') ?>Sign In</a>
            <a href="/candidates/register" class="btn btn--gradient btn--block"><?= icon('sparkle', 'icon icon--sm') ?>Join Talent Pool</a>
        <?php endif; ?>
    </div>
</header>

<script src="<?= asset_url('/assets/js/site-nav.js') ?>" defer></script>
