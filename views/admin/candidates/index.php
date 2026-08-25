<h1 class="page-title">Candidates</h1>
<p class="page-subtitle">Search the talent pool by keyword, skill, location, experience, and availability.</p>

<div class="panel candidate-search" id="candidate-search" data-search-url="/admin/candidates/search">
    <form class="filter-bar filter-bar--wrap" id="candidate-filters" onsubmit="return false;">
        <input type="search" name="q" placeholder="Search name, title, headline..." autocomplete="off">
        <select name="skill_id">
            <option value="">Any Skill</option>
            <?php foreach ($skills as $skill): ?>
                <option value="<?= (int) $skill['id'] ?>"><?= e($skill['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">Any Status</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= e($s) ?>"><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="availability">
            <option value="">Any Availability</option>
            <option value="immediate">Immediate</option>
            <option value="2_weeks">2 Weeks Notice</option>
            <option value="1_month">1 Month Notice</option>
            <option value="not_looking">Not Looking</option>
        </select>
        <input type="text" name="location" placeholder="City or state">
        <input type="number" name="min_experience" placeholder="Min yrs" min="0" step="0.5" class="input--narrow">
        <input type="number" name="max_experience" placeholder="Max yrs" min="0" step="0.5" class="input--narrow">
        <label class="checkbox-inline">
            <input type="checkbox" name="remote_ok" value="1"> Remote OK
        </label>
        <button type="button" class="btn btn--ghost" id="candidate-filters-reset">Reset</button>
    </form>

    <div class="candidate-search__meta">
        <span id="candidate-search-count" aria-live="polite"></span>
    </div>

    <div class="candidate-grid" id="candidate-results" aria-live="polite"></div>
    <div class="pagination" id="candidate-pagination"></div>
</div>

<script src="<?= asset_url('/assets/js/admin-candidates.js') ?>" defer></script>
