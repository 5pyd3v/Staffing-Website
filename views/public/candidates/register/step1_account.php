<div class="wizard-form-inner">

    <h1>Let's get you into the talent pool</h1>
    <p class="wizard-card__subtitle">Create your account to start. You can save your progress and finish the rest of your profile at your own pace.</p>

    <?php include VIEW_PATH . '/partials/form-alert.php'; ?>

    <form action="/candidates/register" method="post" class="form" novalidate>
        <?= $csrfField ?>

        <label class="<?= field_class($errors, 'email') ?>">
            <span>Email address</span>
            <span class="input-icon">
                <?= icon('mail', 'icon') ?>
                <input type="email" name="email" required autofocus placeholder="you@example.com" value="<?= e($draft['email'] ?? \App\Core\Session::old('email')) ?>">
            </span>
            <?php if ($msg = field_error($errors, 'email')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
        </label>

        <div class="form-grid">
            <label class="<?= field_class($errors, 'password') ?>">
                <span>Password</span>
                <span class="input-icon">
                    <?= icon('lock', 'icon') ?>
                    <input type="password" name="password" required minlength="8" data-password-input>
                    <button type="button" class="input-icon__toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                        <?= icon('eye', 'icon password-toggle__show') ?>
                        <?= icon('eye-off', 'icon password-toggle__hide') ?>
                    </button>
                </span>
                <?php if ($msg = field_error($errors, 'password')): ?><span class="field-error"><?= e($msg) ?></span><?php endif; ?>
            </label>
            <label class="form-field">
                <span>Confirm password</span>
                <span class="input-icon">
                    <?= icon('lock', 'icon') ?>
                    <input type="password" name="password_confirmation" required minlength="8">
                </span>
            </label>
        </div>

        <div class="form-actions form-actions--wizard">
            <span></span>
            <button type="submit" class="btn btn--primary btn--lg">Continue</button>
        </div>
    </form>

    <p class="auth-card__footer">Already registered? <a href="/login">Sign in</a></p>
</div>

<script src="<?= asset_url('/assets/js/password-toggle.js') ?>" defer></script>
