<?php
/** Expects $c (one ranked candidate row from MatchingService) and $req, $savedCandidateIds in scope. */
$isSaved = in_array((int) $c['id'], $savedCandidateIds, true);
?>
<div class="match-card">
    <div class="match-card__header">
        <div class="candidate-card__identity">
            <?= avatar($c['first_name'] . ' ' . $c['last_name'], 'sm') ?>
            <a href="/admin/candidates/<?= (int) $c['id'] ?>"><strong><?= e($c['first_name'] . ' ' . $c['last_name']) ?></strong></a>
        </div>
        <span class="match-score">
            <span class="match-score__value"><?= e($c['score']) ?></span><span class="match-score__max">/100</span>
        </span>
    </div>
    <p class="text-muted"><?= e($c['headline'] ?? $c['current_title'] ?? '') ?></p>
    <p class="candidate-card__meta"><?= icon('map-pin', 'icon icon--sm') ?><?= e(trim(($c['location_city'] ?? '') . ', ' . ($c['location_state'] ?? ''), ', ') ?: 'Location not set') ?> &middot; <?= e($c['experience_years'] ?? '0') ?> yrs</p>

    <div class="match-breakdown">
        <span title="Must-have skills">Skills: <?= (int) $c['breakdown']['must_have_skills'] + (int) $c['breakdown']['nice_to_have_skills'] ?>/55</span>
        <span title="Location fit">Location: <?= (int) $c['breakdown']['location'] ?>/15</span>
        <span title="Availability">Availability: <?= (int) $c['breakdown']['availability'] ?>/10</span>
        <span title="Budget fit">Budget: <?= (int) $c['breakdown']['budget'] ?>/10</span>
    </div>

    <?php if ($isSaved): ?>
        <p class="badge badge--active"><?= icon('check-circle', 'icon icon--sm') ?>Already matched</p>
    <?php else: ?>
        <form action="/admin/staffing-requests/<?= (int) $req['id'] ?>/matches" method="post">
            <?= $csrfField ?>
            <input type="hidden" name="candidate_id" value="<?= (int) $c['id'] ?>">
            <button type="submit" class="btn btn--primary btn--block"><?= icon('sparkle', 'icon icon--sm') ?>Save Match &amp; Notify</button>
        </form>
    <?php endif; ?>
</div>
