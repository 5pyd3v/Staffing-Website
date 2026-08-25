(function () {
    'use strict';

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }

    document.querySelectorAll('[data-tag-input]').forEach(function (root) {
        var tagsEl = root.querySelector('.tag-input__tags');
        var fieldEl = root.querySelector('.tag-input__field');
        var hiddenEl = root.querySelector('[data-tag-input-value]');

        var tags = (hiddenEl.value || '')
            .split(',')
            .map(function (t) { return t.trim(); })
            .filter(Boolean);

        function render() {
            tagsEl.innerHTML = tags.map(function (tag, i) {
                return '<span class="tag-chip">' + escapeHtml(tag) +
                    '<button type="button" aria-label="Remove ' + escapeHtml(tag) + '" data-index="' + i + '">&times;</button></span>';
            }).join('');
            hiddenEl.value = tags.join(', ');
        }

        function addTag(raw) {
            var value = raw.trim().replace(/,+$/, '');
            if (value && tags.indexOf(value) === -1) {
                tags.push(value);
                render();
            }
            fieldEl.value = '';
        }

        fieldEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag(fieldEl.value);
            } else if (e.key === 'Backspace' && fieldEl.value === '' && tags.length > 0) {
                tags.pop();
                render();
            }
        });

        fieldEl.addEventListener('blur', function () {
            if (fieldEl.value.trim() !== '') {
                addTag(fieldEl.value);
            }
        });

        tagsEl.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-index]');
            if (!btn) {
                return;
            }
            tags.splice(parseInt(btn.dataset.index, 10), 1);
            render();
        });

        render();
    });
})();
