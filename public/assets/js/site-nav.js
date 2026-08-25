(function () {
    'use strict';

    var nav = document.querySelector('.site-nav');
    var toggle = document.getElementById('nav-toggle');
    var panel = document.getElementById('mobile-nav-panel');

    if (nav) {
        var onScroll = function () {
            nav.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    if (!toggle || !panel) {
        return;
    }

    function closeMenu() {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('nav-open');
    }

    function openMenu() {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('nav-open');
    }

    toggle.addEventListener('click', function () {
        if (panel.hidden) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    panel.addEventListener('click', function (e) {
        if (e.target.closest('a, button[type="submit"]')) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) {
            closeMenu();
            toggle.focus();
        }
    });

    // Collapse the mobile panel automatically if the viewport grows back
    // past the breakpoint (e.g. rotating a tablet), so it can't be left
    // open-but-invisible behind the desktop nav.
    window.addEventListener('resize', function () {
        if (window.innerWidth > 900 && !panel.hidden) {
            closeMenu();
        }
    });
})();
