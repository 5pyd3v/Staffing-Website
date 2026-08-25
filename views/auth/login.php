<div class="auth-form-wrap">
    <p class="hero__eyebrow"><?= icon('sparkle', 'icon icon--sm') ?> Welcome back</p>
    <h1>Sign in to your account</h1>
    <p class="auth-card__subtitle">Access your candidate, employer, or admin workspace.</p>

    <?php $errors = $errors ?? []; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="/login" method="post" class="form" novalidate>
        <?= $csrfField ?>

        <label class="<?= field_class($errors, 'email') ?>">
            <span>Email address</span>
            <span class="input-icon">
                <?= icon('mail', 'icon') ?>
                <input type="email" name="email" required autofocus placeholder="you@company.com" value="<?= e(\App\Core\Session::old('email')) ?>" aria-describedby="email-error">
            </span>
            <?php if ($msg = field_error($errors, 'email')): ?><span class="field-error" id="email-error"><?= e($msg) ?></span><?php endif; ?>
        </label>

        <label class="<?= field_class($errors, 'password') ?>">
            <span>Password</span>
            <span class="input-icon">
                <?= icon('lock', 'icon') ?>
                <input type="password" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" aria-describedby="password-error" data-password-input>
                <button type="button" class="input-icon__toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                    <?= icon('eye', 'icon password-toggle__show') ?>
                    <?= icon('eye-off', 'icon password-toggle__hide') ?>
                </button>
            </span>
            <?php if ($msg = field_error($errors, 'password')): ?><span class="field-error" id="password-error"><?= e($msg) ?></span><?php endif; ?>
        </label>

        <button type="submit" class="btn btn--gradient btn--block btn--lg">Sign In</button>
    </form>

    <p class="auth-card__footer">New to TalentBridge? <a href="/candidates/register">Join the talent pool</a> or <a href="/hire-talent">request staffing help</a>.</p>
</div>

<script src="<?= asset_url('/assets/js/password-toggle.js') ?>" defer></script>
