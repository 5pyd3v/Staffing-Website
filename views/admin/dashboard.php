<h1 class="page-title">Welcome back<?= $currentUser ? ', ' . e(explode('@', $currentUser['email'])[0]) : '' ?></h1>
<p class="page-subtitle">Here's what's happening across the platform right now.</p>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-card__icon"><?= icon('users') ?></span>
        <div><span>New Candidates</span><strong><?= e($stats['new_candidates']) ?></strong></div>
    </div>
    <div class="stat-card">
        <span class="stat-card__icon stat-card__icon--accent"><?= icon('file-text') ?></span>
        <div><span>Open Staffing Requests</span><strong><?= e($stats['open_requests']) ?></strong></div>
    </div>
    <div class="stat-card">
        <span class="stat-card__icon stat-card__icon--gold"><?= icon('briefcase') ?></span>
        <div><span>Open Jobs</span><strong><?= e($stats['open_jobs']) ?></strong></div>
    </div>
    <div class="stat-card">
        <span class="stat-card__icon stat-card__icon--success"><?= icon('trending-up') ?></span>
        <div><span>Placements (30d)</span><strong><?= e($stats['placements_30d']) ?></strong></div>
    </div>
</div>

<div class="dash-columns">
    <div class="panel">
        <div class="panel__header">
            <h2>Recent Candidates</h2>
            <a href="/admin/candidates" class="btn btn--text">View all</a>
        </div>
        <?php if (empty($recentCandidates)): ?>
            <div class="empty-state"><?= icon('users') ?><p>No candidates yet.</p></div>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($recentCandidates as $c): ?>
                    <li style="display:flex;align-items:center;gap:0.75rem;">
                        <?= avatar($c['first_name'] . ' ' . $c['last_name'], 'sm') ?>
                        <span style="flex:1;">
                            <a href="/admin/candidates/<?= (int) $c['id'] ?>"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></a>
                            <span class="badge badge--<?= e($c['status']) ?>"><?= e(str_replace('_', ' ', $c['status'])) ?></span>
                        </span>
                        <small><?= e(date('M j', strtotime($c['created_at']))) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="panel">
        <div class="panel__header">
            <h2>Recent Employer Activity</h2>
            <a href="/admin/companies" class="btn btn--text">View all</a>
        </div>
        <?php if (empty($recentActivity)): ?>
            <div class="empty-state"><?= icon('building') ?><p>No activity logged yet.</p></div>
        <?php else: ?>
            <ul class="simple-list">
                <?php foreach ($recentActivity as $a): ?>
                    <li>
                        <strong><?= e($a['company_name']) ?></strong> — <?= e($a['subject']) ?>
                        <small><?= e(date('M j', strtotime($a['occurred_at']))) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
