<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'TalentBridge Partners') ?></title>
<?php include VIEW_PATH . '/partials/head-assets.php'; ?>
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>
<?php include VIEW_PATH . '/partials/nav.php'; ?>
<main class="site-main" id="main-content">
    <?= $content ?>
</main>
<?php include VIEW_PATH . '/partials/footer.php'; ?>
</body>
</html>
