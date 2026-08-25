(function () {
    'use strict';

    var targets = document.querySelectorAll('[data-count-to]');
    if (!targets.length) {
        return;
    }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function setFinal(el) {
        var to = Number(el.dataset.countTo);
        var suffix = el.dataset.countSuffix || '';
        el.textContent = to.toLocaleString('en-US') + suffix;
    }

    if (reduceMotion || !('IntersectionObserver' in window)) {
        targets.forEach(setFinal);
        return;
    }

    function animate(el) {
        var to = Number(el.dataset.countTo);
        var suffix = el.dataset.countSuffix || '';
        var duration = 1100;
        var start = null;

        function step(timestamp) {
            if (start === null) {
                start = timestamp;
            }
            var progress = Math.min((timestamp - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var current = Math.round(eased * to);
            el.textContent = current.toLocaleString('en-US') + suffix;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        }

        window.requestAnimationFrame(step);
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animate(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    targets.forEach(function (el) {
        el.textContent = '0';
        observer.observe(el);
    });
})();
