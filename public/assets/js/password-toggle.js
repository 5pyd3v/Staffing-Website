(function () {
    'use strict';

    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        var field = btn.parentElement.querySelector('[data-password-input]');
        if (!field) {
            return;
        }

        btn.addEventListener('click', function () {
            var showing = field.type === 'text';
            field.type = showing ? 'password' : 'text';
            btn.setAttribute('aria-pressed', String(!showing));
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    });
})();
