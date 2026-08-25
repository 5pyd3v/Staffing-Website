(function () {
    'use strict';

    var root = document.getElementById('candidate-search');
    if (!root) {
        return;
    }

    var form = document.getElementById('candidate-filters');
    var resultsEl = document.getElementById('candidate-results');
    var countEl = document.getElementById('candidate-search-count');
    var paginationEl = document.getElementById('candidate-pagination');
    var resetBtn = document.getElementById('candidate-filters-reset');
    var searchUrl = root.dataset.searchUrl;

    var state = { page: 1 };
    var debounceTimer = null;
    var activeController = null;

    var ICONS = {
        'map-pin': '<path d="M12 21s7-6.3 7-11.5A7 7 0 0 0 5 9.5C5 14.7 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
    };

    function iconHtml(name, cls) {
        return '<svg class="' + (cls || 'icon icon--sm') + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + ICONS[name] + '</svg>';
    }

    function avatarHtml(name, size) {
        var parts = (name || '').trim().split(/\s+/).slice(0, 2);
        var initials = parts.map(function (p) { return p.charAt(0).toUpperCase(); }).join('') || '?';
        var hue = 0;
        for (var i = 0; i < name.length; i++) { hue += name.charCodeAt(i); }
        hue = hue % 360;
        return '<span class="avatar avatar--' + (size || 'md') + '" style="--avatar-hue:' + hue + '" aria-hidden="true">' + escapeHtml(initials) + '</span>';
    }

    function statusLabel(status) {
        return status.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function availabilityLabel(value) {
        var map = {
            immediate: 'Available immediately',
            '2_weeks': '2 weeks notice',
            '1_month': '1 month notice',
            not_looking: 'Not actively looking',
        };
        return map[value] || value;
    }

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function renderSkeleton() {
        var html = '';
        for (var i = 0; i < 6; i++) {
            html += '<div class="candidate-card candidate-card--skeleton"></div>';
        }
        resultsEl.innerHTML = html;
    }

    function renderResults(payload) {
        var rows = payload.data || [];
        var meta = payload.meta || {};

        if (rows.length === 0) {
            resultsEl.innerHTML = '<div class="empty-state">' + iconHtml('map-pin', 'icon') + '<p>No candidates match these filters.</p></div>';
        } else {
            resultsEl.innerHTML = rows.map(function (c) {
                var skillsHtml = (c.skills || []).slice(0, 5).map(function (s) {
                    return '<span class="chip">' + escapeHtml(s) + '</span>';
                }).join('');

                return '' +
                    '<a class="candidate-card" href="' + escapeHtml(c.profile_url) + '">' +
                    '  <div class="candidate-card__header">' +
                    '    <div class="candidate-card__identity">' + avatarHtml(c.name, 'sm') + '<strong>' + escapeHtml(c.name) + '</strong></div>' +
                    '    <span class="badge badge--' + escapeHtml(c.status) + '">' + escapeHtml(statusLabel(c.status)) + '</span>' +
                    '  </div>' +
                    '  <p class="candidate-card__title">' + escapeHtml(c.current_title || c.headline || '') + '</p>' +
                    '  <p class="candidate-card__meta">' + iconHtml('map-pin') +
                            escapeHtml(c.location || 'Location not set') +
                            (c.experience_years ? ' &middot; ' + escapeHtml(c.experience_years) + ' yrs exp' : '') +
                    '  </p>' +
                    '  <p class="candidate-card__meta text-muted">' + iconHtml('clock') + escapeHtml(availabilityLabel(c.availability)) + '</p>' +
                    '  <div class="candidate-card__skills">' + skillsHtml + '</div>' +
                    '</a>';
            }).join('');
        }

        countEl.textContent = meta.total + (meta.total === 1 ? ' candidate found' : ' candidates found');
        renderPagination(meta);
    }

    function renderPagination(meta) {
        if (!meta.total_pages || meta.total_pages <= 1) {
            paginationEl.innerHTML = '';
            return;
        }

        var html = '';
        if (meta.page > 1) {
            html += '<button type="button" class="btn btn--ghost" data-page="' + (meta.page - 1) + '">Previous</button>';
        }
        html += '<span class="pagination__status">Page ' + meta.page + ' of ' + meta.total_pages + '</span>';
        if (meta.page < meta.total_pages) {
            html += '<button type="button" class="btn btn--ghost" data-page="' + (meta.page + 1) + '">Next</button>';
        }
        paginationEl.innerHTML = html;
    }

    function runSearch() {
        var params = new URLSearchParams(new FormData(form));
        params.set('page', state.page);

        if (activeController) {
            activeController.abort();
        }
        activeController = new AbortController();

        renderSkeleton();

        fetch(searchUrl + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: activeController.signal,
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Search request failed');
                }
                return response.json();
            })
            .then(renderResults)
            .catch(function (err) {
                if (err.name === 'AbortError') {
                    return;
                }
                resultsEl.innerHTML = '<p class="empty-state">Something went wrong loading results. Please try again.</p>';
            });
    }

    function scheduleSearch(immediate) {
        state.page = 1;
        window.clearTimeout(debounceTimer);
        if (immediate) {
            runSearch();
        } else {
            debounceTimer = window.setTimeout(runSearch, 300);
        }
    }

    form.addEventListener('input', function (e) {
        if (e.target.type === 'search' || e.target.type === 'text' || e.target.type === 'number') {
            scheduleSearch(false);
        }
    });

    form.addEventListener('change', function (e) {
        if (e.target.type === 'checkbox' || e.target.tagName === 'SELECT') {
            scheduleSearch(true);
        }
    });

    resetBtn.addEventListener('click', function () {
        form.reset();
        scheduleSearch(true);
    });

    paginationEl.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-page]');
        if (!btn) {
            return;
        }
        state.page = parseInt(btn.dataset.page, 10);
        runSearch();
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    runSearch();
})();
