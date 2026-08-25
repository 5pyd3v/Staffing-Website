<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Admin') ?> · TalentBridge Partners</title>
<?php include VIEW_PATH . '/partials/head-assets.php'; ?>
</head>
<body class="admin-body">
<a href="#main-content" class="skip-link">Skip to main content</a>
<div class="admin-shell">
    <?php include VIEW_PATH . '/partials/admin-sidebar.php'; ?>
    <div class="admin-main">
        <?php include VIEW_PATH . '/partials/admin-topbar.php'; ?>
        <section class="admin-content" id="main-content">
            <?= $content ?>
        </section>
    </div>
</div>
</body>
</html>
