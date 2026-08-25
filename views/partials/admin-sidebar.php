<?php
$path = $_SERVER['REQUEST_URI'] ?? '';
$unreadCount = \App\Models\Notification::countSince('24 HOUR');
$navItems = [
    ['href' => '/admin/dashboard', 'icon' => 'grid', 'label' => 'Dashboard'],
    ['href' => '/admin/candidates', 'icon' => 'users', 'label' => 'Candidates'],
    ['href' => '/admin/companies', 'icon' => 'building', 'label' => 'Employers'],
    ['href' => '/admin/jobs', 'icon' => 'briefcase', 'label' => 'Jobs'],
    ['href' => '/admin/staffing-requests', 'icon' => 'file-text', 'label' => 'Staffing Requests'],
    ['href' => '/admin/matches', 'icon' => 'sparkle', 'label' => 'Matching'],
    ['href' => '/admin/industries', 'icon' => 'award', 'label' => 'Industries'],
    ['href' => '/admin/services', 'icon' => 'star', 'label' => 'Services'],
];
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
        <span class="brand-mark__logo"><?= logo_mark() ?></span>
        TalentBridge<span>Admin</span>
    </div>
    <nav class="admin-sidebar__nav">
        <?php foreach ($navItems as $item): ?>
            <a href="<?= e($item['href']) ?>" class="<?= str_starts_with($path, $item['href']) ? 'is-active' : '' ?>">
                <?= icon($item['icon'], 'icon icon--sm') ?><span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
        <a href="/admin/notifications" class="admin-sidebar__nav-item <?= str_starts_with($path, '/admin/notifications') ? 'is-active' : '' ?>">
            <?= icon('bell', 'icon icon--sm') ?><span>Notifications</span>
            <?php if ($unreadCount > 0): ?><span class="nav-badge"><?= (int) $unreadCount ?></span><?php endif; ?>
        </a>
        <a href="/admin/settings" class="<?= str_starts_with($path, '/admin/settings') ? 'is-active' : '' ?>">
            <?= icon('settings', 'icon icon--sm') ?><span>Settings</span>
        </a>
    </nav>
</aside>
