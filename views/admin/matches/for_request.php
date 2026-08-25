<div class="panel-header-row">
    <div>
        <h1 class="page-title">Matches for <?= e($req['role_title']) ?></h1>
        <p class="page-subtitle">Ranked automatically from must-have/nice-to-have skills, location, employment type, availability, and budget fit.</p>
    </div>
    <a href="/admin/staffing-requests/<?= (int) $req['id'] ?>" class="btn btn--ghost">Back to Request</a>
</div>
<?php include VIEW_PATH . '/partials/admin-alert.php'; ?>

<?php
$strong = array_filter($ranked, fn($r) => $r['match_type'] === 'strong_match');
$review = array_filter($ranked, fn($r) => $r['match_type'] === 'needs_review');
?>

<h2 class="section-title" style="font-size:1.15rem;">Strong Matches (<?= count($strong) ?>)</h2>
<div class="match-grid">
    <?php if (empty($strong)): ?>
        <p class="empty-state">No strong matches in the current talent pool.</p>
    <?php endif; ?>
    <?php foreach ($strong as $c): ?>
        <?php include VIEW_PATH . '/partials/match-card.php'; ?>
    <?php endforeach; ?>
</div>

<h2 class="section-title" style="font-size:1.15rem;margin-top:2rem;">Needs Review (<?= count($review) ?>)</h2>
<div class="match-grid">
    <?php if (empty($review)): ?>
        <p class="empty-state">No borderline candidates right now.</p>
    <?php endif; ?>
    <?php foreach ($review as $c): ?>
        <?php include VIEW_PATH . '/partials/match-card.php'; ?>
    <?php endforeach; ?>
</div>
