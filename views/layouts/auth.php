<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Sign In') ?> · TalentBridge Partners</title>
<?php include VIEW_PATH . '/partials/head-assets.php'; ?>
</head>
<body class="auth-body">
<main class="auth-split">
    <div class="auth-split__panel">
        <img src="/assets/img/stock/businesswoman.jpg" alt="" class="auth-split__image">
        <div class="auth-split__scrim"></div>
        <div class="auth-split__content">
            <a href="/" class="brand-mark brand-mark--invert">
                <span class="brand-mark__logo"><?= logo_mark() ?></span>
                TalentBridge<span>Partners</span>
            </a>
            <blockquote class="auth-split__quote">
                &ldquo;We've cut our time-to-hire in half since partnering with TalentBridge. Their team feels like an extension of ours, not a vendor.&rdquo;
                <cite><?= avatar('Maria Alvarez', 'sm') ?> Maria Alvarez, VP People</cite>
            </blockquote>
        </div>
    </div>
    <div class="auth-split__form">
        <?= $content ?>
    </div>
</main>
</body>
</html>
