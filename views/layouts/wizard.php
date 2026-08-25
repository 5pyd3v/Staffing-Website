<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'TalentBridge Partners') ?></title>
<?php include VIEW_PATH . '/partials/head-assets.php'; ?>
</head>
<body class="wizard-body">
<div class="wizard-split">
    <aside class="wizard-split__panel">
        <img src="<?= e($panelImage ?? '/assets/img/stock/hero-team.jpg') ?>" alt="" class="wizard-split__image">
        <div class="wizard-split__scrim"></div>
        <div class="wizard-split__content">
            <a href="/" class="brand-mark brand-mark--invert">
                <span class="brand-mark__logo"><?= logo_mark() ?></span>
                TalentBridge<span>Partners</span>
            </a>
            <div>
                <?php if (!empty($panelHeadline)): ?>
                    <h2 class="wizard-split__headline"><?= e($panelHeadline) ?></h2>
                <?php endif; ?>
                <?php if (!empty($stepLabels)): ?>
                    <ol class="wizard-split__steps">
                        <?php foreach ($stepLabels as $i => $label): $n = $i + 1; ?>
                            <li class="<?= $n < $step ? 'is-complete' : ($n === $step ? 'is-current' : '') ?>">
                                <span class="wizard-split__step-dot"><?= $n < $step ? icon('check-circle', 'icon icon--sm') : $n ?></span>
                                <?= e($label) ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>
    </aside>
    <main class="wizard-split__form">
        <?php if (!empty($stepLabels)): ?>
            <p class="wizard-split__mobile-progress">Step <?= (int) $step ?> of <?= (int) $totalSteps ?> &middot; <?= e($stepLabels[$step - 1] ?? '') ?></p>
        <?php endif; ?>
        <?= $content ?>
    </main>
</div>
</body>
</html>
